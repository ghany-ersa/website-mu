<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\ImageManager;

/**
 * Uploads a single cover image for a platform Article straight to the media disk (R2) and
 * hands back its public URL - no Media row, since articles aren't tenant-owned and only ever
 * need one cover image each, unlike the reusable per-organization library MediaController
 * serves the builder.
 */
class ArticleImageController extends Controller
{
    private const MAX_DIMENSION = 1920;

    private const WEBP_QUALITY = 80;

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'file' => ['required', 'image', 'max:10240'],
        ]);

        $manager = new ImageManager(new Driver);
        $disk = config('media.disk');

        $image = $manager->decodePath($validated['file']->getRealPath());
        $image->scaleDown(width: self::MAX_DIMENSION, height: self::MAX_DIMENSION);
        $encoded = $image->encode(new WebpEncoder(quality: self::WEBP_QUALITY));

        $path = 'articles/'.Str::uuid().'.webp';
        Storage::disk($disk)->put($path, (string) $encoded, 'public');

        return response()->json([
            'url' => Storage::disk($disk)->url($path),
        ]);
    }
}
