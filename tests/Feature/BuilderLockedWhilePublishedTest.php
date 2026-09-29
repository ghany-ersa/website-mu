<?php

namespace Tests\Feature;

use App\Enums\OrganizationRole;
use App\Enums\OrganizationStatus;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The page builder (structure: pages, sections, variant, template swap) is locked while an
 * organization is Published - OrganizationPolicy::manageBuilder() gates it, deliberately
 * narrower than update(). The CMS (posts, agenda, ...) is unaffected: publishing content is the
 * normal day-to-day flow and must not require going offline first.
 */
class BuilderLockedWhilePublishedTest extends TestCase
{
    use RefreshDatabase;

    private function organizationWithOwner(OrganizationStatus $status): array
    {
        $organization = Organization::factory()->create(['status' => $status]);
        $organization->ensureHomePageExists();

        $owner = User::factory()->create();
        $organization->members()->attach($owner, ['role' => OrganizationRole::Owner->value]);

        return [$organization, $owner];
    }

    public function test_builder_edit_is_blocked_while_published(): void
    {
        [$organization, $owner] = $this->organizationWithOwner(OrganizationStatus::Published);

        $this->actingAs($owner)
            ->get(route('organizations.builder.edit', $organization))
            ->assertForbidden();
    }

    public function test_builder_edit_is_reachable_while_draft(): void
    {
        [$organization, $owner] = $this->organizationWithOwner(OrganizationStatus::Draft);

        $this->actingAs($owner)
            ->get(route('organizations.builder.edit', $organization))
            ->assertOk();
    }

    public function test_adding_a_section_is_blocked_while_published(): void
    {
        [$organization, $owner] = $this->organizationWithOwner(OrganizationStatus::Published);
        $page = $organization->pages()->firstOrFail();

        $this->actingAs($owner)
            ->post(route('organizations.sections.store', [$organization, $page]), ['key' => 'galeri'])
            ->assertForbidden();
    }

    public function test_adding_a_section_is_allowed_while_draft(): void
    {
        [$organization, $owner] = $this->organizationWithOwner(OrganizationStatus::Draft);
        $page = $organization->pages()->firstOrFail();

        $this->actingAs($owner)
            ->post(route('organizations.sections.store', [$organization, $page]), ['key' => 'galeri'])
            ->assertSessionHasNoErrors();
    }

    public function test_adding_a_page_is_blocked_while_published(): void
    {
        [$organization, $owner] = $this->organizationWithOwner(OrganizationStatus::Published);

        $this->actingAs($owner)
            ->post(route('organizations.pages.store', $organization), [
                'name' => 'Halaman Baru',
                'slug' => 'halaman-baru',
            ])
            ->assertForbidden();
    }

    public function test_switching_template_is_blocked_while_published(): void
    {
        [$organization, $owner] = $this->organizationWithOwner(OrganizationStatus::Published);

        $this->actingAs($owner)
            ->get(route('organizations.template.edit', $organization))
            ->assertForbidden();
    }

    /**
     * The point of the gate: the CMS is a completely different authorization path
     * (PostPolicy::create(), not OrganizationPolicy::manageBuilder()), so publishing content
     * must keep working on a live site without unpublishing first.
     */
    public function test_cms_content_stays_editable_while_published(): void
    {
        [$organization, $owner] = $this->organizationWithOwner(OrganizationStatus::Published);
        // OrganizationPostController requires the daftar-berita section to exist on some page.
        $organization->pages()->firstOrFail()->sections()->create(['key' => 'daftar-berita', 'order' => 1, 'content' => []]);

        $this->actingAs($owner)
            ->get(route('organizations.posts.create', $organization))
            ->assertOk();

        $this->actingAs($owner)
            ->post(route('organizations.posts.store', $organization), [
                'title' => 'Berita Saat Live',
                'body' => 'Konten berita.',
                'category' => 'Kegiatan',
                'status' => 'draft',
            ])
            ->assertSessionHasNoErrors();
    }
}
