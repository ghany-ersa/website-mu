<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

/**
 * Rejects hex colors too close to white to read as text on the white/near-white
 * backgrounds most section partials use (e.g. `text-secondary` in daftar-berita,
 * donasi-zakat-infak, tentang-organisasi, sambutan-ketua) - see prd.md §24.4.
 */
class NotTooLightColor implements ValidationRule
{
    /**
     * Relative luminance (WCAG) above this is considered "too light" - chosen so pure
     * white (1.0) and near-white grays/pastels fail, while legitimate light-but-readable
     * brand colors (e.g. a bright secondary green/amber) still pass.
     */
    private const MAX_LUMINANCE = 0.85;

    /**
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || ! preg_match('/^#([0-9a-fA-F]{6})$/', $value, $matches)) {
            return;
        }

        if ($this->relativeLuminance($matches[1]) > self::MAX_LUMINANCE) {
            $fail('Warna terlalu terang (mendekati putih) dan akan sulit dibaca sebagai teks. Pilih warna yang lebih gelap.');
        }
    }

    private function relativeLuminance(string $hex): float
    {
        [$r, $g, $b] = array_map(
            fn (string $channel) => $this->linearize(hexdec($channel) / 255),
            str_split($hex, 2)
        );

        return 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;
    }

    private function linearize(float $channel): float
    {
        return $channel <= 0.03928
            ? $channel / 12.92
            : (($channel + 0.055) / 1.055) ** 2.4;
    }
}
