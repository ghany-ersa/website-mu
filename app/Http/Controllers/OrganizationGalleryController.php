<?php

namespace App\Http\Controllers;

use App\Models\GalleryPhoto;
use App\Models\Organization;
use App\Services\PlanLimitService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrganizationGalleryController extends Controller
{
    public function __construct(private readonly PlanLimitService $planLimitService) {}

    public function index(Organization $organization): View
    {
        abort_unless($organization->hasSection('galeri'), 404);
        $this->authorize('viewAny', [GalleryPhoto::class, $organization]);

        return view('organizations.gallery.index', [
            'organization' => $organization,
            'photos' => $organization->photos()->get(),
        ]);
    }

    public function create(Organization $organization): View|RedirectResponse
    {
        abort_unless($organization->hasSection('galeri'), 404);
        $this->authorize('create', [GalleryPhoto::class, $organization]);

        if (! $this->planLimitService->canCreate($organization, 'gallery_photos')) {
            return redirect()
                ->route('organizations.gallery.index', $organization)
                ->with('warning', 'Batas jumlah foto galeri paket Anda sudah tercapai. Upgrade paket untuk menambah lagi.');
        }

        return view('organizations.gallery.form', [
            'organization' => $organization,
            'photo' => new GalleryPhoto,
        ]);
    }

    public function store(Request $request, Organization $organization): RedirectResponse
    {
        abort_unless($organization->hasSection('galeri'), 404);
        $this->authorize('create', [GalleryPhoto::class, $organization]);

        if (! $this->planLimitService->canCreate($organization, 'gallery_photos')) {
            return redirect()
                ->route('organizations.gallery.index', $organization)
                ->with('warning', 'Batas jumlah foto galeri paket Anda sudah tercapai. Upgrade paket untuk menambah lagi.');
        }

        $organization->photos()->create([
            ...$this->validated($request),
            'order' => $organization->photos()->max('order') + 1,
        ]);

        return redirect()
            ->route('organizations.gallery.index', $this->indexParams($request, $organization))
            ->with('status', 'Foto berhasil ditambahkan.');
    }

    public function edit(Organization $organization, GalleryPhoto $photo): View
    {
        $this->authorize('update', $photo);
        $this->ensureBelongsToOrganization($organization, $photo);

        return view('organizations.gallery.form', [
            'organization' => $organization,
            'photo' => $photo,
        ]);
    }

    public function update(Request $request, Organization $organization, GalleryPhoto $photo): RedirectResponse
    {
        $this->authorize('update', $photo);
        $this->ensureBelongsToOrganization($organization, $photo);

        $photo->update($this->validated($request));

        return redirect()
            ->route('organizations.gallery.index', $this->indexParams($request, $organization))
            ->with('status', 'Foto berhasil diperbarui.');
    }

    public function destroy(Request $request, Organization $organization, GalleryPhoto $photo): RedirectResponse
    {
        $this->authorize('delete', $photo);
        $this->ensureBelongsToOrganization($organization, $photo);

        $photo->delete();

        return redirect()
            ->route('organizations.gallery.index', $this->indexParams($request, $organization))
            ->with('status', 'Foto berhasil dihapus.');
    }

    /**
     * Persist a new gallery photo order (drag-and-drop reorder).
     */
    public function reorder(Request $request, Organization $organization): RedirectResponse
    {
        $this->authorize('update', $organization);

        $validated = $request->validate([
            'photo_ids' => ['required', 'array'],
            'photo_ids.*' => ['integer', 'exists:gallery_photos,id'],
        ]);

        foreach ($validated['photo_ids'] as $index => $photoId) {
            $organization->photos()->where('id', $photoId)->update(['order' => $index]);
        }

        return redirect()
            ->route('organizations.gallery.index', $organization)
            ->with('status', 'Urutan galeri berhasil disimpan.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'url' => ['required', 'string'],
            'caption' => ['nullable', 'string', 'max:255'],
        ]);
    }

    private function ensureBelongsToOrganization(Organization $organization, GalleryPhoto $photo): void
    {
        abort_unless($photo->organization_id === $organization->id, 404);
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
