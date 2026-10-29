<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\OrganizationPage;
use App\Models\Template;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * The fold of `layanan` into program-unggulan is not reversible, so its data rewrite is run
 * here against rows shaped like production's rather than trusted from reading.
 */
class FoldLayananMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_layanan_sections_and_template_structures_become_program_unggulan_with_layanan_source(): void
    {
        $page = OrganizationPage::factory()->create(['organization_id' => Organization::factory()->create()->id]);
        $section = $page->sections()->create(['key' => 'layanan', 'variant' => 'standar', 'content' => ['title' => 'Layanan Klinik'], 'order' => 0]);
        $program = $page->sections()->create(['key' => 'program-unggulan', 'content' => ['title' => 'Program Sosial Klinik'], 'order' => 1]);

        $rumahSehat = Template::factory()->create([
            'slug' => 'klinik-aisyiyah-ambulu-standar',
            'structure' => ['pages' => [['slug' => 'home', 'sections' => [
                ['key' => 'layanan', 'variant' => 'standar', 'content' => ['title' => 'Layanan Klinik']],
                ['key' => 'program-unggulan', 'variant' => 'standar', 'content' => ['title' => 'Program Sosial Klinik']],
            ]]]],
        ]);
        $other = Template::factory()->create([
            'structure' => ['pages' => [['slug' => 'home', 'sections' => [
                ['key' => 'program-unggulan', 'variant' => 'modern', 'content' => ['title' => 'Program Sosial Klinik']],
            ]]]],
        ]);

        (require database_path('migrations/2026_10_30_100000_fold_layanan_section_into_program_unggulan.php'))->up();

        $section->refresh();
        $this->assertSame('program-unggulan', $section->key);
        $this->assertSame(['title' => 'Layanan Klinik', 'source' => 'layanan'], $section->content);
        // An organization's own pages are never pruned - only the Rumah Sehat template is.
        $this->assertNotNull($program->fresh());

        $this->assertSame(
            [['key' => 'program-unggulan', 'variant' => 'standar', 'content' => ['title' => 'Layanan Klinik', 'source' => 'layanan']]],
            $rumahSehat->fresh()->structure['pages'][0]['sections'],
        );
        // Another template's program-sourced "Program Sosial Klinik" is left alone.
        $this->assertCount(1, $other->fresh()->structure['pages'][0]['sections']);
        $this->assertSame(0, DB::table('section_variants')->where('section_key', 'layanan')->count());
    }
}
