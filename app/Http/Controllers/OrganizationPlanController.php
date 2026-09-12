<?php

namespace App\Http\Controllers;

use App\Enums\PlanChangeRequestStatus;
use App\Models\DiscountCode;
use App\Models\Organization;
use App\Models\Plan;
use App\Models\PlanChangeRequest;
use App\Services\MidtransService;
use App\Services\PlanChangeRequestService;
use App\Services\PlanLimitService;
use Illuminate\Http\JsonResponse;
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
            // 0) so the view can tell "at the limit" apart from "N over the limit" - see
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
            // re-derives a discount percentage in JS - it only ever reads a precomputed total.
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
                    // months - precomputed here so the view never re-derives it from a percentage.
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
     * Submits a plan change and immediately redirects to Midtrans Snap to pay for it —
     * organizations.plan_id is not touched here. Only PlanChangeRequestService::approve()
     * (called from MidtransWebhookController once payment settles, or by an admin retrying it)
     * actually flips it.
     */
    public function store(Request $request, Organization $organization, MidtransService $midtrans, PlanChangeRequestService $planChangeRequestService): RedirectResponse
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

        // Only block re-requesting the same plan once it's actually paid for - a brand-new
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
            $discountCode = $this->findUsableDiscountCode($validated['discount_code']);
            $discountAmount = $discountCode->amountFor($plan->priceForDuration($validated['duration_months']));
        }

        $planChangeRequest = $organization->planChangeRequests()->create([
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

        // A discount that fully covers the plan's price (plus admin fee, if any) leaves nothing
        // to charge - Midtrans' Snap API rejects a zero gross_amount, so approve directly
        // instead of ever creating a Snap transaction. Safe to auto-approve without payment
        // confirmation because the amount owed is derived entirely server-side (DiscountCode::
        // amountFor() clamps the discount to the plan's own price - see PlanChangeRequestService
        // for how discount_amount reaches here), never from user input.
        if ($planChangeRequest->gatewayAmount() === 0) {
            $planChangeRequestService->approve($planChangeRequest, note: 'Disetujui otomatis - dibayar penuh dengan kode diskon.');

            return redirect()
                ->route('organizations.plan.edit', $organization)
                ->with('success', 'Paket berhasil diaktifkan menggunakan kode diskon.');
        }

        // Midtrans disabled (config('billing.manual_transfer.only')) - leave the request Pending
        // and send the tenant back to the plan page, which shows the transfer instructions in
        // place of the Snap button rather than attempting a Snap call that would fail.
        if (config('billing.manual_transfer.only')) {
            return redirect()
                ->route('organizations.plan.edit', $organization)
                ->with('success', 'Permintaan dibuat. Silakan selesaikan pembayaran melalui transfer manual.');
        }

        $redirectUrl = $midtrans->createSnapTransaction($planChangeRequest);

        return redirect()->away($redirectUrl);
    }

    /**
     * Re-opens the Snap payment page for a Pending request that already exists - e.g. the
     * tenant closed the Snap window without paying and came back to the plan page. Creates a
     * fresh Snap transaction under a new order_id each time, since Midtrans requires order_id
     * to be unique forever (a previous transaction may have already expired).
     */
    public function pay(Organization $organization, PlanChangeRequest $planChangeRequest, MidtransService $midtrans, PlanChangeRequestService $planChangeRequestService): RedirectResponse
    {
        $this->authorize('manageBilling', $organization);

        // Midtrans is off - the plan page hides the Snap button in this mode, so reaching here
        // means a stale link or a hand-typed URL.
        abort_if(config('billing.manual_transfer.only'), 404);

        abort_unless($planChangeRequest->organization_id === $organization->id, 404);
        abort_unless($planChangeRequest->status === PlanChangeRequestStatus::Pending, 409, 'Permintaan ini sudah diproses.');

        // See store() - a pre-existing request whose discount already covers the full amount
        // (e.g. from before this guard existed) should never reach Midtrans either.
        if ($planChangeRequest->gatewayAmount() === 0) {
            $planChangeRequestService->approve($planChangeRequest, note: 'Disetujui otomatis - dibayar penuh dengan kode diskon.');

            return redirect()
                ->route('organizations.plan.edit', $organization)
                ->with('success', 'Paket berhasil diaktifkan menggunakan kode diskon.');
        }

        $redirectUrl = $midtrans->createSnapTransaction($planChangeRequest);

        return redirect()->away($redirectUrl);
    }

    /**
     * Records that the tenant says they've paid by manual bank transfer (see
     * config('billing.manual_transfer')) - always available, whether or not Midtrans is the
     * primary option. Deliberately moves the request only to PaymentConfirmed, never Approved:
     * this is an unverified tenant claim, so an admin still has to check the transfer actually
     * landed and approve it from admin/plan-change-requests. PaymentConfirmed already counts as
     * in-flight for Organization::pendingPlanChangeRequest(), so this can't be used to queue up
     * extra requests.
     */
    public function confirmManualPayment(Organization $organization, PlanChangeRequest $planChangeRequest): RedirectResponse
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
            ->with('success', 'Konfirmasi pembayaran terkirim. Paket akan aktif setelah tim kami memverifikasi transfer Anda.');
    }

    /**
     * Live-validates a voucher against the plan/duration currently picked in the form, so the
     * owner sees the real discount before submitting rather than only "verified at submission."
     * Doesn't touch any records - the code is re-validated again in store() as the final guard.
     */
    public function applyDiscount(Request $request, Organization $organization): JsonResponse
    {
        $this->authorize('manageBilling', $organization);

        $validated = $request->validate([
            'discount_code' => ['required', 'string', 'max:50'],
            'plan_id' => ['required', Rule::exists('plans', 'id')->where('is_active', true)],
            'duration_months' => ['required', 'integer', Rule::in([3, 6, 12])],
        ]);

        $discountCode = $this->findUsableDiscountCode($validated['discount_code']);
        $plan = Plan::findOrFail($validated['plan_id']);
        $amount = $discountCode->amountFor($plan->priceForDuration($validated['duration_months']));

        return response()->json([
            'code' => $discountCode->code,
            'type' => $discountCode->type->value,
            'value' => $discountCode->value,
            'amount' => $amount,
        ]);
    }

    /**
     * @throws ValidationException if no active, in-window, under-cap code matches.
     */
    private function findUsableDiscountCode(string $code): DiscountCode
    {
        $discountCode = DiscountCode::whereRaw('lower(code) = ?', [strtolower($code)])->first();

        if (! $discountCode || ! $discountCode->isUsable()) {
            throw ValidationException::withMessages([
                'discount_code' => 'Kode diskon tidak valid atau sudah tidak berlaku.',
            ]);
        }

        return $discountCode;
    }
}
