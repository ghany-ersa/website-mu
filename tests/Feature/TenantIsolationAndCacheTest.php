<?php

namespace Tests\Feature;

use App\Enums\OrganizationStatus;
use App\Enums\PublishStatus;
use App\Models\Organization;
use App\Models\OrganizationPage;
use App\Models\Post;
use App\Models\User;
use App\Services\TenantPageCache;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * Two properties of the public tenant site that a QA pass has to treat as non-negotiable:
 *
 *  - One tenant's content is never reachable from another tenant's subdomain. Every detail
 *    page takes a slug/id from the URL, so an id belonging to a different organization is the
 *    obvious way that breaks.
 *  - A CMS edit shows up on the public site. Tenant pages are cached as fully rendered HTML
 *    (TenantPageCache), invalidated by a version counter rather than by deleting keys, so a
 *    model that forgets to bump the version would serve stale content for up to six hours.
 */
class TenantIsolationAndCacheTest extends TestCase
{
    use RefreshDatabase;

    private function tenantUrl(Organization $organization, string $path): string
    {
        return 'http://'.$organization->slug.'.'.config('tenancy.domain').$path;
    }

    /**
     * A published organization with a home page carrying one section - the tenant home route
     * 404s without a page to render, so the page is part of the fixture rather than something
     * each test remembers to add.
     */
    private function publishedOrganization(string $slug): Organization
    {
        $organization = Organization::factory()->create([
            'slug' => $slug,
            'status' => OrganizationStatus::Published,
        ]);

        OrganizationPage::factory()
            ->create(['organization_id' => $organization->id, 'slug' => 'home', 'is_home' => true])
            ->sections()
            ->create(['key' => 'hero', 'content' => ['headline' => 'Selamat Datang'], 'order' => 0]);

        return $organization;
    }

    private function publishedPost(Organization $organization, string $slug, string $title = 'Judul Berita'): Post
    {
        return Post::factory()->create([
            'organization_id' => $organization->id,
            'slug' => $slug,
            'title' => $title,
            'status' => PublishStatus::Published,
            'published_at' => now(),
        ]);
    }

    // ------------------------------------------------------------- isolation

    public function test_a_post_is_not_reachable_from_another_tenants_subdomain(): void
    {
        $owner = $this->publishedOrganization('pemilik-berita');
        $neighbour = $this->publishedOrganization('tetangga');
        $this->publishedPost($owner, 'berita-rahasia');

        $this->get($this->tenantUrl($owner, '/berita/berita-rahasia'))->assertOk();
        $this->get($this->tenantUrl($neighbour, '/berita/berita-rahasia'))->assertNotFound();
    }

    /**
     * The same slug existing on both tenants must resolve to each tenant's own record - the
     * failure mode here isn't a 404 but silently serving the neighbour's article.
     */
    public function test_an_identical_slug_on_two_tenants_resolves_to_each_ones_own_post(): void
    {
        $first = $this->publishedOrganization('ranting-satu');
        $second = $this->publishedOrganization('ranting-dua');

        $this->publishedPost($first, 'kegiatan', 'Kegiatan Ranting Satu');
        $this->publishedPost($second, 'kegiatan', 'Kegiatan Ranting Dua');

        $this->get($this->tenantUrl($first, '/berita/kegiatan'))
            ->assertOk()
            ->assertSee('Kegiatan Ranting Satu')
            ->assertDontSee('Kegiatan Ranting Dua');

        $this->get($this->tenantUrl($second, '/berita/kegiatan'))
            ->assertOk()
            ->assertSee('Kegiatan Ranting Dua')
            ->assertDontSee('Kegiatan Ranting Satu');
    }

    public function test_a_draft_organizations_detail_pages_are_not_reachable(): void
    {
        $organization = Organization::factory()->create(['slug' => 'masih-draf']);
        $this->publishedPost($organization, 'berita-draf');

        $this->get($this->tenantUrl($organization, '/'))->assertNotFound();
        $this->get($this->tenantUrl($organization, '/berita/berita-draf'))->assertNotFound();
    }

    /**
     * Sandbox organizations back the admin template designer - they hold real-looking content
     * and are never meant to be publicly addressable, even when flagged published.
     */
    public function test_a_sandbox_organization_is_not_publicly_reachable(): void
    {
        $sandbox = Organization::factory()->create([
            'slug' => 'sandbox-tersembunyi',
            'status' => OrganizationStatus::Published,
            'is_sandbox' => true,
        ]);

        $this->get($this->tenantUrl($sandbox, '/'))->assertNotFound();
    }

    public function test_an_unknown_subdomain_404s(): void
    {
        $this->get('http://tidak-ada.'.config('tenancy.domain').'/')->assertNotFound();
    }

    /**
     * The tenant route group runs without the `web` middleware (no session, no CSRF) because
     * the pages are pure reads. A visitor must never need to authenticate.
     */
    public function test_the_public_site_needs_no_session(): void
    {
        $organization = $this->publishedOrganization('tanpa-sesi');

        $this->get($this->tenantUrl($organization, '/'))
            ->assertOk()
            ->assertCookieMissing('laravel_session');
    }

    // ----------------------------------------------------------------- cache

    public function test_a_rendered_tenant_page_is_cached(): void
    {
        $organization = $this->publishedOrganization('uji-cache');

        $this->get($this->tenantUrl($organization, '/'))->assertOk();

        // The version isn't 1 here - creating the fixture's page and section already bumped it -
        // so read the current version rather than hardcoding one.
        $version = (int) Cache::get("tenant-page-version:{$organization->id}");

        $this->assertNotNull(
            Cache::get("tenant-page:{$organization->id}:home:v{$version}"),
            'The home page should have been stored under the organization\'s current cache version.'
        );
    }

