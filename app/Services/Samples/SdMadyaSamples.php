<?php

namespace App\Services\Samples;

/**
 * Sample content for the SD Muhammadiyah Ambulu school template - the achievements behind its
 * `prestasi` sections and the activities behind its `kegiatan` section.
 *
 * Lives in app/ rather than database/seeders/ for the same reason as the other Samples classes
 * (see KlinikAisyiyahAmbuluSamples' doc comment): App\Services\CmsSampleDataSeeder runs in
 * PRODUCTION on every organization creation and cannot depend on seed-only code.
 *
 * Why this class exists at all: `prestasi` and `kegiatan` were the only two CMS-backed sections
 * in the registry with no branch in CmsSampleDataSeeder, so they seeded zero rows. Both
 * auto-bind to their CMS table whenever $organization is in scope - which is always, on a real
 * page and on the builder canvas alike - so their `$content['items']` fallbacks never applied
 * and the two most school-specific sections of the template rendered their empty states
 * ("Belum ada prestasi.") in the very editor meant to show what the design looks like filled in.
 *
 * Content is representative rather than the real school's record: unlike the clinic and masjid
 * showcases, whose data was ported from those organizations' own live sites, no authoritative
 * SD Madya list was available. Every entry is therefore plausible-but-generic school content an
 * owner is expected to replace - which is also why no pupil names appear (achievements link to
 * Student profiles, and inventing a named child's competition record is not something a sample
 * should do).
 */
class SdMadyaSamples
{
    /** Template slug this content is keyed to - see SdMadyaTemplateSeeder::SLUG. */
    public const TEMPLATE_SLUG = 'sd-madya-eksklusif';

    /**
     * Achievements for the `prestasi` section, in the order they should appear.
     *
     * Ordered deliberately: CmsSampleDataSeeder::sampleCount() truncates to the smaller of
     * MAX_SAMPLES_PER_RESOURCE (3) and the plan's `achievements` limit (Starter allows 3), so
     * only the first three survive on the lower tiers. Those three are picked to span three
     * different categories - Akademik, Olahraga, Seni - because the standar variant builds its
     * filter rail from the categories actually present, and three identical categories would
     * render a filter with a single meaningful tab.
     *
     * `category` and `level` values must match Achievement::CATEGORIES / ::LEVELS, which the
     * CMS form validates against.
     *
     * @return array<int, array<string, string>>
     */
    public static function prestasiItems(): array
    {
        return [
            [
                'title' => 'Juara 1 Olimpiade Matematika',
                'category' => 'Akademik',
                'level' => 'Tingkat Kabupaten',
                'rank' => 'Juara 1',
                'year' => '2026',
                'description' => 'Meraih medali emas pada olimpiade matematika tingkat kabupaten setelah pembinaan rutin klub sains sekolah.',
            ],
            [
                'title' => 'Juara 2 Turnamen Bulu Tangkis',
                'category' => 'Olahraga',
                'level' => 'Tingkat Kecamatan',
                'rank' => 'Juara 2',
                'year' => '2026',
                'description' => 'Runner-up pada turnamen antar sekolah dasar se-kecamatan, hasil latihan ekstrakurikuler setiap pekan.',
            ],
            [
                'title' => 'Juara 1 Lomba Tari Tradisional',
                'category' => 'Seni',
                'level' => 'Tingkat Kabupaten',
                'rank' => 'Juara 1',
                'year' => '2025',
                'description' => 'Menampilkan tari tradisional pada festival seni pelajar dan membawa pulang piala bergilir.',
            ],
            [
                'title' => 'Juara 3 Lomba Tahfidz Juz 30',
                'category' => 'Akademik',
                'level' => 'Tingkat Kecamatan',
                'rank' => 'Juara 3',
                'year' => '2025',
                'description' => 'Capaian program tahfidz sekolah yang menargetkan hafalan Juz 30 bagi seluruh lulusan.',
            ],
            [
                'title' => 'Juara Harapan 1 Lomba Pidato Bahasa Arab',
                'category' => 'Seni',
                'level' => 'Tingkat Kabupaten',
                'rank' => 'Juara Harapan 1',
                'year' => '2025',
                'description' => 'Diikuti perwakilan kelas atas dalam ajang pekan bahasa tingkat kabupaten.',
            ],
        ];
    }

    /**
     * Activities for the `kegiatan` section - Program rows of type 'kegiatan', which is how the
     * registry maps that section (config/page-builder.php's cms.params.type).
     *
     * The first three are the pembiasaan/ibadah routines the section's own seeded subtitle
     * promises ("pembiasaan ibadah dan pengembangan diri"), so the surviving subset on a tight
     * plan still matches the heading above it.
     *
     * `icon` holds an emoji, matching CmsSampleDataSeeder's generic program defaults; the
     * bento layout (program-unggulan/sd-madya.blade.php) renders it when there is no image.
     *
     * @return array<int, array<string, string>>
     */
    public static function kegiatanItems(): array
    {
        return [
            [
                'title' => 'Salat Dhuha Berjamaah',
                'category' => 'Pembiasaan',
                'description' => 'Pembiasaan salat Dhuha berjamaah setiap pagi sebelum jam pelajaran pertama dimulai.',
                'icon' => '🕌',
            ],
            [
                'title' => 'Tahfidz Juz 30',
                'category' => 'Keagamaan',
                'description' => 'Program hafalan Al-Qur\'an Juz 30 dengan setoran rutin dan pendampingan guru tahfidz.',
                'icon' => '📖',
            ],
            [
                'title' => 'Literasi Pagi',
                'category' => 'Akademik',
                'description' => 'Lima belas menit membaca bersama setiap hari untuk menumbuhkan minat baca sejak dini.',
                'icon' => '📚',
            ],
            [
                'title' => 'Ekstrakurikuler Hizbul Wathan',
                'category' => 'Kepanduan',
                'description' => 'Kepanduan khas Muhammadiyah yang melatih kemandirian, kedisiplinan, dan kerja sama.',
                'icon' => '⛺',
            ],
            [
                'title' => 'Klub Sains dan Matematika',
                'category' => 'Akademik',
                'description' => 'Pembinaan siswa berbakat untuk persiapan olimpiade dan kompetisi akademik.',
                'icon' => '🔬',
            ],
            [
                'title' => 'Seni Tari dan Musik',
                'category' => 'Seni',
                'description' => 'Pengembangan bakat seni melalui latihan tari tradisional dan ansambel musik sekolah.',
                'icon' => '🎭',
            ],
        ];
    }
}
