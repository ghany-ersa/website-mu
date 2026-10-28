<?php

namespace Tests\Feature;

use App\Enums\OrganizationStatus;
use App\Enums\PublishStatus;
use App\Models\Organization;
use App\Models\OrganizationDomain;
use App\Models\Post;
use App\Models\User;
use App\Services\CustomDomainCheck;
use App\Services\TenantUrl;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Custom domains end to end: serving, the subdomain/www redirects, isolation from the main
 * app's own hosts, and the admin register -> check -> activate flow. phpunit.xml switches
 * CUSTOM_DOMAINS_ENABLED on for the whole suite, so every other test also runs with the
 * catch-any-host route group registered - which is what guards the main app's routes from
 * being swallowed by it.
 */
class CustomDomainTest extends TestCase
{
    use RefreshDatabase;

    private function organizationWithDomain(bool $active = true, string $hostname = 'pcmambulu.or.id'): Organization
    {
        $organization = Organization::factory()->create([
            'slug' => 'pcm-ambulu',
            'status' => OrganizationStatus::Published,
        ]);
        $organization->pages()->create(['name' => 'Beranda', 'slug' => 'beranda', 'is_home' => true, 'order' => 0]);
        $organization->customDomain()->create(['hostname' => $hostname, 'is_active' => $active]);

        return $organization;
    }

    public function test_an_active_custom_domain_serves_the_organization_site(): void
    {
        $organization = $this->organizationWithDomain();

        $this->get('http://pcmambulu.or.id/')->assertOk()->assertSee($organization->name);
    }

    public function test_links_on_the_site_point_at_the_custom_domain(): void
    {
        $organization = $this->organizationWithDomain();
        Post::factory()->create([
            'organization_id' => $organization->id,
            'slug' => 'berita-uji',
            'status' => PublishStatus::Published,
            'published_at' => now(),
        ]);

        $this->get('http://pcmambulu.or.id/berita/berita-uji')->assertOk();
        $this->assertSame(
            'http://pcmambulu.or.id/berita/berita-uji',
            TenantUrl::route($organization->fresh(), 'tenant.posts.show', ['post_slug' => 'berita-uji']),
        );
    }

    public function test_the_subdomain_redirects_to_the_active_custom_domain_keeping_the_path(): void
    {
        $this->organizationWithDomain();

        $this->get('http://pcm-ambulu.website-mu.test/berita/berita-uji?ref=wa')
            ->assertStatus(301)
            ->assertRedirect('http://pcmambulu.or.id/berita/berita-uji?ref=wa');
    }

    public function test_the_www_counterpart_redirects_to_the_registered_hostname(): void
    {
        $this->organizationWithDomain();

        $this->get('http://www.pcmambulu.or.id/agenda')
            ->assertStatus(301)
            ->assertRedirect('http://pcmambulu.or.id/agenda');
    }

    public function test_an_inactive_domain_is_not_served_and_the_subdomain_keeps_working(): void
    {
        $organization = $this->organizationWithDomain(active: false);

        $this->get('http://pcmambulu.or.id/')->assertNotFound();
        $this->get('http://pcm-ambulu.website-mu.test/')->assertOk()->assertSee($organization->name);
    }

    public function test_an_unknown_host_404s(): void
    {
        $this->get('http://domain-asing.com/')->assertNotFound();
    }

    public function test_an_active_domain_of_an_unpublished_organization_404s(): void
    {
        $organization = $this->organizationWithDomain();
        $organization->update(['status' => OrganizationStatus::Draft]);

        $this->get('http://pcmambulu.or.id/')->assertNotFound();
    }

    public function test_the_main_app_hosts_are_not_captured_by_the_custom_domain_group(): void
    {
        $this->get('http://localhost/')->assertOk()->assertSee('Platform Bikin Website Organisasi');
        $this->get('http://website-mu.test/sitemap.xml')->assertOk();
        $this->get('http://127.0.0.1/')->assertOk();
    }

