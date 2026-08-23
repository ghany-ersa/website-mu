<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Rules\NotTooLightColor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OrganizationBrandController extends Controller
{
    public function edit(Organization $organization): View
    {
        $this->authorize('update', $organization);

        return view('organizations.brand.edit', [
            'organization' => $organization,
        ]);
    }

    public function update(Request $request, Organization $organization): RedirectResponse
    {
        $this->authorize('update', $organization);

        $validated = $request->validate([
            'primary_color' => ['nullable', 'string', 'regex:/^#[0-9a-fA-F]{6}$/', new NotTooLightColor],
            'secondary_color' => ['nullable', 'string', 'regex:/^#[0-9a-fA-F]{6}$/', new NotTooLightColor],
            'logo' => ['nullable', 'string'],
            'font_family' => ['nullable', 'string', Rule::in(array_keys(config('branding.fonts')))],
            'border_radius' => ['nullable', 'string', Rule::in(array_keys(config('branding.radii')))],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'whatsapp' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:255'],
            'instagram_url' => ['nullable', 'url', 'max:255'],
            'facebook_url' => ['nullable', 'url', 'max:255'],
        ]);

        $organization->update($validated);

        return redirect()
            ->route('organizations.brand.edit', $organization)
            ->with('status', 'Brand settings berhasil disimpan.');
    }
}
