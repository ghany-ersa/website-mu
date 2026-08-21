<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\OrganizationNetwork;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrganizationNetworkController extends Controller
{
    public function index(Organization $organization): View
    {
        $this->authorize('viewAny', [OrganizationNetwork::class, $organization]);

        return view('organizations.networks.index', [
            'organization' => $organization,
            'networks' => $organization->networks()->get(),
        ]);
    }

    public function create(Organization $organization): View
    {
        $this->authorize('create', [OrganizationNetwork::class, $organization]);

        return view('organizations.networks.form', [
            'organization' => $organization,
            'network' => new OrganizationNetwork,
        ]);
    }

    public function store(Request $request, Organization $organization): RedirectResponse
    {
        $this->authorize('create', [OrganizationNetwork::class, $organization]);

        $organization->networks()->create([
            ...$this->validated($request),
            'order' => $organization->networks()->max('order') + 1,
        ]);

        return redirect()
            ->route('organizations.networks.index', $this->indexParams($request, $organization))
            ->with('status', 'Jaringan AUM/Ortom berhasil ditambahkan.');
    }

    public function edit(Organization $organization, OrganizationNetwork $network): View
    {
        $this->authorize('update', $network);
        $this->ensureBelongsToOrganization($organization, $network);

        return view('organizations.networks.form', [
            'organization' => $organization,
            'network' => $network,
        ]);
    }

    public function update(Request $request, Organization $organization, OrganizationNetwork $network): RedirectResponse
    {
        $this->authorize('update', $network);
        $this->ensureBelongsToOrganization($organization, $network);

        $network->update($this->validated($request));

        return redirect()
            ->route('organizations.networks.index', $this->indexParams($request, $organization))
            ->with('status', 'Jaringan AUM/Ortom berhasil diperbarui.');
    }

    public function destroy(Request $request, Organization $organization, OrganizationNetwork $network): RedirectResponse
    {
        $this->authorize('delete', $network);
        $this->ensureBelongsToOrganization($organization, $network);

        $network->delete();

        return redirect()
            ->route('organizations.networks.index', $this->indexParams($request, $organization))
            ->with('status', 'Jaringan AUM/Ortom berhasil dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'max:255'],
        ]);
    }

    private function ensureBelongsToOrganization(Organization $organization, OrganizationNetwork $network): void
    {
        abort_unless($network->organization_id === $organization->id, 404);
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
