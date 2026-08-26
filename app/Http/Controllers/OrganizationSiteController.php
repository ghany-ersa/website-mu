<?php

namespace App\Http\Controllers;

use App\Enums\OrganizationStatus;
use App\Enums\PublishStatus;
use App\Models\Agenda;
use App\Models\Announcement;
use App\Models\Organization;
use App\Models\OrganizationPage;
use Illuminate\View\View;

class OrganizationSiteController extends Controller
{
    /**
     * Render an organization's published site at its subdomain. Unauthenticated and
     * unauthorized by design — once Published, the site is public. The status filter
     * lives in the lookup query itself so a Draft organization's subdomain 404s
     * identically to one that was never claimed, instead of leaking its existence.
     */
    public function show(string $organization_slug): View
    {
        $organization = $this->publishedOrganization($organization_slug);

        $organization->load('pages.sections');
        $page = $organization->pages->firstWhere('is_home', true) ?? $organization->pages->first();
        abort_if($page === null, 404);

        return view('organizations.public.show', [
            'organization' => $organization,
            'page' => $page,
        ]);
    }

    /**
     * Preview a page exactly as it renders on the tenant subdomain, without going through
     * that subdomain — for owners/admins checking a page (published or not) from the main
     * app domain, e.g. in local dev where wildcard subdomains aren't routable. Reuses the
     * same public view as show(), so this stays a faithful preview rather than a
     * lookalike that can drift from the real tenant output.
     */
    public function preview(Organization $organization, ?OrganizationPage $page = null): View
    {
        $this->authorize('update', $organization);

        $organization->load('pages.sections');
        $currentPage = $page ?? $organization->pages->firstWhere('is_home', true) ?? $organization->pages->first();
        abort_if($currentPage === null, 404);

        return view('organizations.public.show', [
            'organization' => $organization,
            'page' => $currentPage,
        ]);
    }

    public function post(string $organization_slug, string $post_slug): View
    {
        $organization = $this->publishedOrganization($organization_slug);

        $post = $organization->posts()
            ->published()
            ->where('slug', $post_slug)
            ->firstOrFail();

        return view('organizations.public.post', [
            'organization' => $organization,
            'post' => $post,
        ]);
    }

    public function announcement(string $organization_slug, Announcement $announcement): View
    {
        $organization = $this->publishedOrganization($organization_slug);

        abort_unless($announcement->organization_id === $organization->id, 404);
        abort_unless($announcement->status === PublishStatus::Published, 404);

        return view('organizations.public.announcement', [
            'organization' => $organization,
            'announcement' => $announcement,
        ]);
    }

    public function agenda(string $organization_slug, Agenda $agenda): View
    {
        $organization = $this->publishedOrganization($organization_slug);

        abort_unless($agenda->organization_id === $organization->id, 404);
        abort_unless($agenda->status === PublishStatus::Published, 404);

        return view('organizations.public.agenda', [
            'organization' => $organization,
            'agenda' => $agenda,
        ]);
    }

    /**
     * Shared published-organization lookup — status filter lives here so an
     * unpublished organization's subdomain/detail pages 404 identically to
     * one that was never claimed, instead of leaking its existence.
     */
    private function publishedOrganization(string $organization_slug): Organization
    {
        return Organization::where('slug', $organization_slug)
            ->where('status', OrganizationStatus::Published)
            ->firstOrFail();
    }
}
