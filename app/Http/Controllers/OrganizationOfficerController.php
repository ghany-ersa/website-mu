<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\BuilderAware;
use App\Models\Officer;
use App\Models\Organization;
use App\Services\PlanLimitService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrganizationOfficerController extends Controller
{
    use BuilderAware;

    public function __construct(private readonly PlanLimitService $planLimitService) {}

    public function index(Request $request, Organization $organization): View
    {
        abort_unless($organization->hasSection('struktur-pengurus'), 404);
        $this->authorize('viewAny', [Officer::class, $organization]);

        return view('organizations.officers.index', [
            'organization' => $organization,
            'officers' => $organization->officers()->get(),
            ...$this->builderViewData($request),
        ]);
    }

    public function create(Request $request, Organization $organization): View|RedirectResponse
    {
        abort_unless($organization->hasSection('struktur-pengurus'), 404);
        $this->authorize('create', [Officer::class, $organization]);

        if (! $this->planLimitService->canCreate($organization, 'officers')) {
            return redirect()
                ->route('organizations.officers.index', $organization)
                ->with('warning', 'Batas jumlah pengurus paket Anda sudah tercapai. Tingkatkan paket infak untuk menambah lagi.');
        }

        return view('organizations.officers.form', [
            'organization' => $organization,
            'officer' => new Officer,
            ...$this->builderViewData($request),
        ]);
    }

    public function store(Request $request, Organization $organization): RedirectResponse
    {
        abort_unless($organization->hasSection('struktur-pengurus'), 404);
        $this->authorize('create', [Officer::class, $organization]);

        if (! $this->planLimitService->canCreate($organization, 'officers')) {
            return redirect()
                ->route('organizations.officers.index', $organization)
                ->with('warning', 'Batas jumlah pengurus paket Anda sudah tercapai. Tingkatkan paket infak untuk menambah lagi.');
        }

        $validated = $this->validated($request);

        $organization->officers()->create([
            ...$validated,
            'slug' => $this->uniqueSlug($organization, $validated['name']),
            'order' => $organization->officers()->max('order') + 1,
        ]);

        return redirect()
            ->route('organizations.officers.index', $this->builderIndexParams($request, ['organization' => $organization]))
            ->with('status', 'Pengurus berhasil ditambahkan.');
    }

    public function edit(Request $request, Organization $organization, Officer $officer): View
    {
        $this->authorize('update', $officer);
        $this->ensureBelongsToOrganization($organization, $officer);

        return view('organizations.officers.form', [
            'organization' => $organization,
            'officer' => $officer,
            ...$this->builderViewData($request),
        ]);
    }

    public function update(Request $request, Organization $organization, Officer $officer): RedirectResponse
    {
        $this->authorize('update', $officer);
        $this->ensureBelongsToOrganization($organization, $officer);

        $validated = $this->validated($request);

        // Only re-slug on an actual rename, so an officer's public URL stays stable across
        // unrelated edits (fixing a typo in the bio must not break an existing link).
        if ($validated['name'] !== $officer->name || $officer->slug === null) {
            $validated['slug'] = $this->uniqueSlug($organization, $validated['name'], $officer);
        }

        $officer->update($validated);

        return redirect()
            ->route('organizations.officers.index', $this->builderIndexParams($request, ['organization' => $organization]))
            ->with('status', 'Pengurus berhasil diperbarui.');
    }

    public function destroy(Request $request, Organization $organization, Officer $officer): RedirectResponse
    {
        $this->authorize('delete', $officer);
        $this->ensureBelongsToOrganization($organization, $officer);

        $officer->delete();

        return redirect()
            ->route('organizations.officers.index', $this->builderIndexParams($request, ['organization' => $organization]))
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
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'role' => ['required', 'string', 'max:255'],
            'subject' => ['nullable', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:2000'],
            'photo' => ['nullable', 'string'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        // An unchecked checkbox posts nothing at all, which would otherwise leave is_active
        // untouched on update and silently keep a hidden officer visible.
        $validated['is_active'] = $request->boolean('is_active');

        return $validated;
    }

    /**
     * Slugs are unique per organization, not globally - two schools may each have a
     * "budi-santoso". $ignore lets an update keep its own slug instead of colliding with it.
     */
    private function uniqueSlug(Organization $organization, string $name, ?Officer $ignore = null): string
    {
        $base = str($name)->slug();
        $slug = (string) $base;
        $suffix = 1;

        while ($organization->officers()
            ->where('slug', $slug)
            ->when($ignore?->exists, fn ($query) => $query->whereKeyNot($ignore->getKey()))
            ->exists()
        ) {
            $slug = "{$base}-".++$suffix;
        }

        return $slug;
    }

    private function ensureBelongsToOrganization(Organization $organization, Officer $officer): void
    {
        abort_unless($officer->organization_id === $organization->id, 404);
    }
}
