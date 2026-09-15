<?php

namespace Tests\Feature;

use App\Enums\PlanChangeRequestStatus;
use App\Models\Organization;
use App\Models\Plan;
use App\Models\PlanChangeRequest;
use App\Models\User;
use App\Services\MidtransService;
use App\Services\PlanChangeRequestService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The money path. MidtransWebhookController is the only thing that turns a payment into an
 * active plan, it's reachable without a session (Midtrans is the caller, not a logged-in
 * tenant), and Midtrans retries notifications - so the properties worth pinning down are:
 * a forged payload changes nothing, a mismatched amount changes nothing, and a settlement
 * replayed twice doesn't extend the plan twice.
 *
 * MidtransService is swapped for a stub rather than hitting Midtrans: fetchStatus() is a
 * network call, and the point of these tests is the controller's branching on the status it
 * gets back, not the SDK's HTTP layer.
 */
class MidtransWebhookTest extends TestCase
{
    use RefreshDatabase;

    private const SERVER_KEY = 'test-server-key';

    protected function setUp(): void
    {
        parent::setUp();

        config(['billing.midtrans.server_key' => self::SERVER_KEY]);
    }

    private function pendingRequest(int $durationMonths = 3, int $discount = 0): PlanChangeRequest
    {
        $owner = User::factory()->create();
        $organization = Organization::factory()->withOwner($owner)->create();

        $plan = Plan::create([
            'key' => 'profesional-test',
            'name' => 'Profesional',
            'price_monthly' => 25000,
            'is_active' => true,
        ]);

        return $organization->planChangeRequests()->create([
            'requested_plan_id' => $plan->id,
            'duration_months' => $durationMonths,
            'discount_amount' => $discount,
            'requested_by_user_id' => $owner->id,
            'status' => PlanChangeRequestStatus::Pending,
            'midtrans_order_id' => 'pcr-test-order',
        ]);
    }

    /**
     * Builds the payload Midtrans would POST, with a signature computed the same way
     * MidtransService::verifySignature() recomputes it.
     */
    private function payload(string $orderId, string $grossAmount, string $statusCode = '200'): array
    {
        return [
            'order_id' => $orderId,
            'status_code' => $statusCode,
            'gross_amount' => $grossAmount,
            'signature_key' => hash('sha512', $orderId.$statusCode.$grossAmount.self::SERVER_KEY),
        ];
    }

    /**
     * Stubs the two MidtransService methods the controller uses. verifySignature() keeps its
     * real implementation (these tests sign payloads for real); only the network fetch is faked.
     */
    private function fakeStatus(string $transactionStatus, int $grossAmount, string $orderId = 'pcr-test-order'): void
    {
        $this->instance(MidtransService::class, new class($transactionStatus, $grossAmount, $orderId) extends MidtransService
        {
            public function __construct(private string $status, private int $amount, private string $orderId)
            {
                // Deliberately not calling parent::__construct() - it only populates the SDK's
                // static Config, which a stub never reaches.
            }

            public function fetchStatus(string $orderId): object
            {
                return (object) [
                    'transaction_id' => 'trx-123',
                    'payment_type' => 'bank_transfer',
                    'transaction_status' => $this->status,
                    'gross_amount' => (string) $this->amount,
                    'order_id' => $this->orderId,
                ];
            }
        });
    }

    public function test_a_forged_signature_is_rejected_and_changes_nothing(): void
    {
        $request = $this->pendingRequest();

        $this->postJson(route('webhooks.midtrans'), [
            'order_id' => $request->midtrans_order_id,
            'status_code' => '200',
            'gross_amount' => (string) $request->gatewayAmount(),
            'signature_key' => 'obviously-not-the-real-signature',
        ])->assertForbidden();

        $this->assertSame(PlanChangeRequestStatus::Pending, $request->fresh()->status);
        $this->assertNull($request->organization->fresh()->plan_expires_at);
    }

    public function test_a_payload_missing_signature_fields_is_rejected(): void
    {
        $this->postJson(route('webhooks.midtrans'), ['order_id' => 'pcr-test-order'])
            ->assertForbidden();
    }

    public function test_an_unknown_order_id_returns_404(): void
    {
        $this->pendingRequest();

        $this->postJson(route('webhooks.midtrans'), $this->payload('pcr-does-not-exist', '75000'))
            ->assertNotFound();
    }

    /**
     * The controller re-fetches the real status from Midtrans rather than trusting the payload,
     * and refuses to settle when the authoritative gross_amount isn't what this request owes -
     * otherwise a validly-signed notification for a cheaper transaction could activate an
     * expensive plan.
     */
    public function test_a_gross_amount_mismatch_is_not_processed(): void
    {
        $request = $this->pendingRequest();
        $this->fakeStatus('settlement', $request->gatewayAmount() - 1000);

        $this->postJson(route('webhooks.midtrans'), $this->payload($request->midtrans_order_id, '1000'))
            ->assertOk()
            ->assertJson(['message' => 'amount mismatch, not processed']);

        $request->refresh();

        $this->assertSame(PlanChangeRequestStatus::Pending, $request->status);
        $this->assertNull($request->organization->fresh()->plan_expires_at);
    }

