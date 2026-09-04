<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\Template;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Lets an organization replace its entire site with another template's starting content
 * (see Organization::seedPagesFromTemplate()) - the builder only supports one page, so
 * switching template means discarding the current page/sections and re-cloning from the
 * new template, not merging. Only the Owner may do this (see OrganizationPolicy::update()
 * is too broad for something this destructive - gated separately below), and templates
 * marked Template::is_exclusive are only selectable when the org's plan grants that
 * entitlement (Organization::canUseExclusiveTemplates()).
 */
class OrganizationTemplateController extends Controller
{
    public function edit(Organization $organization): View
    {
        $this->authorize('update', $organization);

        $templates = Template::where('is_active', true)
            ->where(function ($query) use ($organization) {
                $query->where('is_exclusive', false);

                if ($organization->canUseExclusiveTemplates()) {
                    $query->orWhere('is_exclusive', true);
                }
            })
            ->orderBy('name')
            ->get();

        $lockedTemplates = $organization->canUseExclusiveTemplates()
            ? collect()
            : Template::where('is_active', true)->where('is_exclusive', true)->orderBy('name')->get();

        return view('organizations.template.edit', [
            'organization' => $organization,
            'templates' => $templates,
            'lockedTemplates' => $lockedTemplates,
        ]);
    }

    public function update(Request $request, Organization $organization): RedirectResponse
    {
        $this->authorize('update', $organization);

        $validated = $request->validate([
            'template_id' => ['required', 'integer', 'exists:templates,id'],
        ]);

        $template = Template::findOrFail($validated['template_id']);

        if ($template->is_exclusive && ! $organization->canUseExclusiveTemplates()) {
            return back()->withErrors([
                'template_id' => 'Template ini eksklusif untuk paket Professional. Upgrade paket organisasi Anda terlebih dahulu.',
            ]);
        }

        if ($template->id === $organization->template_id) {
            return redirect()
                ->route('organizations.template.edit', $organization)
                ->with('status', 'Organisasi ini sudah menggunakan template tersebut.');
        }

        $organization->pages()->delete();
        $organization->update(['template_id' => $template->id]);
        $organization->refresh();
        $organization->ensureHomePageExists();

        return redirect()
            ->route('organizations.show', $organization)
            ->with('status', 'Template berhasil diganti. Semua halaman sebelumnya telah digantikan dengan template baru.');
    }
}
