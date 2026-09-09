<?php

namespace Tests\Feature;

use App\Enums\OrganizationStatus;
use App\Enums\PublishStatus;
use App\Models\Organization;
use App\Models\OrganizationPage;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BeritaLoadMoreTest extends TestCase
{
    use RefreshDatabase;

    private function makeOrganization(string $slug): Organization
    {
        return Organization::factory()->create([
            'status' => OrganizationStatus::Published,
            'slug' => $slug,
        ]);
    }

    public function test_initial_page_shows_only_limit_items_with_a_load_more_button(): void
    {
        $organization = $this->makeOrganization('smoke-berita');
        $page = OrganizationPage::factory()->create([
            'organization_id' => $organization->id,
            'slug' => 'berita',
            'is_home' => true,
        ]);
        $page->sections()->create([
            'key' => 'daftar-berita',
            'variant' => 'standar',
            'content' => ['title' => 'Berita', 'limit' => 5],
            'order' => 0,
        ]);

        foreach (range(1, 12) as $i) {
            Post::factory()->create([
                'organization_id' => $organization->id,
                'title' => "Berita Nomor {$i}",
                'status' => PublishStatus::Published,
                'published_at' => now()->subDays($i),
            ]);
        }

        $host = 'smoke-berita.'.config('tenancy.domain');
        $response = $this->get("http://{$host}/");

        $response->assertOk();
        $response->assertSee('Berita Nomor 1', false);
        $response->assertDontSee('Berita Nomor 6', false);
        $response->assertSee('Muat Lebih Banyak', false);
    }

    public function test_load_more_endpoint_returns_next_batch_and_hasmore_flag(): void
    {
        $organization = $this->makeOrganization('smoke-berita-2');
        $page = OrganizationPage::factory()->create([
            'organization_id' => $organization->id,
            'slug' => 'berita',
            'is_home' => true,
        ]);
        $page->sections()->create([
            'key' => 'daftar-berita',
            'variant' => 'standar',
            'content' => ['title' => 'Berita', 'limit' => 5],
            'order' => 0,
        ]);

        foreach (range(1, 12) as $i) {
            Post::factory()->create([
                'organization_id' => $organization->id,
                'title' => "Berita Nomor {$i}",
                'status' => PublishStatus::Published,
                'published_at' => now()->subDays($i),
            ]);
        }

        $host = 'smoke-berita-2.'.config('tenancy.domain');

        $batch2 = $this->getJson("http://{$host}/berita-lebih-banyak?variant=standar&offset=5&limit=5");
        $batch2->assertOk();
        $batch2->assertJsonPath('hasMore', true);
        $batch2->assertJsonPath('nextOffset', 10);
        $this->assertStringContainsString('Berita Nomor 6', $batch2->json('html'));
        $this->assertStringNotContainsString('Berita Nomor 1<', $batch2->json('html'));

        $batch3 = $this->getJson("http://{$host}/berita-lebih-banyak?variant=standar&offset=10&limit=5");
        $batch3->assertOk();
        $batch3->assertJsonPath('hasMore', false);
        $this->assertStringContainsString('Berita Nomor 11', $batch3->json('html'));
    }

    /**
     * Regression guard for the multi-section collision bug: two daftar-berita sections on one
     * page must each drive their own independent Alpine x-data scope and their own
     * category_filter against the load-more endpoint - fetching more for one must never surface
     * the other section's posts.
     */
    public function test_load_more_respects_category_filter_for_multi_section_pages(): void
    {
        $organization = $this->makeOrganization('smoke-berita-3');
        $page = OrganizationPage::factory()->create([
            'organization_id' => $organization->id,
            'slug' => 'berita',
            'is_home' => true,
        ]);
        $page->sections()->create([
            'key' => 'daftar-berita',
            'variant' => 'standar',
            'content' => ['title' => 'Organisasi', 'category_filter' => 'Organisasi', 'limit' => 3],
            'order' => 0,
        ]);
        $page->sections()->create([
            'key' => 'daftar-berita',
            'variant' => 'ringkas',
            'content' => ['title' => 'Kaderisasi', 'category_filter' => 'Kaderisasi', 'limit' => 3],
            'order' => 1,
        ]);

        foreach (range(1, 6) as $i) {
            Post::factory()->create([
                'organization_id' => $organization->id,
                'title' => "Organisasi Nomor {$i}",
                'category' => 'Organisasi',
                'status' => PublishStatus::Published,
                'published_at' => now()->subDays($i),
            ]);
        }
        foreach (range(1, 6) as $i) {
            Post::factory()->create([
                'organization_id' => $organization->id,
                'title' => "Kaderisasi Nomor {$i}",
                'category' => 'Kaderisasi',
                'status' => PublishStatus::Published,
                'published_at' => now()->subDays($i),
            ]);
        }

        $host = 'smoke-berita-3.'.config('tenancy.domain');

        $response = $this->getJson("http://{$host}/berita-lebih-banyak?variant=standar&offset=3&limit=3&category_filter=Organisasi");
        $response->assertOk();
        $this->assertStringContainsString('Organisasi Nomor 4', $response->json('html'));
        $this->assertStringNotContainsString('Kaderisasi', $response->json('html'));
    }
}
