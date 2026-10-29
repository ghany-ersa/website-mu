<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\BuilderAware;
use App\Models\GalleryPhoto;
use App\Models\Organization;
use App\Services\PlanLimitService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrganizationGalleryController extends Controller
{
    use BuilderAware;

    public function __construct(private readonly PlanLimitService $planLimitService) {}

    public function index(Request $request, Organization $organization): View
    {
        abort_unless($organization->hasSection('galeri'), 404);
        $this->authorize('viewAny', [GalleryPhoto::class, $organization]);

        return view('organizations.gallery.index', [
            'organization' => $organization,
            'photos' => $organization->photos()->get(),
            ...$this->builderViewData($request),
        ]);
    }

    public function create(Request $request, Organization $organization): View|RedirectResponse
    {
        abort_unless($organization->hasSection('galeri'), 404);
        $this->authorize('create', [GalleryPhoto::class, $organization]);

        if (! $this->planLimitService->canCreate($organization, 'gallery_photos')) {
            return redirect()
                ->route('organizations.gallery.index', $organization)
                ->with('warning', 'Batas jumlah foto galeri paket Anda sudah tercapai. Tingkatkan paket infak untuk menambah lagi.');
        }

        return view('organizations.gallery.form', [
            'organization' => $organization,
            'photo' => new GalleryPhoto,
            'categoryOptions' => GalleryPhoto::categoryOptions($organization),
            ...$this->builderViewData($request),
        ]);
    }

    public function store(Request $request, Organization $organization): RedirectResponse
    {
        abort_unless($organization->hasSection('galeri'), 404);
        $this->authorize('create', [GalleryPhoto::class, $organization]);

        if (! $this->planLimitService->canCreate($organization, 'gallery_photos')) {
            return redirect()
                ->route('organizations.gallery.index', $organization)
                ->with('warning', 'Batas jumlah foto galeri paket Anda sudah tercapai. Tingkatkan paket infak untuk menambah lagi.');
        }

        $organization->photos()->create([
            ...$this->validated($request, $organization),
            'order' => $organization->photos()->max('order') + 1,
        ]);

        return redirect()
            ->route('organizations.gallery.index', $this->builderIndexParams($request, ['organization' => $organization]))
            ->with('status', 'Foto berhasil ditambahkan.');
    }

    public function edit(Request $request, Organization $organization, GalleryPhoto $photo): View
    {
        $this->authorize('update', $photo);
        $this->ensureBelongsToOrganization($organization, $photo);

        return view('organizations.gallery.form', [
            'organization' => $organization,
            'photo' => $photo,
            'categoryOptions' => GalleryPhoto::categoryOptions($organization),
            ...$this->builderViewData($request),
        ]);
    }

    public function update(Request $request, Organization $organization, GalleryPhoto $photo): RedirectResponse
    {
        $this->authorize('update', $photo);
        $this->ensureBelongsToOrganization($organization, $photo);

        $photo->update($this->validated($request, $organization));

        return redirect()
            ->route('organizations.gallery.index', $this->builderIndexParams($request, ['organization' => $organization]))
            ->with('status', 'Foto berhasil diperbarui.');
    }

    public function destroy(Request $request, Organization $organization, GalleryPhoto $photo): RedirectResponse
    {
        $this->authorize('delete', $photo);
        $this->ensureBelongsToOrganization($organization, $photo);

        $photo->delete();

        return redirect()
            ->route('organizations.gallery.index', $this->builderIndexParams($request, ['organization' => $organization]))
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
    private function validated(Request $request, Organization $organization): array
    {
        $validated = $request->validate([
            'url' => ['required', 'string'],
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'category' => ['nullable', 'string', 'max:50'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        // Each distinct category is its own filter button on the public gallery, so "kegiatan"
        // typed next to an existing "Kegiatan" would split one filter into two. Snap to the
        // spelling already in use when one matches case-insensitively.
        if (filled($validated['category'] ?? null)) {
            $validated['category'] = collect(GalleryPhoto::categoryOptions($organization))
                ->first(fn (string $option) => mb_strtolower($option) === mb_strtolower($validated['category']))
                ?? $validated['category'];
        }

        // An unchecked checkbox posts nothing, which on update would leave is_active untouched
        // and keep a photo the user just hid still visible.
        $validated['is_active'] = $request->boolean('is_active');

        return $validated;
    }

    private function ensureBelongsToOrganization(Organization $organization, GalleryPhoto $photo): void
    {
        abort_unless($photo->organization_id === $organization->id, 404);
    }
}