    /**
     * The invalidation contract: publishing a post bumps the organization's cache version, so
     * the next request renders fresh HTML rather than serving the previously cached page.
     */
    public function test_publishing_a_post_invalidates_the_cached_home_page(): void
    {
        $organization = $this->publishedOrganization('uji-invalidasi');

        $this->get($this->tenantUrl($organization, '/'))->assertOk();
        $versionBefore = Cache::get("tenant-page-version:{$organization->id}");

        $this->publishedPost($organization, 'berita-baru', 'Berita Yang Baru Terbit');

        $this->assertGreaterThan(
            $versionBefore,
            Cache::get("tenant-page-version:{$organization->id}"),
            'Saving a post must bump the tenant page cache version.'
        );
    }

    public function test_a_cache_bump_only_affects_its_own_organization(): void
    {
        $first = $this->publishedOrganization('org-pertama');
        $second = $this->publishedOrganization('org-kedua');

        $this->get($this->tenantUrl($first, '/'))->assertOk();
        $this->get($this->tenantUrl($second, '/'))->assertOk();

        $secondVersionBefore = Cache::get("tenant-page-version:{$second->id}");

        $this->publishedPost($first, 'hanya-milik-pertama');

        $this->assertSame(
            $secondVersionBefore,
            Cache::get("tenant-page-version:{$second->id}"),
            'One tenant\'s CMS write must not invalidate another tenant\'s cache.'
        );
    }

    /**
     * bump() has to work on a store that doesn't auto-vivify a missing key (file/database),
     * which is why it seeds with add() before incrementing - worth pinning since a regression
     * would only show up on production's cache store, never on the array store used in tests.
     */
    public function test_bumping_a_never_cached_organization_starts_the_counter_safely(): void
    {
        TenantPageCache::bump(4242);

        $this->assertSame(2, (int) Cache::get('tenant-page-version:4242'));
    }

    public function test_remember_renders_once_and_serves_the_cached_copy_afterwards(): void
    {
        $organization = $this->publishedOrganization('uji-remember');
        $renders = 0;

        $callback = function () use (&$renders) {
            $renders++;

            return '<p>dirender</p>';
        };

        $first = TenantPageCache::remember($organization, 'halaman-uji', $callback);
        $second = TenantPageCache::remember($organization, 'halaman-uji', $callback);

        $this->assertSame('<p>dirender</p>', $first);
        $this->assertSame($first, $second);
        $this->assertSame(1, $renders, 'The second call should have been served from cache.');
    }

    public function test_a_bump_makes_remember_render_again(): void
    {
        $organization = $this->publishedOrganization('uji-bump-render');
        $renders = 0;

        $callback = function () use (&$renders) {
            $renders++;

            return "render-{$renders}";
        };

        TenantPageCache::remember($organization, 'halaman-uji', $callback);
        TenantPageCache::bump($organization->id);
        $afterBump = TenantPageCache::remember($organization, 'halaman-uji', $callback);

        $this->assertSame('render-2', $afterBump);
        $this->assertSame(2, $renders);
    }

    // ------------------------------------------------------------- load more

    public function test_load_more_endpoints_are_scoped_to_their_own_tenant(): void
    {
        $owner = $this->publishedOrganization('pemilik-loadmore');
        $this->publishedPost($owner, 'berita-satu', 'Berita Milik Pemilik');

        // `variant` picks which partial renders the batch; the endpoint 404s without a known one.
        $query = '/berita-lebih-banyak?variant=standar&offset=0&limit=6';

        $this->getJson($this->tenantUrl($owner, $query))
            ->assertOk()
            ->assertJsonStructure(['html', 'nextOffset', 'hasMore']);

        $neighbour = $this->publishedOrganization('tetangga-loadmore');
        $neighbourResponse = $this->getJson($this->tenantUrl($neighbour, $query))->assertOk();

        $this->assertStringNotContainsString('Berita Milik Pemilik', $neighbourResponse->getContent());
    }

    // ------------------------------------------------------ member isolation

    public function test_a_member_of_one_organization_cannot_manage_another(): void
    {
        $userA = User::factory()->create();
        $organizationA = Organization::factory()->withOwner($userA)->create();
        $organizationB = Organization::factory()->withOwner()->create();

        // Routes guarded purely by OrganizationPolicy - so a 403 here is the membership check
        // firing, not some earlier precondition (organizations.posts.index, for instance, 404s
        // first when the org has no daftar-berita section, which would prove nothing).
        $this->actingAs($userA)->get(route('organizations.builder.edit', $organizationB))->assertForbidden();
        $this->actingAs($userA)->get(route('organizations.brand.edit', $organizationB))->assertForbidden();
        $this->actingAs($userA)->get(route('organizations.plan.edit', $organizationB))->assertForbidden();
        $this->actingAs($userA)->get(route('organizations.edit.edit', $organizationB))->assertForbidden();
        $this->actingAs($userA)->get(route('organizations.show', $organizationB))->assertForbidden();
        $this->actingAs($userA)->get(route('organizations.media.index', $organizationB))->assertForbidden();

        // ...while their own organization stays reachable, so the assertions above aren't
        // passing for some unrelated reason.
        $this->actingAs($userA)->get(route('organizations.builder.edit', $organizationA))->assertOk();
    }
}
