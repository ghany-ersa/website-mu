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
 * A tenant cancelling their own plan change request (OrganizationPlanController::cancel()) so
 * they can pick a different plan/duration instead of being stuck waiting on the one they
 * submitted. Deliberately narrower than an admin's reject(): only reachable from Pending, never
 * from PaymentConfirmed - once a manual transfer is claimed, only an admin can resolve it (see
 * cancel()'s own doc comment for why).
 */
class CancelPlanChangeRequestTest extends TestCase
{
    use RefreshDatabase;

    private function pendingRequestFor(Organization $organization, User $owner): PlanChangeRequest
    {
        $plan = Plan::create([
            'key' => 'plan-uji-cancel',
            'name' => 'Plan Uji',
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

    public function test_owner_can_cancel_a_pending_request(): void
    {
        $owner = User::factory()->create();
        $organization = Organization::factory()->withOwner($owner)->create();
        $request = $this->pendingRequestFor($organization, $owner);

        $this->actingAs($owner)
            ->delete(route('organizations.plan.cancel', [$organization, $request]))
            ->assertRedirect(route('organizations.plan.edit', $organization));

        $request->refresh();
        $this->assertSame(PlanChangeRequestStatus::Rejected, $request->status);
        $this->assertSame($owner->id, $request->reviewed_by_user_id);
        $this->assertNotNull($request->reviewed_at);
    }

    /**
     * The whole point of the feature: once the request is gone, pendingPlanChangeRequest() no
     * longer blocks a fresh one, so the plan page's picker form renders again instead of staying
     * stuck on the "Selesaikan Pembayaran" panel.
     */
    public function test_after_cancelling_the_owner_can_pick_a_different_plan(): void
    {
        $owner = User::factory()->create();
        $organization = Organization::factory()->withOwner($owner)->create();
        $request = $this->pendingRequestFor($organization, $owner);

        $this->actingAs($owner)->delete(route('organizations.plan.cancel', [$organization, $request]));

        $this->assertNull($organization->fresh()->pendingPlanChangeRequest());

        $planPage = $this->actingAs($owner)->get(route('organizations.plan.edit', $organization));
        $planPage->assertOk();
        $planPage->assertSee('Permintaan dibatalkan. Silakan pilih paket Infak lain.');
        // The picker form (id="pricing-cards"), not the "Selesaikan Pembayaran" panel.
        $planPage->assertSee('id="pricing-cards"', false);
    }

    /**
     * Once a manual transfer is claimed, the tenant can no longer back out on their own - an
     * admin has to verify whether the money actually landed first (see cancel()'s doc comment).
     */
    public function test_a_payment_confirmed_request_cannot_be_self_cancelled(): void
    {
        $owner = User::factory()->create();
        $organization = Organization::factory()->withOwner($owner)->create();
        $request = $this->pendingRequestFor($organization, $owner);
        $request->update(['status' => PlanChangeRequestStatus::PaymentConfirmed, 'payment_confirmed_at' => now()]);

        $this->actingAs($owner)
            ->delete(route('organizations.plan.cancel', [$organization, $request]))
            ->assertStatus(409);

        $this->assertSame(PlanChangeRequestStatus::PaymentConfirmed, $request->fresh()->status);
    }

    public function test_a_non_member_cannot_cancel_another_organizations_request(): void
    {
        $owner = User::factory()->create();
        $outsider = User::factory()->create();
        $organization = Organization::factory()->withOwner($owner)->create();
        $request = $this->pendingRequestFor($organization, $owner);

        $this->actingAs($outsider)
            ->delete(route('organizations.plan.cancel', [$organization, $request]))
            ->assertForbidden();

        $this->assertSame(PlanChangeRequestStatus::Pending, $request->fresh()->status);
    }

    public function test_a_request_belonging_to_another_organization_404s(): void
    {
        $owner = User::factory()->create();
        $organization = Organization::factory()->withOwner($owner)->create();
        $otherOrganization = Organization::factory()->withOwner($owner)->create();
        $request = $this->pendingRequestFor($otherOrganization, $owner);

        $this->actingAs($owner)
            ->delete(route('organizations.plan.cancel', [$organization, $request]))
            ->assertNotFound();
    }
}
