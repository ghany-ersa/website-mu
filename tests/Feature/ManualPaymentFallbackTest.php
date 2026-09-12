<?php

namespace Tests\Feature;

use App\Enums\PlanChangeRequestStatus;
use App\Models\Organization;
use App\Models\Plan;
use App\Models\PlanChangeRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The manual bank-transfer fallback used when Midtrans is unavailable. The property these tests
 * pin down is that a tenant confirming their own transfer never activates a plan on its own -
 * it only records the claim for an admin to verify (see
 * OrganizationPlanController::confirmManualPayment()).
 */
class ManualPaymentFallbackTest extends TestCase
{
    use RefreshDatabase;

    private function pendingRequestFor(Organization $organization, User $owner): PlanChangeRequest
    {
        $plan = Plan::create([
            'key' => 'organisasi-test',
            'name' => 'Organisasi',
            'price_monthly' => 18000,
            'is_active' => true,
        ]);

        return $organization->planChangeRequests()->create([
            'requested_plan_id' => $plan->id,
            'duration_months' => 3,
            'discount_amount' => 0,
            'requested_by_user_id' => $owner->id,
            'status' => PlanChangeRequestStatus::Pending,
        ]);
    }

    public function test_owner_confirming_transfer_does_not_activate_the_plan(): void
    {
        $owner = User::factory()->create();
        $organization = Organization::factory()->withOwner($owner)->create();
        $request = $this->pendingRequestFor($organization, $owner);

        $this->actingAs($owner)
            ->post(route('organizations.plan.confirm-manual-payment', [$organization, $request]))
            ->assertRedirect(route('organizations.plan.edit', $organization));

        $request->refresh();
        $organization->refresh();

        $this->assertSame(PlanChangeRequestStatus::PaymentConfirmed, $request->status);
        $this->assertNotNull($request->payment_confirmed_at);
        // The whole point of the fallback: the claim is recorded, but the plan is untouched
        // until an admin verifies the transfer.
        $this->assertNotSame($request->requested_plan_id, $organization->plan_id);
        $this->assertNull($organization->plan_expires_at);
    }

    public function test_non_owner_cannot_confirm_a_transfer(): void
    {
        $owner = User::factory()->create();
        $outsider = User::factory()->create();
        $organization = Organization::factory()->withOwner($owner)->create();
        $request = $this->pendingRequestFor($organization, $owner);

        $this->actingAs($outsider)
            ->post(route('organizations.plan.confirm-manual-payment', [$organization, $request]))
            ->assertForbidden();

        $this->assertSame(PlanChangeRequestStatus::Pending, $request->fresh()->status);
    }

    public function test_confirming_twice_is_rejected(): void
    {
        $owner = User::factory()->create();
        $organization = Organization::factory()->withOwner($owner)->create();
        $request = $this->pendingRequestFor($organization, $owner);

        $this->actingAs($owner)->post(route('organizations.plan.confirm-manual-payment', [$organization, $request]));

        $this->actingAs($owner)
            ->post(route('organizations.plan.confirm-manual-payment', [$organization, $request]))
            ->assertStatus(409);
    }

    public function test_admin_approving_a_confirmed_transfer_activates_the_plan(): void
    {
        $owner = User::factory()->create();
        $admin = User::factory()->admin()->create();
        $organization = Organization::factory()->withOwner($owner)->create();
        $request = $this->pendingRequestFor($organization, $owner);

        $this->actingAs($owner)->post(route('organizations.plan.confirm-manual-payment', [$organization, $request]));

        $this->actingAs($admin)
            ->post(route('admin.plan-change-requests.approve-manual', $request))
            ->assertRedirect(route('admin.plan-change-requests.index'));

        $request->refresh();
        $organization->refresh();

        $this->assertSame(PlanChangeRequestStatus::Approved, $request->status);
        $this->assertSame($request->requested_plan_id, $organization->plan_id);
        $this->assertNotNull($organization->plan_expires_at);
    }

    /**
     * With config('billing.manual_transfer.only') set, no Snap transaction is created at all -
     * store() leaves the request Pending and returns to the plan page (which renders the
     * transfer instructions) rather than redirecting away to Midtrans.
     */
    public function test_manual_only_mode_skips_midtrans_entirely(): void
    {
        config(['billing.manual_transfer.only' => true]);

        $owner = User::factory()->create();
        $organization = Organization::factory()->withOwner($owner)->create();
        $plan = Plan::create([
            'key' => 'organisasi-test',
            'name' => 'Organisasi',
            'price_monthly' => 18000,
            'is_active' => true,
        ]);

        $this->actingAs($owner)
            ->post(route('organizations.plan.store', $organization), [
                'plan_id' => $plan->id,
                'duration_months' => 3,
            ])
            ->assertRedirect(route('organizations.plan.edit', $organization));

        $request = $organization->planChangeRequests()->firstOrFail();
        $this->assertSame(PlanChangeRequestStatus::Pending, $request->status);
        // Never handed to Midtrans, so no order id was ever assigned.
        $this->assertNull($request->midtrans_order_id);
    }

    public function test_snap_route_is_unavailable_in_manual_only_mode(): void
    {
        $owner = User::factory()->create();
        $organization = Organization::factory()->withOwner($owner)->create();
        $request = $this->pendingRequestFor($organization, $owner);

        config(['billing.manual_transfer.only' => true]);

        $this->actingAs($owner)
            ->get(route('organizations.plan.pay', [$organization, $request]))
            ->assertNotFound();
    }

    public function test_admin_cannot_approve_a_request_the_tenant_never_confirmed(): void
    {
        $owner = User::factory()->create();
        $admin = User::factory()->admin()->create();
        $organization = Organization::factory()->withOwner($owner)->create();
        $request = $this->pendingRequestFor($organization, $owner);

        $this->actingAs($admin)
            ->post(route('admin.plan-change-requests.approve-manual', $request))
            ->assertStatus(409);

        $this->assertSame(PlanChangeRequestStatus::Pending, $request->fresh()->status);
    }
}
