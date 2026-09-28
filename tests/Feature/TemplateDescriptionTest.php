<?php

namespace Tests\Feature;

use App\Models\Template;
use Database\Seeders\BlankTemplateSeeder;
use Database\Seeders\OrganizationTypeSeeder;
use Database\Seeders\SectionVariantSeeder;
use Database\Seeders\TemplateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Catalog descriptions are read side by side in the template picker, so they have to read as one
 * family AND be true: a description promising a feature the template doesn't contain is worse
 * than a vague one, because the user only finds out after building on it.
 */
class TemplateDescriptionTest extends TestCase
{
    use RefreshDatabase;

    /** Phrase a description may use => the section key that has to be present for it to be true. */
    private const CLAIMS = [
        'ortom' => 'jaringan-aum-ortom',
        'amal usaha' => 'jaringan-aum-ortom',
        'kajian' => 'agenda',
        'galeri' => 'galeri',
        'prestasi' => 'prestasi',
        'pengumuman' => 'pengumuman',
        'praktik' => 'jadwal-praktik',
        'laporan keuangan' => 'laporan-keuangan',
        'kalkulator zakat' => 'kalkulator-zakat',
        'sewa aula' => 'akad-venue',
        'testimoni' => 'testimoni',
        'ekstrakurikuler' => 'kegiatan',
    ];

    private function seedTemplates(): void
    {
        $this->seed(OrganizationTypeSeeder::class);
        $this->seed(SectionVariantSeeder::class);

        foreach (glob(database_path('seeders/*TemplateSeeder.php')) as $file) {
            $class = 'Database\\Seeders\\'.basename($file, '.php');

            if ($class === TemplateSeeder::class) {
                continue;
            }

            $this->seed($class);
        }
    }

    public function test_no_description_promises_a_section_the_template_lacks(): void
    {
        $this->seedTemplates();

        foreach (Template::all() as $template) {
            $keys = [];

            foreach ($template->structure['pages'] ?? [] as $page) {
                foreach ($page['sections'] ?? [] as $section) {
                    $keys[$section['key']] = true;
                }
            }

            $description = mb_strtolower((string) $template->description);

            foreach (self::CLAIMS as $phrase => $sectionKey) {
                if (str_contains($description, $phrase)) {
                    $this->assertArrayHasKey(
                        $sectionKey,
                        $keys,
                        "[{$template->slug}] description mentions \"{$phrase}\" but has no [{$sectionKey}] section."
                    );
                }
            }
        }
    }

    public function test_descriptions_stay_scannable_and_describe_the_site(): void
    {
        $this->seedTemplates();

        foreach (Template::all() as $template) {
            $description = (string) $template->description;
            $this->assertNotEmpty($description, "[{$template->slug}] has no description.");

            // "Halaman Kosong" is deliberately shorter: it has no features to list.
            if ($template->slug === BlankTemplateSeeder::SLUG) {
                continue;
            }

            $this->assertLessThanOrEqual(260, mb_strlen($description), "[{$template->slug}] is too long to scan on a card.");
            $this->assertGreaterThanOrEqual(150, mb_strlen($description), "[{$template->slug}] is too thin to say what you get.");

            // Describe the site, not the product tier - the card already badges exclusivity.
            $this->assertStringNotContainsString('entitlement', mb_strtolower($description));
            $this->assertFalse(
                str_starts_with($description, 'Template'),
                "[{$template->slug}] should open with what the visitor sees, not the word 'Template'."
            );
        }
    }
}
