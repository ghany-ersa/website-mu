<?php

namespace Tests\Feature;

use App\Enums\OrganizationStatus;
use App\Enums\PublishStatus;
use App\Models\Agenda;
use App\Models\Announcement;
use App\Models\Organization;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantDetailPagesTest extends TestCase
{
    use RefreshDatabase;

    private function tenantUrl(Organization $organization, string $path): string
    {
        return 'http://'.$organization->slug.'.'.config('tenancy.domain').$path;
    }

    public function test_post_detail_page_renders(): void
    {
        $organization = Organization::factory()->create(['status' => OrganizationStatus::Published]);
        $post = Post::factory()->create([
            'organization_id' => $organization->id,
            'slug' => 'berita-uji',
            'status' => PublishStatus::Published,
            'published_at' => now(),
        ]);

        $response = $this->get($this->tenantUrl($organization, '/berita/berita-uji'));

        $response->assertOk();
        $response->assertSee($post->title);
        $response->assertSee('<title>'.$post->title.' - '.$organization->name.'</title>', false);
        $response->assertSee('property="og:title" content="'.$post->title.' - '.$organization->name.'"', false);
        $response->assertSee('"@type":"NewsArticle"', false);
    }

    public function test_announcement_detail_page_renders(): void
    {
        $organization = Organization::factory()->create(['status' => OrganizationStatus::Published]);
        $announcement = Announcement::factory()->create([
            'organization_id' => $organization->id,
            'status' => PublishStatus::Published,
        ]);

        $response = $this->get($this->tenantUrl($organization, '/pengumuman/'.$announcement->id));

        $response->assertOk();
        $response->assertSee($announcement->title);
        $response->assertSee('<title>'.$announcement->title.' - '.$organization->name.'</title>', false);
    }

    public function test_agenda_detail_page_renders(): void
    {
        $organization = Organization::factory()->create(['status' => OrganizationStatus::Published]);
        $agenda = Agenda::factory()->create([
            'organization_id' => $organization->id,
            'status' => PublishStatus::Published,
            'starts_at' => now()->addDays(3),
        ]);

        $response = $this->get($this->tenantUrl($organization, '/agenda/'.$agenda->id));

        $response->assertOk();
        $response->assertSee($agenda->title);
        $response->assertSee('<title>'.$agenda->title.' - '.$organization->name.'</title>', false);
    }

    public function test_unpublished_post_404s(): void
    {
        $organization = Organization::factory()->create(['status' => OrganizationStatus::Published]);
        Post::factory()->create([
            'organization_id' => $organization->id,
            'slug' => 'draft-post',
            'status' => PublishStatus::Draft,
        ]);

        $response = $this->get($this->tenantUrl($organization, '/berita/draft-post'));

        $response->assertNotFound();
    }
}
