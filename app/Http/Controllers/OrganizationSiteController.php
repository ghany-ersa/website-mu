<?php

namespace App\Http\Controllers;

use App\Enums\OrganizationStatus;
use App\Enums\PublishStatus;
use App\Models\Agenda;
use App\Models\Announcement;
use App\Models\DonationProgram;
use App\Models\Organization;
use App\Models\OrganizationPage;
use App\Services\TenantPageCache;
use Illuminate\Http\Response;
use Illuminate\View\View;

class OrganizationSiteController extends Controller
{
    /**
     * Render an organization's published site at its subdomain. Unauthenticated and
     * unauthorized by design - once Published, the site is public. The status filter
     * lives in the lookup query itself so a Draft organization's subdomain 404s
     * identically to one that was never claimed, instead of leaking its existence.
     *
     * The rendered HTML itself is cached (see TenantPageCache) - unlike preview()/
     * previewDonationProgram(), which stay uncached so an owner editing the builder always
     * sees their latest save immediately. A cache lookup still costs the readonly-connection
     * lookup in publishedOrganization() below to resolve the organization and its 404/draft
     * gate; only the expensive part (loading pages/sections/CMS content and rendering every
     * section partial) is skipped on a hit.
     */
    public function show(string $organization_slug): Response
    {
        $organization = $this->publishedOrganization($organization_slug);

        $html = TenantPageCache::remember($organization, 'home', function () use ($organization) {
            $this->loadForRender($organization, ['pages.sections', 'limitOverrides', 'planChangeRequests']);
            $page = $organization->pages->firstWhere('is_home', true) ?? $organization->pages->first();
            abort_if($page === null, 404);

            return view('organizations.public.show', [
                'organization' => $organization,
                'page' => $page,
            ])->render();
        });

        return response($html);
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
    public function showPage(string $organization_slug, string $page_slug): Response
    {
        $organization = $this->publishedOrganization($organization_slug);

        $html = TenantPageCache::remember($organization, "page:{$page_slug}", function () use ($organization, $page_slug) {
            $this->loadForRender($organization, ['pages.sections', 'limitOverrides', 'planChangeRequests']);
            $page = $organization->pages->firstWhere('slug', $page_slug);
            abort_if($page === null, 404);

            return view('organizations.public.show', [
                'organization' => $organization,
                'page' => $page,
            ])->render();
        });

        return response($html);
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

    public function post(string $organization_slug, string $post_slug): Response
    {
        $organization = $this->publishedOrganization($organization_slug);

        $html = TenantPageCache::remember($organization, "post:{$post_slug}", function () use ($organization, $post_slug) {
            $this->loadForRender($organization, ['limitOverrides', 'planChangeRequests', 'pages.sections']);

            $post = $organization->posts()
                ->published()
                ->where('slug', $post_slug)
                ->firstOrFail();

            return view('organizations.public.post', [
                'organization' => $organization,
                'post' => $post,
            ])->render();
        });

        return response($html);
    }

    public function announcement(string $organization_slug, Announcement $announcement): Response
    {
        $organization = $this->publishedOrganization($organization_slug);

        abort_unless($announcement->organization_id === $organization->id, 404);
        abort_unless($announcement->status === PublishStatus::Published, 404);

        $html = TenantPageCache::remember($organization, "announcement:{$announcement->id}", function () use ($organization, $announcement) {
            $this->loadForRender($organization, ['limitOverrides', 'planChangeRequests', 'pages.sections']);

            return view('organizations.public.announcement', [
                'organization' => $organization,
                'announcement' => $announcement,
            ])->render();
        });

        return response($html);
    }

    public function agenda(string $organization_slug, Agenda $agenda): Response
    {
        $organization = $this->publishedOrganization($organization_slug);

        abort_unless($agenda->organization_id === $organization->id, 404);
        abort_unless($agenda->status === PublishStatus::Published, 404);

        $html = TenantPageCache::remember($organization, "agenda:{$agenda->id}", function () use ($organization, $agenda) {
            $this->loadForRender($organization, ['limitOverrides', 'planChangeRequests', 'pages.sections']);

            return view('organizations.public.agenda', [
                'organization' => $organization,
                'agenda' => $agenda,
            ])->render();
        });

        return response($html);
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
    public function donationProgram(string $organization_slug, string $program_slug): Response
    {
        $organization = $this->publishedOrganization($organization_slug);

        $html = TenantPageCache::remember($organization, "donation:{$program_slug}", function () use ($organization, $program_slug) {
            $this->loadForRender($organization, ['limitOverrides', 'planChangeRequests', 'pages']);

            $program = $organization->donationPrograms()
                ->with('transactions')
                ->where('slug', $program_slug)
                ->firstOrFail();

            return view('organizations.public.donation-program', [
                'organization' => $organization,
                'program' => $program,
            ])->render();
        });

        return response($html);
    }

    /**
     * Shared published-organization lookup - status filter lives here so an
     * unpublished organization's subdomain/detail pages 404 identically to
     * one that was never claimed, instead of leaking its existence.
     *
     * Deliberately minimal (no eager loading): every caller needs this row just to resolve the
     * slug and check the published gate before even touching TenantPageCache, so on a cache
     * hit nothing else about the organization is used at all. See loadForRender() for the
     * heavier eager-loading, done only inside a cache-miss closure.
     */
    private function publishedOrganization(string $organization_slug): Organization
    {
        return Organization::where('slug', $organization_slug)
            ->where('status', OrganizationStatus::Published)
            ->firstOrFail();
    }

    /**
     * Eager-loads what rendering (as opposed to just resolving) an organization's tenant page
     * needs, run only on a TenantPageCache cache miss - see publishedOrganization()'s doc
     * comment for why that lookup itself stays bare.
     *
     * 'plan.limits' lets PlanLimitService::effectiveLimitForFreshOrganization() (used by
     * Organization::planViolations(), rendered on every public tenant page) read the plan off
     * this instance instead of re-querying plans/plan_limits per limit key. Safe here
     * specifically because every caller fetches the organization fresh and never mutates its
     * plan_id before rendering - unlike preview()/previewDonationProgram(), which resolve their
     * Organization via route-model binding and deliberately don't get this eager load (see
     * PlanLimitService::effectivePlan()'s doc comment for why a just-mutated plan_id needs a
     * fresh reload rather than the cached relation).
     *
     * @param  array<int, string>  $relations  Additional relations beyond 'plan.limits', varying
     *                                          per page type (e.g. 'pages.sections' for a builder
     *                                          page, just 'pages' for a donation program detail).
     */
    private function loadForRender(Organization $organization, array $relations): void
    {
        $organization->load([...$relations, 'plan.limits']);
    }
}
