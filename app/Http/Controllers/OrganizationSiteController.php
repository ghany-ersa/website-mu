<?php

namespace App\Http\Controllers;

use App\Enums\PublishStatus;
use App\Http\Middleware\ResolveTenantOrganization;
use App\Models\Agenda;
use App\Models\Announcement;
use App\Models\DonationProgram;
use App\Models\Organization;
use App\Models\OrganizationPage;
use App\Services\TenantPageCache;
use App\Services\TenantUrl;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\View\View;

class OrganizationSiteController extends Controller
{
    /**
     * Render an organization's published site at its subdomain. Unauthenticated and
     * unauthorized by design - once Published, the site is public. The published/sandbox
     * gate runs before this action, in ResolveTenantOrganization (the 'tenant' middleware
     * group), so a Draft organization's subdomain 404s identically to one never claimed.
     *
     * The rendered HTML itself is cached (see TenantPageCache) - unlike preview()/
     * previewDonationProgram(), which stay uncached so an owner editing the builder always
     * sees their latest save immediately. A cache lookup still costs the readonly-connection
     * lookup in ResolveTenantOrganization to resolve the organization and its 404/draft
     * gate; only the expensive part (loading pages/sections/CMS content and rendering every
     * section partial) is skipped on a hit.
     */
    public function show(Request $request): Response
    {
        $organization = ResolveTenantOrganization::from($request);

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
    public function showPage(Request $request, string $page_slug): Response
    {
        $organization = ResolveTenantOrganization::from($request);

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
     * "Muat Lebih Banyak" batch endpoint for daftar-berita's ringkas/standar variants (see the
     * Alpine `loadMore()` component inline in those partials): fetches exactly the next `limit`
     * published posts after `offset`, in the same order and shape the section's own initial
     * query used, and renders them through the same card partial - never the full list, so a
     * long-running news portal's list never re-fetches everything it already fetched on an
     * earlier click. Deliberately uncached (unlike show()/showPage()): TenantPageCache stores
     * one fixed render per page, with no room for per-offset variants, and this response is
     * cheap enough (one bounded SELECT) that caching it would add complexity for no real gain.
     *
     * `hasMore` comes from fetching one extra row (`take($limit + 1)`) instead of a separate
     * `count()` query over every matching post - that count would only get more expensive as
     * the portal accumulates posts, to answer a question this single indexed, LIMITed SELECT
     * already has the answer to (see ringkas/standar.blade.php's matching comment for their own
     * initial-render use of the same trick).
     */
    public function loadMoreBerita(Request $request): JsonResponse
    {
        $organization = ResolveTenantOrganization::from($request);

        $variant = $request->query('variant');
        abort_unless(in_array($variant, ['ringkas', 'standar'], true), 404);

        $categoryFilter = $request->query('category_filter') ?: null;
        $offset = max(0, (int) $request->query('offset', 0));
        // Clamped the same way a builder-authored `limit` would be malformed into 0 elsewhere
        // on this section - see daftar-berita/ringkas|standar.blade.php's own `limit` handling.
        $limit = max(1, min(50, (int) $request->query('limit', 6)));

        $items = $organization->posts()->published()
            ->when($categoryFilter, fn ($q) => $q->where('category', $categoryFilter))
            ->skip($offset)->take($limit + 1)->get()->map(fn ($post) => [
                'title' => $post->title,
                'image' => $post->image,
                'category' => $post->category,
                'date' => $post->published_at?->translatedFormat('d M Y'),
                'excerpt' => Str::limit(strip_tags($post->body), 140),
                'url' => TenantUrl::route($organization, 'tenant.posts.show', ['post_slug' => $post->slug]) ?? '#',
            ]);

        $hasMore = $items->count() > $limit;
        $items = $hasMore ? $items->take($limit) : $items;

        $html = view("templates.sections.daftar-berita._items-{$variant}", [
            'items' => $items,
            'startIndex' => $offset,
        ])->render();

        return response()->json([
            'html' => $html,
            'nextOffset' => $offset + $items->count(),
            'hasMore' => $hasMore,
        ]);
    }

    /**
     * "Muat Lebih Banyak" batch endpoint for galeri/standar (see the Alpine `loadMore()`
     * component inline in that partial): fetches exactly the next `limit` gallery photos after
     * `offset`, same order as the section's own initial query. Returns plain {image, caption}
     * data rather than rendered HTML (contrast loadMoreBerita()): the grid is driven by an
     * Alpine `photos` array that the shared lightbox component also indexes into, so a new
     * batch has to land in that same reactive array, not be appended as inert markup next to
     * it - see galeri/standar.blade.php's doc comment.
     *
     * `hasMore` comes from fetching one extra row (`take($limit + 1)`) instead of a separate
     * `count()` query, the same trick loadMoreBerita() uses - see that method's doc comment.
     */
    public function loadMoreGaleri(Request $request): JsonResponse
    {
        $organization = ResolveTenantOrganization::from($request);

        $offset = max(0, (int) $request->query('offset', 0));
        // Clamped the same way a builder-authored `limit` would be malformed into 0 elsewhere
        // on this section - see galeri/standar.blade.php's own `limit` handling.
        $limit = max(1, min(50, (int) $request->query('limit', 4)));

        $photos = $organization->photos()
            ->skip($offset)->take($limit + 1)->get()
            ->map(fn ($photo) => ['image' => $photo->url, 'caption' => $photo->label]);

        $hasMore = $photos->count() > $limit;
        $photos = $hasMore ? $photos->take($limit) : $photos;

        return response()->json([
            'photos' => $photos->values(),
            'nextOffset' => $offset + $photos->count(),
            'hasMore' => $hasMore,
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

    public function post(Request $request, string $post_slug): Response
    {
        $organization = ResolveTenantOrganization::from($request);

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

    public function announcement(Request $request, Announcement $announcement): Response
    {
        $organization = ResolveTenantOrganization::from($request);

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

    public function agenda(Request $request, Agenda $agenda): Response
    {
        $organization = ResolveTenantOrganization::from($request);

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
    public function donationProgram(Request $request, string $program_slug): Response
    {
        $organization = ResolveTenantOrganization::from($request);

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
     * A pupil's public profile: their details plus every achievement they're credited with.
     * Inactive pupils 404 rather than rendering, so hiding one from the CMS also takes its
     * profile page down.
     */
    public function student(Request $request, string $student_slug): Response
    {
        $organization = ResolveTenantOrganization::from($request);

        $html = TenantPageCache::remember($organization, "student:{$student_slug}", function () use ($organization, $student_slug) {
            $this->loadForRender($organization, ['limitOverrides', 'planChangeRequests', 'pages']);

            $student = $organization->students()
                ->active()
                ->with(['achievements' => fn ($query) => $query->active()->orderByDesc('year')])
                ->where('slug', $student_slug)
                ->firstOrFail();

            return view('organizations.public.student', [
                'organization' => $organization,
                'student' => $student,
            ])->render();
        });

        return response($html);
    }

    /**
     * Eager-loads what rendering (as opposed to just resolving) an organization's tenant page
     * needs, run only on a TenantPageCache cache miss - see ResolveTenantOrganization's doc
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
     *                                         per page type (e.g. 'pages.sections' for a builder
     *                                         page, just 'pages' for a donation program detail).
     */
    private function loadForRender(Organization $organization, array $relations): void
    {
        $organization->load([...$relations, 'plan.limits']);
    }
}
