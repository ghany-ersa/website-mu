<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\Plan;
use App\Models\Template;
use App\Models\User;
use App\Services\PageBuilderFields;
use Database\Seeders\OrganizationTypeSeeder;
use Database\Seeders\SdMadyaTemplateSeeder;
use Database\Seeders\SectionVariantSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HeroSdMadyaImagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_hero_second_image_and_highlight_card_survive_an_edit(): void
    {
        $this->seed(OrganizationTypeSeeder::class);
        $this->seed(SectionVariantSeeder::class);
        $this->seed(SdMadyaTemplateSeeder::class);

        $template = Template::where('slug', SdMadyaTemplateSeeder::SLUG)->firstOrFail();
        $user = User::factory()->create();

        $organization = Organization::create([
            'user_id' => $user->id,
            'organization_type_id' => $template->organization_type_id,
            'template_id' => $template->id,
            'plan_id' => Plan::where('key', 'eksklusif')->firstOrFail()->id,
            'name' => 'SD Hero',
            'slug' => 'sd-hero',
        ]);
        $organization->members()->attach($user->id, ['role' => 'owner']);
        $organization->ensureHomePageExists();
        $organization = $organization->fresh();

        $section = $organization->pages()->where('is_home', true)->firstOrFail()
            ->sections()->where('key', 'hero')->firstOrFail();

        $this->assertNotEmpty($section->content['portrait'], 'template should seed the second image');

        // Editing only the headline must not wipe the fields this variant renders. Before
        // `portrait`/`highlight_*` were registered in config/page-builder.php, update() rebuilt
        // content from that list and dropped everything else.
        $this->actingAs($user)->patch(
            route('organizations.sections.update', [$organization, $section]),
            ['content' => [
                'headline' => 'Judul Baru',
                'portrait' => 'https://example.test/portrait.jpg',
                'highlight_title' => 'Akreditasi A',
                'highlight_subtitle' => 'Predikat Unggul (BAN-S/M)',
            ]]
        )->assertRedirect();

        $content = $section->fresh()->content;

        $this->assertSame('Judul Baru', $content['headline']);
        $this->assertSame('https://example.test/portrait.jpg', $content['portrait']);
        $this->assertSame('Akreditasi A', $content['highlight_title']);
        $this->assertSame('Predikat Unggul (BAN-S/M)', $content['highlight_subtitle']);
    }

    /**
     * The second image belongs to the `sd-madya` layout alone. Every other hero variant must
     * neither show an editor for it nor persist it - otherwise four of the five hero variants
     * carry three controls that change nothing on the page.
     */
    public function test_only_the_sd_madya_variant_exposes_the_second_image(): void
    {
        $this->seed(OrganizationTypeSeeder::class);
        $this->seed(SectionVariantSeeder::class);

        $this->assertContains('portrait', PageBuilderFields::for('hero', 'sd-madya'));

        foreach (['standar', 'modern', 'nurul-huda', 'headline-berita', null] as $variant) {
            $fields = PageBuilderFields::for('hero', $variant);

            foreach (['portrait', 'highlight_title', 'highlight_subtitle'] as $extra) {
                $this->assertNotContains($extra, $fields, "hero/{$variant} must not expose [{$extra}].");
            }

            // The shared fields are still all there.
            $this->assertContains('headline', $fields);
            $this->assertContains('image', $fields);
        }

        // A section with no variant_fields entry is unaffected.
        $this->assertSame(
            config('page-builder.sections.galeri.fields'),
            PageBuilderFields::for('galeri', 'standar')
        );
    }

    /** The builder panel shows the extra editors on sd-madya and hides them elsewhere. */
    public function test_builder_shows_the_second_image_editor_only_for_sd_madya(): void
    {
        $this->seed(OrganizationTypeSeeder::class);
        $this->seed(SectionVariantSeeder::class);
        $this->seed(SdMadyaTemplateSeeder::class);

        $template = Template::where('slug', SdMadyaTemplateSeeder::SLUG)->firstOrFail();
        $user = User::factory()->create();

        $organization = Organization::create([
            'user_id' => $user->id,
            'organization_type_id' => $template->organization_type_id,
            'template_id' => $template->id,
            'plan_id' => Plan::where('key', 'eksklusif')->firstOrFail()->id,
            'name' => 'SD Panel',
            'slug' => 'sd-panel',
        ]);
        $organization->members()->attach($user->id, ['role' => 'owner']);
        $organization->ensureHomePageExists();
        $organization = $organization->fresh();

        $page = $organization->pages()->where('is_home', true)->firstOrFail();
        $section = $page->sections()->where('key', 'hero')->firstOrFail();
        $url = route('organizations.builder.page', [$organization, $page]);

        $this->assertSame('sd-madya', $section->variant);
        $html = $this->actingAs($user)->get($url)->assertOk()->getContent();
        $this->assertStringContainsString('Gambar Utama', $html);
        $this->assertStringContainsString('content[portrait]', $html);

        $section->update(['variant' => 'standar']);

        $html = $this->actingAs($user)->get($url)->assertOk()->getContent();
        $this->assertStringNotContainsString('content[portrait]', $html);
        $this->assertStringNotContainsString('Kartu Sorotan', $html);
    }

    /**
     * The portrait is `w-full` inside a grid column, so without a cap the COLUMN width sets its
     * height: at a 1280px shell the column is ~592px, which at the 4/5 ratio is 740px against a
     * section that maxes out at 42rem (672px). It overflowed the section, crowded the headline
     * and pushed the accreditation card past the bottom edge.
     */
    public function test_hero_portrait_is_height_capped_so_it_cannot_overflow_the_section(): void
    {
        $blade = file_get_contents(resource_path('views/templates/sections/hero/sd-madya.blade.php'));

        // Capped on both axes: the box itself, and the image inside it.
        $this->assertStringContainsString('md:max-w-sm', $blade);
        $this->assertStringContainsString('md:max-h-[26rem]', $blade);
        $this->assertStringContainsString('lg:max-h-[30rem]', $blade);

        // 30rem (480px) has to stay clear of the section's own 42rem (672px) ceiling, with room
        // left for the highlight card that hangs off the image's bottom-left corner.
        $this->assertLessThan(42 - 4, 30, 'portrait cap must leave room for the highlight card');

        // The empty-state placeholder is capped identically, or an image-less hero still breaks.
        $this->assertSame(
            substr_count($blade, 'md:max-h-[26rem]'),
            substr_count($blade, 'aspect-[16/10] md:aspect-[4/5]'),
            'every portrait box (image and placeholder alike) must carry the height cap'
        );
    }

    /**
     * Capping the portrait fixed the overflow but left the headline breaking mid-name: with an
     * even 1fr/1fr split the copy column is ~592px at a 1280px shell, and "SD Muhammadiyah 1
     * Ambulu" needs roughly 650px at t-hero's 52px ceiling. The portrait is capped at 384px, so
     * the right column no longer needs half the row.
     */
    public function test_hero_gives_the_headline_enough_room_to_stay_on_one_line(): void
    {
        $blade = file_get_contents(resource_path('views/templates/sections/hero/sd-madya.blade.php'));

        $this->assertStringContainsString('md:grid-cols-[7fr_5fr]', $blade);
        $this->assertStringNotContainsString('md:grid-cols-2', $blade, 'an even split leaves the headline too little room');

        // Widening the column alone is not enough and never was: at t-hero's 52px ceiling the
        // name is ~737px wide in Inter ExtraBold, more than a 1120px shell can give any column.
        // The headline sizes against its own column instead, so it shrinks to fit rather than
        // wrapping - which needs the column to be a container.
        $this->assertStringContainsString('@container', $blade);
        $this->assertStringContainsString('cqi', $blade);
        // The <h1> must not carry the shared viewport-sized step any more (the word may still
        // appear in comments explaining why).
        $this->assertDoesNotMatchRegularExpression('/<h1[^>]*\bt-hero\b/', $blade, 'the shared viewport-sized step is what forced the wrap');

        // A longer name is still possible, so it has to wrap into even lines rather than
        // stranding a single trailing word.
        $this->assertStringContainsString('text-balance', $blade);
    }

    /**
     * Switching a hero to `sd-madya` in the same request that sets its second image must keep
     * that image: the controller resolves the variant BEFORE the field list for this reason.
     */
    public function test_switching_to_sd_madya_keeps_the_fields_that_layout_adds(): void
    {
        $this->seed(OrganizationTypeSeeder::class);
        $this->seed(SectionVariantSeeder::class);
        $this->seed(SdMadyaTemplateSeeder::class);

        $template = Template::where('slug', SdMadyaTemplateSeeder::SLUG)->firstOrFail();
        $user = User::factory()->create();

        $organization = Organization::create([
            'user_id' => $user->id,
            'organization_type_id' => $template->organization_type_id,
            'template_id' => $template->id,
            'plan_id' => Plan::where('key', 'eksklusif')->firstOrFail()->id,
            'name' => 'SD Switch',
            'slug' => 'sd-switch',
        ]);
        $organization->members()->attach($user->id, ['role' => 'owner']);
        $organization->ensureHomePageExists();
        $organization = $organization->fresh();

        $section = $organization->pages()->where('is_home', true)->firstOrFail()
            ->sections()->where('key', 'hero')->firstOrFail();

        // Away from sd-madya: the extra fields are not this layout's, so they go.
        $this->actingAs($user)->patch(
            route('organizations.sections.update', [$organization, $section]),
            ['variant' => 'standar', 'content' => ['headline' => 'Tetap']]
        )->assertRedirect();

        $this->assertSame('standar', $section->fresh()->variant);
        $this->assertArrayNotHasKey('portrait', $section->fresh()->content);

        // Back to sd-madya, setting the image in the same request.
        $this->actingAs($user)->patch(
            route('organizations.sections.update', [$organization, $section]),
            ['variant' => 'sd-madya', 'content' => [
                'headline' => 'Tetap',
                'portrait' => 'https://example.test/p.jpg',
            ]]
        )->assertRedirect();

        $fresh = $section->fresh();
        $this->assertSame('sd-madya', $fresh->variant);
        $this->assertSame('https://example.test/p.jpg', $fresh->content['portrait']);
    }
}
