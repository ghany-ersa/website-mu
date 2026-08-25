<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\OrganizationPage;
use Illuminate\View\View;

class OrganizationBuilderController extends Controller
{
    /**
     * Show the page builder for an organization's page, ensuring it owns a home page
     * (cloned from its template, or a blank "Beranda") on first visit.
     */
    public function edit(Organization $organization, ?OrganizationPage $page = null): View
    {
        $this->authorize('update', $organization);

        $organization->ensureHomePageExists();
        $organization->load('pages.sections');

        $currentPage = $page ?? $organization->pages->firstWhere('is_home', true) ?? $organization->pages->first();

        return view('organizations.builder.edit', [
            'organization' => $organization,
            'pages' => $organization->pages,
            'currentPage' => $currentPage,
            'sectionRegistry' => config('page-builder.sections'),
        ]);
    }

    /**
     * Render a page's sections as a standalone HTML document, in the organization's own
     * brand colors (see Organization::primaryColor()/secondaryColor()). Loaded into the
     * builder's canvas <iframe> — isolating it in its own document (rather than including
     * it inline in the builder page) is what lets it use different Tailwind theme colors
     * than the builder chrome around it, which stays platform-branded.
     */
    public function canvas(Organization $organization, OrganizationPage $page): View
    {
        $this->authorize('update', $organization);

        $page->load('sections');

        return view('organizations.builder.canvas', [
            'organization' => $organization,
            'page' => $page,
        ]);
    }

    /**
     * Render a single section type with its registry default content, in the organization's
     * brand colors — used as the thumbnail preview in the "Tambah Section" dropdown
     * (organizations/builder/edit.blade.php) so a user can see what a section looks like
     * before adding it, instead of picking blind from a label-only list.
     */
    public function sectionPreview(Organization $organization, OrganizationPage $page, string $key): View
    {
        $this->authorize('update', $organization);

        abort_unless(array_key_exists($key, config('page-builder.sections')), 404);

        $content = config("page-builder.sections.{$key}.defaults", []);
        foreach ($content as $field => $value) {
            if (is_string($value)) {
                $content[$field] = str_replace('{org_name}', $organization->name, $value);
            }
        }

        return view('organizations.builder.section-preview', [
            // Passed as `brand`, not `organization` — section partials branch on
            // isset($organization) to decide whether to pull live DB data (posts, officers,
            // programs, ...) instead of their built-in sample content (see the view's own
            // comment below). @include inherits this view's whole data array, so naming it
            // `organization` here would leak into the partial and blank out the preview for
            // a brand-new org with no such data yet.
            'brand' => $organization,
            'key' => $key,
            'section' => ['content' => $content],
        ]);
    }
}