    /**
     * Production runs `php artisan optimize`, which caches routes into Symfony's compiled
     * matcher - and that matcher tests "{host}.{path}" as one string, so a host pattern can
     * behave differently there than in the uncached matcher every other test uses. This is
     * the regression test for exactly that: website-mu.id/ being captured only when cached.
     */
    public function test_platform_hosts_stay_excluded_under_the_cached_route_matcher(): void
    {
        $compiled = app('router')->getRoutes()->toCompiledRouteCollection(app('router'), app());

        foreach (['http://website-mu.test/', 'http://website-mu.test/login', 'http://localhost/', 'http://127.0.0.1/', 'http://www.website-mu.test/'] as $url) {
            $this->assertStringStartsNotWith('custom-domain.', (string) $compiled->match(Request::create($url))->getName(), $url);
        }

        $this->assertSame('tenant.home', $compiled->match(Request::create('http://pcm-ambulu.website-mu.test/'))->getName());
        $this->assertSame('custom-domain.tenant.home', $compiled->match(Request::create('http://pcmambulu.or.id/'))->getName());
        $this->assertSame('custom-domain.tenant.posts.show', $compiled->match(Request::create('http://pcmambulu.or.id/berita/x'))->getName());
    }

    public function test_the_check_endpoint_answers_with_the_host_token_before_activation(): void
    {
        $this->organizationWithDomain(active: false);

        $this->get('http://pcmambulu.or.id/.well-known/website-mu-check')
            ->assertOk()
            ->assertSeeText(CustomDomainCheck::token('pcmambulu.or.id'));
    }

    public function test_admin_registers_a_domain_normalizing_what_was_pasted(): void
    {
        $organization = Organization::factory()->create();

        $this->actingAs(User::factory()->admin()->create())
            ->put(route('admin.organizations.domain.update', $organization), ['hostname' => 'https://PcmAmbulu.or.id/beranda'])
            ->assertRedirect(route('admin.organizations.show', $organization));

        $this->assertSame('pcmambulu.or.id', $organization->customDomain()->first()->hostname);
        $this->assertFalse($organization->customDomain()->first()->is_active);
    }

    public function test_admin_cannot_register_a_platform_host_or_a_taken_domain(): void
    {
        $this->organizationWithDomain();
        $other = Organization::factory()->create();
        $admin = User::factory()->admin()->create();

        foreach (['website-mu.test', 'apa.website-mu.test', 'www.pcmambulu.or.id', 'bukan domain'] as $hostname) {
            $this->actingAs($admin)
                ->put(route('admin.organizations.domain.update', $other), ['hostname' => $hostname])
                ->assertSessionHasErrors('hostname', null, 'domain');
        }

        $this->assertNull($other->customDomain()->first());
    }

    public function test_registering_is_refused_once_the_hosting_slots_are_used_up(): void
    {
        config(['tenancy.custom_domain_slots' => 1]);
        $this->organizationWithDomain();
        $other = Organization::factory()->create();

        $this->actingAs(User::factory()->admin()->create())
            ->put(route('admin.organizations.domain.update', $other), ['hostname' => 'pcaambulu.or.id'])
            ->assertSessionHasErrors('hostname', null, 'domain');

        $this->assertNull($other->customDomain()->first());
    }

    public function test_activation_requires_a_passing_check(): void
    {
        $organization = $this->organizationWithDomain(active: false);
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post(route('admin.organizations.domain.activate', $organization))
            ->assertSessionHasErrors('hostname', null, 'domain');
        $this->assertFalse($organization->customDomain()->first()->is_active);

        Http::fake(['https://pcmambulu.or.id/*' => Http::response(CustomDomainCheck::token('pcmambulu.or.id'))]);

        $this->actingAs($admin)->post(route('admin.organizations.domain.check', $organization));
        $this->assertTrue($organization->customDomain()->first()->last_check_ok);

        $this->actingAs($admin)->post(route('admin.organizations.domain.activate', $organization));
        $this->assertTrue($organization->customDomain()->first()->is_active);

        $this->get('http://pcm-ambulu.website-mu.test/')->assertRedirect('http://pcmambulu.or.id/');
    }

