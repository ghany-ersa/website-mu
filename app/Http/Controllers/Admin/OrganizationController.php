<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrganizationRole;
use App\Enums\PlanOverrideAction;
use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\OrganizationType;
use App\Models\Plan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class OrganizationController extends Controller
{
    /**
     * Display a listing of all registered organizations as a searchable, filterable,
     * paginated table.
     */
    public function index(): View
    {
        $search = trim((string) request('q'));
        $typeId = request('organization_type_id');

        $organizations = Organization::query()
            ->with(['organizationType', 'members' => function ($query) {
                $query->wherePivot('role', OrganizationRole::Owner->value);
            }])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%")
                        ->orWhereHas('members', function ($query) use ($search) {
                            $query->where('users.name', 'like', "%{$search}%")
                                ->orWhere('users.email', 'like', "%{$search}%");
                        });
                });
            })
            ->when($typeId, fn ($query) => $query->where('organization_type_id', $typeId))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.organizations.index', [
            'organizations' => $organizations,
            'organizationTypes' => OrganizationType::orderBy('name')->get(),
        ]);
    }

    /**
     * Admin-only detail page: organization info plus a manual plan-override panel and its
     * audit trail. Distinct from the tenant-facing OrganizationController::show(), which is
     * scoped to members and doesn't expose plan overrides.
     */
    public function show(Organization $organization): View
    {
        $organization->load(['organizationType', 'plan', 'planOverrideLogs.admin', 'planOverrideLogs.fromPlan', 'planOverrideLogs.toPlan']);

        return view('admin.organizations.show', [
            'organization' => $organization,
            'plans' => Plan::where('is_active', true)->orderBy('price_monthly')->get(),
        ]);
    }

    /**
     * Directly sets an organization's plan/expiry, bypassing PlanChangeRequest entirely — for
     * cases a normal payment flow can't cover (complaints, manual arrangements, data fixes).
     * Every use is logged to plan_override_logs since it silently overrides what the org paid
     * (or didn't pay) for.
     */
    public function overridePlan(Request $request, Organization $organization): RedirectResponse
    {
        $validated = $request->validate([
            'plan_id' => ['required', 'exists:plans,id'],
            'plan_expires_at' => ['required', 'date'],
            'note' => ['required', 'string', 'max:1000'],
        ]);

        $fromPlanId = $organization->plan_id;
        $fromExpiresAt = $organization->plan_expires_at;

        $organization->update([
            'plan_id' => $validated['plan_id'],
            'plan_expires_at' => $validated['plan_expires_at'],
        ]);

        $organization->planOverrideLogs()->create([
            'admin_user_id' => Auth::id(),
            'action' => PlanOverrideAction::OverridePlan,
            'from_plan_id' => $fromPlanId,
            'to_plan_id' => $validated['plan_id'],
            'from_expires_at' => $fromExpiresAt,
            'to_expires_at' => $validated['plan_expires_at'],
            'note' => $validated['note'],
        ]);

        return redirect()
            ->route('admin.organizations.show', $organization)
            ->with('status', 'Paket organisasi berhasil diubah secara manual.');
    }

    /**
     * Permanently deletes an organization from the admin panel. Reuses the same `delete`
     * policy ability as the tenant-facing OrganizationController::destroy() (Owner-only by
     * role, but every admin passes it via the Gate::before bypass in AppServiceProvider) — this
     * just gives admins a way to do it from /admin without having to navigate to the tenant
     * page, which previously had no delete UI at all.
     */
    public function destroy(Organization $organization): RedirectResponse
    {
        $this->authorize('delete', $organization);

        $organization->delete();

        return redirect()
            ->route('admin.organizations.index')
            ->with('status', 'Organisasi berhasil dihapus.');
    }
}
