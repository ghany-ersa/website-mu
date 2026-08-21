<?php

namespace Tests\Feature;

use App\Enums\OrganizationRole;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganizationEditTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_can_update_name(): void
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create(['name' => 'Nama Lama']);
        $organization->members()->attach($user->id, ['role' => OrganizationRole::Owner->value]);

        $this->actingAs($user)
            ->patch(route('organizations.edit.name.update', $organization), ['name' => 'Nama Baru'])
            ->assertRedirect();

        $this->assertSame('Nama Baru', $organization->fresh()->name);
    }

    public function test_name_is_required(): void
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create(['name' => 'Nama Lama']);
        $organization->members()->attach($user->id, ['role' => OrganizationRole::Owner->value]);

        $this->actingAs($user)
            ->patch(route('organizations.edit.name.update', $organization), ['name' => ''])
            ->assertSessionHasErrors('name');

        $this->assertSame('Nama Lama', $organization->fresh()->name);
    }

    public function test_member_can_update_slug(): void
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create(['slug' => 'org-lama']);
        $organization->members()->attach($user->id, ['role' => OrganizationRole::Owner->value]);

        $this->actingAs($user)
            ->patch(route('organizations.edit.slug.update', $organization), ['slug' => 'org-baru'])
            ->assertRedirect();

        $this->assertSame('org-baru', $organization->fresh()->slug);
    }

    public function test_slug_must_be_unique(): void
    {
        $user = User::factory()->create();
        Organization::factory()->create(['slug' => 'sudah-dipakai']);
        $organization = Organization::factory()->create(['slug' => 'org-saya']);
        $organization->members()->attach($user->id, ['role' => OrganizationRole::Owner->value]);

        $this->actingAs($user)
            ->patch(route('organizations.edit.slug.update', $organization), ['slug' => 'sudah-dipakai'])
            ->assertSessionHasErrors('slug');

        $this->assertSame('org-saya', $organization->fresh()->slug);
    }

    public function test_reserved_slug_is_rejected(): void
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create(['slug' => 'org-saya']);
        $organization->members()->attach($user->id, ['role' => OrganizationRole::Owner->value]);

        $this->actingAs($user)
            ->patch(route('organizations.edit.slug.update', $organization), ['slug' => 'admin'])
            ->assertSessionHasErrors('slug');
    }

    public function test_organization_can_keep_its_own_slug(): void
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create(['slug' => 'org-saya']);
        $organization->members()->attach($user->id, ['role' => OrganizationRole::Owner->value]);

        $this->actingAs($user)
            ->patch(route('organizations.edit.slug.update', $organization), ['slug' => 'org-saya'])
            ->assertRedirect()
            ->assertSessionDoesntHaveErrors();
    }

    public function test_member_can_update_description(): void
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create();
        $organization->members()->attach($user->id, ['role' => OrganizationRole::Owner->value]);

        $this->actingAs($user)
            ->patch(route('organizations.edit.description.update', $organization), ['description' => 'Deskripsi organisasi.'])
            ->assertRedirect();

        $this->assertSame('Deskripsi organisasi.', $organization->fresh()->description);
    }

    public function test_description_can_be_cleared(): void
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create(['description' => 'Ada isi.']);
        $organization->members()->attach($user->id, ['role' => OrganizationRole::Owner->value]);

        $this->actingAs($user)
            ->patch(route('organizations.edit.description.update', $organization), ['description' => ''])
            ->assertRedirect()
            ->assertSessionDoesntHaveErrors();

        $this->assertNull($organization->fresh()->description);
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

    public function test_non_member_cannot_view_or_update_organization_edit_settings(): void
    {
        $stranger = User::factory()->create();
        $organization = Organization::factory()->create();

        $this->actingAs($stranger)->get(route('organizations.edit.edit', $organization))->assertForbidden();
        $this->actingAs($stranger)->patch(route('organizations.edit.name.update', $organization), [])->assertForbidden();
        $this->actingAs($stranger)->patch(route('organizations.edit.slug.update', $organization), [])->assertForbidden();
        $this->actingAs($stranger)->patch(route('organizations.edit.description.update', $organization), [])->assertForbidden();
    }
}
