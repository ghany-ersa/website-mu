<?php

namespace App\Http\Controllers;

use App\Models\Officer;
use App\Models\Organization;
use App\Services\PlanLimitService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrganizationOfficerController extends Controller
{
    public function __construct(private readonly PlanLimitService $planLimitService) {}

    public function index(Organization $organization): View
    {
        abort_unless($organization->hasSection('struktur-pengurus'), 404);
        $this->authorize('viewAny', [Officer::class, $organization]);

        return view('organizations.officers.index', [
            'organization' => $organization,
            'officers' => $organization->officers()->get(),
        ]);
    }

    public function create(Organization $organization): View|RedirectResponse
    {
        abort_unless($organization->hasSection('struktur-pengurus'), 404);
        $this->authorize('create', [Officer::class, $organization]);

        if (! $this->planLimitService->canCreate($organization, 'officers')) {
            return redirect()
                ->route('organizations.officers.index', $organization)
                ->with('warning', 'Batas jumlah pengurus paket Anda sudah tercapai. Upgrade paket untuk menambah lagi.');
        }

        return view('organizations.officers.form', [
            'organization' => $organization,
            'officer' => new Officer,
        ]);
    }

    public function store(Request $request, Organization $organization): RedirectResponse
    {
        abort_unless($organization->hasSection('struktur-pengurus'), 404);
        $this->authorize('create', [Officer::class, $organization]);

        if (! $this->planLimitService->canCreate($organization, 'officers')) {
            return redirect()
                ->route('organizations.officers.index', $organization)
                ->with('warning', 'Batas jumlah pengurus paket Anda sudah tercapai. Upgrade paket untuk menambah lagi.');
        }

        $organization->officers()->create([
            ...$this->validated($request),
            'order' => $organization->officers()->max('order') + 1,
        ]);

        return redirect()
            ->route('organizations.officers.index', $this->indexParams($request, $organization))
            ->with('status', 'Pengurus berhasil ditambahkan.');
    }

    public function edit(Organization $organization, Officer $officer): View
    {
        $this->authorize('update', $officer);
        $this->ensureBelongsToOrganization($organization, $officer);

        return view('organizations.officers.form', [
            'organization' => $organization,
            'officer' => $officer,
        ]);
    }

    public function update(Request $request, Organization $organization, Officer $officer): RedirectResponse
    {
        $this->authorize('update', $officer);
        $this->ensureBelongsToOrganization($organization, $officer);

        $officer->update($this->validated($request));

        return redirect()
            ->route('organizations.officers.index', $this->indexParams($request, $organization))
            ->with('status', 'Pengurus berhasil diperbarui.');
    }

    public function destroy(Request $request, Organization $organization, Officer $officer): RedirectResponse
    {
        $this->authorize('delete', $officer);
        $this->ensureBelongsToOrganization($organization, $officer);

        $officer->delete();

        return redirect()
            ->route('organizations.officers.index', $this->indexParams($request, $organization))
            ->with('status', 'Pengurus berhasil dihapus.');
    }

    /**
     * Persist a new officer order (drag-and-drop reorder).
     */
    public function reorder(Request $request, Organization $organization): RedirectResponse
    {
        $this->authorize('update', $organization);

        $validated = $request->validate([
            'officer_ids' => ['required', 'array'],
            'officer_ids.*' => ['integer', 'exists:officers,id'],
        ]);

        foreach ($validated['officer_ids'] as $index => $officerId) {
            $organization->officers()->where('id', $officerId)->update(['order' => $index]);
        }

        return redirect()
            ->route('organizations.officers.index', $organization)
            ->with('status', 'Urutan pengurus berhasil disimpan.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'role' => ['required', 'string', 'max:255'],
            'photo' => ['nullable', 'string'],
        ]);
    }

    private function ensureBelongsToOrganization(Organization $organization, Officer $officer): void
    {
        abort_unless($officer->organization_id === $organization->id, 404);
    }

    /**
     * @return array<string, mixed>
     */
    private function indexParams(Request $request, Organization $organization): array
    {
        return $request->input('from') === 'builder'
            ? ['organization' => $organization, 'from' => 'builder', 'section' => $request->input('section')]
            : ['organization' => $organization];
    }
}
