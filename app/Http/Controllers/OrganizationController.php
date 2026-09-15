<?php

namespace App\Http\Controllers;

use App\Enums\OrganizationRole;
use App\Enums\OrganizationStatus;
use App\Http\Requests\StoreOrganizationRequest;
use App\Models\Organization;
use App\Models\Template;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class OrganizationController extends Controller
{
    /**
     * Display the organizations the current user is a member of.
     */
    public function index(): View
    {
        $organizations = Auth::user()->organizations()->excludingSandbox()->with('organizationType')->get();

        return view('organizations.index', ['organizations' => $organizations]);
    }

    /**
     * Step 1 of creating an organization: pick a template.
     *
     * This replaced an "organization type" dropdown on the create form. That field's only real
     * job was guessing a template for the user, which meant answering an abstract question to
     * settle a decision they never saw; the organization's type is now derived from the template
     * instead (see StoreOrganizationRequest::prepareForValidation()).
     *
     * Exclusive templates are listed but rendered locked rather than filtered out. Every new
     * organization starts on Starter (see store()), so none of them is selectable here and the
     * gating is static - unlike OrganizationTemplateController::edit(), which checks the existing
     * organization's plan. Showing them locked is deliberate: hiding them made "Gunakan Template"
     * on an exclusive catalog card land on a form with nothing pre-selected and no explanation.
     *
     * Templates with no organization_type_id are excluded outright: organizations.organization_type_id
     * is NOT NULL, so one of these could never produce a valid organization (see
     * StoreOrganizationRequest::typelessTemplateIds()).
     */
    public function createTemplate(): View
    {
        $this->authorize('create', Organization::class);

        return view('organizations.create-template', [
            'templates' => Template::query()
                ->with('organizationType')
                ->where('is_active', true)
                ->whereNotNull('organization_type_id')
                ->orderBy('is_exclusive')
                ->orderBy('name')
                ->get(),
        ]);
    }

    /**
     * Step 2 of creating an organization: the identity form (name/slug/region/description).
     *
     * Requires ?template=slug, set either by step 1 above or by TemplateUseController's "Gunakan
     * Template" flow (which skips step 1 - a user who already picked a template in the public
     * catalog shouldn't be asked to pick again). Anything unusable - missing, unknown, inactive,
     * exclusive, or typeless - sends the user back to step 1 rather than rendering a form whose
     * submission is guaranteed to fail validation.
     */
    public function create(Request $request): View|RedirectResponse
    {
        $this->authorize('create', Organization::class);

        $selectedTemplate = $request->filled('template')
            ? Template::where('slug', $request->query('template'))
                ->where('is_active', true)
                ->where('is_exclusive', false)
                ->whereNotNull('organization_type_id')
                ->first()
            : null;

        if (! $selectedTemplate) {
            return redirect()
                ->route('organizations.template-picker')
                ->with('error', $request->filled('template')
                    ? 'Template tersebut tidak tersedia. Silakan pilih template lain.'
                    : null);
        }

        return view('organizations.create', [
            'selectedTemplate' => $selectedTemplate,
            'hasSeenCreateTour' => Auth::user()->hasSeenOnboardingTour('create'),
        ]);
    }

    /**
     * Store a newly created organization and attach the creator as Owner.
     *
     * Defaults to plan_id 1 (Starter, the cheapest paid plan) rather than leaving it null —
     * there's no free tier, so every organization needs a real plan from the moment it's
     * created, not just once an admin later approves a PlanChangeRequest. Hardcoded to id 1
     * rather than looked up by key: this app has exactly one database, so the id is stable,
     * and PlanSeeder always creates the Starter plan first.
     */
    public function store(StoreOrganizationRequest $request): RedirectResponse
    {
        $organization = Organization::create([
            ...$request->validated(),
            'plan_id' => 1,
        ]);

        $organization->members()->attach(Auth::id(), ['role' => OrganizationRole::Owner->value]);

        return redirect()
            ->route('organizations.show', $organization)
            ->with('status', 'Organisasi berhasil dibuat.');
    }

    /**
     * Display the organization dashboard and its members.
     */
    public function show(Organization $organization): View
    {
        $this->authorize('view', $organization);

        $organization->load(['organizationType', 'template', 'members']);

        return view('organizations.show', [
            'organization' => $organization,
            'canManageMembers' => Auth::user()->can('manageMembers', $organization),
            'canDelete' => Auth::user()->can('delete', $organization),
            'tenantDomain' => config('tenancy.domain'),
            'hasSeenDashboardTour' => Auth::user()->hasSeenOnboardingTour('dashboard'),
        ]);
    }

    /**
     * Toggle the organization's publish status. Any member may do this (same tier as
     * brand settings) - no product requirement yet for restricting it to the Owner.
     *
     * Publishing (not un-publishing) requires a plan that's been paid for, isn't expired, and
     * whose usage doesn't exceed the plan's limits - an org that never had a plan approved,
     * whose plan_expires_at has lapsed, or that's over its content/component limits (see
     * Organization::planViolations()) is redirected to the subscription page instead.
     * Un-publishing is always allowed regardless of plan state, and a site already published
     * before it started violating its plan stays live (no auto-teardown - the public page
     * shows a violation badge instead, see organizations/pages/_document.blade.php).
     */
    public function publish(Organization $organization): RedirectResponse
    {
        $this->authorize('update', $organization);

        $publishing = $organization->status !== OrganizationStatus::Published;

        if ($publishing && (blank($organization->plan_id) || $organization->violatesPlanRules())) {
            return redirect()
                ->route('organizations.plan.edit', $organization)
                ->with('error', 'Situs belum bisa dipublikasikan. Selesaikan pembayaran atau sesuaikan konten dengan batas paket terlebih dahulu.');
        }

        $organization->publish($publishing);

        return back()->with('status', $organization->status === OrganizationStatus::Published
            ? 'Situs berhasil dipublikasikan.'
            : 'Situs kembali ke draft.');
    }

    /**
     * Delete the organization. Only the Owner may do this.
     */
    public function destroy(Organization $organization): RedirectResponse
    {
        $this->authorize('delete', $organization);

        $organization->delete();

        return redirect()
            ->route('organizations.index')
            ->with('status', 'Organisasi berhasil dihapus.');
    }
}
