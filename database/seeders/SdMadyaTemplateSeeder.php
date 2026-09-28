<?php

namespace Database\Seeders;

use App\Models\OrganizationType;
use App\Models\Template;
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
 *     `sd-madya` font pairing (Inter headlines over Newsreader serif-italic body), which is
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

    /**
     * Stand-in photography from Unsplash, consistent with the other seeded templates. The real
     * school's own photos live in its manajemen-SD-Madya storage and arrive with the data
     * migration; every section guards on an empty image, so a broken URL degrades to the empty
     * state rather than a broken layout.
     */
    private const HERO_IMAGE = 'https://images.unsplash.com/photo-1580582932707-520aed937b7b?auto=format&fit=crop&w=1600&q=80';

    private const PORTRAIT_IMAGE = 'https://images.unsplash.com/photo-1503676260728-1c00da094a0b?auto=format&fit=crop&w=1000&q=80';

    private const ABOUT_IMAGE = 'https://images.unsplash.com/photo-1497633762265-9d179a990aa6?auto=format&fit=crop&w=1000&q=80';

    private const VIDEO_POSTER = 'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?auto=format&fit=crop&w=1400&q=80';

    public function run(): void
    {
        $organizationType = OrganizationType::where('slug', self::ORGANIZATION_TYPE_SLUG)->first();

        $header = ['key' => 'header', 'variant' => 'standar'];
        $footer = ['key' => 'footer', 'variant' => 'standar'];

        Template::updateOrCreate(
            ['slug' => self::SLUG],
            [
                'organization_type_id' => $organizationType?->id,
                'name' => 'SD Muhammadiyah Ambulu (Eksklusif)',
                'description' => 'Template eksklusif multi-halaman untuk sekolah: beranda dengan profil dan prestasi, wall of fame dengan filter dan pencarian, halaman profil siswa beserta riwayat prestasinya, kegiatan dan ekstrakurikuler, profil tim pengajar, galeri dengan video profil, serta formulir pendaftaran via WhatsApp. Khusus paket dengan entitlement template eksklusif.',
                'is_active' => true,
                'is_exclusive' => true,
                'structure' => [
                    'sample_org_name' => 'SD Muhammadiyah 1 Ambulu',
                    'brand' => [
                        'primary' => '#002447',
                        'secondary' => '#E9C349',
                        'font_pair' => 'sd-madya',
                        'radius' => 'soft',
                    ],
                    'pages' => [
                        [
                            'slug' => 'home',
                            'name' => 'Beranda',
                            'sections' => [
                                $header,
                                ['key' => 'hero', 'variant' => 'sd-madya', 'content' => [
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
                                    'highlight_subtitle' => 'Unggul & Terpercaya',
                                ]],
                                ['key' => 'tentang-organisasi', 'variant' => 'sd-madya', 'content' => [
                                    'label' => 'Profil Sekolah',
                                    'title' => 'Membina Akhlak, Mengasah Logika Sejak Dini',
                                    'body' => 'Berdiri dengan semangat dakwah Muhammadiyah, kami mengintegrasikan kurikulum nasional dengan nilai-nilai spiritual yang kokoh. Sejarah panjang kami di Ambulu telah melahirkan ribuan lulusan yang berprestasi dan beradab.',
                                    'image' => self::ABOUT_IMAGE,
                                    'vision_title' => 'Visi Kami',
                                    'vision_body' => 'Menjadi lembaga pendidikan dasar yang unggul dalam Imtaq dan Iptek bertaraf nasional.',
                                    'values_title' => 'Nilai Utama',
                                    'values' => ['Kejujuran (Siddiq)', 'Kecerdasan (Fathanah)', 'Amanah', 'Tabligh'],
                                    'stats' => [
                                        ['value' => 'A', 'label' => 'Akreditasi'],
                                        ['value' => '540', 'label' => 'Siswa Aktif'],
                                        ['value' => '32', 'label' => 'Tenaga Pendidik'],
                                    ],
                                ]],
                                ['key' => 'prestasi', 'variant' => 'sd-madya', 'content' => [
                                    'label' => 'Wall of Fame',
                                    'title' => 'Prestasi Membanggakan',
                                    'subtitle' => 'Dedikasi siswa dan guru kami dalam meraih keunggulan di berbagai bidang kompetisi.',
                                    // No 'limit' here, unlike the galeri section below: the
                                    // sd-madya variant is a fixed 3-tile bento (one 2x2 main tile
                                    // plus two stacked beside it) and hard-codes take(3), so a
                                    // limit set here would be silently ignored rather than honored
                                    // the way prestasi/standar honors it. The full, filterable list
                                    // lives on the dedicated Prestasi page below.
                                ]],
                                ['key' => 'testimoni', 'variant' => 'standar', 'content' => [
                                    'label' => 'Suara Alumni',
                                    'title' => 'Apa Kata Mereka?',
                                    'items' => [
                                        ['quote' => 'SD Muhammadiyah 1 Ambulu memberikan landasan karakter yang sangat kuat bagi saya. Kedisiplinan dan nilai keislaman yang diajarkan terbawa hingga saya menempuh karir sekarang.', 'name' => 'dr. Hendra Wijaya', 'role' => 'Alumni Tahun 2005'],
                                        ['quote' => 'Saya sangat bersyukur pernah sekolah di sini. Gurunya sangat perhatian dan mampu menemukan bakat seni saya yang saat itu belum terlihat.', 'name' => 'Siti Aminah, M.Ds', 'role' => 'Desainer & Alumni 2010'],
                                        ['quote' => 'Ekosistem belajarnya sangat kompetitif namun tetap suportif. Saya diajarkan untuk tidak pernah takut gagal dalam berkompetisi.', 'name' => 'Ir. Rizky Pratama', 'role' => 'Alumni Tahun 2008'],
                                    ],
                                ]],
                                ['key' => 'cta', 'variant' => 'sd-madya', 'content' => [
                                    'title' => 'Mulai Perjalanan Prestasi Anak Anda Bersama Kami',
                                    'subtitle' => 'Pendaftaran Peserta Didik Baru telah dibuka. Kuota terbatas.',
                                    'cta_label' => 'Daftar Online Sekarang',
                                    'cta_type' => 'scroll',
                                    'cta_section' => 'formulir-kontak',
                                    'cta_secondary_label' => 'Panduan PPDB',
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
                                    'subtitle' => 'Sorotan prestasi akademik dan non-akademik siswa-siswi terbaik kami di tingkat regional maupun nasional.',
                                    'stats' => [
                                        ['value' => '500+', 'label' => 'Trofi'],
                                        ['value' => '98%', 'label' => 'Lulusan Terbaik'],
                                        ['value' => '12', 'label' => 'Ekskul Unggulan'],
                                        ['value' => '20+', 'label' => 'Tahun Pengabdian'],
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
                                ['key' => 'kegiatan', 'variant' => 'sd-madya', 'content' => [
                                    'title' => 'Kegiatan Sekolah',
                                    'subtitle' => 'Membentuk generasi Robbani yang cerdas dan kreatif melalui pembiasaan ibadah dan pengembangan diri.',
                                ]],
                                $footer,
                            ],
                        ],
                        [
                            'slug' => 'pengajar',
                            'name' => 'Pengajar',
                            'sections' => [
                                $header,
                                ['key' => 'struktur-pengurus', 'variant' => 'tim-pengajar', 'content' => [
                                    'label' => 'Akademik',
                                    'title' => 'Tim Pengajar, Pilar Kecemerlangan',
                                    'subtitle' => 'Kami mendedikasikan diri untuk membimbing tunas bangsa dengan integritas spiritual dan kemajuan intelektual.',
                                ]],
                                $footer,
                            ],
                        ],
                        [
                            'slug' => 'galeri',
                            'name' => 'Galeri',
                            'sections' => [
                                $header,
                                ['key' => 'galeri', 'variant' => 'sd-madya', 'content' => [
                                    'label' => 'Dokumentasi Visual',
                                    'title' => 'Jejak Langkah & Pencapaian Kami',
                                    'subtitle' => 'Menangkap momen-momen berharga dalam perjalanan mendidik generasi rabbani yang unggul dalam Imtaq dan Iptek.',
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
                                ['key' => 'formulir-kontak', 'variant' => 'sd-madya', 'content' => [
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
                                ]],
                                $footer,
                            ],
                        ],
                    ],
                ],
            ]
        );
    }
}
