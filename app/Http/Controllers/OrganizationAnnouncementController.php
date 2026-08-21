<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Organization;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrganizationAnnouncementController extends Controller
{
    public function index(Organization $organization): View
    {
        $this->authorize('viewAny', [Announcement::class, $organization]);

        return view('organizations.announcements.index', [
            'organization' => $organization,
            'announcements' => $organization->announcements()->get(),
        ]);
    }

    public function create(Organization $organization): View
    {
        $this->authorize('create', [Announcement::class, $organization]);

        return view('organizations.announcements.form', [
            'organization' => $organization,
            'announcement' => new Announcement,
        ]);
    }

    public function store(Request $request, Organization $organization): RedirectResponse
    {
        $this->authorize('create', [Announcement::class, $organization]);

        $organization->announcements()->create($this->validated($request));

        return redirect()
            ->route('organizations.announcements.index', $this->indexParams($request, $organization))
            ->with('status', 'Pengumuman berhasil disimpan.');
    }

    public function edit(Organization $organization, Announcement $announcement): View
    {
        $this->authorize('update', $announcement);
        $this->ensureBelongsToOrganization($organization, $announcement);

        return view('organizations.announcements.form', [
            'organization' => $organization,
            'announcement' => $announcement,
        ]);
    }

    public function update(Request $request, Organization $organization, Announcement $announcement): RedirectResponse
    {
        $this->authorize('update', $announcement);
        $this->ensureBelongsToOrganization($organization, $announcement);

        $announcement->update($this->validated($request));

        return redirect()
            ->route('organizations.announcements.index', $this->indexParams($request, $organization))
            ->with('status', 'Pengumuman berhasil diperbarui.');
    }

    public function destroy(Request $request, Organization $organization, Announcement $announcement): RedirectResponse
    {
        $this->authorize('delete', $announcement);
        $this->ensureBelongsToOrganization($organization, $announcement);

        $announcement->delete();

        return redirect()
            ->route('organizations.announcements.index', $this->indexParams($request, $organization))
            ->with('status', 'Pengumuman berhasil dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'body' => ['nullable', 'string'],
            'priority' => ['required', 'in:Rendah,Sedang,Tinggi'],
            'valid_until' => ['nullable', 'date'],
            'status' => ['required', 'in:draft,published'],
        ]);
    }

    private function ensureBelongsToOrganization(Organization $organization, Announcement $announcement): void
    {
        abort_unless($announcement->organization_id === $organization->id, 404);
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
