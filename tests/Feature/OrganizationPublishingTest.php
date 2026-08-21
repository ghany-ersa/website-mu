<?php

namespace Tests\Feature;

use App\Enums\OrganizationRole;
use App\Enums\OrganizationStatus;
use App\Models\GalleryPhoto;
use App\Models\Organization;
use App\Models\OrganizationPage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganizationPublishingTest extends TestCase
{
    use RefreshDatabase;

    private function tenantUrl(Organization $organization): string
    {
        return 'http://'.$organization->slug.'.'.config('tenancy.domain').'/';
    }

    public function test_published_organization_is_publicly_reachable_at_its_subdomain(): void
    {
        $organization = Organization::factory()->create(['status' => OrganizationStatus::Published]);
        $page = OrganizationPage::factory()->create(['organization_id' => $organization->id, 'slug' => 'home', 'is_home' => true]);
        $page->sections()->create(['key' => 'hero', 'content' => ['headline' => 'Selamat Datang'], 'order' => 0]);

        $response = $this->get($this->tenantUrl($organization));

        $response->assertOk();
        $response->assertSee('Selamat Datang');
    }

    public function test_draft_organization_404s_on_its_subdomain(): void
    {
        $organization = Organization::factory()->create(['status' => OrganizationStatus::Draft]);
        OrganizationPage::factory()->create(['organization_id' => $organization->id, 'slug' => 'home', 'is_home' => true]);

        $this->get($this->tenantUrl($organization))->assertNotFound();
    }

    public function test_public_site_is_reachable_without_authentication_or_membership(): void
    {
        $organization = Organization::factory()->create(['status' => OrganizationStatus::Published]);
        OrganizationPage::factory()->create(['organization_id' => $organization->id, 'slug' => 'home', 'is_home' => true]);

        // Deliberately no actingAs() — proves the route isn't behind auth middleware.
        $this->get($this->tenantUrl($organization))->assertOk();
    }

    public function test_public_render_shows_cms_bound_gallery_content(): void
    {
        $organization = Organization::factory()->create(['status' => OrganizationStatus::Published]);
        $page = OrganizationPage::factory()->create(['organization_id' => $organization->id, 'slug' => 'home', 'is_home' => true]);
        $page->sections()->create(['key' => 'galeri', 'content' => [], 'order' => 0]);

        GalleryPhoto::factory()->create([
            'organization_id' => $organization->id,
            'url' => 'https://example.test/foto-kegiatan.jpg',
            'caption' => 'Kegiatan bakti sosial',
        ]);

        $response = $this->get($this->tenantUrl($organization));

        $response->assertOk();
        $response->assertSee('https://example.test/foto-kegiatan.jpg', false);
        $response->assertSee('Kegiatan bakti sosial');
    }

    public function test_member_can_publish_and_unpublish_organization(): void
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create(['status' => OrganizationStatus::Draft, 'published_at' => null]);
        $organization->members()->attach($user->id, ['role' => OrganizationRole::Owner->value]);

        $this->actingAs($user)
            ->patch(route('organizations.publish', $organization))
            ->assertRedirect();

        $organization->refresh();
        $this->assertSame(OrganizationStatus::Published, $organization->status);
        $this->assertNotNull($organization->published_at);
        $firstPublishedAt = $organization->published_at;

        $this->actingAs($user)
            ->patch(route('organizations.publish', $organization))
            ->assertRedirect();

        $organization->refresh();
        $this->assertSame(OrganizationStatus::Draft, $organization->status);
        $this->assertEquals($firstPublishedAt, $organization->published_at);

        $this->actingAs($user)
            ->patch(route('organizations.publish', $organization))
            ->assertRedirect();

        $organization->refresh();
        $this->assertSame(OrganizationStatus::Published, $organization->status);
        $this->assertEquals($firstPublishedAt, $organization->published_at);
    }

    public function test_non_member_cannot_publish_organization(): void
    {
        $stranger = User::factory()->create();
        $organization = Organization::factory()->create(['status' => OrganizationStatus::Draft]);

        $this->actingAs($stranger)
            ->patch(route('organizations.publish', $organization))
            ->assertForbidden();
    }

    public function test_dashboard_shows_live_url_and_publish_toggle_label_by_status(): void
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create(['status' => OrganizationStatus::Draft]);
        $organization->members()->attach($user->id, ['role' => OrganizationRole::Owner->value]);

        $draftResponse = $this->actingAs($user)->get(route('organizations.show', $organization));
        $draftResponse->assertSee('Publikasikan');
        $draftResponse->assertDontSee('Lihat Situs');

        $organization->publish();

        $publishedResponse = $this->actingAs($user)->get(route('organizations.show', $organization));
        $publishedResponse->assertSee('Jadikan Draft');
        $publishedResponse->assertSee($organization->slug.'.'.config('tenancy.domain'));
        $publishedResponse->assertSee('Lihat Situs');
    }
}
