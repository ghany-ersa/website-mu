<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Renames two section variant keys to match the template's own brand name rather than the
 * takmir/school's real-world name (which made sense as a placeholder but reads oddly once a
 * variant is offered as a generic "masjid" or "sekolah" style, not literally Masjid Nurul Huda
 * or SD Madya specifically):
 *
 *   - `nurul-huda` -> `masjidmu` (hero, fasilitas-masjid, donasi-progress, laporan-keuangan,
 *     kalkulator-zakat, akad-venue - the premium mosque sections plus hero)
 *   - `sd-madya` -> `sekolahmu` (hero, tentang-organisasi, struktur-pengurus, program-unggulan,
 *     galeri, kegiatan, testimoni, prestasi, formulir-kontak, cta - the premium school sections
 *     plus hero), and its sibling `font_pairs` key in config/branding.php (not a `section_variants`
 *     row, but renamed alongside for consistency - see structure['brand']['font_pair']).
 *
 * A data migration rather than a seeder edit alone, for the same reason the earlier
 * 2026_09_09_100000 (standar -> nurul-huda) and 2026_09_29_110000 (tim-pengajar -> sd-madya)
 * migrations were: `variant` is a stored column on both section_variants and
 * organization_sections, and SectionVariantResolver::resolveFrom() does not fail on an unknown
 * variant - it silently falls back to the key's `is_default` row. Skipping either table would not
 * error anywhere; it would just have an existing organization's section quietly re-render in the
 * wrong (or missing-default) layout.
 *
 * Three places store the key, and all three move together per rename:
 *   - section_variants      - the catalogue row the resolver looks the key up in.
 *   - organization_sections - what organizations already using the variant point at.
 *   - templates.structure   - the cloning blueprint (a JSON column, so this is a scoped string
 *     replacement on the exact `"variant":"<key>"` fragment, never a blind find-replace - the
 *     structure also carries slugs/prose like "masjid-nurul-huda-eksklusif" or "Masjid Nurul
 *     Huda" that must NOT be touched). font_pair lives at structure['brand']['font_pair'], a
 *     different JSON path, so it needs its own scoped replacement.
 */
return new class extends Migration
{
    private const MASJIDMU_SECTION_KEYS = [
        'hero',
        'fasilitas-masjid',
        'donasi-progress',
        'laporan-keuangan',
        'kalkulator-zakat',
        'akad-venue',
    ];

    private const SEKOLAHMU_SECTION_KEYS = [
        'hero',
        'tentang-organisasi',
        'struktur-pengurus',
        'program-unggulan',
        'galeri',
        'kegiatan',
        'testimoni',
        'prestasi',
        'formulir-kontak',
        'cta',
    ];

    public function up(): void
    {
        $this->renameVariant('nurul-huda', 'masjidmu', self::MASJIDMU_SECTION_KEYS);
        $this->renameVariant('sd-madya', 'sekolahmu', self::SEKOLAHMU_SECTION_KEYS);
        $this->renameFontPair('sd-madya', 'sekolahmu');
    }

    public function down(): void
    {
        $this->renameVariant('masjidmu', 'nurul-huda', self::MASJIDMU_SECTION_KEYS);
        $this->renameVariant('sekolahmu', 'sd-madya', self::SEKOLAHMU_SECTION_KEYS);
        $this->renameFontPair('sekolahmu', 'sd-madya');
    }

    /**
     * @param  array<int, string>  $sectionKeys
     */
    private function renameVariant(string $from, string $to, array $sectionKeys): void
    {
        // Per key rather than one bulk UPDATE with a SQL concat: `view` embeds the section key,
        // and the concat operator differs across drivers (this app runs SQLite locally, MySQL in
        // production) - a handful of statements is a cheap price for portability. Same approach
        // as 2026_09_09_100000_rename_exclusive_mosque_section_variants_to_nurul_huda.php.
        foreach ($sectionKeys as $key) {
            DB::table('section_variants')
                ->where('section_key', $key)
                ->where('variant_key', $from)
                ->update([
                    'variant_key' => $to,
                    'view' => "templates.sections.{$key}.{$to}",
                    'updated_at' => now(),
                ]);
        }

        DB::table('organization_sections')
            ->whereIn('key', $sectionKeys)
            ->where('variant', $from)
            ->update(['variant' => $to, 'updated_at' => now()]);

        // Scoped to the exact JSON fragment ("variant":"<key>") rather than the bare key, so a
        // template whose prose happens to contain the word (slugs, display names) is left alone
        // - same approach as 2026_09_29_110000_rename_tim_pengajar_variant_to_sd_madya.php.
        foreach (DB::table('templates')->select('id', 'structure')->get() as $template) {
            if (! is_string($template->structure) || ! str_contains($template->structure, '"variant":"'.$from.'"')) {
                continue;
            }

            DB::table('templates')->where('id', $template->id)->update([
                'structure' => str_replace('"variant":"'.$from.'"', '"variant":"'.$to.'"', $template->structure),
                'updated_at' => now(),
            ]);
        }
    }

    private function renameFontPair(string $from, string $to): void
    {
        foreach (DB::table('templates')->select('id', 'structure')->get() as $template) {
            if (! is_string($template->structure) || ! str_contains($template->structure, '"font_pair":"'.$from.'"')) {
                continue;
            }

            DB::table('templates')->where('id', $template->id)->update([
                'structure' => str_replace('"font_pair":"'.$from.'"', '"font_pair":"'.$to.'"', $template->structure),
                'updated_at' => now(),
            ]);
        }
    }
};
