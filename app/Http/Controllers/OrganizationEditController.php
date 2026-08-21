<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Rules\ReservedSlug;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OrganizationSeoController extends Controller
{
    public function edit(Organization $organization): View
    {
        $this->authorize('update', $organization);

        return view('organizations.seo.edit', [
            'organization' => $organization,
            'tenantDomain' => config('tenancy.domain'),
        ]);
    }

    public function update(Request $request, Organization $organization): RedirectResponse
    {
        $this->authorize('update', $organization);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'min:3',
                'max:63',
                'regex:/^[a-z0-9]+(-[a-z0-9]+)*$/',
                new ReservedSlug,
                Rule::unique('organizations', 'slug')->ignore($organization->id),
            ],
            'description' => ['nullable', 'string', 'max:1000'],
        ], [
            'slug.regex' => 'Slug hanya boleh berisi huruf kecil, angka, dan tanda hubung (tidak di awal/akhir atau berurutan).',
            'slug.unique' => 'Slug ini sudah digunakan oleh organisasi lain.',
        ]);

        $organization->update($validated);

        return redirect()
            ->route('organizations.seo.edit', $organization)
            ->with('status', 'Pengaturan SEO berhasil disimpan.');
    }
}
