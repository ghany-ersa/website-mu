<?php

namespace App\Http\Controllers;

use App\Models\Media;
use App\Models\Organization;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\ImageManager;

class MediaController extends Controller
{
    /**
     * Longest side, in pixels, an uploaded image is downscaled to before storage.
     */
    private const MAX_DIMENSION = 1920;

    /**
     * WebP re-encode quality (0-100). Applied after any downscaling.
     */
    private const WEBP_QUALITY = 80;

    /**
     * Valid values for the 'category' upload param, used as the storage
     * subfolder under organizations/{id}/. Requests without a recognized
     * category fall back to 'lainnya' rather than an arbitrary path segment.
     */
    private const CATEGORIES = ['berita', 'pengurus', 'galeri', 'brand', 'builder'];

    /**
     * List an organization's media library as JSON, for the builder's image picker.
     */
    public function index(Organization $organization): JsonResponse
    {
        $this->authorize('viewAny', [Media::class, $organization]);

        return response()->json(
            $organization->media->map(fn (Media $item) => [
                'id' => $item->id,
                'url' => $item->url(),
                'original_name' => $item->original_name,
            ])
        );
    }

    /**
     * Upload one or more images into the organization's media library.
     */
    public function store(Request $request, Organization $organization): RedirectResponse|JsonResponse
    {
        $this->authorize('create', [Media::class, $organization]);

        $validated = $request->validate([
            'files' => ['required', 'array'],
            'files.*' => ['image', 'max:10240'],
            'category' => ['nullable', 'string', 'in:'.implode(',', self::CATEGORIES)],
        ]);

        $manager = new ImageManager(new Driver);
        $disk = config('media.disk');
        $category = $validated['category'] ?? 'lainnya';

        $uploaded = collect($validated['files'])->map(function ($file) use ($organization, $request, $manager, $disk, $category) {
            $image = $manager->decodePath($file->getRealPath());
            $image->scaleDown(width: self::MAX_DIMENSION, height: self::MAX_DIMENSION);
            $encoded = $image->encode(new WebpEncoder(quality: self::WEBP_QUALITY));

            $filename = Str::uuid().'.webp';
            $path = "organizations/{$organization->id}/{$category}/{$filename}";
            Storage::disk($disk)->put($path, (string) $encoded, 'public');

            $media = $organization->media()->create([
                'uploaded_by' => $request->user()->id,
                'disk' => $disk,
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => 'image/webp',
                'size' => $encoded->size(),
                'width' => $image->width(),
                'height' => $image->height(),
            ]);

            return ['id' => $media->id, 'url' => $media->url(), 'original_name' => $media->original_name];
        });

        if ($request->wantsJson()) {
            return response()->json($uploaded);
        }

        return back()->with('status', 'Gambar berhasil diunggah.');
    }

    /**
     * Delete a media item and its underlying file.
     */
    public function destroy(Organization $organization, Media $media): RedirectResponse
    {
        $this->authorize('delete', $media);

        if ($media->organization_id !== $organization->id) {
            abort(404);
        }

        Storage::disk($media->disk)->delete($media->path);
        $media->delete();

        return back()->with('status', 'Gambar berhasil dihapus.');
    }
}
