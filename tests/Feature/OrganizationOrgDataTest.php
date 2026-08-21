<?php

namespace Tests\Feature;

use App\Enums\OrganizationRole;
use App\Models\GalleryPhoto;
use App\Models\Officer;
use App\Models\Organization;
use App\Models\OrganizationNetwork;
use App\Models\OrganizationPage;
use App\Models\Program;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganizationOrgDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_can_create_update_delete_and_reorder_officers(): void
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create();
        $organization->members()->attach($user->id, ['role' => OrganizationRole::Owner->value]);

        $this->actingAs($user)
            ->post(route('organizations.officers.store', $organization), [
                'name' => 'Ahmad Fulan',
                'role' => 'Ketua',
            ])
            ->assertRedirect();

        $officer = $organization->officers()->first();
        $this->assertNotNull($officer);

        $this->actingAs($user)
            ->patch(route('organizations.officers.update', [$organization, $officer]), [
                'name' => 'Ahmad Fulan',
                'role' => 'Sekretaris',
            ])
            ->assertRedirect();
        $this->assertSame('Sekretaris', $officer->fresh()->role);

        $second = Officer::factory()->create(['organization_id' => $organization->id, 'order' => 1]);
        $ids = [$second->id, $officer->id];
        $this->actingAs($user)
            ->post(route('organizations.officers.reorder', $organization), ['officer_ids' => $ids])
            ->assertRedirect();
        $this->assertSame($ids, $organization->officers()->pluck('id')->all());

        $this->actingAs($user)
            ->delete(route('organizations.officers.destroy', [$organization, $officer]))
            ->assertRedirect();
        $this->assertSame(1, $organization->officers()->count());
    }

    public function test_member_can_create_program_and_layanan_as_separate_pools(): void
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create();
        $organization->members()->attach($user->id, ['role' => OrganizationRole::Owner->value]);

        $this->actingAs($user)
            ->post(route('organizations.programs.store', $organization).'?type=program', [
                'title' => 'Program Beasiswa',
            ])
            ->assertRedirect();

        $this->actingAs($user)
            ->post(route('organizations.programs.store', $organization).'?type=layanan', [
                'title' => 'Layanan Konsultasi',
            ])
            ->assertRedirect();

        $this->assertSame(1, $organization->programs()->ofType('program')->count());
        $this->assertSame(1, $organization->programs()->ofType('layanan')->count());
    }

    public function test_member_can_create_update_and_delete_network_entries(): void
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create();
        $organization->members()->attach($user->id, ['role' => OrganizationRole::Owner->value]);

        $this->actingAs($user)
            ->post(route('organizations.networks.store', $organization), [
                'name' => 'SD Muhammadiyah 1',
                'type' => 'AUM Pendidikan',
            ])
            ->assertRedirect();

        $network = $organization->networks()->first();
        $this->assertNotNull($network);

        $this->actingAs($user)
            ->delete(route('organizations.networks.destroy', [$organization, $network]))
            ->assertRedirect();
        $this->assertSame(0, $organization->networks()->count());
    }

    public function test_member_can_create_update_delete_and_reorder_gallery_photos(): void
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create();
        $organization->members()->attach($user->id, ['role' => OrganizationRole::Owner->value]);

        $this->actingAs($user)
            ->post(route('organizations.gallery.store', $organization), [
                'url' => 'https://example.test/foto-1.jpg',
                'caption' => 'Kegiatan bakti sosial',
            ])
            ->assertRedirect();

        $photo = $organization->photos()->first();
        $this->assertNotNull($photo);

        $this->actingAs($user)
            ->patch(route('organizations.gallery.update', [$organization, $photo]), [
                'url' => 'https://example.test/foto-1.jpg',
                'caption' => 'Bakti sosial Ramadan',
            ])
            ->assertRedirect();
        $this->assertSame('Bakti sosial Ramadan', $photo->fresh()->caption);

        $second = GalleryPhoto::factory()->create(['organization_id' => $organization->id, 'order' => 1]);
        $ids = [$second->id, $photo->id];
        $this->actingAs($user)
            ->post(route('organizations.gallery.reorder', $organization), ['photo_ids' => $ids])
            ->assertRedirect();
        $this->assertSame($ids, $organization->photos()->pluck('id')->all());

        $this->actingAs($user)
            ->delete(route('organizations.gallery.destroy', [$organization, $photo]))
            ->assertRedirect();
        $this->assertSame(1, $organization->photos()->count());
    }

    public function test_non_member_cannot_manage_officers_programs_networks_or_gallery(): void
    {
        $stranger = User::factory()->create();
        $organization = Organization::factory()->create();

        $this->actingAs($stranger)->get(route('organizations.officers.index', $organization))->assertForbidden();
        $this->actingAs($stranger)->get(route('organizations.programs.index', $organization))->assertForbidden();
        $this->actingAs($stranger)->get(route('organizations.networks.index', $organization))->assertForbidden();
        $this->actingAs($stranger)->get(route('organizations.gallery.index', $organization))->assertForbidden();
    }

    public function test_officer_from_another_organization_is_not_found(): void
    {
        $user = User::factory()->create();
        $organizationA = Organization::factory()->create();
        $organizationA->members()->attach($user->id, ['role' => OrganizationRole::Owner->value]);
        $organizationB = Organization::factory()->create();
        $organizationB->members()->attach($user->id, ['role' => OrganizationRole::Owner->value]);

        $officerOfB = Officer::factory()->create(['organization_id' => $organizationB->id]);

        $this->actingAs($user)
            ->get(route('organizations.officers.edit', [$organizationA, $officerOfB]))
            ->assertNotFound();
    }

    public function test_gallery_photo_from_another_organization_is_not_found(): void
    {
        $user = User::factory()->create();
        $organizationA = Organization::factory()->create();
        $organizationA->members()->attach($user->id, ['role' => OrganizationRole::Owner->value]);
        $organizationB = Organization::factory()->create();
        $organizationB->members()->attach($user->id, ['role' => OrganizationRole::Owner->value]);

        $photoOfB = GalleryPhoto::factory()->create(['organization_id' => $organizationB->id]);

        $this->actingAs($user)
            ->get(route('organizations.gallery.edit', [$organizationA, $photoOfB]))
            ->assertNotFound();
    }

    public function test_struktur_pengurus_section_auto_binds_officers_on_tenant_page(): void
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create();
        $organization->members()->attach($user->id, ['role' => OrganizationRole::Owner->value]);
        $page = OrganizationPage::factory()->create(['organization_id' => $organization->id, 'slug' => 'home']);
        $page->sections()->create(['key' => 'struktur-pengurus', 'content' => [], 'order' => 0]);

        Officer::factory()->create(['organization_id' => $organization->id, 'name' => 'Budi Santoso', 'role' => 'Ketua']);

        $response = $this->actingAs($user)->get(route('organizations.builder.canvas', [$organization, $page]));

        $response->assertOk();
        $response->assertSee('Budi Santoso');
    }

    public function test_program_unggulan_and_layanan_sections_auto_bind_to_separate_pools(): void
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create();
        $organization->members()->attach($user->id, ['role' => OrganizationRole::Owner->value]);
        $page = OrganizationPage::factory()->create(['organization_id' => $organization->id, 'slug' => 'home']);
        $page->sections()->create(['key' => 'program-unggulan', 'content' => [], 'order' => 0]);
        $page->sections()->create(['key' => 'layanan', 'content' => [], 'order' => 1]);

        Program::factory()->create(['organization_id' => $organization->id, 'type' => 'program', 'title' => 'Program Beasiswa Piatu']);
        Program::factory()->service()->create(['organization_id' => $organization->id, 'title' => 'Layanan Konsultasi Keluarga']);

        $response = $this->actingAs($user)->get(route('organizations.builder.canvas', [$organization, $page]));

        $response->assertOk();
        $response->assertSee('Program Beasiswa Piatu');
        $response->assertSee('Layanan Konsultasi Keluarga');
    }

    public function test_jaringan_aum_ortom_section_auto_binds_on_tenant_page(): void
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create();
        $organization->members()->attach($user->id, ['role' => OrganizationRole::Owner->value]);
        $page = OrganizationPage::factory()->create(['organization_id' => $organization->id, 'slug' => 'home']);
        $page->sections()->create(['key' => 'jaringan-aum-ortom', 'content' => [], 'order' => 0]);

        OrganizationNetwork::factory()->create(['organization_id' => $organization->id, 'name' => 'SMP Muhammadiyah 2']);

        $response = $this->actingAs($user)->get(route('organizations.builder.canvas', [$organization, $page]));

        $response->assertOk();
        $response->assertSee('SMP Muhammadiyah 2');
    }

    public function test_galeri_section_auto_binds_gallery_photos_on_tenant_page(): void
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create();
        $organization->members()->attach($user->id, ['role' => OrganizationRole::Owner->value]);
        $page = OrganizationPage::factory()->create(['organization_id' => $organization->id, 'slug' => 'home']);
        $page->sections()->create(['key' => 'galeri', 'content' => [], 'order' => 0]);

        GalleryPhoto::factory()->create([
            'organization_id' => $organization->id,
            'url' => 'https://example.test/foto-kegiatan.jpg',
            'caption' => 'Kegiatan bakti sosial',
        ]);

        $response = $this->actingAs($user)->get(route('organizations.builder.canvas', [$organization, $page]));

        $response->assertOk();
        $response->assertSee('https://example.test/foto-kegiatan.jpg', false);
        $response->assertSee('Kegiatan bakti sosial');
    }
}
