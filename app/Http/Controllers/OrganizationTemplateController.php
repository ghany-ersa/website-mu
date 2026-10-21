<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\Template;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Lets an organization replace its entire site with another template's starting content and
 * layout (see Organization::replaceTemplateKeepingContent()) - the current pages/sections are
 * discarded and re-cloned from the new template, but each cloned section whose `key` matches one
 * the organization already had keeps that section's own content instead of the new template's
 * default, so switching template restyles the site without silently discarding what the owner
 * already wrote in. Gated by OrganizationPolicy::manageBuilder() rather than update(), the same
 * as the page builder - restructuring this destructively while the site is live isn't allowed,
 * so a member has to unpublish (Draft) first.
 *
 * An is_exclusive template is selectable from any plan - the same "build now, pay to publish"
 * split as picking one at organization-creation time (StoreOrganizationRequest) and the page
 * builder's exclusive sections/variants (Organization::planViolations()): switching onto it here
 * never checks canUseExclusiveTemplates(), and the entitlement is enforced only when publishing.
 * Before this, creating a new organization let you pick an exclusive template freely but
 * switching an existing one to the same template was blocked outright - two different rules for
 * reaching the identical end state (an org on an exclusive template, unpublished, on a plan that
 * doesn't cover it) made the block feel arbitrary rather than a real limitation.
 */
class OrganizationTemplateController extends Controller
{
    public function edit(Organization $organization): View
    {
        $this->authorize('manageBuilder', $organization);

        $templates = Template::where('is_active', true)
            // is_public excludes templates meant only as an organization-creation picker option
            // (e.g. "Halaman Kosong" - see BlankTemplateSeeder). Switching an existing,
            // already-designed organization onto a blank template would just discard everything
            // it has for nothing - that option belongs at creation time only, not here.
            ->where('is_public', true)
            ->orderBy('is_exclusive')
            ->orderBy('name')
            ->get();

        return view('organizations.template.edit', [
            'organization' => $organization,
            'templates' => $templates,
        ]);
    }

    public function update(Request $request, Organization $organization): RedirectResponse
    {
        $this->authorize('manageBuilder', $organization);

        $validated = $request->validate([
            'template_id' => ['required', 'integer', 'exists:templates,id'],
        ]);

        $template = Template::findOrFail($validated['template_id']);

        if ($template->id === $organization->template_id) {
            return redirect()
                ->route('organizations.template.edit', $organization)
                ->with('status', 'Organisasi ini sudah menggunakan template tersebut.');
        }

        $organization->replaceTemplateKeepingContent($template);

        $status = 'Template berhasil diganti. Section dengan jenis yang sama mempertahankan isi sebelumnya; section lain mengikuti template baru.';

        // Publish is gated on the widgets/variants actually used (Organization::planViolations()),
        // not on the template label itself - an is_exclusive template built entirely from
        // non-exclusive widgets publishes fine on any plan. This is just a heads-up that the
        // template is *labelled* exclusive, since some of its widgets/variants may require one.
        if ($template->is_exclusive && ! $organization->canUseExclusiveTemplates()) {
            $status .= ' Template ini berlabel eksklusif - sebagian widget/tampilannya mungkin memerlukan paket Infak Eksklusif saat dipublikasikan.';
        }

        return redirect()
            ->route('organizations.show', $organization)
            ->with('status', $status);
    }
}
