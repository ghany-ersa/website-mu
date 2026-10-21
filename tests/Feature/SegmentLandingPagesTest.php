<?php

namespace Tests\Feature;

use App\Enums\PublishStatus;
use App\Models\Article;
use App\Models\OrganizationType;
use App\Models\Template;
use Database\Seeders\KlinikSeoArticleSeeder;
use Database\Seeders\MasjidSeoArticleSeeder;
use Database\Seeders\SekolahSeoArticleSeeder;
use Database\Seeders\SeoArticleClusterSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * The per-segment SEO landing pages (config/landing-pages.php) and their blog article clusters.
 * Beyond "it renders", the things worth guarding are the ones a crawler reads: only that
 * segment's publicly usable templates, valid FAQ JSON-LD matching visible text, the URL being
 * discoverable (sitemap + internal link), and the cluster articles' hardcoded links resolving.
 */
class SegmentLandingPagesTest extends TestCase
{
    use RefreshDatabase;

    /** @return array<string, array{string}> */
    public static function segments(): array
    {
        $config = require __DIR__.'/../../config/landing-pages.php';

        return collect(array_keys($config['segments']))->mapWithKeys(fn ($s) => [$s => [$s]])->all();
    }

    #[DataProvider('segments')]
    public function test_it_renders_for_a_guest_with_its_own_title_and_canonical(string $segment): void
    {
        $page = config("landing-pages.segments.$segment");

        $this->get(route("landing.$segment"))
            ->assertOk()
            ->assertSee('<title>'.e($page['meta_title']).'</title>', escape: false)
            ->assertSee('<link rel="canonical" href="'.route("landing.$segment").'">', escape: false)
            ->assertSee($page['hero']['highlight']);
    }

    #[DataProvider('segments')]
    public function test_it_renders_without_a_template_grid_when_the_type_is_missing(string $segment): void
    {
        $this->get(route("landing.$segment"))->assertOk()->assertDontSee('id="template"', escape: false);
    }

    #[DataProvider('segments')]
    public function test_it_lists_only_active_public_templates_of_its_own_type(string $segment): void
    {
        $type = OrganizationType::factory()->create(['slug' => config("landing-pages.segments.$segment.organization_type_slug")]);
        $other = OrganizationType::factory()->create(['slug' => 'tipe-lain']);

        $shown = Template::factory()->create(['name' => 'Template Tayang', 'organization_type_id' => $type->id]);
        $inactive = Template::factory()->create(['name' => 'Template Nonaktif', 'organization_type_id' => $type->id, 'is_active' => false]);
        $private = Template::factory()->create(['name' => 'Template Privat', 'organization_type_id' => $type->id, 'is_public' => false]);
        $otherType = Template::factory()->create(['name' => 'Template Tipe Lain', 'organization_type_id' => $other->id]);

        $this->get(route("landing.$segment"))
            ->assertOk()
            ->assertSee($shown->name)
            ->assertDontSee($inactive->name)
            ->assertDontSee($private->name)
            ->assertDontSee($otherType->name);
    }

    #[DataProvider('segments')]
    public function test_it_emits_faq_structured_data_matching_visible_text(string $segment): void
    {
        $html = $this->get(route("landing.$segment"))->assertOk()->getContent();

        preg_match_all('#<script type="application/ld\+json">(.*?)</script>#s', $html, $matches);
        $schemas = array_map(fn ($json) => json_decode($json, true, flags: JSON_THROW_ON_ERROR), $matches[1]);
        $faq = collect($schemas)->firstWhere('@type', 'FAQPage');

        $this->assertNotNull($faq, 'No FAQPage JSON-LD found.');
        $this->assertCount(count(config("landing-pages.segments.$segment.faq.items")), $faq['mainEntity']);

        foreach ($faq['mainEntity'] as $question) {
            $this->assertStringContainsString(e($question['name']), $html);
        }
    }

    #[DataProvider('segments')]
    public function test_it_is_in_the_sitemap_and_linked_from_the_homepage(string $segment): void
    {
        $url = route("landing.$segment");

        $this->get(route('sitemap'))->assertOk()->assertSee($url, escape: false);
        $this->get(route('home'))->assertOk()->assertSee('href="'.$url.'"', escape: false);
    }

    /** Every feature icon must exist in the view's icon map, or the page throws. */
    public function test_every_configured_icon_is_known_to_the_view(): void
    {
        $view = file_get_contents(resource_path('views/landing/segment.blade.php'));

        foreach (config('landing-pages.segments') as $segment => $page) {
            foreach ($page['features']['items'] as $feature) {
                $this->assertStringContainsString("'{$feature['icon']}' =>", $view, "Unknown icon '{$feature['icon']}' in segment '$segment'.");
            }
        }
    }

    /** @return array<string, array{class-string<SeoArticleClusterSeeder>, string}> */
    public static function articleClusters(): array
    {
        return [
            'masjid' => [MasjidSeoArticleSeeder::class, '/website-masjid'],
            'sekolah' => [SekolahSeoArticleSeeder::class, '/website-sekolah'],
            'klinik' => [KlinikSeoArticleSeeder::class, '/website-klinik'],
        ];
    }

    /**
     * The articles' value is their internal links, which are hardcoded root-relative strings in
     * stored HTML - a renamed route or slug would silently 404 every one of them.
     */
    #[DataProvider('articleClusters')]
    public function test_cluster_articles_seed_as_drafts_with_working_internal_links(string $seeder, string $landingPath): void
    {
        $this->seed($seeder);
        $this->seed($seeder);

        $articles = Article::whereIn('slug', $seeder::SLUGS)->get();
        $this->assertCount(count($seeder::SLUGS), $articles, 'Re-seeding must not duplicate articles.');
        $this->assertTrue($articles->every(fn (Article $a) => $a->status === PublishStatus::Draft));

        $articles->each->update(['status' => PublishStatus::Published, 'published_at' => now()]);

        foreach ($articles as $article) {
            $this->assertStringContainsString('href="'.$landingPath.'"', $article->body);

            preg_match_all('#href="(/[^"]*)"#', $article->body, $matches);
            foreach (array_unique($matches[1]) as $path) {
                $this->get($path)->assertOk();
            }
        }
    }
}
