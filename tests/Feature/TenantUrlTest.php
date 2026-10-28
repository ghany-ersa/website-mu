<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Services\TenantUrl;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * TenantUrl replaced direct route('tenant.*', ['organization_slug' => ...]) calls so the host
 * can later change per organization (custom domains). These pin that it still produces exactly
 * what route() did, so the swap stays a pure refactor until host() learns about other hosts.
 */
class TenantUrlTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_matches_the_subdomain_url_route_would_build(): void
    {
        $organization = Organization::factory()->create(['slug' => 'pcm-uji']);

        $this->assertSame(
            route('tenant.posts.show', ['organization_slug' => 'pcm-uji', 'post_slug' => 'berita-a']),
            TenantUrl::route($organization, 'tenant.posts.show', ['post_slug' => 'berita-a']),
        );
        $this->assertSame(
            route('tenant.home', ['organization_slug' => 'pcm-uji']),
            TenantUrl::route($organization, 'tenant.home'),
        );
        $this->assertSame('pcm-uji.'.config('tenancy.domain'), TenantUrl::host($organization));
    }

    public function test_it_returns_null_for_an_unknown_route(): void
    {
        $organization = Organization::factory()->create();

        $this->assertNull(TenantUrl::route($organization, 'tenant.tidak-ada'));
    }

    public function test_it_returns_null_when_tenant_routing_is_off(): void
    {
        $organization = Organization::factory()->create();
        config(['tenancy.domain' => null]);

        $this->assertNull(TenantUrl::host($organization));
        $this->assertNull(TenantUrl::route($organization, 'tenant.home'));
    }
}
