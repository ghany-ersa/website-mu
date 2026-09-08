<?php

namespace Tests\Feature;

use App\Models\Template;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TemplateThumbnailUploadTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return [
            'name' => 'Template Uji',
            'slug' => 'template-uji',
            'description' => 'Deskripsi',
            'structure' => json_encode(['pages' => []]),
            ...$overrides,
        ];
    }

    public function test_admin_can_upload_a_thumbnail_when_creating_a_template(): void
    {
        Storage::fake(config('media.disk'));

        $this->actingAs($this->admin())
            ->post(route('admin.templates.store'), $this->payload([
                'thumbnail' => UploadedFile::fake()->image('preview.jpg', 1200, 900),
            ]))
            ->assertRedirect();

        $template = Template::firstWhere('slug', 'template-uji');

        $this->assertNotNull($template->thumbnail_path);
        $this->assertStringEndsWith('.webp', $template->thumbnail_path);
        Storage::disk(config('media.disk'))->assertExists($template->thumbnail_path);
    }

    public function test_uploading_a_new_thumbnail_replaces_and_deletes_the_old_file(): void
    {
        Storage::fake($disk = config('media.disk'));

        $admin = $this->admin();
        $this->actingAs($admin)->post(route('admin.templates.store'), $this->payload([
            'thumbnail' => UploadedFile::fake()->image('first.jpg'),
        ]));

        $template = Template::firstWhere('slug', 'template-uji');
        $original = $template->thumbnail_path;

        $this->actingAs($admin)->put(route('admin.templates.update', $template), $this->payload([
            'thumbnail' => UploadedFile::fake()->image('second.jpg'),
        ]));

        $template->refresh();

        $this->assertNotSame($original, $template->thumbnail_path);
        Storage::disk($disk)->assertMissing($original);
        Storage::disk($disk)->assertExists($template->thumbnail_path);
    }

    /**
     * An edit that doesn't touch the file input must keep the existing image rather than
     * clearing it - the field is absent from the request in that case.
     */
    public function test_editing_without_choosing_a_file_keeps_the_existing_thumbnail(): void
    {
        Storage::fake(config('media.disk'));

        $admin = $this->admin();
        $this->actingAs($admin)->post(route('admin.templates.store'), $this->payload([
            'thumbnail' => UploadedFile::fake()->image('keep.jpg'),
        ]));

        $template = Template::firstWhere('slug', 'template-uji');
        $original = $template->thumbnail_path;

        $this->actingAs($admin)->put(
            route('admin.templates.update', $template),
            $this->payload(['name' => 'Nama Baru'])
        );

        $template->refresh();

        $this->assertSame('Nama Baru', $template->name);
        $this->assertSame($original, $template->thumbnail_path);
        Storage::disk(config('media.disk'))->assertExists($original);
    }

    public function test_remove_thumbnail_clears_the_field_and_deletes_the_file(): void
    {
        Storage::fake($disk = config('media.disk'));

        $admin = $this->admin();
        $this->actingAs($admin)->post(route('admin.templates.store'), $this->payload([
            'thumbnail' => UploadedFile::fake()->image('gone.jpg'),
        ]));

        $template = Template::firstWhere('slug', 'template-uji');
        $original = $template->thumbnail_path;

        $this->actingAs($admin)->put(route('admin.templates.update', $template), $this->payload([
            'remove_thumbnail' => '1',
        ]));

        $template->refresh();

        $this->assertNull($template->thumbnail_path);
        Storage::disk($disk)->assertMissing($original);
    }

    public function test_non_image_uploads_are_rejected(): void
    {
        Storage::fake(config('media.disk'));

        $this->actingAs($this->admin())
            ->post(route('admin.templates.store'), $this->payload([
                'thumbnail' => UploadedFile::fake()->create('brochure.pdf', 100, 'application/pdf'),
            ]))
            ->assertSessionHasErrors('thumbnail');

        $this->assertDatabaseMissing('templates', ['slug' => 'template-uji']);
    }

    /**
     * thumbnail_path predates uploading and may still hold an absolute URL, which must be
     * returned untouched rather than resolved against the media disk.
     */
    public function test_thumbnail_url_passes_through_absolute_urls(): void
    {
        $template = Template::factory()->create([
            'thumbnail_path' => 'https://example.com/shot.png',
        ]);

        $this->assertSame('https://example.com/shot.png', $template->thumbnailUrl());
    }

    public function test_thumbnail_url_is_null_without_a_thumbnail(): void
    {
        $this->assertNull(Template::factory()->create(['thumbnail_path' => null])->thumbnailUrl());
    }
}
