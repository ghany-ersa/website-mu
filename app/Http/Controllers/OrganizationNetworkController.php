<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\BuilderAware;
use App\Models\Organization;
use App\Models\OrganizationNetwork;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrganizationNetworkController extends Controller
{
    use BuilderAware;

    public function index(Request $request, Organization $organization): View
    {
        abort_unless($organization->hasSection('jaringan-aum-ortom'), 404);
        $this->authorize('viewAny', [OrganizationNetwork::class, $organization]);

        return view('organizations.networks.index', [
            'organization' => $organization,
            'networks' => $organization->networks()->get(),
            ...$this->builderViewData($request),
        ]);
    }

    public function create(Request $request, Organization $organization): View
    {
        abort_unless($organization->hasSection('jaringan-aum-ortom'), 404);
        $this->authorize('create', [OrganizationNetwork::class, $organization]);

        return view('organizations.networks.form', [
            'organization' => $organization,
            'network' => new OrganizationNetwork,
            ...$this->builderViewData($request),
        ]);
    }

    public function store(Request $request, Organization $organization): RedirectResponse
    {
        abort_unless($organization->hasSection('jaringan-aum-ortom'), 404);
        $this->authorize('create', [OrganizationNetwork::class, $organization]);

        $organization->networks()->create([
            ...$this->validated($request),
            'order' => $organization->networks()->max('order') + 1,
        ]);

        return redirect()
            ->route('organizations.networks.index', $this->builderIndexParams($request, ['organization' => $organization]))
            ->with('status', 'Jaringan AUM/Ortom berhasil ditambahkan.');
    }

    public function edit(Request $request, Organization $organization, OrganizationNetwork $network): View
    {
        $this->authorize('update', $network);
        $this->ensureBelongsToOrganization($organization, $network);

        return view('organizations.networks.form', [
            'organization' => $organization,
            'network' => $network,
            ...$this->builderViewData($request),
        ]);
    }

    public function update(Request $request, Organization $organization, OrganizationNetwork $network): RedirectResponse
    {
        $this->authorize('update', $network);
        $this->ensureBelongsToOrganization($organization, $network);

        $network->update($this->validated($request));

        return redirect()
            ->route('organizations.networks.index', $this->builderIndexParams($request, ['organization' => $organization]))
            ->with('status', 'Jaringan AUM/Ortom berhasil diperbarui.');
    }

    public function destroy(Request $request, Organization $organization, OrganizationNetwork $network): RedirectResponse
    {
        $this->authorize('delete', $network);
        $this->ensureBelongsToOrganization($organization, $network);

        $network->delete();

        return redirect()
            ->route('organizations.networks.index', $this->builderIndexParams($request, ['organization' => $organization]))
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
}
