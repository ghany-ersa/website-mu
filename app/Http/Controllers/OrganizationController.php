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
     */
    public function create(Request $request): View
    {
        $this->authorize('create', Organization::class);

        $selectedTemplate = $request->filled('template')
            ? Template::where('slug', $request->query('template'))->where('is_active', true)->first()
            : null;

        return view('organizations.create', [
            'organizationTypes' => OrganizationType::orderBy('name')->get(),
            'selectedTemplate' => $selectedTemplate,
        ]);
    }

    /**
     * Store a newly created organization and attach the creator as Owner.
     */
    public function store(StoreOrganizationRequest $request): RedirectResponse
    {
        $organization = Organization::create($request->validated());

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
     * brand settings) — no product requirement yet for restricting it to the Owner.
     */
    public function publish(Organization $organization): RedirectResponse
    {
        $this->authorize('update', $organization);

        $organization->publish($organization->status !== OrganizationStatus::Published);

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
