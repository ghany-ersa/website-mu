<?php

namespace App\Http\Controllers;

use App\Enums\PlanChangeRequestStatus;
use App\Models\Organization;
use App\Models\Plan;
use App\Models\PlanChangeRequest;
use App\Services\PlanLimitService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OrganizationPlanController extends Controller
{
    public function edit(Organization $organization, PlanLimitService $planLimitService): View
    {
        $this->authorize('manageBilling', $organization);

        $usageKeys = ['posts', 'agendas', 'announcements', 'officers', 'programs', 'gallery_photos', 'sections_total'];

        $plans = Plan::with('limits')->where('is_active', true)->orderBy('price_monthly')->get();

        return view('organizations.plan.edit', [
            'organization' => $organization,
            'plans' => $plans,
            // used/limit are the raw, unclamped figures (unlike remaining(), which floors at
            // 0) so the view can tell "at the limit" apart from "N over the limit" — see
            // PlanLimitService::currentCount()/effectiveLimit().
            'usage' => collect($usageKeys)->mapWithKeys(fn (string $key) => [
                $key => [
                    'used' => $planLimitService->currentCount($organization, $key),
                    'limit' => $planLimitService->effectiveLimit($organization, $key),
                    'remaining' => $planLimitService->remaining($organization, $key),
                ],
            ]),
            'pendingRequest' => $organization->pendingPlanChangeRequest(),
            // Plain array (not a Collection) so it round-trips through @json() as a JS object
            // keyed by plan id, for the confirmation modal to read the selected plan's name/price.
            'plansForConfirm' => $plans->mapWithKeys(fn (Plan $plan) => [
                $plan->id => [
                    'name' => $plan->name,
                    'price' => number_format($plan->price_monthly, 0, ',', '.'),
                    'priceRaw' => $plan->price_monthly,
                ],
            ])->all(),
        ]);
    }

    /**
     * Submits a plan change for admin approval — organizations.plan_id is not touched here.
     * Only App\Services\PlanChangeRequestService::approve() (called from the admin review
     * screen) actually flips it, once payment has been confirmed out of band.
     */
    public function store(Request $request, Organization $organization): RedirectResponse
    {
        $this->authorize('manageBilling', $organization);

        if ($organization->pendingPlanChangeRequest()) {
            return redirect()
                ->route('organizations.plan.edit', $organization)
                ->with('warning', 'Anda masih memiliki permintaan pergantian paket yang menunggu persetujuan.');
        }

        $validated = $request->validate([
            'plan_id' => ['required', Rule::exists('plans', 'id')->where('is_active', true)],
            'duration_months' => ['required', 'integer', Rule::in([3, 6, 12])],
        ]);

        // Only block re-requesting the same plan once it's actually paid for — a brand-new
        // organization already has plan_id set (see OrganizationController::store()) but
        // hasn't paid yet (plan_expires_at is still null), so its very first payment request
        // is for the plan it's already "on," and that has to be allowed through.
        if ((int) $validated['plan_id'] === $organization->plan_id && $organization->hasPaidForCurrentPlan()) {
            return redirect()
                ->route('organizations.plan.edit', $organization)
                ->with('warning', 'Paket tersebut sudah menjadi paket aktif Anda.');
        }

        $organization->planChangeRequests()->create([
            'requested_plan_id' => $validated['plan_id'],
            'duration_months' => $validated['duration_months'],
            'requested_by_user_id' => Auth::id(),
            'status' => PlanChangeRequestStatus::Pending,
        ]);

        return redirect()
            ->route('organizations.plan.edit', $organization)
            ->with('status', 'Permintaan pergantian paket berhasil dikirim. Silakan lakukan pembayaran dan konfirmasi di bawah.');
    }

    /**
     * Org owner confirms they've sent the transfer — only flips a status/timestamp so the
     * admin's approval queue can tell "just submitted" apart from "claims to have paid,
     * please verify." No payment gateway is involved; the admin still verifies manually
     * before approving (see PlanChangeRequestService::approve()).
     */
    public function confirmPayment(Organization $organization, PlanChangeRequest $planChangeRequest): RedirectResponse
    {
        $this->authorize('manageBilling', $organization);

        abort_unless($planChangeRequest->organization_id === $organization->id, 404);
        abort_unless($planChangeRequest->status === PlanChangeRequestStatus::Pending, 409, 'Permintaan ini sudah diproses.');

        $planChangeRequest->update([
            'status' => PlanChangeRequestStatus::PaymentConfirmed,
            'payment_confirmed_at' => now(),
        ]);

        return redirect()
            ->route('organizations.plan.edit', $organization)
            ->with('status', 'Konfirmasi pembayaran diterima. Menunggu verifikasi admin.');
    }
}
