<?php

namespace Database\Seeders;

use App\Models\OrganizationType;
use App\Models\Template;
use Illuminate\Database\Seeder;

/**
 * Seeds the "Masjid Nurul Huda" exclusive template - kept in its own seeder rather than
 * folded into TemplateSeeder::exclusiveTemplates() because, unlike every other exclusive
 * template today, it's deliberately MULTI-page (see Organization::seedPagesFromTemplate(),
 * which as of this template now clones every entry in structure['pages'], not just the
 * first). structure['pages'][0] is always this template's home page, matching
 * seedPagesFromTemplate()'s is_home => $pageIndex === 0 assumption.
 *
 * Faithful port of the standalone nurul-huda Laravel project (Masjid Nurul Huda Ambulu's
 * real site):
 *   - Page set and order mirror its navbar.blade.php: Beranda, Pengurus, Donasi, Laporan
 *     Keuangan, Kajian & Event, Akad Venue.
 *   - The home page's section order mirrors its home.blade.php: hero -> tentang -> fasilitas
 *     -> galeri -> donasi aktif -> kajian terdekat -> ajakan donasi (CTA).
 *   - Brand: primary #2c368B, accent #1e79cc, plain sans font (no serif/Lora override like
 *     other exclusive templates), rounded-2xl white cards with a slate-100 border.
 *   - header/footer use the `nurul-huda` section variants for its cross-page top nav and
 *     app-style mobile bottom nav (see resources/views/templates/sections/header|footer/).
 *   - Copy is lifted from that project's own Blade views; the matching CMS sample records
 *     (13 facilities, 5 donation programs, 6 months of books, 4 kajian, 2 takmir, 8 gallery
 *     captions) come from CmsSampleDataSeeder, which keys off this template's slug.
 *
 * Section total across all 6 pages is 15 unlocked sections, under the Professional plan's
 * sections_total limit (25) - this template is is_exclusive, so only Professional-plan
 * organizations can pick it in the first place (see Organization::canUseExclusiveTemplates()).
 */
class MasjidNurulHudaTemplateSeeder extends Seeder
{
    /**
     * Imagery is hotlinked straight from the live Masjid Nurul Huda Ambulu site's own S3
     * bucket, so the template preview shows the actual mosque rather than stand-in stock
     * photography. Every URL below was checked to return 200. If the source ever moves these,
     * the affected cards fall back to their empty state - the sections all guard on an empty
     * photo - and an organization can replace them from its own media library.
     */
    private const S3 = 'https://s3.nurul-huda.ambulu.or.id';

    private const HERO_IMAGE = self::S3.'/venue-page/NH.jpg';

    private const VENUE_IMAGE = self::S3.'/gallery/01M0F8PE5ECH8A2S0HM4BPH4PS.jpg';

    private const ABOUT_IMAGE = self::S3.'/facilities/01M0CQBN9Z92DPB8KC69WCRGA7.jpg';

