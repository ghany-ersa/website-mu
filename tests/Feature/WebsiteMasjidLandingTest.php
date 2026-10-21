<?php

namespace Tests\Feature;

use App\Enums\PublishStatus;
use App\Models\Article;
use App\Models\OrganizationType;
use App\Models\Template;
use Database\Seeders\MasjidSeoArticleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * /website-masjid is an SEO landing page, so beyond "it renders" the things worth guarding are
 * the ones a crawler reads: only masjid templates that are publicly usable, valid FAQ JSON-LD,
 * and the URL actually being discoverable (sitemap + internal link).
 */
class WebsiteMasjidLandingTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_renders_for_a_guest_with_the_target_keyword(): void
    {
        $this->get(route('landing.masjid'))
            ->assertOk()
            ->assertSee('<title>Website Masjid', escape: false)
            ->assertSee('<link rel="canonical" href="'.route('landing.masjid').'">', escape: false);
    }

    public function test_it_renders_when_the_masjid_organization_type_is_missing(): void
    {
        $this->get(route('landing.masjid'))->assertOk()->assertDontSee('id="template-masjid"', escape: false);
    }

    public function test_it_lists_only_active_public_masjid_templates(): void
    {
        $masjid = OrganizationType::factory()->create(['slug' => 'masjidmushola']);
        $other = OrganizationType::factory()->create(['slug' => 'muhammadiyah']);

        $shown = Template::factory()->create(['name' => 'Masjid Tayang', 'organization_type_id' => $masjid->id]);
        $inactive = Template::factory()->create(['name' => 'Masjid Nonaktif', 'organization_type_id' => $masjid->id, 'is_active' => false]);
        $private = Template::factory()->create(['name' => 'Masjid Privat', 'organization_type_id' => $masjid->id, 'is_public' => false]);
        $otherType = Template::factory()->create(['name' => 'Cabang Lain', 'organization_type_id' => $other->id]);

        $this->get(route('landing.masjid'))
            ->assertOk()
            ->assertSee($shown->name)
            ->assertDontSee($inactive->name)
            ->assertDontSee($private->name)
            ->assertDontSee($otherType->name);
    }

    public function test_it_emits_valid_faq_structured_data(): void
    {
        $html = $this->get(route('landing.masjid'))->assertOk()->getContent();

        preg_match_all('#<script type="application/ld\+json">(.*?)</script>#s', $html, $matches);
        $schemas = array_map(fn ($json) => json_decode($json, true, flags: JSON_THROW_ON_ERROR), $matches[1]);
        $faq = collect($schemas)->firstWhere('@type', 'FAQPage');

        $this->assertNotNull($faq, 'No FAQPage JSON-LD found.');
        $this->assertNotEmpty($faq['mainEntity']);

        // Google requires FAQ structured data to match visible content.
        foreach ($faq['mainEntity'] as $question) {
            $this->assertStringContainsString(e($question['name']), $html);
        }
    }

    /**
     * The cluster articles' value is their internal links, which are hardcoded root-relative
     * strings in stored HTML - a renamed route or slug would silently 404 every one of them.
     */
    public function test_masjid_cluster_articles_seed_as_drafts_with_working_internal_links(): void
    {
        $this->seed(MasjidSeoArticleSeeder::class);
        $this->seed(MasjidSeoArticleSeeder::class);

        $articles = Article::whereIn('slug', MasjidSeoArticleSeeder::SLUGS)->get();
        $this->assertCount(count(MasjidSeoArticleSeeder::SLUGS), $articles, 'Re-seeding must not duplicate articles.');
        $this->assertTrue($articles->every(fn (Article $a) => $a->status === PublishStatus::Draft));

        $articles->each->update(['status' => PublishStatus::Published, 'published_at' => now()]);

        foreach ($articles as $article) {
            $this->assertStringContainsString('href="/website-masjid"', $article->body);

            preg_match_all('#href="(/[^"]*)"#', $article->body, $matches);
            foreach (array_unique($matches[1]) as $path) {
                $this->get($path)->assertOk();
            }
        }
    }

    public function test_it_is_listed_in_the_sitemap_and_linked_from_the_homepage(): void
    {
        $this->get(route('sitemap'))->assertOk()->assertSee(route('landing.masjid'), escape: false);
        $this->get(route('home'))->assertOk()->assertSee('href="'.route('landing.masjid').'"', escape: false);
    }
}
