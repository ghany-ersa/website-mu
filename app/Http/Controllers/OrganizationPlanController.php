<?php

namespace App\Http\Controllers;

use App\Enums\PlanChangeRequestStatus;
use App\Models\Organization;
use App\Models\Plan;
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

        $plans = Plan::with(['limits', 'components'])->where('is_active', true)->orderBy('price_monthly')->get();

        return view('organizations.plan.edit', [
            'organization' => $organization,
            'plans' => $plans,
            'usage' => collect($usageKeys)->mapWithKeys(fn (string $key) => [
                $key => $planLimitService->remaining($organization, $key),
            ]),
            'pendingRequest' => $organization->pendingPlanChangeRequest(),
            // Plain array (not a Collection) so it round-trips through @json() as a JS object
            // keyed by plan id, for the confirmation modal to read the selected plan's name/price.
            'plansForConfirm' => $plans->mapWithKeys(fn (Plan $plan) => [
                $plan->id => [
                    'name' => $plan->name,
                    'price' => number_format($plan->price_monthly, 0, ',', '.'),
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
        ]);

        if ((int) $validated['plan_id'] === $organization->plan_id) {
            return redirect()
                ->route('organizations.plan.edit', $organization)
                ->with('warning', 'Paket tersebut sudah menjadi paket aktif Anda.');
        }

        $organization->planChangeRequests()->create([
            'requested_plan_id' => $validated['plan_id'],
            'requested_by_user_id' => Auth::id(),
            'status' => PlanChangeRequestStatus::Pending,
        ]);

        return redirect()
            ->route('organizations.plan.edit', $organization)
            ->with('status', 'Permintaan pergantian paket berhasil dikirim. Menunggu konfirmasi pembayaran oleh admin.');
    }
}
