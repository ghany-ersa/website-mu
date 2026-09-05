<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\BuilderAware;
use App\Models\MasjidFacility;
use App\Models\Organization;
use App\Services\PlanLimitService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrganizationFacilityController extends Controller
{
    use BuilderAware;

    public function __construct(private readonly PlanLimitService $planLimitService) {}

    public function index(Request $request, Organization $organization): View
    {
        abort_unless($organization->hasSection('fasilitas-masjid'), 404);
        $this->authorize('viewAny', [MasjidFacility::class, $organization]);

        return view('organizations.facilities.index', [
            'organization' => $organization,
            'facilities' => $organization->facilities()->get(),
            ...$this->builderViewData($request),
        ]);
    }

    public function create(Request $request, Organization $organization): View|RedirectResponse
    {
        abort_unless($organization->hasSection('fasilitas-masjid'), 404);
        $this->authorize('create', [MasjidFacility::class, $organization]);

        if (! $this->planLimitService->canCreate($organization, 'facilities')) {
            return redirect()
                ->route('organizations.facilities.index', $organization)
                ->with('warning', 'Batas jumlah fasilitas paket Anda sudah tercapai. Upgrade paket untuk menambah lagi.');
        }

        return view('organizations.facilities.form', [
            'organization' => $organization,
            'facility' => new MasjidFacility,
            ...$this->builderViewData($request),
        ]);
    }

    public function store(Request $request, Organization $organization): RedirectResponse
    {
        abort_unless($organization->hasSection('fasilitas-masjid'), 404);
        $this->authorize('create', [MasjidFacility::class, $organization]);

        if (! $this->planLimitService->canCreate($organization, 'facilities')) {
            return redirect()
                ->route('organizations.facilities.index', $organization)
                ->with('warning', 'Batas jumlah fasilitas paket Anda sudah tercapai. Upgrade paket untuk menambah lagi.');
        }

        $organization->facilities()->create([
            ...$this->validated($request),
            'order' => $organization->facilities()->max('order') + 1,
        ]);

        return redirect()
            ->route('organizations.facilities.index', $this->builderIndexParams($request, ['organization' => $organization]))
            ->with('status', 'Fasilitas berhasil ditambahkan.');
    }

    public function edit(Request $request, Organization $organization, MasjidFacility $facility): View
    {
        $this->authorize('update', $facility);
        $this->ensureBelongsToOrganization($organization, $facility);

        return view('organizations.facilities.form', [
            'organization' => $organization,
            'facility' => $facility,
            ...$this->builderViewData($request),
        ]);
    }

    public function update(Request $request, Organization $organization, MasjidFacility $facility): RedirectResponse
    {
        $this->authorize('update', $facility);
        $this->ensureBelongsToOrganization($organization, $facility);

        $facility->update($this->validated($request));

        return redirect()
            ->route('organizations.facilities.index', $this->builderIndexParams($request, ['organization' => $organization]))
            ->with('status', 'Fasilitas berhasil diperbarui.');
    }

    public function destroy(Request $request, Organization $organization, MasjidFacility $facility): RedirectResponse
    {
        $this->authorize('delete', $facility);
        $this->ensureBelongsToOrganization($organization, $facility);

        $facility->delete();

        return redirect()
            ->route('organizations.facilities.index', $this->builderIndexParams($request, ['organization' => $organization]))
            ->with('status', 'Fasilitas berhasil dihapus.');
    }

    /**
     * Persist a new facility order (drag-and-drop reorder).
     */
    public function reorder(Request $request, Organization $organization): RedirectResponse
    {
        $this->authorize('update', $organization);

        $validated = $request->validate([
            'facility_ids' => ['required', 'array'],
            'facility_ids.*' => ['integer', 'exists:masjid_facilities,id'],
        ]);

        foreach ($validated['facility_ids'] as $index => $facilityId) {
            $organization->facilities()->where('id', $facilityId)->update(['order' => $index]);
        }

        return redirect()
            ->route('organizations.facilities.index', $organization)
            ->with('status', 'Urutan fasilitas berhasil disimpan.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'photo' => ['nullable', 'string'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);
    }

    private function ensureBelongsToOrganization(Organization $organization, MasjidFacility $facility): void
    {
        abort_unless($facility->organization_id === $organization->id, 404);
    }
}