    public function test_a_check_against_some_other_server_fails(): void
    {
        $organization = $this->organizationWithDomain(active: false);
        Http::fake(['https://pcmambulu.or.id/*' => Http::response('halaman default cPanel')]);

        $this->actingAs(User::factory()->admin()->create())
            ->post(route('admin.organizations.domain.check', $organization));

        $domain = $organization->customDomain()->first();
        $this->assertFalse($domain->last_check_ok);
        $this->assertStringStartsWith('Gagal', $domain->last_check_result);
    }

    public function test_a_cloudflare_origin_error_explains_the_proxy_instead_of_the_document_root(): void
    {
        $organization = $this->organizationWithDomain(active: false);
        Http::fake(['https://pcmambulu.or.id/*' => Http::response('error code: 525', 525, ['Server' => 'cloudflare'])]);

        $this->actingAs(User::factory()->admin()->create())
            ->post(route('admin.organizations.domain.check', $organization));

        $domain = $organization->customDomain()->first();
        $this->assertFalse($domain->last_check_ok);
        $this->assertStringContainsString('DNS only', $domain->last_check_result);
    }

    public function test_changing_the_hostname_deactivates_it_until_rechecked(): void
    {
        $organization = $this->organizationWithDomain();
        $organization->customDomain->update(['last_check_ok' => true]);

        $this->actingAs(User::factory()->admin()->create())
            ->put(route('admin.organizations.domain.update', $organization), ['hostname' => 'pcmambulu.id']);

        $domain = $organization->customDomain()->first();
        $this->assertSame('pcmambulu.id', $domain->hostname);
        $this->assertFalse($domain->is_active);
        $this->assertNull($domain->last_check_ok);
    }

    public function test_activating_invalidates_cached_pages_still_linking_to_the_subdomain(): void
    {
        $organization = $this->organizationWithDomain(active: false);

        $this->get('http://pcm-ambulu.website-mu.test/')->assertOk();

        $organization->customDomain->update(['is_active' => true]);

        $this->get('http://pcmambulu.or.id/')->assertOk()->assertDontSee('pcm-ambulu.website-mu.test');
    }

    public function test_non_admins_cannot_manage_domains(): void
    {
        $organization = Organization::factory()->create();

        $this->actingAs(User::factory()->create())
            ->put(route('admin.organizations.domain.update', $organization), ['hostname' => 'pcmambulu.or.id'])
            ->assertForbidden();
    }

    public function test_nothing_moves_while_the_feature_is_switched_off(): void
    {
        $organization = $this->organizationWithDomain();
        config(['tenancy.custom_domains' => false]);

        $this->assertNull($organization->fresh()->activeCustomHostname());
        $this->get('http://pcm-ambulu.website-mu.test/')->assertOk();
    }

    public function test_admin_page_shows_the_panel(): void
    {
        $organization = $this->organizationWithDomain(active: false);

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.organizations.show', $organization))
            ->assertOk()
            ->assertSee('Custom Domain')
            ->assertSee('pcmambulu.or.id')
            ->assertSee('Belum aktif');
    }

    public function test_normalize_strips_everything_but_the_hostname(): void
    {
        $this->assertSame('pcmambulu.or.id', OrganizationDomain::normalize(' HTTPS://PcmAmbulu.or.id:443/beranda?x=1 '));
        $this->assertSame('pcmambulu.or.id', OrganizationDomain::normalize('pcmambulu.or.id.'));
        $this->assertSame('www.pcmambulu.or.id', OrganizationDomain::wwwCounterpart('pcmambulu.or.id'));
        $this->assertSame('pcmambulu.or.id', OrganizationDomain::wwwCounterpart('www.pcmambulu.or.id'));
    }
}
