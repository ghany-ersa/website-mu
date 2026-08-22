<?php

namespace Tests\Feature;

use App\Enums\OrganizationRole;
use App\Models\Organization;
use App\Models\OrganizationPage;
use App\Models\Template;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganizationBuilderTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_can_open_builder_and_pages_are_cloned_from_template(): void
    {
        $user = User::factory()->create();
        $template = Template::factory()->create();
        $organization = Organization::factory()->create(['template_id' => $template->id]);
        $organization->members()->attach($user->id, ['role' => OrganizationRole::Owner->value]);

        $response = $this->actingAs($user)->get(route('organizations.builder.edit', $organization));

        $response->assertOk();
        $this->assertSame(1, $organization->pages()->count());
    }

    public function test_non_member_is_denied(): void
    {
        $stranger = User::factory()->create();
        $organization = Organization::factory()->create();

        $response = $this->actingAs($stranger)->get(route('organizations.builder.edit', $organization));

        $response->assertForbidden();
    }

    public function test_page_from_another_organization_is_not_found(): void
    {
        $user = User::factory()->create();
        $organizationA = Organization::factory()->create();
        $organizationA->members()->attach($user->id, ['role' => OrganizationRole::Owner->value]);
        $organizationB = Organization::factory()->create();
        $organizationB->members()->attach($user->id, ['role' => OrganizationRole::Owner->value]);

        $pageOfB = OrganizationPage::factory()->create(['organization_id' => $organizationB->id, 'slug' => 'home']);

        $response = $this->actingAs($user)->get(route('organizations.builder.page', [$organizationA, $pageOfB]));

        $response->assertNotFound();
    }

    public function test_member_can_add_update_duplicate_reorder_and_delete_sections(): void
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create();
        $organization->members()->attach($user->id, ['role' => OrganizationRole::Owner->value]);
        $page = OrganizationPage::factory()->create(['organization_id' => $organization->id, 'slug' => 'home']);

        $this->actingAs($user)
            ->post(route('organizations.sections.store', [$organization, $page]), ['key' => 'hero'])
            ->assertRedirect();

        $section = $page->sections()->first();
        $this->assertNotNull($section);

        $this->actingAs($user)
            ->patch(route('organizations.sections.update', [$organization, $section]), [
                'content' => ['headline' => 'Halo Dunia'],
                'is_visible' => '1',
            ])
            ->assertRedirect();
        $this->assertSame('Halo Dunia', $section->fresh()->content['headline']);

        $this->actingAs($user)
            ->post(route('organizations.sections.duplicate', [$organization, $section]))
            ->assertRedirect();
        $this->assertSame(2, $page->sections()->count());

        $ids = $page->sections()->pluck('id')->reverse()->values()->all();
        $this->actingAs($user)
            ->post(route('organizations.sections.reorder', [$organization, $page]), ['section_ids' => $ids])
            ->assertOk()
            ->assertJsonStructure(['canvas']);
        $this->assertSame($ids, $page->sections()->pluck('id')->all());

        $this->actingAs($user)
            ->delete(route('organizations.sections.destroy', [$organization, $section]))
            ->assertRedirect();
        $this->assertSame(1, $page->sections()->count());
    }

    public function test_updating_a_section_via_ajax_returns_rendered_canvas_instead_of_redirecting(): void
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create();
        $organization->members()->attach($user->id, ['role' => OrganizationRole::Owner->value]);
        $page = OrganizationPage::factory()->create(['organization_id' => $organization->id, 'slug' => 'home']);
        $section = $page->sections()->create(['key' => 'hero', 'content' => [], 'order' => 0]);

        $response = $this->actingAs($user)
            ->postJson(route('organizations.sections.update', [$organization, $section]), [
                '_method' => 'PATCH',
                'content' => ['headline' => 'Judul Baru'],
                'is_visible' => '0',
            ]);

        $response->assertOk();
        $response->assertJsonStructure(['is_visible', 'canvas']);
        $this->assertFalse($response->json('is_visible'));
        $this->assertSame('Judul Baru', $section->fresh()->content['headline']);
    }

    public function test_uploaded_logo_renders_in_header_and_footer_on_canvas(): void
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create(['logo' => 'https://example.test/logo.png']);
        $organization->members()->attach($user->id, ['role' => OrganizationRole::Owner->value]);
        $page = OrganizationPage::factory()->create(['organization_id' => $organization->id, 'slug' => 'home']);
        $page->sections()->create(['key' => 'header', 'content' => [], 'order' => 0]);
        $page->sections()->create(['key' => 'footer', 'content' => [], 'order' => 1]);

        $response = $this->actingAs($user)
            ->get(route('organizations.builder.canvas', [$organization, $page]));

        $response->assertOk();
        $response->assertSeeInOrder(['https://example.test/logo.png', 'https://example.test/logo.png']);
    }

    public function test_logo_is_used_as_favicon_and_hero_image_as_og_image_on_canvas(): void
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create(['logo' => 'https://example.test/logo.png']);
        $organization->members()->attach($user->id, ['role' => OrganizationRole::Owner->value]);
        $page = OrganizationPage::factory()->create(['organization_id' => $organization->id, 'slug' => 'home']);
        $page->sections()->create(['key' => 'hero', 'content' => ['image' => 'https://example.test/hero.jpg'], 'order' => 0]);

        $response = $this->actingAs($user)
            ->get(route('organizations.builder.canvas', [$organization, $page]));

        $response->assertOk();
        $response->assertSee('<link rel="icon" href="https://example.test/logo.png">', false);
        $response->assertSee('<meta property="og:image" content="https://example.test/hero.jpg">', false);
    }

    public function test_missing_logo_and_hero_image_omit_favicon_and_og_image_tags(): void
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create(['logo' => null]);
        $organization->members()->attach($user->id, ['role' => OrganizationRole::Owner->value]);
        $page = OrganizationPage::factory()->create(['organization_id' => $organization->id, 'slug' => 'home']);

        $response = $this->actingAs($user)
            ->get(route('organizations.builder.canvas', [$organization, $page]));

        $response->assertOk();
        $response->assertDontSee('rel="icon"', false);
        $response->assertDontSee('og:image', false);
    }

    public function test_new_section_is_seeded_with_its_registry_default_title(): void
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create();
        $organization->members()->attach($user->id, ['role' => OrganizationRole::Owner->value]);
        $page = OrganizationPage::factory()->create(['organization_id' => $organization->id, 'slug' => 'home']);

        $this->actingAs($user)
            ->post(route('organizations.sections.store', [$organization, $page]), ['key' => 'agenda'])
            ->assertRedirect();

        $section = $page->sections()->first();
        $this->assertSame('Agenda Kegiatan', $section->content['title']);
    }

    public function test_a_new_blank_page_is_seeded_with_a_footer(): void
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create();
        $organization->members()->attach($user->id, ['role' => OrganizationRole::Owner->value]);

        $this->actingAs($user)
            ->post(route('organizations.pages.store', $organization), ['name' => 'Kontak', 'slug' => 'kontak'])
            ->assertRedirect();

        $page = $organization->pages()->where('slug', 'kontak')->firstOrFail();
        $this->assertTrue($page->sections()->where('key', 'footer')->exists());
    }

    public function test_footer_section_cannot_be_added_updated_duplicated_or_deleted(): void
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create();
        $organization->members()->attach($user->id, ['role' => OrganizationRole::Owner->value]);
        $page = OrganizationPage::factory()->create(['organization_id' => $organization->id, 'slug' => 'home']);
        $footer = $page->sections()->create(['key' => 'footer', 'content' => [], 'order' => 0]);

        $this->actingAs($user)
            ->post(route('organizations.sections.store', [$organization, $page]), ['key' => 'footer'])
            ->assertSessionHasErrors('key');

        $this->actingAs($user)
            ->patch(route('organizations.sections.update', [$organization, $footer]), ['is_visible' => '0'])
            ->assertForbidden();

        $this->actingAs($user)
            ->post(route('organizations.sections.duplicate', [$organization, $footer]))
            ->assertForbidden();

        $this->actingAs($user)
            ->delete(route('organizations.sections.destroy', [$organization, $footer]))
            ->assertForbidden();

        $this->assertSame(1, $page->sections()->count());
    }

    public function test_footer_always_renders_last_regardless_of_reorder_request(): void
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create();
        $organization->members()->attach($user->id, ['role' => OrganizationRole::Owner->value]);
        $page = OrganizationPage::factory()->create(['organization_id' => $organization->id, 'slug' => 'home']);
        $footer = $page->sections()->create(['key' => 'footer', 'content' => [], 'order' => 1]);
        $hero = $page->sections()->create(['key' => 'hero', 'content' => [], 'order' => 0]);

        // Attempt to smuggle the footer to the front of the order.
        $this->actingAs($user)
            ->post(route('organizations.sections.reorder', [$organization, $page]), [
                'section_ids' => [$footer->id, $hero->id],
            ])
            ->assertOk();

        $orderedKeys = $page->sections()->get()->pluck('key')->all();
        $this->assertSame(['hero', 'footer'], $orderedKeys);
    }
}
