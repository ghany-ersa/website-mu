<?php

namespace Tests\Feature;

use App\Enums\OrganizationStatus;
use App\Models\GalleryPhoto;
use App\Models\Organization;
use App\Models\OrganizationPage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GaleriLoadMoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_initial_page_shows_only_limit_photos_with_a_load_more_button(): void
    {
        $organization = Organization::factory()->create([
            'status' => OrganizationStatus::Published,
            'slug' => 'smoke-galeri',
        ]);
        OrganizationPage::factory()->create([
            'organization_id' => $organization->id,
            'slug' => 'home',
            'is_home' => true,
        ])->sections()->create([
            'key' => 'galeri',
            'variant' => 'standar',
            'content' => ['title' => 'Galeri', 'limit' => 4],
            'order' => 0,
        ]);

        foreach (range(1, 12) as $i) {
            GalleryPhoto::factory()->create([
                'organization_id' => $organization->id,
                'url' => "https://example.com/{$i}.jpg",
                'caption' => "Foto {$i}",
                'order' => $i,
            ]);
        }

        $host = 'smoke-galeri.'.config('tenancy.domain');
        $response = $this->get("http://{$host}/");

        $response->assertOk();
        $response->assertSee('Foto 1', false);
        $response->assertDontSee('Foto 5', false);
        $response->assertSee('Muat Lebih Banyak', false);
    }

    public function test_load_more_endpoint_returns_next_batch_and_hasmore_flag(): void
    {
        $organization = Organization::factory()->create([
            'status' => OrganizationStatus::Published,
            'slug' => 'smoke-galeri-2',
        ]);

        foreach (range(1, 12) as $i) {
            GalleryPhoto::factory()->create([
                'organization_id' => $organization->id,
                'url' => "https://example.com/{$i}.jpg",
                'caption' => "Foto {$i}",
                'order' => $i,
            ]);
        }

        $host = 'smoke-galeri-2.'.config('tenancy.domain');

        $batch2 = $this->getJson("http://{$host}/galeri-lebih-banyak?offset=4&limit=4");
        $batch2->assertOk();
        $batch2->assertJsonPath('hasMore', true);
        $batch2->assertJsonPath('nextOffset', 8);
        $batch2->assertJsonPath('photos.0.caption', 'Foto 5');
        $batch2->assertJsonCount(4, 'photos');

        $batch3 = $this->getJson("http://{$host}/galeri-lebih-banyak?offset=8&limit=4");
        $batch3->assertOk();
        $batch3->assertJsonPath('hasMore', false);
        $batch3->assertJsonCount(4, 'photos');
    }
}
