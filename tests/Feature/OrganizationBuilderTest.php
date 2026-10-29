<?php

namespace Tests\Feature;

use App\Enums\OrganizationRole;
use App\Models\Organization;
use App\Models\OrganizationPage;
use App\Models\Plan;
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

    public function test_widget_picker_follows_page_flow_order(): void
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create(['template_id' => Template::factory()->create()->id]);
        $organization->members()->attach($user->id, ['role' => OrganizationRole::Owner->value]);

        $this->actingAs($user)->get(route('organizations.builder.edit', $organization))
            ->assertOk()
            ->assertSeeInOrder(['Hero', 'Tentang Organisasi', 'Sambutan Ketua', 'Lokasi', 'Call To Action'], false);
    }

    public function test_every_addable_section_is_placed_in_the_picker_order(): void
    {
        // A section missing from picker_order still shows, but only after the closing CTA -
        // this catches a new section that was added to the registry and forgotten here.
        $addable = collect(config('page-builder.sections'))
            ->reject(fn ($meta) => ! empty($meta['locked']) || ! empty($meta['hidden']))
            ->keys()
            ->all();

        $this->assertEqualsCanonicalizing($addable, config('page-builder.picker_order'));
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

    public function test_header_and_footer_settings_are_shared_across_pages(): void
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create();
        $organization->members()->attach($user->id, ['role' => OrganizationRole::Owner->value]);
        $home = OrganizationPage::factory()->create(['organization_id' => $organization->id, 'slug' => 'home']);
        $other = OrganizationPage::factory()->create(['organization_id' => $organization->id, 'slug' => 'kontak']);
        foreach ([$home, $other] as $page) {
            $page->ensureHeader();
            $page->ensureFooter();
        }
        $somethingElse = $other->sections()->create(['key' => 'hero', 'content' => ['title' => 'Tetap'], 'order' => 1]);

        foreach (['header', 'footer'] as $key) {
            $this->actingAs($user)
                ->patch(route('organizations.sections.update', [$organization, $home->sections()->where('key', $key)->first()]), [
                    'content' => ['org_name' => 'PCM Singkat'],
                    'is_visible' => '1',
                ])
                ->assertRedirect();

            $this->assertSame('PCM Singkat', $other->sections()->where('key', $key)->first()->content['org_name']);
        }
        $this->assertSame('Tetap', $somethingElse->fresh()->content['title']);

        // A page created afterwards starts from the site's header/footer, not registry defaults.
        $this->actingAs($user)
            ->post(route('organizations.pages.store', $organization), ['name' => 'Profil', 'slug' => 'profil'])
            ->assertRedirect();
        $new = $organization->pages()->where('slug', 'profil')->firstOrFail();
        $this->assertSame('PCM Singkat', $new->sections()->where('key', 'header')->first()->content['org_name']);
        $this->assertSame('PCM Singkat', $new->sections()->where('key', 'footer')->first()->content['org_name']);
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
        $response->assertSee('All Rights Reserved');
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
            'plan_id' => Plan::where('key', 'eksklusif')->firstOrFail()->id,
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
     * fixture happens to exercise. Two things a literal `['field']`/`'field'` search alone
     * would miss, both handled below:
     *
     *  - A variant may reach a field through a computed key rather than a literal one -
     *    cta/sekolahmu and hero/headline-berita both resolve two CTAs through a
     *    $prefix.'_wa_number'-style helper, where $prefix is 'cta' or 'cta_secondary'. Tried
     *    against every underscore split of the field (not just the last one), so a multi-part
     *    suffix like 'wa_number' in 'cta_wa_number' is still recognized as read.
     *  - A variant may delegate its entire body to another variant via @include (kegiatan's
     *    only layout difference from program-unggulan is which Program `type` it queries, so
     *    kegiatan/sekolahmu just includes program-unggulan/sekolahmu rather than duplicating
     *    it) - followed one level deep so the field is checked against the included source,
     *    not the forwarding shim.
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
                    $source = $this->resolveIncludedSource($dir.'/'.$view);

                    $renders[$view] = str_contains($source, "['".$field."']")
                        || str_contains($source, "'".$field."'")
                        || $this->readsFieldViaComputedKey($field, $source);
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

    /**
     * A variant source may be a thin forwarding shim to an ENTIRELY DIFFERENT section's variant
     * rather than the actual layout - e.g. kegiatan/sekolahmu.blade.php is just
     * `@include('program-unggulan.sekolahmu', [...])`, since the two sections differ only in
     * which Program `type` they query, not in markup. Appended (not substituted) to the
     * variant's own source, which still matters: daftar-berita/standar.blade.php reads `items`
     * itself before @include-ing its own _items-standar partial to render them, so replacing its
     * source with the partial's would lose that.
     *
     * Only an include naming another SECTION's variant counts - every section's own internal
     * partial is named with a leading underscore (_items-standar, _items-ringkas) and is
     * already part of $source via file_get_contents(), so following it here would double up.
     * One level deep (no section's variant chains two includes deep today).
     */
    private function resolveIncludedSource(string $path): string
    {
        $source = file_get_contents($path);

        if (! preg_match('/@include\(\s*[\'"]templates\.sections\.([\w-]+)\.([\w-]+)[\'"]/', $source, $m)
            || str_starts_with($m[2], '_')) {
            return $source;
        }

        $included = resource_path("views/templates/sections/{$m[1]}/{$m[2]}.blade.php");

        return is_file($included) ? $source."\n".file_get_contents($included) : $source;
    }

    /**
     * Whether $source reaches $field through a computed key rather than a literal one -
     * cta/sekolahmu and hero/headline-berita both resolve two CTAs through a
     * $prefix.'_wa_number'-style helper, where $prefix is 'cta' or 'cta_secondary', so
     * 'cta_wa_number' is only ever written as $content[$prefix.'_wa_number'] in that source.
     *
     * Tried against every underscore split of $field (prefix/suffix pairs from the first
     * underscore to the last), not just the split on the last underscore, because the dynamic
     * suffix itself can contain an underscore ('wa_number', not just 'number').
     */
    private function readsFieldViaComputedKey(string $field, string $source): bool
    {
        $parts = explode('_', $field);

        for ($i = 1; $i < count($parts); $i++) {
            $suffix = implode('_', array_slice($parts, $i));

            if (preg_match('/\$\w+\s*\.\s*\x27_'.preg_quote($suffix, '/').'\x27/', $source)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Every CMS-backed section must offer its "Kelola X ->" link from the section being edited,
     * exactly once.
     *
     * The link used to be rendered from inside the properties-panel field loop, on the `items`
     * branch, so it only appeared for sections that expose an `items` field. laporan-keuangan
     * declares `fields => ['title']` and was therefore the one CMS-backed section with no way to
     * reach its CMS from the builder, even though the CMS itself existed. Driving the assertion
     * off the registry's `cms` entry means a future section that omits `items` fails here rather
     * than silently shipping without the link.
     */
    public function test_every_cms_backed_section_links_to_its_cms_exactly_once(): void
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create();
        $organization->members()->attach($user->id, ['role' => OrganizationRole::Owner->value]);
        $page = OrganizationPage::factory()->create(['organization_id' => $organization->id, 'slug' => 'home']);

        $cmsKeys = collect(config('page-builder.sections'))
            ->filter(fn ($meta) => isset($meta['cms']))
            ->keys();

        $this->assertNotEmpty($cmsKeys, 'no CMS-backed sections in the registry');

        $order = 0;
        foreach ($cmsKeys as $key) {
            $page->sections()->create(['key' => $key, 'content' => [], 'order' => $order++]);
        }

        $html = $this->actingAs($user)
            ->get(route('organizations.builder.page', [$organization, $page]))
            ->getContent();

        foreach ($page->sections()->get() as $section) {
            $cms = config("page-builder.sections.{$section->key}.cms");
            $url = route($cms['route'], ['organization' => $organization, ...$cms['params'] ?? []]);
            $separator = str_contains($url, '?') ? '&' : '?';
            // Blade escapes the href, so a route that already carries a query string (e.g.
            // programs?type=program) appears with &amp; separators - compare against the
            // escaped form rather than route()'s raw output.
            $needle = e($url.$separator.'from=builder').'&amp;section='.$section->id;

            $this->assertSame(
                1,
                substr_count($html, $needle),
                "section {$section->key} should link to its CMS exactly once"
            );
        }
    }
}
