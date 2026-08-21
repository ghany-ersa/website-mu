<?php

namespace Tests\Feature;

use App\Enums\OrganizationRole;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganizationSeoTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_can_update_name_slug_and_description(): void
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create(['name' => 'Nama Lama', 'slug' => 'org-lama']);
        $organization->members()->attach($user->id, ['role' => OrganizationRole::Owner->value]);

        $this->actingAs($user)
            ->patch(route('organizations.seo.update', $organization), [
                'name' => 'Nama Baru',
                'slug' => 'org-baru',
                'description' => 'Deskripsi organisasi.',
            ])
            ->assertRedirect();

        $organization->refresh();
        $this->assertSame('Nama Baru', $organization->name);
        $this->assertSame('org-baru', $organization->slug);
        $this->assertSame('Deskripsi organisasi.', $organization->description);
    }

    public function test_name_is_required(): void
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create(['slug' => 'org-saya']);
        $organization->members()->attach($user->id, ['role' => OrganizationRole::Owner->value]);

        $this->actingAs($user)
            ->patch(route('organizations.seo.update', $organization), [
                'slug' => 'org-saya',
                'name' => '',
            ])
            ->assertSessionHasErrors('name');
    }

    public function test_public_site_title_and_meta_description_come_from_name_and_description(): void
    {
        $organization = Organization::factory()->create([
            'name' => 'PCM Ambulu',
            'description' => 'Deskripsi umum organisasi.',
        ]);
        $organization->ensureHomePageExists();

        $this->assertSame('PCM Ambulu', $organization->name);
        $this->assertSame('Deskripsi umum organisasi.', $organization->description);
    }

    public function test_slug_must_be_unique(): void
    {
        $user = User::factory()->create();
        Organization::factory()->create(['slug' => 'sudah-dipakai']);
        $organization = Organization::factory()->create(['slug' => 'org-saya']);
        $organization->members()->attach($user->id, ['role' => OrganizationRole::Owner->value]);

        $this->actingAs($user)
            ->patch(route('organizations.seo.update', $organization), [
                'slug' => 'sudah-dipakai',
            ])
            ->assertSessionHasErrors('slug');

        $this->assertSame('org-saya', $organization->fresh()->slug);
    }

    public function test_reserved_slug_is_rejected(): void
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create(['slug' => 'org-saya']);
        $organization->members()->attach($user->id, ['role' => OrganizationRole::Owner->value]);

        $this->actingAs($user)
            ->patch(route('organizations.seo.update', $organization), [
                'slug' => 'admin',
            ])
            ->assertSessionHasErrors('slug');
    }

    public function test_organization_can_keep_its_own_slug(): void
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create(['slug' => 'org-saya']);
        $organization->members()->attach($user->id, ['role' => OrganizationRole::Owner->value]);

        $this->actingAs($user)
            ->patch(route('organizations.seo.update', $organization), [
                'name' => $organization->name,
                'slug' => 'org-saya',
            ])
            ->assertRedirect()
            ->assertSessionDoesntHaveErrors();
    }

    public function test_non_member_cannot_view_or_update_seo_settings(): void
    {
        $stranger = User::factory()->create();
        $organization = Organization::factory()->create();

        $this->actingAs($stranger)->get(route('organizations.seo.edit', $organization))->assertForbidden();
        $this->actingAs($stranger)->patch(route('organizations.seo.update', $organization), [])->assertForbidden();
    }
}
