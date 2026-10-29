<?php

namespace Tests\Feature;

use App\Enums\OrganizationRole;
use App\Enums\OrganizationStatus;
use App\Models\GalleryPhoto;
use App\Models\Organization;
use App\Models\OrganizationPage;
use App\Models\Plan;
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

        // Deliberately no actingAs() - proves the route isn't behind auth middleware.
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
        // The grid is Alpine-driven (x-for over a `photos` array - see galeri/standar.blade.php)
        // so the photo data lands in the page as a JSON-encoded string (slashes escaped), not a
        // plain <img src> - assert on the filename rather than the full URL to sidestep that.
        $response->assertSee('foto-kegiatan.jpg', false);
        $response->assertSee('Kegiatan bakti sosial');
    }

    public function test_member_can_publish_and_unpublish_organization(): void
    {
        $user = User::factory()->create();
        $plan = Plan::create(['key' => 'test-plan', 'name' => 'Test Plan', 'price_monthly' => 0]);
        $organization = Organization::factory()->create([
            'status' => OrganizationStatus::Draft,
            'published_at' => null,
            'plan_id' => $plan->id,
            'plan_expires_at' => now()->addMonth(),
        ]);
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

    public function test_publish_is_blocked_without_an_active_plan(): void
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create([
            'status' => OrganizationStatus::Draft,
            'plan_id' => null,
        ]);
        $organization->members()->attach($user->id, ['role' => OrganizationRole::Owner->value]);

        $this->actingAs($user)
            ->patch(route('organizations.publish', $organization))
            ->assertRedirect(route('organizations.plan.edit', $organization));

        $organization->refresh();
        $this->assertSame(OrganizationStatus::Draft, $organization->status);
    }

    public function test_publish_is_blocked_when_plan_has_expired(): void
    {
        $user = User::factory()->create();
        $plan = Plan::create(['key' => 'expired-test-plan', 'name' => 'Expired Test Plan', 'price_monthly' => 0]);
        $organization = Organization::factory()->create([
            'status' => OrganizationStatus::Draft,
            'plan_id' => $plan->id,
            'plan_expires_at' => now()->subDay(),
        ]);
        $organization->members()->attach($user->id, ['role' => OrganizationRole::Owner->value]);

        $this->actingAs($user)
            ->patch(route('organizations.publish', $organization))
            ->assertRedirect(route('organizations.plan.edit', $organization));

        $organization->refresh();
        $this->assertSame(OrganizationStatus::Draft, $organization->status);
    }

    public function test_unpublish_is_allowed_even_when_plan_has_expired(): void
    {
        $user = User::factory()->create();
        $plan = Plan::create(['key' => 'expired-unpublish-plan', 'name' => 'Expired Unpublish Plan', 'price_monthly' => 0]);
        $organization = Organization::factory()->create([
            'status' => OrganizationStatus::Published,
            'published_at' => now(),
            'plan_id' => $plan->id,
            'plan_expires_at' => now()->subDay(),
        ]);
        $organization->members()->attach($user->id, ['role' => OrganizationRole::Owner->value]);

        $this->actingAs($user)
            ->patch(route('organizations.publish', $organization))
            ->assertRedirect();

        $organization->refresh();
        $this->assertSame(OrganizationStatus::Draft, $organization->status);
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

    /**
     * Owners reported not knowing where to publish or change their link: the address has to be
     * visible (and editable) while still in Draft, not only once the site is already live.
     */
    public function test_dashboard_shows_site_address_and_change_link_before_publishing(): void
    {
        $user = User::factory()->create();
        $plan = Plan::create(['key' => 'address-card-plan', 'name' => 'Address Card', 'price_monthly' => 0]);
        $organization = Organization::factory()->create([
            'status' => OrganizationStatus::Draft,
            'plan_id' => $plan->id,
            'plan_expires_at' => now()->addMonth(),
        ]);
        $organization->members()->attach($user->id, ['role' => OrganizationRole::Owner->value]);

        $this->actingAs($user)->get(route('organizations.show', $organization))
            ->assertOk()
            ->assertSee('Belum dipublikasikan (Draft)')
            ->assertSee($organization->slug.'.'.config('tenancy.domain'))
            ->assertSee(route('organizations.edit.edit', $organization).'#subdomain', false)
            // The card's publish form (its confirm label), not the button text, which is copy.
            ->assertSee('Ya, Publikasikan');
    }

    public function test_blocked_draft_gets_a_visible_path_to_the_plan_page(): void
    {
        $user = User::factory()->create();
        $plan = Plan::create(['key' => 'expired-dashboard-plan', 'name' => 'Expired', 'price_monthly' => 0]);
        $organization = Organization::factory()->create([
            'status' => OrganizationStatus::Draft,
            'plan_id' => $plan->id,
            'plan_expires_at' => now()->subDay(),
        ]);
        $organization->members()->attach($user->id, ['role' => OrganizationRole::Owner->value]);

        $this->actingAs($user)->get(route('organizations.show', $organization))
            ->assertOk()
            ->assertSee('Pilih Paket Sebelum Publish')
            ->assertSee(route('organizations.plan.edit', $organization), false)
            ->assertDontSee('Ya, Publikasikan');
    }

    /**
     * The builder carries its own publish button, and a successful publish lands on the
     * dashboard rather than back() - the builder locks once the site is Published.
     */
    public function test_builder_offers_publish_and_publishing_lands_on_the_dashboard(): void
    {
        $user = User::factory()->create();
        $plan = Plan::create(['key' => 'builder-publish-plan', 'name' => 'Builder Publish', 'price_monthly' => 0]);
        $organization = Organization::factory()->create([
            'status' => OrganizationStatus::Draft,
            'plan_id' => $plan->id,
            'plan_expires_at' => now()->addMonth(),
        ]);
        $organization->members()->attach($user->id, ['role' => OrganizationRole::Owner->value]);

        $this->actingAs($user)->get(route('organizations.builder.edit', $organization))
            ->assertOk()
            ->assertSee('id="btn-builder-publish"', false);

        $this->actingAs($user)
            ->from(route('organizations.builder.edit', $organization))
            ->patch(route('organizations.publish', $organization))
            ->assertRedirect(route('organizations.show', $organization));

        $this->assertSame(OrganizationStatus::Published, $organization->fresh()->status);
    }
}
