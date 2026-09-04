<?php

namespace App\Http\Controllers;

use App\Enums\OrganizationRole;
use App\Enums\OrganizationStatus;
use App\Http\Requests\StoreOrganizationRequest;
use App\Models\Organization;
use App\Models\OrganizationType;
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
        $organizations = Auth::user()->organizations()->with('organizationType')->get();

        return view('organizations.index', ['organizations' => $organizations]);
    }

    /**
     * Show the form for creating a new organization.
     *
     * Accepts an optional ?template=slug (set by TemplateUseController's "Gunakan
     * Template" flow) to pre-select the template and its matching organization type.
     *
     * Excludes Template::is_exclusive templates: every new organization is created on the
     * Starter plan (see store()), which never has has_exclusive_templates, so an exclusive
     * template can never legitimately be picked here - falling through to null instead of
     * pre-selecting one avoids a dead-end where the form looks fine but submission always
     * fails validation (see StoreOrganizationRequest::exclusiveTemplateIds()).
     */
    public function create(Request $request): View
    {
        $this->authorize('create', Organization::class);

        $selectedTemplate = $request->filled('template')
            ? Template::where('slug', $request->query('template'))->where('is_active', true)->where('is_exclusive', false)->first()
            : null;

        return view('organizations.create', [
            'organizationTypes' => OrganizationType::orderBy('name')->get(),
            'selectedTemplate' => $selectedTemplate,
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
