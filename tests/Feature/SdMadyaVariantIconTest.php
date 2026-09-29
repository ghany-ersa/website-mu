<?php

namespace Tests\Feature;

use App\Models\Achievement;
use App\Models\Organization;
use App\Models\Plan;
use App\Models\Template;
use App\Models\User;
use Database\Seeders\OrganizationTypeSeeder;
use Database\Seeders\SdMadyaTemplateSeeder;
use Database\Seeders\SectionVariantSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Unlike PrestasiStandarIconTest (which covers the shared `standar` variants, where the icon
 * font is never loaded at all because those templates carry a plain font, not a pairing), this
 * covers the `sd-madya` variants specifically - the ones where the font IS requested
 * (organizations/pages/_brand-head.blade.php loads Material Symbols whenever the template
 * declares a font_pair, and sd-madya-eksklusif does).
 *
 * The ligature approach still fails there whenever the font simply doesn't arrive in time - an
 * ad-blocker or privacy extension blocking fonts.gstatic.com, a slow connection, a stale cache on
 * a server that hasn't redeployed the font link yet. A visitor in any of those cases sees literal
 * icon names ("stars", "arrow_forward", "workspace_premium") as text instead of glyphs, which is
 * exactly what showed up in production. Inline SVG has no such window: it renders correctly
 * whether or not any external font ever loads.
 */
class SdMadyaVariantIconTest extends TestCase
{
    use RefreshDatabase;

    public function test_no_sd_madya_variant_uses_the_icon_ligature_component(): void
    {
        $files = glob(resource_path('views/templates/sections/*/sd-madya.blade.php'));
        $this->assertNotEmpty($files, 'expected to find sd-madya section variants');

        foreach ($files as $file) {
            $this->assertStringNotContainsString(
                'x-tenant.icon',
                file_get_contents($file),
                basename(dirname($file)).'/sd-madya.blade.php uses x-tenant.icon - a font that fails to '
                .'load leaves the ligature name as visible text, so this variant must use inline SVG.'
            );
        }
    }

    public function test_every_sd_madya_page_renders_without_a_single_leaked_ligature(): void
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
            'name' => 'SD Semua Ikon',
            'slug' => 'sd-semua-ikon',
        ]);
        $organization->members()->attach($user->id, ['role' => 'owner']);
        $organization->ensureHomePageExists();
        $organization = $organization->fresh();

        Achievement::create([
            'organization_id' => $organization->id,
            'title' => 'Juara 1 Olimpiade',
            'category' => 'Akademik',
            'level' => 'Tingkat Kecamatan',
            'year' => '2026',
            'is_active' => true,
            'order' => 0,
        ]);

        // Every ligature name the sd-madya family used before this fix, across hero, galeri,
        // struktur-pengurus, formulir-kontak and prestasi.
        $ligatures = [
            'stars', 'arrow_forward', 'workspace_premium', 'play_arrow', 'image', 'person',
            'contact_support', 'location_on', 'call', 'schedule', 'expand_more',
            'music_note', 'menu_book',
        ];

        foreach ($organization->pages as $page) {
            $html = view('organizations.pages._render', [
                'organization' => $organization,
                'page' => $page->load('sections'),
            ])->render();

            foreach ($ligatures as $ligature) {
                $this->assertStringNotContainsString(
                    ">{$ligature}<",
                    $html,
                    "Ligature '{$ligature}' leaked as text on page [{$page->slug}]."
                );
            }
        }
    }
}
