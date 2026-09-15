<?php

namespace Tests\Feature;

use App\Enums\DiscountCodeType;
use App\Enums\PlanChangeRequestStatus;
use App\Models\DiscountCode;
use App\Models\Organization;
use App\Models\Plan;
use App\Models\PlanChangeRequest;
use App\Models\User;
use App\Services\PlanChangeRequestService;
use App\Services\PlanLimitService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Subscription lifecycle around the gateway: what a plan change request costs, how a voucher
 * changes that, and what approving one does to the organization's paid-for window and limits.
 *
 * The arithmetic here decides what a tenant is charged and how long they keep what they paid
 * for, so each rule gets pinned to a concrete number rather than only asserting "something
 * changed".
 */
class PlanLifecycleTest extends TestCase
{
    use RefreshDatabase;

    private function plan(string $key, int $monthly, int $discount6 = 0, int $discount12 = 0): Plan
    {
        return Plan::create([
            'key' => $key,
            'name' => ucfirst($key),
            'price_monthly' => $monthly,
            'discount_percent_6' => $discount6,
            'discount_percent_12' => $discount12,
            'is_active' => true,
        ]);
    }

    // ---------------------------------------------------------------- pricing

    public function test_duration_discount_is_applied_per_plan(): void
    {
        $plan = $this->plan('uji-harga', 10_000, discount6: 10, discount12: 20);

        $this->assertSame(30_000, $plan->priceForDuration(3), '3 months is the undiscounted baseline.');
        $this->assertSame(54_000, $plan->priceForDuration(6), '60.000 less 10%.');
        $this->assertSame(96_000, $plan->priceForDuration(12), '120.000 less 20%.');
    }

    public function test_a_percentage_voucher_discounts_the_post_duration_price(): void
    {
        $plan = $this->plan('uji-persen', 10_000, discount6: 10);
        $code = DiscountCode::create([
            'code' => 'HEMAT25',
            'type' => DiscountCodeType::Percent,
            'value' => 25,
            'is_active' => true,
        ]);

        // 25% of 54.000 (the already duration-discounted price), not of the 60.000 list price.
        $this->assertSame(13_500, $code->amountFor($plan->priceForDuration(6)));
    }

    /**
     * A fixed-amount voucher worth more than the plan itself must never produce a negative
     * total - the clamp is what keeps gatewayAmount() from going below zero.
     */
    public function test_a_fixed_voucher_larger_than_the_price_is_clamped(): void
    {
        $plan = $this->plan('uji-nominal', 10_000);
        $code = DiscountCode::create([
            'code' => 'POTONG999',
            'type' => DiscountCodeType::Fixed,
            'value' => 999_000,
            'is_active' => true,
        ]);

        $this->assertSame(30_000, $code->amountFor($plan->priceForDuration(3)));
    }

    public function test_an_inactive_expired_or_exhausted_voucher_is_unusable(): void
    {
        $base = ['type' => DiscountCodeType::Percent, 'value' => 10];

        $inactive = DiscountCode::create($base + ['code' => 'NONAKTIF', 'is_active' => false]);
        $notYet = DiscountCode::create($base + ['code' => 'BELUMMULAI', 'is_active' => true, 'valid_from' => now()->addDay()]);
        $expired = DiscountCode::create($base + ['code' => 'KEDALUWARSA', 'is_active' => true, 'valid_until' => now()->subDay()]);
        $exhausted = DiscountCode::create($base + ['code' => 'HABIS', 'is_active' => true, 'max_uses' => 2, 'used_count' => 2]);
        $usable = DiscountCode::create($base + ['code' => 'AKTIF', 'is_active' => true, 'max_uses' => 2, 'used_count' => 1]);

        $this->assertFalse($inactive->isUsable());
        $this->assertFalse($notYet->isUsable());
        $this->assertFalse($expired->isUsable());
        $this->assertFalse($exhausted->isUsable());
        $this->assertTrue($usable->isUsable());
    }

    // ------------------------------------------------------- request creation

    public function test_creating_a_request_does_not_activate_the_plan(): void
    {
        config(['billing.manual_transfer.only' => true]); // avoid a real Snap call

        $owner = User::factory()->create();
        $organization = Organization::factory()->withOwner($owner)->create();
        $plan = $this->plan('uji-buat', 18_000);
        $planIdBefore = $organization->plan_id;

        $this->actingAs($owner)->post(route('organizations.plan.store', $organization), [
            'plan_id' => $plan->id,
            'duration_months' => 3,
        ])->assertRedirect(route('organizations.plan.edit', $organization));

        $organization->refresh();

        $this->assertSame($planIdBefore, $organization->plan_id, 'plan_id must only change on approval.');
        $this->assertNull($organization->plan_expires_at);
        $this->assertSame(PlanChangeRequestStatus::Pending, $organization->planChangeRequests()->first()->status);
    }

