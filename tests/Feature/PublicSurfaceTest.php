<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Organization;
use App\Models\Template;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Everything an anonymous visitor (or a search engine crawler) sees on the main domain:
 * the landing page, the template catalog, the platform's own blog, and sitemap.xml.
 *
 * The recurring risk across all of them is leaking something unfinished - a draft article, an
 * inactive template, a sandbox organization - into a public, indexable page, so each test
 * seeds both a publishable and an unpublishable record and asserts on the difference.
 */
class PublicSurfaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_homepage_renders_for_a_guest(): void
    {
        $this->get(route('home'))->assertOk();
    }

    /**
     * The homepage grid is a curated preview, not the catalog - only templates an admin has
     * flagged is_featured belong there.
     */
    public function test_the_homepage_shows_only_featured_active_templates(): void
    {
        $featured = Template::factory()->create(['name' => 'Template Unggulan', 'is_featured' => true, 'is_active' => true]);
        $notFeatured = Template::factory()->create(['name' => 'Template Biasa', 'is_featured' => false, 'is_active' => true]);
        $inactive = Template::factory()->create(['name' => 'Template Nonaktif', 'is_featured' => true, 'is_active' => false]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee($featured->name)
            ->assertDontSee($notFeatured->name)
            ->assertDontSee($inactive->name);
    }

    public function test_the_template_catalog_renders_and_hides_inactive_templates(): void
    {
        $active = Template::factory()->create(['name' => 'Katalog Aktif', 'is_active' => true]);
        $inactive = Template::factory()->create(['name' => 'Katalog Nonaktif', 'is_active' => false]);

        $this->get(route('templates.index'))
            ->assertOk()
            ->assertSee($active->name)
            ->assertDontSee($inactive->name);
    }

    // --------------------------------------------------------------- articles

    public function test_the_article_index_lists_only_published_articles(): void
    {
        $published = Article::factory()->published()->create(['title' => 'Artikel Tayang']);
        $draft = Article::factory()->create(['title' => 'Artikel Draf']);

        $this->get(route('articles.index'))
            ->assertOk()
            ->assertSee($published->title, escape: false)
            ->assertDontSee($draft->title, escape: false);
    }

    public function test_a_published_article_is_readable(): void
    {
        $article = Article::factory()->published()->create();

        $this->get(route('articles.show', $article))
            ->assertOk()
            ->assertSee($article->title, escape: false);
    }

    public function test_a_draft_article_is_not_reachable(): void
    {
        $article = Article::factory()->create(); // draft by default

        $this->get(route('articles.show', $article))->assertNotFound();
    }

    /**
     * A future published_at means "scheduled", not "live" - the scope checks the date, not just
     * the status, so a scheduled article must stay unreachable until its moment arrives.
     */
    public function test_a_scheduled_article_is_not_reachable_before_its_publish_date(): void
    {
        $article = Article::factory()->published()->create(['published_at' => now()->addWeek()]);

        $this->get(route('articles.show', $article))->assertNotFound();
    }

    public function test_the_article_index_can_be_filtered_by_search_and_category(): void
    {
        $match = Article::factory()->published()->create(['title' => 'Digitalisasi Ranting', 'category' => 'Digitalisasi']);
        $other = Article::factory()->published()->create(['title' => 'Topik Berbeda Sekali', 'category' => 'Lainnya']);

        $this->get(route('articles.index', ['q' => 'Digitalisasi Ranting']))
            ->assertOk()
            ->assertSee($match->title, escape: false)
            ->assertDontSee($other->title, escape: false);

        $this->get(route('articles.index', ['category' => 'Lainnya']))
            ->assertOk()
            ->assertSee($other->title, escape: false)
            ->assertDontSee($match->title, escape: false);
    }

    // ---------------------------------------------------------------- sitemap

    public function test_the_sitemap_is_valid_xml_and_lists_the_homepage(): void
    {
        $response = $this->get(route('sitemap'));

        $response->assertOk()->assertHeader('Content-Type', 'application/xml');

        $xml = simplexml_load_string($response->getContent());

        $this->assertNotFalse($xml, 'sitemap.xml did not parse as XML.');
        $this->assertGreaterThanOrEqual(1, count($xml->url));
    }

    /**
     * The sitemap is what search engines index, so a draft or sandbox organization appearing
     * here would advertise a URL that 404s (draft) or was never meant to be public (sandbox).
     */
    public function test_the_sitemap_lists_published_organizations_only(): void
    {
        $published = Organization::factory()->published()->create(['slug' => 'situs-tayang']);
        $draft = Organization::factory()->create(['slug' => 'situs-draf']);

        $content = $this->get(route('sitemap'))->assertOk()->getContent();

        $this->assertStringContainsString($published->slug, $content);
        $this->assertStringNotContainsString($draft->slug, $content);
    }

    public function test_the_sitemap_excludes_sandbox_organizations(): void
    {
        $sandbox = Organization::factory()->published()->create([
            'slug' => 'situs-sandbox',
            'is_sandbox' => true,
        ]);

        $this->assertStringNotContainsString(
            $sandbox->slug,
            $this->get(route('sitemap'))->getContent(),
            'A sandbox organization must never be advertised to crawlers.'
        );
    }

    // ------------------------------------------------------------ hardening

    public function test_pages_are_served_with_a_clickjacking_header(): void
    {
        $this->get(route('home'))->assertHeader('X-Frame-Options', 'SAMEORIGIN');
    }
}
