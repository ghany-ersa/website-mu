<?php

namespace Tests\Feature;

use App\Enums\OrganizationRole;
use App\Models\Media;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OrganizationMediaTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_can_upload_and_list_media(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $organization = Organization::factory()->create();
        $organization->members()->attach($user->id, ['role' => OrganizationRole::Owner->value]);

        $file = UploadedFile::fake()->image('logo.jpg', 800, 600);

        $this->actingAs($user)
            ->post(route('organizations.media.store', $organization), ['files' => [$file]])
            ->assertRedirect();

        $this->assertSame(1, $organization->media()->count());
        Storage::disk('public')->assertExists($organization->media()->first()->path);

        $this->actingAs($user)
            ->getJson(route('organizations.media.index', $organization))
            ->assertOk()
            ->assertJsonCount(1);
    }

    public function test_non_member_cannot_upload_or_list_media(): void
    {
        $stranger = User::factory()->create();
        $organization = Organization::factory()->create();

        $this->actingAs($stranger)
            ->post(route('organizations.media.store', $organization), [])
            ->assertForbidden();

        $this->actingAs($stranger)
            ->getJson(route('organizations.media.index', $organization))
            ->assertForbidden();
    }

    public function test_member_can_delete_own_organization_media_but_not_another_organizations(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $organizationA = Organization::factory()->create();
        $organizationA->members()->attach($user->id, ['role' => OrganizationRole::Owner->value]);
        $organizationB = Organization::factory()->create();
        $organizationB->members()->attach($user->id, ['role' => OrganizationRole::Owner->value]);

        $mediaOfB = Media::factory()->create(['organization_id' => $organizationB->id]);

        $this->actingAs($user)
            ->delete(route('organizations.media.destroy', [$organizationA, $mediaOfB]))
            ->assertNotFound();

        $this->actingAs($user)
            ->delete(route('organizations.media.destroy', [$organizationB, $mediaOfB]))
            ->assertRedirect();

        $this->assertModelMissing($mediaOfB);
    }
}
