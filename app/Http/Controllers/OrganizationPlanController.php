<?php

namespace App\Http\Controllers;

use App\Enums\PlanChangeRequestStatus;
use App\Models\DiscountCode;
use App\Models\Organization;
use App\Models\Plan;
use App\Models\PlanChangeRequest;
use App\Services\PlanLimitService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
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
            // keyed by plan id, for the picker/confirmation modal to read the selected plan's
            // name and its price per duration. Prices are computed server-side via
            // Plan::priceForDuration() (which folds in the duration discount) so the view never
            // re-derives a discount percentage in JS — it only ever reads a precomputed total.
            'plansForConfirm' => $plans->mapWithKeys(fn (Plan $plan) => [
                $plan->id => [
                    'name' => $plan->name,
                    'prices' => collect([3, 6, 12])->mapWithKeys(fn (int $months) => [
                        $months => $plan->priceForDuration($months),
                    ]),
                    'discounts' => collect([3, 6, 12])->mapWithKeys(fn (int $months) => [
                        $months => $plan->discountPercentFor($months),
                    ]),
                    // Rupiah saved vs. paying the undiscounted monthly rate for that many
                    // months — precomputed here so the view never re-derives it from a percentage.
                    'savings' => collect([3, 6, 12])->mapWithKeys(fn (int $months) => [
                        $months => ($plan->price_monthly * $months) - $plan->priceForDuration($months),
                    ]),
                ],
            ])->all(),
            // ISO string for the picker to compute "active until" client-side, matching
            // PlanChangeRequestService::approve()'s baseline logic: extend from this date if
            // it's still in the future, otherwise count from today.
            'planExpiresAt' => $organization->plan_expires_at?->toIso8601String(),
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
            'discount_code' => ['nullable', 'string', 'max:50'],
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

        $plan = Plan::findOrFail($validated['plan_id']);
        $discountCode = null;
        $discountAmount = 0;

        if (filled($validated['discount_code'] ?? null)) {
            $discountCode = DiscountCode::whereRaw('lower(code) = ?', [strtolower($validated['discount_code'])])->first();

            if (! $discountCode || ! $discountCode->isUsable()) {
                throw ValidationException::withMessages([
                    'discount_code' => 'Kode diskon tidak valid atau sudah tidak berlaku.',
                ]);
            }

            $discountAmount = $discountCode->amountFor($plan->priceForDuration($validated['duration_months']));
        }

        $organization->planChangeRequests()->create([
            'requested_plan_id' => $validated['plan_id'],
            'duration_months' => $validated['duration_months'],
            'discount_code_id' => $discountCode?->id,
            'discount_amount' => $discountAmount,
            'requested_by_user_id' => Auth::id(),
            'status' => PlanChangeRequestStatus::Pending,
        ]);

        if ($discountCode) {
            $discountCode->increment('used_count');
        }

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