    public function test_an_invalid_voucher_is_rejected_at_submission(): void
    {
        config(['billing.manual_transfer.only' => true]);

        $owner = User::factory()->create();
        $organization = Organization::factory()->withOwner($owner)->create();
        $plan = $this->plan('uji-voucher-invalid', 18_000);

        $this->actingAs($owner)
            ->from(route('organizations.plan.edit', $organization))
            ->post(route('organizations.plan.store', $organization), [
                'plan_id' => $plan->id,
                'duration_months' => 3,
                'discount_code' => 'TIDAK-ADA',
            ])
            ->assertSessionHasErrors('discount_code');

        $this->assertSame(0, PlanChangeRequest::count());
    }

    /**
     * A voucher covering the whole price leaves nothing for Midtrans to charge (Snap rejects a
     * zero gross_amount), so the request is approved directly. Safe because the amount is
     * derived server-side from the plan and the code, never from the request body.
     */
    public function test_a_voucher_covering_the_full_price_activates_without_the_gateway(): void
    {
        $owner = User::factory()->create();
        $organization = Organization::factory()->withOwner($owner)->create();
        $plan = $this->plan('uji-gratis', 18_000);

        DiscountCode::create([
            'code' => 'GRATIS100',
            'type' => DiscountCodeType::Percent,
            'value' => 100,
            'is_active' => true,
        ]);

        $this->actingAs($owner)->post(route('organizations.plan.store', $organization), [
            'plan_id' => $plan->id,
            'duration_months' => 3,
            'discount_code' => 'GRATIS100',
        ])->assertRedirect(route('organizations.plan.edit', $organization));

        $organization->refresh();
        $request = $organization->planChangeRequests()->first();

        $this->assertSame(PlanChangeRequestStatus::Approved, $request->status);
        $this->assertSame(0, $request->gatewayAmount());
        $this->assertSame($plan->id, $organization->plan_id);
        $this->assertNotNull($organization->plan_expires_at);
        $this->assertSame(1, DiscountCode::first()->used_count);
    }

    public function test_a_second_request_is_blocked_while_one_is_pending(): void
    {
        config(['billing.manual_transfer.only' => true]);

        $owner = User::factory()->create();
        $organization = Organization::factory()->withOwner($owner)->create();
        $plan = $this->plan('uji-antre', 18_000);

        $payload = ['plan_id' => $plan->id, 'duration_months' => 3];

        $this->actingAs($owner)->post(route('organizations.plan.store', $organization), $payload);
        $this->actingAs($owner)->post(route('organizations.plan.store', $organization), $payload);

        $this->assertSame(1, PlanChangeRequest::count());
    }

    public function test_a_non_member_cannot_submit_a_plan_change(): void
    {
        $outsider = User::factory()->create();
        $organization = Organization::factory()->withOwner()->create();
        $plan = $this->plan('uji-orang-luar', 18_000);

        $this->actingAs($outsider)->post(route('organizations.plan.store', $organization), [
            'plan_id' => $plan->id,
            'duration_months' => 3,
        ])->assertForbidden();

        $this->assertSame(0, PlanChangeRequest::count());
    }

    // ------------------------------------------------------------- approval

    /**
     * Renewing the same plan before it lapses keeps the time already paid for - a tenant who
     * renews early must not lose the remainder of their current term.
     */
    public function test_renewing_the_same_plan_early_extends_from_the_existing_expiry(): void
    {
        $owner = User::factory()->create();
        $plan = $this->plan('uji-perpanjang', 18_000);
        $organization = Organization::factory()->withOwner($owner)->create([
            'plan_id' => $plan->id,
            'plan_expires_at' => now()->addMonths(2),
        ]);
        $existingExpiry = $organization->plan_expires_at->copy();

        $request = $organization->planChangeRequests()->create([
            'requested_plan_id' => $plan->id,
            'duration_months' => 3,
            'discount_amount' => 0,
            'requested_by_user_id' => $owner->id,
            'status' => PlanChangeRequestStatus::Pending,
        ]);

        app(PlanChangeRequestService::class)->approve($request);

        $this->assertTrue(
            $organization->fresh()->plan_expires_at->equalTo($existingExpiry->addMonths(3)),
            'An early renewal should stack on the remaining term, not restart from today.'
        );
    }

    /**
     * Switching to a *different* plan always restarts from now - inheriting the cheaper plan's
     * remaining term would hand out time that was never paid for at the new rate.
     */
    public function test_switching_plans_restarts_the_term_from_today(): void
    {
        $owner = User::factory()->create();
        $starter = $this->plan('uji-starter', 10_000);
        $pro = $this->plan('uji-pro', 25_000);
        $organization = Organization::factory()->withOwner($owner)->create([
            'plan_id' => $starter->id,
            'plan_expires_at' => now()->addMonths(5),
        ]);

        $request = $organization->planChangeRequests()->create([
            'requested_plan_id' => $pro->id,
            'duration_months' => 3,
            'discount_amount' => 0,
            'requested_by_user_id' => $owner->id,
            'status' => PlanChangeRequestStatus::Pending,
        ]);

        app(PlanChangeRequestService::class)->approve($request);
        $organization->refresh();

        $this->assertSame($pro->id, $organization->plan_id);
        $this->assertTrue(
            $organization->plan_expires_at->lessThan(now()->addMonths(4)),
            'An upgrade must not inherit the old plan\'s remaining months.'
        );
    }

