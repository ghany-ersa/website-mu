<?php

namespace Tests\Feature;

use App\Models\Template;
use Database\Seeders\TemplateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TemplatePreviewAnchorTest extends TestCase
{
    use RefreshDatabase;

    private function previewFor(array $heroContent): string
    {
        $template = Template::factory()->create([
            'structure' => [
                'pages' => [
                    ['slug' => 'home', 'name' => 'Home', 'sections' => [
                        ['key' => 'hero', 'variant' => 'standar', 'content' => $heroContent],
                        ['key' => 'jadwal-praktik', 'variant' => 'standar', 'content' => []],
                    ]],
                ],
            ],
        ]);

        return $this->get(route('templates.preview', ['template' => $template->slug]))
            ->assertOk()
            ->getContent();
    }

    public function test_preview_wraps_sections_in_anchors_keyed_by_section_key(): void
    {
        $html = $this->previewFor([]);

        $this->assertStringContainsString('id="section-hero"', $html);
        $this->assertStringContainsString('id="section-jadwal-praktik"', $html);
    }

    public function test_scroll_cta_targeting_a_section_key_resolves_to_a_real_anchor(): void
    {
        $html = $this->previewFor([
            'cta_label' => 'Jadwal Praktik',
            'cta_type' => 'scroll',
            'cta_section' => 'jadwal-praktik',
        ]);

        $this->assertStringContainsString('href="#section-jadwal-praktik"', $html);
        $this->assertStringContainsString('id="section-jadwal-praktik"', $html);
    }

    /**
     * The builder's dropdown stores an OrganizationSection primary key, which is meaningless
     * inside a template preview - it matches no element, so it must not render as a link that
     * scrolls nowhere.
     */
    public function test_scroll_cta_targeting_a_numeric_section_id_is_dropped_in_preview(): void
    {
        $html = $this->previewFor([
            'cta_label' => 'Jadwal Praktik',
            'cta_type' => 'scroll',
            'cta_section' => '136',
        ]);

        $this->assertStringNotContainsString('section-136', $html);
    }

    /**
     * Guards the whole point of this: no anchor on a preview may point at a missing id.
     */
    public function test_every_seeded_template_preview_has_only_resolvable_anchors(): void
    {
        $this->seed(TemplateSeeder::class);

        foreach (Template::all() as $template) {
            $html = $this->get(route('templates.preview', ['template' => $template->slug]))
                ->assertOk()
                ->getContent();

            preg_match_all('/href="#([^"]+)"/', $html, $links);
            preg_match_all('/\bid="([^"]+)"/', $html, $ids);

            foreach (array_unique($links[1]) as $target) {
                $this->assertContains(
                    $target,
                    $ids[1],
                    "Template [{$template->slug}] links to #{$target} but no element has that id."
                );
            }
        }
    }
}
