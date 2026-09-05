<?php

namespace App\Http\Controllers;

use App\Enums\OrganizationStatus;
use App\Enums\PublishStatus;
use App\Models\Agenda;
use App\Models\Announcement;
use App\Models\DonationProgram;
use App\Models\Organization;
use App\Models\OrganizationPage;
use Illuminate\View\View;

class OrganizationSiteController extends Controller
{
    /**
     * Render an organization's published site at its subdomain. Unauthenticated and
     * unauthorized by design - once Published, the site is public. The status filter
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
     * that subdomain - for owners/admins checking a page (published or not) from the main
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

    /**
     * Render a non-home page of an organization's published site at its subdomain, by page
     * slug - e.g. {slug}.{domain}/donasi. Sits behind the other tenant routes (berita/,
     * pengumuman/, agenda/) in routes/web.php so this catch-all {page_slug} segment never
     * shadows them. Gated the same way show() is: Organization::status is the single source
     * of truth for whether a site is public at all (see OrganizationPage::published_at's
     * doc comment in the builder view for why per-page publish state was deliberately
     * removed) - any page belonging to a Published organization is public.
     */
    public function showPage(string $organization_slug, string $page_slug): View
    {
        $organization = $this->publishedOrganization($organization_slug);

        $organization->load('pages.sections');
        $page = $organization->pages->firstWhere('slug', $page_slug);
        abort_if($page === null, 404);

        return view('organizations.public.show', [
            'organization' => $organization,
            'page' => $page,
        ]);
    }

    /**
     * Preview one donation program's detail page from the main app domain, the same way
     * preview() does for builder pages - without it the only way to reach this page is the
     * tenant subdomain, which isn't routable under `php artisan serve` locally, so neither an
     * owner checking their site nor a developer could ever see it.
     */
    public function previewDonationProgram(Organization $organization, DonationProgram $program): View
    {
        $this->authorize('update', $organization);
        abort_unless($program->organization_id === $organization->id, 404);

        $organization->load('pages');

        return view('organizations.public.donation-program', [
            'organization' => $organization,
            'program' => $program->load('transactions'),
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
     * Public detail page for one donation program, resolved by its per-organization slug
     * (donation_programs has a unique(['organization_id','slug']), so the slug is only unique
     * within a tenant - hence the explicit where() rather than route-model binding on slug).
     *
     * Unlike posts/agendas/announcements there's no draft/published state on a program: an
     * organization only creates one when it wants to collect for it, so belonging to this
     * published organization is the whole gate.
     */
    public function donationProgram(string $organization_slug, string $program_slug): View
    {
        $organization = $this->publishedOrganization($organization_slug);

        $program = $organization->donationPrograms()
            ->with('transactions')
            ->where('slug', $program_slug)
            ->firstOrFail();

        return view('organizations.public.donation-program', [
            'organization' => $organization,
            'program' => $program,
        ]);
    }

    /**
     * Shared published-organization lookup - status filter lives here so an
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