    /**
     * A lapsed renewal counts from today, not from the expiry that's already in the past -
     * otherwise a tenant returning after six months would buy time that had already elapsed.
     */
    public function test_renewing_after_expiry_restarts_from_today(): void
    {
        $owner = User::factory()->create();
        $plan = $this->plan('uji-lewat', 18_000);
        $organization = Organization::factory()->withOwner($owner)->create([
            'plan_id' => $plan->id,
            'plan_expires_at' => now()->subMonths(4),
        ]);

        $request = $organization->planChangeRequests()->create([
            'requested_plan_id' => $plan->id,
            'duration_months' => 3,
            'discount_amount' => 0,
            'requested_by_user_id' => $owner->id,
            'status' => PlanChangeRequestStatus::Pending,
        ]);

        app(PlanChangeRequestService::class)->approve($request);

        $this->assertTrue($organization->fresh()->plan_expires_at->isFuture());
    }

    public function test_approving_an_already_approved_request_is_a_no_op(): void
    {
        $owner = User::factory()->create();
        $plan = $this->plan('uji-idempoten', 18_000);
        $organization = Organization::factory()->withOwner($owner)->create();

        $request = $organization->planChangeRequests()->create([
            'requested_plan_id' => $plan->id,
            'duration_months' => 3,
            'discount_amount' => 0,
            'requested_by_user_id' => $owner->id,
            'status' => PlanChangeRequestStatus::Pending,
        ]);

        $service = app(PlanChangeRequestService::class);
        $service->approve($request);
        $firstExpiry = $organization->fresh()->plan_expires_at;

        $service->approve($request->fresh());

        $this->assertTrue($firstExpiry->equalTo($organization->fresh()->plan_expires_at));
    }

    // ------------------------------------------------------------ limits

    /**
     * The snapshot is what protects a paying tenant from an admin tightening the plan's limits
     * afterwards.
     */
    public function test_a_frozen_limits_snapshot_survives_a_later_change_to_the_plan(): void
    {
        $owner = User::factory()->create();
        $plan = $this->plan('uji-snapshot', 18_000);
        $plan->limits()->create(['key' => 'posts', 'max_count' => 50]);

        $organization = Organization::factory()->withOwner($owner)->create();
        $request = $organization->planChangeRequests()->create([
            'requested_plan_id' => $plan->id,
            'duration_months' => 3,
            'discount_amount' => 0,
            'requested_by_user_id' => $owner->id,
            'status' => PlanChangeRequestStatus::Pending,
        ]);

        app(PlanChangeRequestService::class)->approve($request);

        // Admin later tightens the plan for everyone.
        $plan->limits()->where('key', 'posts')->update(['max_count' => 5]);

        $organization = $organization->fresh();

        $this->assertSame(
            50,
            app(PlanLimitService::class)->effectiveLimit($organization, 'posts'),
            'The org that already paid should keep the limit it paid for.'
        );
    }

    public function test_a_tenant_override_beats_both_the_snapshot_and_the_plan(): void
    {
        $owner = User::factory()->create();
        $plan = $this->plan('uji-override', 18_000);
        $plan->limits()->create(['key' => 'posts', 'max_count' => 50]);

        $organization = Organization::factory()->withOwner($owner)->create();
        $request = $organization->planChangeRequests()->create([
            'requested_plan_id' => $plan->id,
            'duration_months' => 3,
            'discount_amount' => 0,
            'requested_by_user_id' => $owner->id,
            'status' => PlanChangeRequestStatus::Pending,
        ]);
        app(PlanChangeRequestService::class)->approve($request);

        $organization->limitOverrides()->create(['key' => 'posts', 'max_count' => 999]);

        $this->assertSame(999, app(PlanLimitService::class)->effectiveLimit($organization->fresh(), 'posts'));
    }

    public function test_an_unknown_limit_key_is_rejected_rather_than_silently_unlimited(): void
    {
        $organization = Organization::factory()->withOwner()->create();

        $this->expectException(\InvalidArgumentException::class);

        app(PlanLimitService::class)->currentCount($organization, 'tidak-dikenal');
    }

    public function test_remaining_never_reports_a_negative_quota(): void
    {
        $owner = User::factory()->create();
        $plan = $this->plan('uji-sisa', 18_000);
        $plan->limits()->create(['key' => 'posts', 'max_count' => 1]);

        $organization = Organization::factory()->withOwner($owner)->create(['plan_id' => $plan->id]);
        $organization->posts()->createMany([
            ['title' => 'Satu', 'slug' => 'satu', 'content' => 'x'],
            ['title' => 'Dua', 'slug' => 'dua', 'content' => 'x'],
            ['title' => 'Tiga', 'slug' => 'tiga', 'content' => 'x'],
        ]);

        $service = app(PlanLimitService::class);
        $organization = $organization->fresh();

        $this->assertSame(0, $service->remaining($organization, 'posts'), 'remaining() floors at zero...');
        $this->assertSame(3, $service->currentCount($organization, 'posts'), '...while currentCount() stays raw.');
        $this->assertFalse($service->canCreate($organization, 'posts'));
    }
}
