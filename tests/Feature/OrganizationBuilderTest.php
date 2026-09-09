<?php

namespace Tests\Feature;

use App\Enums\OrganizationRole;
use App\Models\Organization;
use App\Models\OrganizationPage;
use App\Models\Plan;
use App\Models\Template;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
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

    public function test_adding_a_section_redirects_straight_to_editing_it(): void
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create();
        $organization->members()->attach($user->id, ['role' => OrganizationRole::Owner->value]);
        $page = OrganizationPage::factory()->create(['organization_id' => $organization->id, 'slug' => 'home']);

        $response = $this->actingAs($user)
            ->post(route('organizations.sections.store', [$organization, $page]), ['key' => 'hero']);

        $section = $page->sections()->first();
        $this->assertNotNull($section);
        $response->assertRedirect(route('organizations.builder.page', [$organization, $page, 'section' => $section->id]));
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

    /**
     * Regression test: 'items' has no form control in edit.blade.php for a CMS-backed section
     * (daftar-berita, struktur-pengurus, etc. - it only renders a "Kelola ... ->" link, never
     * an <input name="content[items]">), so update() used to write null for it on every save,
     * silently wiping the section's fallback sample content (which the public tenant page
     * doesn't need - it queries live CMS records - but a TEMPLATE preview with no organization
     * yet falls back to exactly that content['items'] array). Saving any OTHER field (here,
     * 'title') must leave 'items' untouched.
     */
    public function test_updating_a_section_does_not_wipe_fields_the_edit_form_has_no_input_for(): void
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create();
        $organization->members()->attach($user->id, ['role' => OrganizationRole::Owner->value]);
        $page = OrganizationPage::factory()->create(['organization_id' => $organization->id, 'slug' => 'home']);
        $section = $page->sections()->create([
            'key' => 'daftar-berita',
            'content' => ['title' => 'Berita Terbaru', 'items' => [['title' => 'Contoh Berita']]],
            'order' => 0,
        ]);

        $this->actingAs($user)
            ->patch(route('organizations.sections.update', [$organization, $section]), [
                'content' => ['title' => 'Kabar Terkini'],
                'is_visible' => '1',
            ])
            ->assertRedirect();

        $fresh = $section->fresh();
        $this->assertSame('Kabar Terkini', $fresh->content['title']);
        $this->assertSame([['title' => 'Contoh Berita']], $fresh->content['items']);
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

    public function test_a_new_blank_page_is_seeded_with_a_header_and_footer(): void
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create();
        $organization->members()->attach($user->id, ['role' => OrganizationRole::Owner->value]);

        $this->actingAs($user)
            ->post(route('organizations.pages.store', $organization), ['name' => 'Kontak', 'slug' => 'kontak'])
            ->assertRedirect();

        $page = $organization->pages()->where('slug', 'kontak')->firstOrFail();
        $this->assertTrue($page->sections()->where('key', 'header')->exists());
        $this->assertTrue($page->sections()->where('key', 'footer')->exists());
    }

    public function test_footer_section_cannot_be_added_duplicated_or_deleted(): void
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
            ->post(route('organizations.sections.duplicate', [$organization, $footer]))
            ->assertForbidden();

        $this->actingAs($user)
            ->delete(route('organizations.sections.destroy', [$organization, $footer]))
            ->assertForbidden();

        $this->assertSame(1, $page->sections()->count());
    }

    public function test_header_and_footer_org_name_can_be_overridden_and_falls_back_when_cleared(): void
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create(['name' => 'Pimpinan Cabang Muhammadiyah Ambulu']);
        $organization->members()->attach($user->id, ['role' => OrganizationRole::Owner->value]);
        $page = OrganizationPage::factory()->create(['organization_id' => $organization->id, 'slug' => 'home']);
        $header = $page->sections()->create(['key' => 'header', 'content' => [], 'order' => 0]);
        $footer = $page->sections()->create(['key' => 'footer', 'content' => [], 'order' => 1]);

        foreach ([$header, $footer] as $section) {
            $this->actingAs($user)
                ->patch(route('organizations.sections.update', [$organization, $section]), [
                    'content' => ['org_name' => 'PCM Ambulu'],
                    'is_visible' => '1',
                ])
                ->assertRedirect();

            $this->assertSame('PCM Ambulu', $section->refresh()->content['org_name']);
        }

        // Assert on the rendered wordmarks specifically: the page's <title> always carries the
        // organization's full name, so a plain assertDontSee() could never distinguish them.
        $this->assertSame(
            ['PCM Ambulu', 'PCM Ambulu'],
            $this->wordmarksOn($user, $organization, $page)
        );

        // Clearing the override must fall back to the organization's real name rather than
        // rendering an empty wordmark - '' is not null, so the views test with filled().
        foreach ([$header, $footer] as $section) {
            $this->actingAs($user)
                ->patch(route('organizations.sections.update', [$organization, $section]), [
                    'content' => ['org_name' => ''],
                    'is_visible' => '1',
                ])
                ->assertRedirect();
        }

        $this->assertSame(
            ['Pimpinan Cabang Muhammadiyah Ambulu', 'Pimpinan Cabang Muhammadiyah Ambulu'],
            $this->wordmarksOn($user, $organization, $page)
        );
    }

    /**
     * The header and footer wordmark text as rendered on the builder canvas.
     *
     * @return array<int, string>
     */
    private function wordmarksOn(User $user, Organization $organization, OrganizationPage $page): array
    {
        $html = $this->actingAs($user)
            ->get(route('organizations.builder.canvas', [$organization, $page]))
            ->getContent();

        preg_match_all('/(?:tracking-tight|text-lg leading-tight)">([^<]*)</', $html, $matches);

        return array_map('trim', $matches[1]);
    }

    public function test_footer_shows_organization_contact_info_and_platform_watermark(): void
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create([
            'phone' => '024-1234567',
            'email' => 'kontak@pcm-ambulu.test',
            'whatsapp' => '081234567890',
            'address' => 'Jl. Ambulu No. 1, Jember',
            'instagram_url' => 'https://instagram.com/pcmambulu',
            'facebook_url' => 'https://facebook.com/pcmambulu',
        ]);
        $organization->members()->attach($user->id, ['role' => OrganizationRole::Owner->value]);
        $page = OrganizationPage::factory()->create(['organization_id' => $organization->id, 'slug' => 'home']);
        $page->sections()->create(['key' => 'footer', 'content' => [], 'order' => 0]);

        $response = $this->actingAs($user)
            ->get(route('organizations.builder.canvas', [$organization, $page]));

        $response->assertOk();
        $response->assertSee('024-1234567');
        $response->assertSee('kontak@pcm-ambulu.test');
        $response->assertSee('081234567890');
        $response->assertSee('Jl. Ambulu No. 1, Jember');
        $response->assertSee('https://instagram.com/pcmambulu', false);
        $response->assertSee('https://facebook.com/pcmambulu', false);
        $response->assertSee('website-mu.id');
        $response->assertSee('Seluruh hak cipta dilindungi');
    }

    public function test_home_page_cannot_be_deleted(): void
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create();
        $organization->members()->attach($user->id, ['role' => OrganizationRole::Owner->value]);
        $homePage = OrganizationPage::factory()->create([
            'organization_id' => $organization->id,
            'slug' => 'home',
            'is_home' => true,
        ]);

        $this->actingAs($user)
            ->delete(route('organizations.pages.destroy', [$organization, $homePage]))
            ->assertRedirect();

        $this->assertNotNull($homePage->fresh());
    }

    public function test_a_non_home_page_can_be_deleted(): void
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create();
        $organization->members()->attach($user->id, ['role' => OrganizationRole::Owner->value]);
        OrganizationPage::factory()->create([
            'organization_id' => $organization->id,
            'slug' => 'home',
            'is_home' => true,
        ]);
        $page = OrganizationPage::factory()->create([
            'organization_id' => $organization->id,
            'slug' => 'kontak',
            'is_home' => false,
        ]);

        $this->actingAs($user)
            ->delete(route('organizations.pages.destroy', [$organization, $page]))
            ->assertRedirect(route('organizations.builder.edit', $organization));

        $this->assertNull($page->fresh());
    }

    public function test_switching_pages_loads_the_requested_page(): void
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create();
        $organization->members()->attach($user->id, ['role' => OrganizationRole::Owner->value]);
        OrganizationPage::factory()->create([
            'organization_id' => $organization->id,
            'slug' => 'home',
            'is_home' => true,
        ]);
        $page = OrganizationPage::factory()->create([
            'organization_id' => $organization->id,
            'name' => 'Kontak',
            'slug' => 'kontak',
            'is_home' => false,
        ]);

        $response = $this->actingAs($user)->get(route('organizations.builder.page', [$organization, $page]));

        $response->assertOk();
        $response->assertSee('Kontak');
    }

    public function test_non_professional_plan_cannot_create_a_second_page(): void
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create([
            'plan_id' => Plan::where('key', 'starter')->firstOrFail()->id,
        ]);
        $organization->members()->attach($user->id, ['role' => OrganizationRole::Owner->value]);
        OrganizationPage::factory()->create([
            'organization_id' => $organization->id,
            'slug' => 'home',
            'is_home' => true,
        ]);

        $this->actingAs($user)
            ->post(route('organizations.pages.store', $organization), ['name' => 'Kontak', 'slug' => 'kontak'])
            ->assertRedirect(route('organizations.builder.edit', $organization));

        $this->assertSame(1, $organization->pages()->count());
    }

    public function test_professional_plan_can_create_additional_pages(): void
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create([
            'plan_id' => Plan::where('key', 'professional')->firstOrFail()->id,
        ]);
        $organization->members()->attach($user->id, ['role' => OrganizationRole::Owner->value]);
        OrganizationPage::factory()->create([
            'organization_id' => $organization->id,
            'slug' => 'home',
            'is_home' => true,
        ]);

        $this->actingAs($user)
            ->post(route('organizations.pages.store', $organization), ['name' => 'Kontak', 'slug' => 'kontak'])
            ->assertRedirect();

        $this->assertSame(2, $organization->pages()->count());
    }

    public function test_builder_hides_add_page_button_and_shows_upgrade_cta_for_non_professional_plan(): void
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create([
            'plan_id' => Plan::where('key', 'starter')->firstOrFail()->id,
        ]);
        $organization->members()->attach($user->id, ['role' => OrganizationRole::Owner->value]);

        $response = $this->actingAs($user)->get(route('organizations.builder.edit', $organization));

        $response->assertOk();
        // "Halaman Baru" also appears as the (Alpine x-show, so still server-rendered)
        // create modal's heading regardless of plan - only the trigger button that opens
        // it is plan-gated, so assert on that exact button markup instead of the phrase.
        $response->assertDontSee('@click="openCreate()"', false);
        $response->assertSee('Upgrade untuk halaman lebih banyak');
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

    /**
     * Every variant of a section must read the same registry `fields`, because the builder shows
     * one properties panel per section regardless of variant: a field one variant renders and
     * another ignores means an organization types copy in, switches variant, and watches it
     * vanish with no indication it is still stored.
     *
     * Text-level check on the Blade sources rather than a render, so it covers variants no
     * fixture happens to exercise. A variant may reach a field through a computed key rather
     * than a literal one - hero/headline-berita resolves both CTAs through a $prefix.'_type'
     * helper - so a field whose name is only a suffix of that computed form counts as read when
     * the source builds it that way.
     */
    public function test_every_variant_of_a_section_reads_the_same_registry_fields(): void
    {
        $inconsistent = [];

        foreach (config('page-builder.sections') as $key => $meta) {
            $dir = resource_path('views/templates/sections/'.$key);

            if (! is_dir($dir)) {
                continue;
            }

            $views = array_values(array_filter(
                scandir($dir),
                fn ($file) => str_ends_with($file, '.blade.php') && ! str_starts_with($file, '_')
            ));

            if (count($views) < 2) {
                continue;
            }

            foreach ($meta['fields'] ?? [] as $field) {
                $renders = [];

                foreach ($views as $view) {
                    $source = file_get_contents($dir.'/'.$view);

                    $renders[$view] = str_contains($source, "['".$field."']")
                        || str_contains($source, "'".$field."'")
                        // e.g. $content[$prefix.'_type'] covers cta_type and cta_secondary_type.
                        || (bool) preg_match('/\$\w+\s*\.\s*\x27_'.preg_quote(
                            Str::afterLast($field, '_'),
                            '/'
                        ).'\x27/', $source);
                }

                // Only a field some variants read and others don't is a problem; one no variant
                // reads is a separate (harmless) case, and one every variant reads is correct.
                if (count(array_unique($renders)) > 1) {
                    $missing = array_keys(array_filter($renders, fn ($read) => ! $read));
                    $inconsistent[] = $key.'.'.$field.' missing from: '.implode(', ', $missing);
                }
            }
        }

        $this->assertSame([], $inconsistent, "Some variants ignore fields their siblings render:\n".implode("\n", $inconsistent));
    }
}
