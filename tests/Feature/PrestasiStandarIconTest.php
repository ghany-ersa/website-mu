<?php

namespace Tests\Feature;

use App\Models\Achievement;
use App\Models\Organization;
use App\Models\Plan;
use App\Models\Template;
use App\Models\User;
use Database\Seeders\OrganizationTypeSeeder;
use Database\Seeders\SdMadyaStandarTemplateSeeder;
use Database\Seeders\SectionVariantSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrestasiStandarIconTest extends TestCase
{
    use RefreshDatabase;

    public function test_shared_standar_variants_never_use_the_icon_font(): void
    {
        // The Material Symbols font is only loaded for templates that declare a font PAIRING
        // (organizations/pages/_brand-head.blade.php). A `standar` variant is usable by every
        // template, including the ones with a plain font, where the ligature renders as its own
        // literal name - "search", "emoji_events" - as visible text. See components/tenant/icon.
        foreach (glob(resource_path('views/templates/sections/*/standar.blade.php')) as $file) {
            $this->assertStringNotContainsString(
                'x-tenant.icon',
                file_get_contents($file),
                basename(dirname($file)).'/standar.blade.php uses x-tenant.icon; shared variants must use inline SVG.'
            );
        }
    }

    public function test_prestasi_renders_icons_as_svg_not_ligature_text(): void
    {
        $this->seed(OrganizationTypeSeeder::class);
        $this->seed(SectionVariantSeeder::class);
        $this->seed(SdMadyaStandarTemplateSeeder::class);

        $template = Template::where('slug', SdMadyaStandarTemplateSeeder::SLUG)->firstOrFail();
        $user = User::factory()->create();

        $organization = Organization::create([
            'user_id' => $user->id,
            'organization_type_id' => $template->organization_type_id,
            'template_id' => $template->id,
            'plan_id' => Plan::where('key', 'starter')->firstOrFail()->id,
            'name' => 'SD Ikon',
            'slug' => 'sd-ikon',
        ]);
        $organization->members()->attach($user->id, ['role' => 'owner']);
        $organization->ensureHomePageExists();

        // Two categories so the filter rail and its search box actually render.
        foreach ([['Akademik', 'Juara 1 Olimpiade'], ['Seni', 'Juara 2 Festival']] as $i => [$category, $title]) {
            Achievement::create([
                'organization_id' => $organization->id,
                'title' => $title,
                'category' => $category,
                'level' => 'Tingkat Kecamatan',
                'year' => '2026',
                'is_active' => true,
                'order' => $i,
            ]);
        }

        // fresh(): Organization has both a `whatsapp` column and a whatsapp() method, and on a
        // just-created instance the attribute isn't loaded, so Eloquent falls through to
        // relationship resolution and throws inside formulir-kontak/standar. Documented in
        // CLAUDE.md; a re-fetched model carries the attribute.
        $organization = $organization->fresh();
        $page = $organization->pages()->firstOrFail();

        // Renders the page exactly as the builder canvas and the public tenant page both do
        // (organizations/pages/_render.blade.php), which is where the ligature leak showed up.
        $html = view('organizations.pages._render', [
            'organization' => $organization,
            'page' => $page->load('sections'),
        ])->render();

        $this->assertStringContainsString('Juara 1 Olimpiade', $html);

        // The bug: ligature names leaking as visible text inside a span.
        foreach (['search', 'emoji_events', 'search_off', 'person'] as $ligature) {
            $this->assertStringNotContainsString(">{$ligature}<", $html, "Icon ligature '{$ligature}' leaked as text.");
        }

        // The icon FONT is correctly absent on this template (it has a plain font, not a
        // pairing) - which is exactly why the ligature approach could never have worked here.
        $this->assertStringNotContainsString('material-symbols-outlined', $html);

        // The replacements render as real SVG instead.
        $this->assertStringContainsString('<svg', $html);
    }
}
