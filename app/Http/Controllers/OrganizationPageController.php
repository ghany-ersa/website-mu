<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\OrganizationPage;
use App\Services\PlanLimitService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OrganizationPageController extends Controller
{
    public function __construct(private readonly PlanLimitService $planLimitService) {}

    /**
     * Create a new blank page for the organization.
     */
    public function store(Request $request, Organization $organization): RedirectResponse
    {
        $this->authorize('update', $organization);

        if (! $this->planLimitService->canCreate($organization, 'pages_total')) {
            return redirect()
                ->route('organizations.builder.edit', $organization)
                ->with('warning', 'Batas jumlah halaman paket Anda sudah tercapai. Upgrade ke paket Professional untuk menambah halaman.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'alpha_dash', 'max:255'],
        ]);

        $page = $organization->pages()->create([
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'order' => $organization->pages()->max('order') + 1,
        ]);

        $page->ensureHeader();
        $page->ensureFooter();

        return redirect()
            ->route('organizations.builder.page', [$organization, $page])
            ->with('status', 'Halaman berhasil dibuat.');
    }

    /**
     * Update a page's name/slug.
     */
    public function update(Request $request, Organization $organization, OrganizationPage $page): RedirectResponse
    {
        $this->authorize('update', $organization);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'alpha_dash', 'max:255'],
        ]);

        $page->update($validated);

        return back()->with('status', 'Halaman berhasil diperbarui.');
    }

    /**
     * Delete a page and its sections.
     */
    public function destroy(Organization $organization, OrganizationPage $page): RedirectResponse
    {
        $this->authorize('update', $organization);

        if ($page->is_home) {
            return back()->with('status', 'Halaman utama (Beranda) tidak bisa dihapus.');
        }

        $page->delete();

        return redirect()
            ->route('organizations.builder.edit', $organization)
            ->with('status', 'Halaman berhasil dihapus.');
    }
}
