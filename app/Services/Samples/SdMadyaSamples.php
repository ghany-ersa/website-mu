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
     * The school's actual staff, transcribed from its own organisational chart. Ordered by
     * seniority rather than by the chart's left-to-right layout, because
     * CmsSampleDataSeeder::sampleCount() truncates this list to the plan's `officers` limit
     * (Starter 5, Premium 10, Eksklusif 25) - so the kepala sekolah must lead, not whoever
     * happened to sit in the chart's top-left box.
     *
     * `role` is the titled position and `subject` the teaching assignment, which is the split
     * the sd-madya variant renders (see add_teacher_fields_to_officers_table). Several
     * people hold two posts - Beni Hendarto is both kepala sekolah and guru PAI, Fais Hayu
     * Usmawati both bendahara and guru kelas IVA, Tri Agung both wakil kepala and guru PJOK,
     * Syahrul Dwi Arifin both operator and guru kelas VB, Amalia Virgi both tata usaha and guru
     * kelas IIB - so each appears ONCE with their senior role and their class in `subject`,
     * rather than twice as two different people.
     *
     * `photo` stays null: the school's own headshots are not ours to publish, and
     * struktur-pengurus renders an empty avatar rather than breaking. PortraitPhotos stand-ins
     * are applied to sandboxes only (see CmsSampleDataSeeder::seed()), never to a real roster
     * of named people.
     *
     * @return array<int, array{name: string, role: string, subject?: string}>
     */
    public static function timPengajar(): array
    {
        return [
            ['name' => 'Beni Hendarto, S.Pd.I', 'role' => 'Kepala Sekolah', 'subject' => 'Pendidikan Agama Islam'],
            ['name' => 'Tri Agung Misbahul Ulum, S.Pd', 'role' => 'Wakil Kepala Sekolah', 'subject' => 'Pendidikan Jasmani & Olahraga'],
            ['name' => 'Ahmad Huda Ermawan', 'role' => 'Komite Sekolah'],
            ['name' => 'Abdurachman Habibullah, S.Pd', 'role' => 'Kurikulum Sekolah', 'subject' => 'Bahasa Arab'],
            ['name' => 'Fais Hayu Usmawati, S.Pd', 'role' => 'Bendahara Sekolah', 'subject' => 'Guru Kelas IVA'],
            ['name' => 'Syahrul Dwi Arifin, S.Pd', 'role' => 'Operator Sekolah', 'subject' => 'Guru Kelas VB'],
            ['name' => 'Amalia Virgi Effendy, S.Ak', 'role' => 'Tata Usaha', 'subject' => 'Guru Kelas IIB'],
            ['name' => "Dwi Jayanti Mu'awanah, S.Pd", 'role' => 'Guru Kelas', 'subject' => 'Kelas 1A'],
            ['name' => 'Inggit Awanda M, S.Pd', 'role' => 'Guru Kelas', 'subject' => 'Kelas 1B'],
            ['name' => 'Jihan Hanifa, S.Pd', 'role' => 'Guru Kelas', 'subject' => 'Kelas IIA'],
            ['name' => "Wenny Asfia'ul Uyyun, S.Pd", 'role' => 'Guru Kelas', 'subject' => 'Kelas III'],
            ['name' => 'Yasintha Mahdiatuz Z, S.Pd', 'role' => 'Guru Kelas', 'subject' => 'Kelas IV'],
            ['name' => "Afifatur Rofi'ah, S.Pd", 'role' => 'Guru Kelas', 'subject' => 'Kelas IVB'],
            ['name' => 'Candrayani Kurnia P P, S.Pd', 'role' => 'Guru Kelas', 'subject' => 'Kelas VA'],
            ['name' => 'Umar Sidiq', 'role' => 'Penjaga Sekolah'],
        ];
    }

    /**
     * Placeholder testimonials for the `testimoni` section.
     *
     * Deliberately UNNAMED, unlike timPengajar(): a testimonial is a claim attributed to a real,
     * identifiable person, and the previous copy invented three - "dr. Hendra Wijaya, Alumni
     * Tahun 2005" and friends - complete with degrees and graduation years. Seeding a school's
     * live site with fabricated quotes from fabricated alumni is a different thing from seeding
     * a placeholder officer roster: it puts words in the mouths of people who do not exist, and
     * a school that forgets to replace them is publishing testimonials it never collected.
     *
     * So the text says what the quote is FOR and the names are bracketed placeholders, which
     * read as "fill this in" rather than as real endorsements.
     *
     * @return array<int, array{quote: string, name: string, role: string}>
     */
    public static function testimoniItems(): array
    {
        return [
            [
                'quote' => 'Ganti dengan testimoni asli dari alumni - misalnya kesan mereka tentang pembiasaan ibadah dan pendampingan guru selama bersekolah di sini.',
                'name' => '[Nama Alumni]',
                'role' => 'Alumni',
            ],
            [
                'quote' => 'Ganti dengan testimoni asli dari wali murid - misalnya perubahan yang mereka lihat pada anak sejak masuk sekolah ini.',
                'name' => '[Nama Wali Murid]',
                'role' => 'Wali Murid',
            ],
            [
                'quote' => 'Minta izin lebih dulu kepada yang bersangkutan sebelum menampilkan nama dan fotonya di situs sekolah.',
                'name' => '[Nama Alumni]',
                'role' => 'Alumni',
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
