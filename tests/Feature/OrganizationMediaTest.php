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
        Storage::fake(config('media.disk'));

        $user = User::factory()->create();
        $organization = Organization::factory()->create();
        $organization->members()->attach($user->id, ['role' => OrganizationRole::Owner->value]);

        $file = UploadedFile::fake()->image('logo.jpg', 800, 600);

        $this->actingAs($user)
            ->post(route('organizations.media.store', $organization), ['files' => [$file]])
            ->assertRedirect();

        $this->assertSame(1, $organization->media()->count());
        Storage::disk(config('media.disk'))->assertExists($organization->media()->first()->path);

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
        Storage::fake(config('media.disk'));

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

    /**
     * Every `category="..."` an x-form.image-picker passes must be accepted by
     * MediaController's whitelist. That list validates with `in:`, so an unlisted category does
     * not quietly fall back to 'lainnya' - it fails validation and rejects the whole upload with
     * a 422, which the picker used to swallow silently. 'agenda', 'fasilitas' and 'donasi' were
     * each unable to upload a single photo until they were added.
     *
     * Scans the views rather than hardcoding the names, so a picker added later with a new
     * category fails here instead of in production.
     */
    public function test_every_image_picker_category_is_accepted_by_the_media_controller(): void
    {
        Storage::fake(config('media.disk'));

        $categories = [];

        $views = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(resource_path('views'), \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($views as $file) {
            if (! $file->isFile() || ! str_ends_with($file->getFilename(), '.blade.php')) {
                continue;
            }

            if (preg_match_all('/category="([^"]+)"/', file_get_contents($file->getPathname()), $m)) {
                $categories = array_merge($categories, $m[1]);
            }
        }

        $categories = array_values(array_unique($categories));
        $this->assertNotEmpty($categories, 'no image-picker categories found - has the component changed?');

        $user = User::factory()->create();
        $organization = Organization::factory()->create();
        $organization->members()->attach($user->id, ['role' => OrganizationRole::Owner->value]);

        foreach ($categories as $category) {
            $this->actingAs($user)
                ->postJson(route('organizations.media.store', $organization), [
                    'files' => [UploadedFile::fake()->image('foto.jpg', 400, 300)],
                    'category' => $category,
                ])
                ->assertOk();
        }

        $this->assertSame(count($categories), $organization->media()->count());
    }
}
