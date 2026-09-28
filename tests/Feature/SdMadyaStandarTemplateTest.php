<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\Plan;
use App\Models\SectionVariant;
use App\Models\Template;
use App\Models\User;
use Database\Seeders\OrganizationTypeSeeder;
use Database\Seeders\SdMadyaStandarTemplateSeeder;
use Database\Seeders\SdMadyaTemplateSeeder;
use Database\Seeders\SectionVariantSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SdMadyaStandarTemplateTest extends TestCase
{
    use RefreshDatabase;

    private function template(): Template
    {
        $this->seed(OrganizationTypeSeeder::class);
        $this->seed(SectionVariantSeeder::class);
        $this->seed(SdMadyaStandarTemplateSeeder::class);

        return Template::where('slug', SdMadyaStandarTemplateSeeder::SLUG)->firstOrFail();
    }

    public function test_it_is_a_non_exclusive_school_template(): void
    {
        $template = $this->template();

        $this->assertFalse($template->is_exclusive);
        $this->assertTrue($template->is_active);
        $this->assertSame('sekolahmadrasah', $template->organizationType->slug);
    }

    public function test_every_section_uses_a_variant_a_lower_plan_may_actually_use(): void
    {
        $template = $this->template();

        foreach ($template->structure['pages'][0]['sections'] as $section) {
            $variant = SectionVariant::where('section_key', $section['key'])
                ->where('variant_key', $section['variant'])
                ->first();

            $this->assertNotNull($variant, "Variant {$section['key']}/{$section['variant']} is not registered.");
            $this->assertFalse(
                (bool) $variant->is_exclusive,
                "Section {$section['key']} uses the exclusive variant [{$section['variant']}] - a non-exclusive template cannot."
            );

            $this->assertEmpty(
                config("page-builder.sections.{$section['key']}.exclusive"),
                "Section {$section['key']} is itself plan-gated and cannot appear here."
            );
        }
    }

    /**
     * Catalog descriptions are read side by side in the template picker, so they have to read
     * as one family: a short tour of what the visitor sees, not a statement about the template
     * itself. The SD Madya exclusive one drifted to 347 characters - half again as long as the
     * next longest - by opening with "Template eksklusif multi-halaman untuk sekolah:" and
     * closing with a sentence about plan entitlement that the card's own "Eksklusif" badge
     * already conveys.
     */
    public function test_school_descriptions_match_the_house_style(): void
    {
        $this->seed(OrganizationTypeSeeder::class);
        $this->seed(SectionVariantSeeder::class);
        $this->seed(SdMadyaTemplateSeeder::class);
        $this->seed(SdMadyaStandarTemplateSeeder::class);

        $slugs = [SdMadyaTemplateSeeder::SLUG, SdMadyaStandarTemplateSeeder::SLUG];

        foreach ($slugs as $slug) {
            $description = Template::where('slug', $slug)->firstOrFail()->description;

            // The band every other designed template sits in (193-236 characters), with a little
            // headroom - short enough to scan on a card, long enough to name what you get.
            $this->assertLessThanOrEqual(260, mb_strlen($description), "[{$slug}] description is too long to scan on a catalog card.");
            $this->assertGreaterThanOrEqual(150, mb_strlen($description), "[{$slug}] description is too thin to say what the template offers.");

            // Describe the site, not the product tier: the card already badges exclusivity.
            $this->assertStringNotContainsString('entitlement', $description);
            $this->assertStringNotContainsString('paket', mb_strtolower($description), "[{$slug}] should not name a plan; the catalog card badges that.");
            $this->assertFalse(
                str_starts_with($description, 'Template'),
                "[{$slug}] should open with what the visitor sees, not the word 'Template'."
            );
        }
    }

    public function test_a_starter_school_gets_the_whole_template_and_can_publish(): void
    {
        $template = $this->template();
        $starter = Plan::where('key', 'starter')->firstOrFail();

        $organization = Organization::create([
            'user_id' => User::factory()->create()->id,
            'organization_type_id' => $template->organization_type_id,
            'template_id' => $template->id,
            'plan_id' => $starter->id,
            'name' => 'SD Standar',
            'slug' => 'sd-standar',
        ]);

        $organization->ensureHomePageExists();
        $organization = $organization->fresh();

        // Nothing was truncated by sections_total: the template is written to that exact budget.
        $expected = collect($template->structure['pages'][0]['sections'])->pluck('key')->all();
        $actual = $organization->pages()->firstOrFail()->sections()->orderBy('order')->pluck('key')->all();
        $this->assertSame($expected, $actual);

        // The point of this template existing: an exclusive one would add a publish-time block.
        $this->assertNotContains('Template ini memerlukan paket Eksklusif', $organization->planViolations());
    }
}
