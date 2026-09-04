<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Rules\ReservedSlug;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Name, slug, and description are each their own independent form on the Edit
 * Organisasi page (organizations/edit/edit.blade.php) - read-only until "Edit" is
 * clicked, saved separately - since each has an outsized, immediate effect on the
 * public site (page title, subdomain URL, and search/link-preview description
 * respectively) and shouldn't be changeable as a side effect of editing another field.
 */
class OrganizationEditController extends Controller
{
    public function edit(Organization $organization): View
    {
        $this->authorize('update', $organization);

        return view('organizations.edit.edit', [
            'organization' => $organization,
            'tenantDomain' => config('tenancy.domain'),
        ]);
    }

    public function updateName(Request $request, Organization $organization): RedirectResponse
    {
        $this->authorize('update', $organization);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $organization->update($validated);

        return redirect()
            ->route('organizations.edit.edit', $organization)
            ->with('status', 'Nama organisasi berhasil disimpan.');
    }

    public function updateSlug(Request $request, Organization $organization): RedirectResponse
    {
        $this->authorize('update', $organization);

        $validated = $request->validate([
            'slug' => [
                'required',
                'string',
                'min:3',
                'max:63',
                'regex:/^[a-z0-9]+(-[a-z0-9]+)*$/',
                new ReservedSlug,
                Rule::unique('organizations', 'slug')->ignore($organization->id),
            ],
        ], [
            'slug.regex' => 'Slug hanya boleh berisi huruf kecil, angka, dan tanda hubung (tidak di awal/akhir atau berurutan).',
            'slug.unique' => 'Slug ini sudah digunakan oleh organisasi lain.',
        ]);

        $organization->update($validated);

        return redirect()
            ->route('organizations.edit.edit', $organization)
            ->with('status', 'Subdomain berhasil disimpan.');
    }

    public function updateDescription(Request $request, Organization $organization): RedirectResponse
    {
        $this->authorize('update', $organization);

        $validated = $request->validate([
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $organization->update($validated);

        return redirect()
            ->route('organizations.edit.edit', $organization)
            ->with('status', 'Deskripsi berhasil disimpan.');
    }
}
