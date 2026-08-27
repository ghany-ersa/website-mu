<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\BuilderAware;
use App\Http\Controllers\Concerns\SanitizesRichText;
use App\Models\Announcement;
use App\Models\Organization;
use App\Services\PlanLimitService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrganizationAnnouncementController extends Controller
{
    use BuilderAware;
    use SanitizesRichText;

    public function __construct(private readonly PlanLimitService $planLimitService) {}

    public function index(Request $request, Organization $organization): View
    {
        abort_unless($organization->hasSection('pengumuman'), 404);
        $this->authorize('viewAny', [Announcement::class, $organization]);

        return view('organizations.announcements.index', [
            'organization' => $organization,
            'announcements' => $organization->announcements()->get(),
            ...$this->builderViewData($request),
        ]);
    }

    public function create(Request $request, Organization $organization): View|RedirectResponse
    {
        abort_unless($organization->hasSection('pengumuman'), 404);
        $this->authorize('create', [Announcement::class, $organization]);

        if (! $this->planLimitService->canCreate($organization, 'announcements')) {
            return redirect()
                ->route('organizations.announcements.index', $organization)
                ->with('warning', 'Batas jumlah pengumuman paket Anda sudah tercapai. Upgrade paket untuk menambah lagi.');
        }

        return view('organizations.announcements.form', [
            'organization' => $organization,
            'announcement' => new Announcement,
            ...$this->builderViewData($request),
        ]);
    }

    public function store(Request $request, Organization $organization): RedirectResponse
    {
        abort_unless($organization->hasSection('pengumuman'), 404);
        $this->authorize('create', [Announcement::class, $organization]);

        if (! $this->planLimitService->canCreate($organization, 'announcements')) {
            return redirect()
                ->route('organizations.announcements.index', $organization)
                ->with('warning', 'Batas jumlah pengumuman paket Anda sudah tercapai. Upgrade paket untuk menambah lagi.');
        }

        $organization->announcements()->create($this->validated($request));

        return redirect()
            ->route('organizations.announcements.index', $this->builderIndexParams($request, ['organization' => $organization]))
            ->with('status', 'Pengumuman berhasil disimpan.');
    }

    public function edit(Request $request, Organization $organization, Announcement $announcement): View
    {
        $this->authorize('update', $announcement);
        $this->ensureBelongsToOrganization($organization, $announcement);

        return view('organizations.announcements.form', [
            'organization' => $organization,
            'announcement' => $announcement,
            ...$this->builderViewData($request),
        ]);
    }

    public function update(Request $request, Organization $organization, Announcement $announcement): RedirectResponse
    {
        $this->authorize('update', $announcement);
        $this->ensureBelongsToOrganization($organization, $announcement);

        $announcement->update($this->validated($request));

        return redirect()
            ->route('organizations.announcements.index', $this->builderIndexParams($request, ['organization' => $organization]))
            ->with('status', 'Pengumuman berhasil diperbarui.');
    }

    public function destroy(Request $request, Organization $organization, Announcement $announcement): RedirectResponse
    {
        $this->authorize('delete', $announcement);
        $this->ensureBelongsToOrganization($organization, $announcement);

        $announcement->delete();

        return redirect()
            ->route('organizations.announcements.index', $this->builderIndexParams($request, ['organization' => $organization]))
            ->with('status', 'Pengumuman berhasil dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'body' => ['nullable', 'string'],
            'priority' => ['required', 'in:Rendah,Sedang,Tinggi'],
            'valid_until' => ['nullable', 'date'],
            'status' => ['required', 'in:draft,published'],
        ]);

        if (array_key_exists('body', $data)) {
            $data['body'] = $this->sanitizeRichText($data['body']);
        }

        return $data;
    }

    private function ensureBelongsToOrganization(Organization $organization, Announcement $announcement): void
    {
        abort_unless($announcement->organization_id === $organization->id, 404);
    }
}
