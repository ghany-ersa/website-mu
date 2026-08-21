<?php

namespace Tests\Feature;

use App\Enums\OrganizationRole;
use App\Models\Agenda;
use App\Models\Announcement;
use App\Models\Organization;
use App\Models\OrganizationPage;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganizationCmsTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_can_create_update_and_delete_a_post(): void
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create();
        $organization->members()->attach($user->id, ['role' => OrganizationRole::Owner->value]);

        $this->actingAs($user)
            ->post(route('organizations.posts.store', $organization), [
                'title' => 'Kegiatan Bakti Sosial',
                'category' => 'Kegiatan',
                'excerpt' => 'Ringkasan singkat',
                'body' => 'Isi lengkap berita',
                'status' => 'published',
            ])
            ->assertRedirect(route('organizations.posts.index', $organization));

        $post = $organization->posts()->first();
        $this->assertNotNull($post);
        $this->assertSame('kegiatan-bakti-sosial', $post->slug);
        $this->assertNotNull($post->published_at);

        $this->actingAs($user)
            ->patch(route('organizations.posts.update', [$organization, $post]), [
                'title' => 'Kegiatan Bakti Sosial (Update)',
                'status' => 'draft',
            ])
            ->assertRedirect();
        $this->assertSame('Kegiatan Bakti Sosial (Update)', $post->fresh()->title);

        $this->actingAs($user)
            ->delete(route('organizations.posts.destroy', [$organization, $post]))
            ->assertRedirect();
        $this->assertSame(0, $organization->posts()->count());
    }

    public function test_non_member_cannot_manage_posts_agendas_or_announcements(): void
    {
        $stranger = User::factory()->create();
        $organization = Organization::factory()->create();

        $this->actingAs($stranger)->get(route('organizations.posts.index', $organization))->assertForbidden();
        $this->actingAs($stranger)->get(route('organizations.agendas.index', $organization))->assertForbidden();
        $this->actingAs($stranger)->get(route('organizations.announcements.index', $organization))->assertForbidden();
    }

    public function test_post_from_another_organization_is_not_found(): void
    {
        $user = User::factory()->create();
        $organizationA = Organization::factory()->create();
        $organizationA->members()->attach($user->id, ['role' => OrganizationRole::Owner->value]);
        $organizationB = Organization::factory()->create();
        $organizationB->members()->attach($user->id, ['role' => OrganizationRole::Owner->value]);

        $postOfB = Post::factory()->create(['organization_id' => $organizationB->id]);

        $this->actingAs($user)
            ->get(route('organizations.posts.edit', [$organizationA, $postOfB]))
            ->assertNotFound();
    }

    public function test_member_can_create_agenda_and_announcement(): void
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create();
        $organization->members()->attach($user->id, ['role' => OrganizationRole::Owner->value]);

        $this->actingAs($user)
            ->post(route('organizations.agendas.store', $organization), [
                'title' => 'Rapat Koordinasi',
                'starts_at' => now()->addDays(3)->format('Y-m-d\TH:i'),
                'location' => 'Kantor PCM',
                'status' => 'published',
            ])
            ->assertRedirect();
        $this->assertSame(1, $organization->agendas()->count());

        $this->actingAs($user)
            ->post(route('organizations.announcements.store', $organization), [
                'title' => 'Libur Idul Fitri',
                'priority' => 'Tinggi',
                'status' => 'published',
            ])
            ->assertRedirect();
        $this->assertSame(1, $organization->announcements()->count());
    }

    public function test_daftar_berita_section_auto_binds_published_posts_on_tenant_page(): void
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create();
        $organization->members()->attach($user->id, ['role' => OrganizationRole::Owner->value]);
        $page = OrganizationPage::factory()->create(['organization_id' => $organization->id, 'slug' => 'home']);
        $page->sections()->create(['key' => 'daftar-berita', 'content' => [], 'order' => 0]);

        Post::factory()->published()->create(['organization_id' => $organization->id, 'title' => 'Berita Terbit']);
        Post::factory()->create(['organization_id' => $organization->id, 'title' => 'Berita Draf']);

        $response = $this->actingAs($user)->get(route('organizations.builder.canvas', [$organization, $page]));

        $response->assertOk();
        $response->assertSee('Berita Terbit');
        $response->assertDontSee('Berita Draf');
    }

    public function test_agenda_and_pengumuman_sections_auto_bind_on_tenant_page(): void
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create();
        $organization->members()->attach($user->id, ['role' => OrganizationRole::Owner->value]);
        $page = OrganizationPage::factory()->create(['organization_id' => $organization->id, 'slug' => 'home']);
        $page->sections()->create(['key' => 'agenda', 'content' => [], 'order' => 0]);
        $page->sections()->create(['key' => 'pengumuman', 'content' => [], 'order' => 1]);

        Agenda::factory()->published()->create([
            'organization_id' => $organization->id,
            'title' => 'Agenda Terbit',
            'starts_at' => now()->addDay(),
        ]);
        Announcement::factory()->published()->create([
            'organization_id' => $organization->id,
            'title' => 'Pengumuman Terbit',
            'valid_until' => now()->addWeek(),
        ]);

        $response = $this->actingAs($user)->get(route('organizations.builder.canvas', [$organization, $page]));

        $response->assertOk();
        $response->assertSee('Agenda Terbit');
        $response->assertSee('Pengumuman Terbit');
    }
}