    public function test_a_settlement_activates_the_plan_and_freezes_its_limits(): void
    {
        $request = $this->pendingRequest(durationMonths: 3);
        $amount = $request->gatewayAmount();
        $this->fakeStatus('settlement', $amount);

        $this->postJson(route('webhooks.midtrans'), $this->payload($request->midtrans_order_id, (string) $amount))
            ->assertOk()
            ->assertJson(['message' => 'ok']);

        $request->refresh();
        $organization = $request->organization->fresh();

        $this->assertSame(PlanChangeRequestStatus::Approved, $request->status);
        $this->assertSame($request->requested_plan_id, $organization->plan_id);
        $this->assertNotNull($organization->plan_expires_at);
        $this->assertNotNull($request->midtrans_paid_at);
        $this->assertSame('trx-123', $request->midtrans_transaction_id);
        // Frozen at approval so a later edit to the plan's limits can't squeeze an org that
        // already paid - see PlanChangeRequestService::approve().
        $this->assertIsArray($request->limits_snapshot);
        // No admin was involved in a webhook-driven approval.
        $this->assertNull($request->reviewed_by_user_id);
    }

    public function test_a_capture_settles_the_same_way_as_a_settlement(): void
    {
        $request = $this->pendingRequest();
        $amount = $request->gatewayAmount();
        $this->fakeStatus('capture', $amount);

        $this->postJson(route('webhooks.midtrans'), $this->payload($request->midtrans_order_id, (string) $amount))
            ->assertOk();

        $this->assertSame(PlanChangeRequestStatus::Approved, $request->fresh()->status);
    }

    /**
     * Midtrans retries notifications with backoff, so the same settlement arrives more than
     * once - the second one must not extend plan_expires_at a second time.
     */
    public function test_a_replayed_settlement_does_not_extend_the_plan_twice(): void
    {
        $request = $this->pendingRequest(durationMonths: 3);
        $amount = $request->gatewayAmount();
        $this->fakeStatus('settlement', $amount);
        $payload = $this->payload($request->midtrans_order_id, (string) $amount);

        $this->postJson(route('webhooks.midtrans'), $payload)->assertOk();
        $firstExpiry = $request->organization->fresh()->plan_expires_at;

        $this->postJson(route('webhooks.midtrans'), $payload)->assertOk();
        $secondExpiry = $request->organization->fresh()->plan_expires_at;

        $this->assertTrue($firstExpiry->equalTo($secondExpiry), 'A duplicate notification extended the plan again.');
    }

    public function test_an_expired_transaction_marks_the_request_expired(): void
    {
        $request = $this->pendingRequest();
        $amount = $request->gatewayAmount();
        $this->fakeStatus('expire', $amount);

        $this->postJson(route('webhooks.midtrans'), $this->payload($request->midtrans_order_id, (string) $amount))
            ->assertOk();

        $this->assertSame(PlanChangeRequestStatus::Expired, $request->fresh()->status);
        $this->assertNull($request->organization->fresh()->plan_expires_at);
    }

    public function test_a_cancelled_transaction_marks_the_request_expired(): void
    {
        $request = $this->pendingRequest();
        $amount = $request->gatewayAmount();
        $this->fakeStatus('cancel', $amount);

        $this->postJson(route('webhooks.midtrans'), $this->payload($request->midtrans_order_id, (string) $amount))
            ->assertOk();

        $this->assertSame(PlanChangeRequestStatus::Expired, $request->fresh()->status);
    }

    public function test_a_denied_transaction_marks_the_request_rejected(): void
    {
        $request = $this->pendingRequest();
        $amount = $request->gatewayAmount();
        $this->fakeStatus('deny', $amount);

        $this->postJson(route('webhooks.midtrans'), $this->payload($request->midtrans_order_id, (string) $amount))
            ->assertOk();

        $this->assertSame(PlanChangeRequestStatus::Rejected, $request->fresh()->status);
    }

    /**
     * A "pending" notification (e.g. a VA number was issued but not paid) records the gateway
     * status but must not activate anything.
     */
    public function test_a_pending_transaction_records_status_without_activating(): void
    {
        $request = $this->pendingRequest();
        $amount = $request->gatewayAmount();
        $this->fakeStatus('pending', $amount);

        $this->postJson(route('webhooks.midtrans'), $this->payload($request->midtrans_order_id, (string) $amount))
            ->assertOk();

        $request->refresh();

        $this->assertSame(PlanChangeRequestStatus::Pending, $request->status);
        $this->assertSame('pending', $request->midtrans_status);
        $this->assertNull($request->organization->fresh()->plan_expires_at);
    }

    /**
     * Money landed but activation threw - the request must end up flagged for an admin rather
     * than silently swallowing a paid-for plan.
     */
    public function test_a_failed_approval_after_settlement_is_flagged_for_review(): void
    {
        $request = $this->pendingRequest();
        $amount = $request->gatewayAmount();
        $this->fakeStatus('settlement', $amount);

        $this->instance(PlanChangeRequestService::class, new class extends PlanChangeRequestService
        {
            public function approve(PlanChangeRequest $request, ?User $admin = null, ?string $note = null): void
            {
                throw new \RuntimeException('simulated approval failure');
            }
        });

        $this->postJson(route('webhooks.midtrans'), $this->payload($request->midtrans_order_id, (string) $amount))
            ->assertOk();

        $request->refresh();

        $this->assertSame(PlanChangeRequestStatus::PaymentReceivedNeedsReview, $request->status);
        $this->assertStringContainsString('simulated approval failure', $request->approve_error);
        $this->assertSame(1, $request->approve_attempts);
        $this->assertNotNull($request->midtrans_paid_at);
    }

    /**
     * The webhook is deliberately outside the `web` middleware group - a CSRF token check
     * would reject every real Midtrans call, since Midtrans has no session.
     */
    public function test_the_webhook_requires_no_csrf_token_or_session(): void
    {
        $request = $this->pendingRequest();
        $amount = $request->gatewayAmount();
        $this->fakeStatus('settlement', $amount);

        // No actingAs(), no session started, no CSRF token supplied.
        $this->postJson(route('webhooks.midtrans'), $this->payload($request->midtrans_order_id, (string) $amount))
            ->assertOk();
    }
}