    public function run(): void
    {
        $organizationType = OrganizationType::where('slug', 'masjidmushola')->first();

        $header = ['key' => 'header', 'variant' => 'standar'];
        $footer = ['key' => 'footer', 'variant' => 'standar'];

        Template::updateOrCreate(
            ['slug' => 'masjid-nurul-huda-eksklusif'],
            [
                'organization_type_id' => $organizationType?->id,
                'name' => 'Masjid Nurul Huda (Eksklusif)',
                'description' => 'Template eksklusif multi-halaman untuk masjid: beranda dengan fasilitas dan galeri, donasi & wakaf dengan progress bar, laporan keuangan transparan, jadwal kajian rutin, sewa aula untuk akad nikah, dan profil takmir - masing-masing sebagai halaman tersendiri. Khusus paket dengan entitlement template eksklusif.',
                'is_active' => true,
                'is_exclusive' => true,
                'structure' => [
                    'sample_org_name' => 'Masjid Nurul Huda',
                    'brand' => ['primary' => '#2c368B', 'secondary' => '#1e79cc'],
                    'pages' => [
                        [
                            'slug' => 'home',
                            'name' => 'Beranda',
                            'sections' => [
                                $header,
                                ['key' => 'hero', 'variant' => 'nurul-huda', 'content' => [
                                    'badge' => 'Terbuka untuk seluruh jamaah',
                                    'headline' => 'Masjid Nurul Huda Ambulu',
                                    'subheadline' => 'Pusat ibadah dan kegiatan umat yang transparan dalam pengelolaan dana dan terbuka untuk seluruh jamaah.',
                                    'cta_label' => 'Lihat Program Donasi',
                                    'cta_type' => 'scroll',
                                    'cta_section' => 'donasi-progress',
                                    'cta_secondary_label' => 'Kenali Pengurus',
                                    'cta_secondary_type' => 'scroll',
                                    'cta_secondary_section' => 'fasilitas-masjid',
                                    'image' => self::HERO_IMAGE,
                                ]],
                                ['key' => 'tentang-organisasi', 'variant' => 'standar', 'content' => [
                                    'title' => 'Pusat Ibadah & Kegiatan Umat',
                                    'body' => 'Masjid Nurul Huda adalah rumah ibadah sekaligus pusat kegiatan keagamaan, pendidikan, dan sosial bagi masyarakat sekitar. Kami berkomitmen mengelola dana umat secara transparan dan menghadirkan kegiatan yang bermanfaat bagi jamaah.',
                                    'image' => self::ABOUT_IMAGE,
                                    'stats' => [
                                        ['value' => '13', 'label' => 'Fasilitas Masjid'],
                                        ['value' => '4x', 'label' => 'Kajian Rutin/Bulan'],
                                        ['value' => '100%', 'label' => 'Dana Transparan'],
                                    ],
                                ]],
                                ['key' => 'fasilitas-masjid', 'variant' => 'standar', 'content' => [
                                    'title' => 'Fasilitas Masjid',
                                    'limit' => 6,
                                ]],
                                ['key' => 'galeri', 'variant' => 'standar', 'content' => [
                                    'title' => 'Dokumentasi Kegiatan',
                                    'limit' => 8,
                                ]],
                                ['key' => 'donasi-progress', 'variant' => 'standar', 'content' => [
                                    'title' => 'Program Donasi Aktif',
                                    'limit' => 3,
                                ]],
                                ['key' => 'jadwal-kajian', 'variant' => 'standar', 'content' => [
                                    'title' => 'Kajian & Event Terdekat',
                                    'limit' => 3,
                                ]],
                                ['key' => 'cta', 'variant' => 'standar', 'content' => [
                                    'title' => 'Mari Ambil Bagian dalam Kebaikan Bersama',
                                    'subtitle' => 'Setiap donasi adalah investasi akhirat.',
                                    'cta_label' => 'Saya Ingin Berdonasi',
                                    'cta_type' => 'scroll',
                                    'cta_section' => 'donasi-progress',
                                ]],
                                $footer,
                            ],
                        ],
                        [
                            'slug' => 'pengurus',
                            'name' => 'Pengurus',
                            'sections' => [
                                $header,
                                ['key' => 'struktur-pengurus', 'variant' => 'standar', 'content' => [
                                    'title' => 'Pengurus Masjid Nurul Huda',
                                ]],
                                $footer,
                            ],
                        ],
                        [
                            'slug' => 'donasi',
                            'name' => 'Donasi',
                            'sections' => [
                                $header,
                                ['key' => 'donasi-progress', 'variant' => 'standar', 'content' => [
                                    'title' => 'Program Donasi',
                                    'subtitle' => 'Setiap donasi yang masuk dapat dilihat riwayat dan peruntukannya secara transparan.',
                                    'limit' => 9,
                                ]],
                                ['key' => 'kalkulator-zakat', 'variant' => 'standar', 'content' => [
                                    'title' => 'Zakat & Infaq',
                                    'cta_label' => 'Hubungi Lazismu',
                                    'wa_message' => 'Assalamu\'alaikum, saya ingin bertanya seputar zakat.',
                                ]],
                                $footer,
                            ],
                        ],
                        [
                            'slug' => 'laporan-keuangan',
                            'name' => 'Laporan Keuangan',
                            'sections' => [
                                $header,
                                ['key' => 'laporan-keuangan', 'variant' => 'standar', 'content' => [
                                    'title' => 'Laporan Keuangan',
                                ]],
                                $footer,
                            ],
                        ],
                        [
                            'slug' => 'kajian-event',
                            'name' => 'Kajian & Event',
                            'sections' => [
                                $header,
                                ['key' => 'jadwal-kajian', 'variant' => 'standar', 'content' => [
                                    'title' => 'Jadwal Kajian & Event',
                                    'limit' => 10,
                                ]],
                                $footer,
                            ],
                        ],
                        [
                            'slug' => 'sewa-aula',
                            'name' => 'Akad Venue',
                            'sections' => [
                                $header,
                                ['key' => 'sewa-aula', 'variant' => 'standar', 'content' => [
                                    'hero_title' => 'Akad Nikah di Aula Serbaguna',
                                    'hero_subtitle' => 'Rayakan momen sakral Anda di tempat yang teduh, penuh berkah, dan siap menampung hingga 150 tamu undangan.',
                                    'availability_badge' => 'Terbuka untuk Pemesanan',
                                    'facilities' => [
                                        'Hingga 150 Tamu',
                                        'Pendingin Ruangan',
                                        'Sound System',
                                        'Area Parkir Luas',
                                        'Wudhu Terpisah',
                                        'Suasana Khidmat',
                                    ],
                                    'image' => self::VENUE_IMAGE,
                                ]],
                                ['key' => 'lokasi-peta', 'variant' => 'standar', 'content' => [
                                    'title' => 'Lokasi Masjid',
                                ]],
                                $footer,
                            ],
                        ],
                    ],
                ],
            ],
        );
    }
}
