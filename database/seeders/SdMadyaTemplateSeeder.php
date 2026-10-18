<?php

namespace Database\Seeders;

use App\Models\OrganizationType;
use App\Models\Template;
use App\Models\User;
use App\Services\Samples\SdMadyaSamples;
use App\Services\TemplateSandboxService;
use Illuminate\Database\Seeder;

/**
 * Seeds the "SD Muhammadiyah Ambulu" exclusive template - kept in its own seeder rather than
 * folded into TemplateSeeder::exclusiveTemplates() for the same reason MasjidNurulHudaTemplateSeeder
 * is: it's multi-page, and seedPagesFromTemplate() clones every entry in structure['pages'],
 * with [0] always the home page.
 *
 * Faithful port of the standalone comprof-SD-Madya Nuxt project (SD Muhammadiyah 1 Ambulu's
 * real site):
 *   - Page set and order mirror its AppNavbar.vue: Beranda, Prestasi, Akademik (kegiatan),
 *     Pengajar, Galeri, Kontak.
 *   - The home page's section order mirrors its pages/index.vue: hero -> profil -> prestasi
 *     -> CTA.
 *   - Brand: the school's navy/gold rather than the Muhammadiyah blue/green, plus the
 *     `sekolahmu` font pairing (Inter headlines over Newsreader serif-italic body), which is
 *     what gives the design its editorial feel. See config/branding.php.
 *   - Copy is lifted from that project's own Vue components.
 *
 * 22 sections across 6 pages, of which 10 count toward the Eksklusif plan's sections_total
 * (25) - header and footer are `locked` and excluded, see PlanLimitService::countedSectionsTotal().
 * 6 pages against a pages_total of 10. It's is_exclusive, so only Eksklusif organizations can
 * pick it, which also matches the students limit, since pupil profiles are Eksklusif-only.
 */
class SdMadyaTemplateSeeder extends Seeder
{
    public const SLUG = 'sd-madya-eksklusif';

    /**
     * The organization type this template is for, as OrganizationTypeSeeder slugs it
     * ('Sekolah/Madrasah' => 'sekolahmadrasah' - the '/' drops out, it does not become a dash).
     * Named here rather than inlined because the lookup below is null-safe, so a slug that
     * doesn't match seeds the template with organization_type_id = NULL and fails SILENTLY -
     * and a typeless template is not merely untyped, it is UNREACHABLE: both
     * OrganizationController::createTemplate() and ::create() filter on
     * whereNotNull('organization_type_id'), and StoreOrganizationRequest::typelessTemplateIds()
     * rejects it, because organizations.organization_type_id is NOT NULL. Rename on either side
     * and this template silently vanishes from the picker.
     */
    private const ORGANIZATION_TYPE_SLUG = 'sekolahmadrasah';

    /** PPDB registration form the school already uses; the hero/CTA link straight to it. */
    public const PPDB_URL = 'https://bit.ly/PPDBSDMADYA';

    /**
     * Pre-resolved from the school's own https://maps.app.goo.gl/JE1radnw6LNuKMacA share link
     * via GoogleMapsEmbedResolver, so the seeded value is already the keyless /maps/embed form
     * the lokasi-peta iframe needs - resolving a short link costs a redirect-following HTTP
     * request, which a seeder should not depend on at run time.
     */
    public const MAP_EMBED = 'https://www.google.com/maps/embed?origin=mfe&pb=!1m2!2m1!1s-8.3530323%2C113.6036492';

    /**
     * The school's actual photography - uploaded through this template's own sandbox editor
     * (organization id 11) rather than hotlinked from a public bucket, same as
     * MasjidNurulHudaStandarTemplateSeeder::LOGO. Per-ORGANIZATION storage (R2, see
     * TemplateSandboxService::sandboxFor()), so these 404 if that sandbox or its storage is ever
     * cleared and would need re-uploading and re-pointing here; every section guards on an empty
     * image, so a broken URL degrades to the empty state rather than a broken layout. Checked to
     * return 200 as of this seeder's last update. Replaces the earlier Unsplash stand-ins once
     * the school's own photos were uploaded in the sandbox.
     */
    private const HERO_IMAGE = 'https://storage.ambulu.or.id/organizations/11/builder/169f07f8-f874-44ef-8e86-37d141145c5e.webp';

    private const PORTRAIT_IMAGE = 'https://storage.ambulu.or.id/organizations/11/builder/05c9f7f0-14c8-466d-b414-09f4633880e6.webp';

