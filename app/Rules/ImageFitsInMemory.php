<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;

/**
 * Rejects an upload whose decoded bitmap would not fit in PHP's memory - the "decompression
 * bomb" case, where a small, highly compressed file declares enormous dimensions and the GD
 * decode in MediaController/ArticleImageController/Admin\TemplateController exhausts
 * memory_limit with a fatal error.
 *
 * Replaces Laravel's `dimensions:max_width=8000,max_height=8000`, which got two things wrong:
 *   - It FAILS when getimagesize() can't read the header. Laravel's `image` rule accepts
 *     HEIC/HEIF (iPhone photos), which getimagesize() on PHP 8.4 can't read, so every such
 *     photo got "The files.0 field has invalid image dimensions" instead of the decoder's own
 *     "simpan ulang sebagai JPG atau PNG" message. Here an unreadable header passes, and the
 *     decoder's DecoderException branch explains it.
 *   - A per-side cap is the wrong measure: an ordinary 50MP phone photo (8160x6120) is over
 *     8000px wide yet decodes fine, while the real limit is total pixels against memory.
 */
class ImageFitsInMemory implements ValidationRule
{
    /**
     * GD keeps a truecolor image at 4 bytes per pixel, plus working overhead during decode and
     * the scaled-down copy - 5 leaves headroom without being so pessimistic that ordinary
     * photos are refused.
     */
    private const BYTES_PER_PIXEL = 5;

    /**
     * Hard ceiling regardless of memory_limit (including an unlimited -1): nothing a tenant
     * legitimately uploads within the 10MB file cap needs more, and an unlimited worker should
     * still not be told to allocate gigabytes.
     */
    private const MAX_PIXELS = 100_000_000;

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $value instanceof UploadedFile) {
            return;
        }

        $size = @getimagesize($value->getRealPath());

        if (! $size || ! $size[0] || ! $size[1]) {
            return;
        }

        $pixels = $size[0] * $size[1];

        if ($pixels > self::MAX_PIXELS || $pixels * self::BYTES_PER_PIXEL > self::availableMemory()) {
            $fail("Gambar \"{$value->getClientOriginalName()}\" terlalu besar ({$size[0]}x{$size[1]} piksel). Perkecil ukurannya lalu unggah lagi.");
        }
    }

    private static function availableMemory(): float
    {
        $limit = self::toBytes((string) ini_get('memory_limit'));

        return $limit < 0 ? INF : $limit - memory_get_usage(true);
    }

    /**
     * memory_limit in php.ini shorthand ("128M", "1G", "-1") to bytes; -1 means unlimited.
     */
    private static function toBytes(string $value): int
    {
        $value = trim($value);

        if ($value === '' || $value === '-1') {
            return -1;
        }

        $number = (int) $value;

        return match (strtolower(substr($value, -1))) {
            'g' => $number * 1024 ** 3,
            'm' => $number * 1024 ** 2,
            'k' => $number * 1024,
            default => $number,
        };
    }
}