    private const ABOUT_IMAGE = 'https://storage.ambulu.or.id/organizations/11/builder/178c48cf-b1f2-44e4-b505-11b37b3c1a18.webp';

    private const VIDEO_POSTER = 'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?auto=format&fit=crop&w=1400&q=80';

    public function run(): void
    {
        $organizationType = OrganizationType::where('slug', self::ORGANIZATION_TYPE_SLUG)->first();

        $header = ['key' => 'header', 'variant' => 'standar'];
        $footer = ['key' => 'footer', 'variant' => 'standar'];

        $template = Template::updateOrCreate(
            ['slug' => self::SLUG],
            [
                'organization_type_id' => $organizationType?->id,
                'name' => 'Sekolah Juara',
                // Matches the house style of every other template's description: what the
                // visitor sees, page by page, in one sentence - not what the template IS. The
                // previous copy opened with "Template eksklusif multi-halaman untuk sekolah:"
                // and closed with "Khusus paket dengan entitlement template eksklusif", both of
                // which the catalog card already says with its amber "Eksklusif" badge, and at
                // 347 characters it ran half again as long as the next longest.
                'description' => 'Prestasi siswa dengan filter dan pencarian mendapat halamannya sendiri, lengkap dengan profil tiap siswa dan riwayat lombanya - plus halaman kegiatan dan ekstrakurikuler, profil tim pengajar, galeri dengan video profil, serta pendaftaran lewat WhatsApp.',
                'thumbnail_path' => 'templates/thumbnails/sekolah-eksklusif.png',
                'is_active' => true,
                'is_exclusive' => true,
                'is_featured' => true,
                'structure' => [
                    'sample_org_name' => 'SD Muhammadiyah 1 Ambulu',
                    'brand' => [
                        'primary' => '#002447',
                        'secondary' => '#E9C349',
                        'font_pair' => 'sekolahmu',
                        'radius' => 'soft',
                        // Same logo as SdMadyaStandarTemplateSeeder - uploaded through that
                        // tier's sandbox (organization id 12) and carried over here so both
                        // tiers show the school's real mark instead of only one of them having
                        // it. Per-ORGANIZATION storage, so it 404s if that sandbox is ever
                        // cleared - see that seeder's own note on why. header/standar.blade.php
                        // falls back to a text wordmark when this is blank, which is what every
                        // visitor saw here until now.
                        'logo' => 'https://storage.ambulu.or.id/organizations/12/brand/e8756aef-3c87-40a3-99e4-289322ce48a8.webp',
                    ],
                    /*
                     * SD Muhammadiyah 1 Ambulu's real contact details and social accounts.
                     * Narahubung: Beni Hendarto, S.Pd.I (Kepala Sekolah).
                     *
                     * Stored on the TEMPLATE, which means these are shown in template-preview
                     * context and inherited by this template's sandbox only - a real organization
                     * created from this template never picks them up (see Organization::phone()'s
                     * doc comment on why template contact info must not leak onto an unrelated
                     * organization's live site).
                     *
                     * whatsapp is bare digits with the country code, no '+' or separators, which
                     * is the shape wa.me links and formulir-kontak's wa_number both expect.
                     */
                    'contact' => [
                        'phone' => '082332583556',
                        'whatsapp' => '6282332583556',
                        'address' => 'Ambulu, Kabupaten Jember, Jawa Timur',
                        'instagram_url' => 'https://instagram.com/sd.madya.ambulu',
                        'facebook_url' => 'https://www.facebook.com/madya.ambulu.5',
                        'tiktok_url' => 'https://tiktok.com/@sd.madya.ambulu',
                        'youtube_url' => 'https://www.youtube.com/@sdmadyaambulu7957',
                    ],
                    'pages' => [
                        [
                            'slug' => 'home',
                            'name' => 'Beranda',
                            'sections' => [
                                $header,
                                ['key' => 'hero', 'variant' => 'sekolahmu', 'content' => [
                                    'badge' => 'Sekolah Juara',
                                    'headline' => 'SD Muhammadiyah 1 Ambulu',
                                    'subheadline' => 'Kokoh dalam Spiritual, Unggul dalam Intelektual.',
                                    'cta_label' => 'Daftar Sekarang',
                                    'cta_type' => 'scroll',
                                    'cta_section' => 'formulir-kontak',
                                    'cta_secondary_label' => 'Lihat Profil',
                                    'cta_secondary_type' => 'scroll',
                                    'cta_secondary_section' => 'tentang-organisasi',
                                    'image' => self::HERO_IMAGE,
                                    'portrait' => self::PORTRAIT_IMAGE,
                                    'highlight_title' => 'Akreditasi A',
                                    // 'Unggul' is BAN-S/M's actual predicate for an A rating.
                                    // The previous copy read 'Unggul & Terpercaya' - 'Terpercaya'
                                    // is not part of that nomenclature and was an unverifiable
                                    // claim dressed up as an official one.
                                    'highlight_subtitle' => 'Predikat Unggul (BAN-S/M)',
                                ]],
                                ['key' => 'tentang-organisasi', 'variant' => 'sekolahmu', 'content' => [
                                    'label' => 'Profil Sekolah',
                                    'title' => 'Membina Akhlak, Mengasah Logika Sejak Dini',
                                    'body' => 'Berdiri dengan semangat dakwah Muhammadiyah, kami mengintegrasikan kurikulum nasional dengan nilai-nilai spiritual yang kokoh. Sejarah panjang kami di Ambulu telah melahirkan ribuan lulusan yang berprestasi dan beradab.',
                                    'image' => self::ABOUT_IMAGE,
                                    'vision_title' => 'Visi Kami',
                                    'vision_body' => 'Menjadi lembaga pendidikan dasar yang unggul dalam Imtaq dan Iptek bertaraf nasional.',
                                    'values_title' => 'Nilai Utama',
                                    'values' => ['Kejujuran (Siddiq)', 'Kecerdasan (Fathanah)', 'Amanah', 'Tabligh'],
                                    // Same facts as the standar tier's tentang-organisasi strip
                                    // (SdMadyaStandarTemplateSeeder) - a school signing checkable
                                    // records like these should see the same numbers on both
                                    // tiers, not two different claims about one school. '540
                                    // Siswa Aktif' / '32 Tenaga Pendidik' were never counts
                                    // anyone had verified; rombel/accreditation/founding year are.
                                    'stats' => [
                                        ['value' => '12', 'label' => 'Rombongan Belajar'],
                                        ['value' => 'A', 'label' => 'Akreditasi'],
                                        ['value' => '1978', 'label' => 'Berdiri Sejak'],
                                    ],
                                ]],
                                ['key' => 'prestasi', 'variant' => 'sekolahmu', 'content' => [
                                    'label' => 'Prestasi',
                                    'title' => 'Prestasi Terbaru',
                                    'subtitle' => 'Tiga capaian terbaru siswa. Daftar lengkapnya ada di halaman Prestasi.',
                                    // No 'limit' here, unlike the galeri section below: the
                                    // sekolahmu variant is a fixed 3-tile bento (one 2x2 main tile
                                    // plus two stacked beside it) and hard-codes take(3), so a
                                    // limit set here would be silently ignored rather than honored
                                    // the way prestasi/standar honors it. The full, filterable list
                                    // lives on the dedicated Prestasi page below.
                                ]],
                                // The quotes themselves are CMS records now (see
                                // SdMadyaSamples::testimoniItems()); `items` here is only the
                                // fallback the template PREVIEW renders, where there is no
                                // organization to read them from.
                                ['key' => 'testimoni', 'variant' => 'sekolahmu', 'content' => [
                                    'label' => 'Suara Alumni',
                                    'title' => 'Apa Kata Mereka?',
                                    'items' => SdMadyaSamples::testimoniItems(),
                                ]],
                                ['key' => 'cta', 'variant' => 'sekolahmu', 'content' => [
                                    'title' => 'Mulai Perjalanan Prestasi Anak Anda Bersama Kami',
                                    'subtitle' => 'Pendaftaran Peserta Didik Baru telah dibuka. Kuota terbatas.',
                                    // Straight to the PPDB form the school already runs, rather
                                    // than scrolling to the contact section and asking twice.
                                    'cta_label' => 'Daftar Online Sekarang',
                                    'cta_type' => 'url',
                                    'cta_url' => self::PPDB_URL,
                                    'cta_secondary_label' => 'Tanya via WhatsApp',
                                    'cta_secondary_type' => 'scroll',
                                    'cta_secondary_section' => 'formulir-kontak',
                                ]],
                                $footer,
                            ],
                        ],
                        [
                            'slug' => 'prestasi',
                            'name' => 'Prestasi',
                            'sections' => [
                                $header,
                                ['key' => 'prestasi', 'variant' => 'standar', 'content' => [
                                    'label' => 'Sekolah Juara',
                                    'title' => 'Tradisi Mencetak Generasi Berprestasi',
                                    'subtitle' => 'Daftar prestasi siswa, dari tingkat kecamatan hingga nasional. Gunakan filter kategori untuk melihat bidang tertentu.',
                                    // Facts a school can actually check against its own records
                                    // (rombel count, accreditation, ekskul list, year founded)
                                    // rather than unverifiable superlatives. The previous set -
                                    // "500+ Trofi", "98% Lulusan Terbaik" - were invented numbers
                                    // no school could confirm, and "Lulusan Terbaik" as a
                                    // percentage is not a measurable thing at all.
                                    'stats' => [
                                        ['value' => '12', 'label' => 'Rombongan Belajar'],
                                        ['value' => 'A', 'label' => 'Akreditasi'],
                                        ['value' => '9', 'label' => 'Ekstrakurikuler'],
                                        ['value' => '1978', 'label' => 'Berdiri Sejak'],
                                    ],
                                ]],
                                $footer,
                            ],
                        ],
                        [
                            'slug' => 'akademik',
                            'name' => 'Akademik',
                            'sections' => [
                                $header,
                                ['key' => 'kegiatan', 'variant' => 'sekolahmu', 'content' => [
                                    'title' => 'Kegiatan Sekolah',
                                    'subtitle' => 'Pembiasaan harian, ekstrakurikuler, dan program pembinaan yang diikuti siswa sepanjang pekan.',
                                ]],
                                $footer,
                            ],
                        ],
                        [
                            'slug' => 'pengajar',
                            'name' => 'Pengajar',
                            'sections' => [
                                $header,
                                ['key' => 'struktur-pengurus', 'variant' => 'sekolahmu', 'content' => [
                                    'label' => 'Akademik',
                                    'title' => 'Tim Pengajar',
                                    'subtitle' => 'Guru kelas dan guru mata pelajaran yang mendampingi siswa setiap hari.',
                                ]],
                                $footer,
                            ],
                        ],
                        [
                            'slug' => 'galeri',
                            'name' => 'Galeri',
                            'sections' => [
                                $header,
                                ['key' => 'galeri', 'variant' => 'sekolahmu', 'content' => [
                                    'label' => 'Galeri',
                                    'title' => 'Galeri Kegiatan',
                                    'subtitle' => 'Dokumentasi kegiatan belajar, ekstrakurikuler, dan acara sekolah.',
                                    'video_poster' => self::VIDEO_POSTER,
                                    'video_title' => 'Video Profil Sekolah',
                                    'video_subtitle' => 'Mengenal lebih dekat lingkungan belajar kami',
                                    'limit' => 12,
                                ]],
                                $footer,
                            ],
                        ],
                        [
                            'slug' => 'kontak',
                            'name' => 'Kontak',
                            'sections' => [
                                $header,
                                ['key' => 'formulir-kontak', 'variant' => 'sekolahmu', 'content' => [
                                    'title' => 'Hubungi Kami',
                                    'form_title' => 'Formulir Pendaftaran Online',
                                    'form_subtitle' => 'Lengkapi data calon siswa di bawah ini. Tim kami akan menghubungi Anda melalui WhatsApp.',
                                    'cta_label' => 'Daftar Sekarang',
                                    'hours_title' => 'Jam Operasional',
                                    'hours' => [
                                        ['day' => 'Senin - Kamis', 'time' => '07:00 - 14:00'],
                                        ['day' => 'Jumat', 'time' => '07:00 - 11:00'],
                                        ['day' => 'Sabtu', 'time' => '07:00 - 12:00'],
                                        ['day' => 'Minggu', 'time' => 'Tutup'],
                                    ],
                                    'grades' => ['Kelas 1', 'Kelas 2', 'Kelas 3', 'Kelas 4', 'Kelas 5', 'Kelas 6'],
                                ]],
                                ['key' => 'lokasi-peta', 'variant' => 'standar', 'content' => [
                                    'title' => 'Lokasi Sekolah',
                                    'map_embed' => self::MAP_EMBED,
                                ]],
                                $footer,
                            ],
                        ],
                    ],
                ],
            ]
        );

        // Give the template its sandbox up front, like every other template seeder does - an
        // admin opening the designer shouldn't be the one to create it, and without this the
        // exclusive tier was the only SD Madya template with no sandbox to preview.
        if ($admin = User::where('is_admin', true)->first()) {
            app(TemplateSandboxService::class)->sandboxFor($template, $admin);
        }
    }
}
