<?php

namespace Database\Seeders;

use App\Models\OrganizationType;
use App\Models\Template;
use App\Models\User;
use App\Services\TemplateSandboxService;
use Illuminate\Database\Seeder;

/**
 * Seeds the "Masjid & Mushola Plus" template - kept in its own seeder rather than
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
 *   - The five premium mosque sections it introduced (fasilitas-masjid, donasi-progress,
 *     laporan-keuangan, kalkulator-zakat, akad-venue) use the `nurul-huda` variant, which is
 *     their only one. Those sections are themselves plan-gated via config/page-builder.php's
 *     `exclusive` flag, so a lower-plan organization can't add them from the page builder.
 *   - Copy is lifted from that project's own Blade views; the matching CMS sample records
 *     (12 facilities, 5 donation programs, 6 months of books, 4 kajian, 2 takmir, 8 gallery
 *     captions) come from CmsSampleDataSeeder, which keys off this template's slug.
 *
 * Section total across all 6 pages is 15 unlocked sections, under the Professional plan's
 * sections_total limit (25) - this template is is_exclusive, so only Professional-plan
 * organizations can pick it in the first place (see Organization::canUseExclusiveTemplates()).
 */
class MasjidNurulHudaTemplateSeeder extends Seeder
{
    public const SLUG = 'masjid-nurul-huda-eksklusif';

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

    /**
     * Uploaded through the sandbox editor's Brand Settings (organization id 9), so unlike the
     * S3 constants above this is a per-ORGANIZATION file, not a public asset - if that sandbox
     * or its storage is ever cleared, the logo 404s and should be re-uploaded. Only ever read
     * back by TemplateSandboxService::sandboxFor(); a real organization created from this
     * template never inherits it (see that service's export() comment).
     */
    private const LOGO = 'https://storage.ambulu.or.id/organizations/9/brand/52e49483-ac67-4e07-be73-6a65a4e70d12.webp';

    /**
     * The takmir's Google Maps pin, used as the hero's secondary CTA ("Arahkan ke Lokasi").
     * A plain share link rather than the /maps/embed?pb=... form lokasi-peta needs, because
     * this one opens the Maps app for directions instead of rendering an inline iframe.
     */
    private const MAPS_LINK = 'https://maps.app.goo.gl/VajCiw3gsWXNxZAc8';

    /**
     * Masjid Nurul Huda's contact details as the takmir filled them in on the sandbox's Brand
     * Settings page. The email and the four social accounts are Suara Muhammadiyah Ambulu's -
     * the cabang's media arm - which is deliberate: the masjid has no accounts of its own yet
     * and publishes through that channel. WhatsApp and the address are the masjid's own.
     *
     * Read by Organization::phone()/whatsapp()/etc. as the fallback an organization on this
     * template shows before filling in its own contact fields.
     *
     * @return array<string, string|null>
     */
    private static function contact(): array
    {
        return [
            'email' => 'mediamu.ambulu@gmail.com',
            'whatsapp' => '6285213683653',
            'address' => 'jl. Raya Suyitman No.178, Sumberan, Ambulu, Kec. Ambulu, Kabupaten Jember',
            'instagram_url' => 'https://www.instagram.com/suaramuhammadiyahambulu',
            'facebook_url' => 'https://www.facebook.com/share/14sYGQiqc4L/',
            'tiktok_url' => 'https://www.tiktok.com/@suaramuhammadiyahambulu',
            'youtube_url' => 'https://www.youtube.com/@suaramuhammadiyahabl',
        ];
    }

    /**
     * The four recurring "Kajian Malam Ilmu & Iman" sessions, each with the flyer the takmir
     * uploaded through the builder's agenda CMS (agendas.poster - the `poster` variant renders
     * these as a grid, see agenda/poster.blade.php). Dates are the ones a freshly seeded
     * organization gets: CmsSampleDataSeeder::nurulHudaKajianSamples() schedules them on
     * consecutive Fridays from seeding time, so they are captured here as the concrete
     * date_day/date_month/date_year trio the preview needs (no $organization to query).
     *
     * Poster URLs are per-ORGANIZATION uploads on the sandbox (id 9), same caveat as self::LOGO.
     *
     * Public because MasjidNurulHudaStandarTemplateSeeder reuses the same list - both tiers
     * describe one masjid, so the kajian schedule must not drift between them.
     *
     * @return array<int, array<string, string|null>>
     */
    public static function agendaItems(): array
    {
        $poster = 'https://storage.ambulu.or.id/organizations/9/agenda/';

        return [
            ['title' => 'Kajian Malam Ilmu & Iman - Ust. Hadi Santoso', 'poster' => $poster.'ab6e857b-3878-419d-9937-77abe8c447ec.webp', 'date_day' => '18', 'date_month' => 'Sep', 'date_year' => '2026', 'location' => 'Ruang Utama Masjid', 'time' => '18:00', 'url' => null],
            ['title' => 'Kajian Malam Ilmu & Iman - Ust. Tyas Hidayatulloh, M.Pd', 'poster' => $poster.'21746fb4-ba32-49c6-84fd-d89c1a137ad9.webp', 'date_day' => '25', 'date_month' => 'Sep', 'date_year' => '2026', 'location' => 'Ruang Utama Masjid', 'time' => '18:00', 'url' => null],
            ['title' => 'Kajian Malam Ilmu & Iman - Ust. Affan Kamal Mubarok, B.S., M.A.', 'poster' => $poster.'dea3e455-d528-4f90-a261-800238653801.webp', 'date_day' => '02', 'date_month' => 'Okt', 'date_year' => '2026', 'location' => 'Ruang Utama Masjid', 'time' => '18:00', 'url' => null],
            ['title' => 'Kajian Malam Ilmu & Iman - Ust. Nurhadi Amin, S.Ag', 'poster' => $poster.'b8e2af80-450c-4dad-9263-90c7d0e00778.webp', 'date_day' => '09', 'date_month' => 'Okt', 'date_year' => '2026', 'location' => 'Ruang Utama Masjid', 'time' => '18:00', 'url' => null],
        ];
    }

    /**
     * Gallery photos as they stand on the sandbox: the four Ramadhan/bakti-sosial captions
     * CmsSampleDataSeeder seeds from the nurul-huda project, plus two "Pembangunan Jembatan"
     * photos the takmir has since uploaded through the builder (hence the per-organization
     * storage URLs, same caveat as self::LOGO). Public for the same reason as agendaItems().
     *
     * @return array<int, array{image: string, caption: string}>
     */
    public static function galeriItems(): array
    {
        return [
            ['image' => self::S3.'/gallery/01M0F8E78MXYJTGZ12GKHMFS33.jpg', 'caption' => 'Kegiatan Kajian Guru Besar Ramadhan 2026'],
            ['image' => self::S3.'/gallery/01M0F8GA32JZ6H6J08PMFTW3M2.jpg', 'caption' => 'Buka Bersama Ramadhan 2026'],
            ['image' => self::S3.'/gallery/01M0F4DHD4N4ZX5AWYMEBF3M8W.jpg', 'caption' => 'Bakti Sosial AMM Ambulu 2026'],
            ['image' => self::S3.'/gallery/01M0F8HZC8FMCW04Y1KTP59HB6.jpg', 'caption' => 'Kunjungan Muspika 2026'],
            ['image' => 'https://storage.ambulu.or.id/organizations/9/galeri/8cdec3e3-293b-4964-8001-c6ab61cde8d8.webp', 'caption' => 'Pembangunan Jembatan'],
            ['image' => 'https://storage.ambulu.or.id/organizations/9/galeri/c3ea38d5-3bcc-4da2-af4a-7cd6717c7ee4.webp', 'caption' => 'Pembangunan Jembatan'],
        ];
    }

    /**
     * The two takmir CmsSampleDataSeeder::nurulHudaOfficerSamples() seeds. Public for the
     * same reason as agendaItems().
     *
     * @return array<int, array{name: string, role: string, photo: null}>
     */
    public static function pengurusItems(): array
    {
        return [
            ['name' => 'Suhartono, S.Pd', 'role' => 'Ketua Takmir', 'photo' => null],
            ['name' => 'Tyas Hidayatulloh, S.Pd, M.Pd', 'role' => 'Sekretaris', 'photo' => null],
        ];
    }

    /**
     * The three donation programs the takmir currently runs, as edited through the builder's
     * donations CMS - NOT the five CmsSampleDataSeeder::seedDonationPrograms() originally
     * seeded (Renovasi Atap and Pembangunan Perpustakaan were removed, and Santunan Anak Yatim
     * renamed to Bakti Sosial). Ordered the way donasi-progress/nurul-huda.blade.php sorts a
     * real organization's rows - still-running programs by largest target first, then expired
     * ones - so the preview matches what an organization on this template sees.
     *
     * `collected_amount`/`percent` are the live figures from the sandbox's own
     * collectedAmount()/progressPercent(); Wakaf Pembangunan Masjid's is deliberately not a
     * round number because it comes from a real donor-by-donor ledger (see
     * CmsSampleDataSeeder::wakafLedger()), not a synthetic percent.
     *
     * @return array<int, array{name: string, cover_photo: string, target_amount: int, collected_amount: int, percent: float|int, status: string, url: null}>
     */
    private static function donasiProgressItems(): array
    {
        return [
            ['name' => 'Wakaf Pembangunan Masjid', 'cover_photo' => self::S3.'/donation-programs/wakaf-pembangunan-masjid/cover.jpg', 'target_amount' => 1_175_600_000, 'collected_amount' => 437_682_000, 'percent' => 37.2, 'status' => 'active', 'url' => null],
            ['name' => 'Wakaf Al-Quran', 'cover_photo' => self::S3.'/facilities/01M17A75E57K13N172M7D1H78R.jpg', 'target_amount' => 10_000_000, 'collected_amount' => 0, 'percent' => 0, 'status' => 'upcoming', 'url' => null],
            ['name' => 'Bakti Sosial', 'cover_photo' => self::S3.'/gallery/01M0F4DHD4N4ZX5AWYMEBF3M8W.jpg', 'target_amount' => 20_000_000, 'collected_amount' => 17_000_000, 'percent' => 85, 'status' => 'expired', 'url' => null],
        ];
    }

    public function run(): void
    {
        $organizationType = OrganizationType::where('slug', 'masjidmushola')->first();

        $header = ['key' => 'header', 'variant' => 'standar'];
        $footer = ['key' => 'footer', 'variant' => 'standar'];

        $template = Template::updateOrCreate(
            ['slug' => self::SLUG],
            [
                'organization_type_id' => $organizationType?->id,
                'name' => 'Masjid Peradaban',
                'description' => 'Fasilitas dan galeri masjid tampil di beranda, donasi & wakaf dengan progress bar per program, laporan keuangan yang transparan, jadwal kajian rutin, aula masjid untuk akad nikah, dan profil takmir - masing-masing punya halaman sendiri.',
                'is_active' => true,
                'is_exclusive' => true,
                'is_featured' => true,
                'structure' => [
                    'sample_org_name' => 'Masjid Nurul Huda',
                    'brand' => [
                        'primary' => '#2c368B',
                        'secondary' => '#1e79cc',
                        'font' => 'Plus Jakarta Sans',
                        'radius' => 'soft',
                        'logo' => self::LOGO,
                    ],
                    'contact' => self::contact(),
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
                                    // Target is the section KEY. "Simpan ke Template" writes a
                                    // raw numeric section id here instead ('126' as of this
                                    // writing) - a builder bug that recurs on every re-save of
                                    // this hero's CTAs; SectionAnchor::href() only resolves a
                                    // key, so an id would 404 the scroll on any other
                                    // organization. Re-check this field when resyncing.
                                    'cta_section' => 'donasi-progress',
                                    'cta_secondary_label' => 'Arahkan ke Lokasi',
                                    'cta_secondary_type' => 'url',
                                    'cta_secondary_url' => self::MAPS_LINK,
                                    'image' => self::HERO_IMAGE,
                                ]],
                                ['key' => 'tentang-organisasi', 'variant' => 'standar', 'content' => [
                                    'title' => 'Pusat Ibadah & Kegiatan Umat',
                                    'body' => 'Masjid Nurul Huda adalah rumah ibadah sekaligus pusat kegiatan keagamaan, pendidikan, dan sosial bagi masyarakat sekitar. Kami berkomitmen mengelola dana umat secara transparan dan menghadirkan kegiatan yang bermanfaat bagi jamaah.',
                                    'image' => self::ABOUT_IMAGE,
                                    'stats' => [
                                        ['value' => '12', 'label' => 'Fasilitas Masjid'],
                                        ['value' => '4x', 'label' => 'Kajian Rutin/Bulan'],
                                        ['value' => '100%', 'label' => 'Dana Transparan'],
                                    ],
                                ]],
                                // No `limit`: the section always shows every facility rather
                                // than capping the count, and `items` is the same 12-facility
                                // list CmsSampleDataSeeder::seedFacilities() inserts as real
                                // MasjidFacility rows for an organization - without it, template-
                                // preview context (no $organization, see the view's own comment)
                                // fell back to fasilitas-masjid/nurul-huda.blade.php's generic
                                // 3-item, photo-less placeholder instead of the real fasilitas.
                                ['key' => 'fasilitas-masjid', 'variant' => 'nurul-huda', 'content' => [
                                    'title' => 'Fasilitas Masjid',
                                    'items' => [
                                        ['name' => 'Halaman dan Teras Depan', 'photo' => self::S3.'/facilities/01M0CQ2ENVEQ1WQYZE5AQM257E.jpg', 'description' => null],
                                        ['name' => 'Parkiran Utama', 'photo' => self::S3.'/facilities/01M0CQYYDATZX1SYKX2APKBRJK.jpg', 'description' => null],
                                        ['name' => 'Taman dan Kolam Masjid', 'photo' => self::S3.'/facilities/01M0CQ3E3PVMX3QJS7XZSQH757.jpg', 'description' => null],
                                        ['name' => 'Tempat Jamaah Laki-laki', 'photo' => self::S3.'/facilities/01M0CQBN9Z92DPB8KC69WCRGA7.jpg', 'description' => null],
                                        ['name' => 'Tempat Jamaah Perempuan', 'photo' => self::S3.'/facilities/01M0CQCASWABEETW67AYWWZT7W.jpg', 'description' => null],
                                        ['name' => 'Tempat Wudhu Laki-laki (Luar)', 'photo' => self::S3.'/facilities/01M0CQD8CBYZ8W04MTY14KZ4WS.jpg', 'description' => null],
                                        ['name' => 'Tempat Wudhu Perempuan (Depan)', 'photo' => self::S3.'/facilities/01M0CQET8624HXDET63JPCTWPM.jpg', 'description' => null],
                                        ['name' => 'Tempat Wudhu Laki-laki (Belakang)', 'photo' => self::S3.'/facilities/01M0CQG09YTAADQE878R8HS78W.jpg', 'description' => null],
                                        ['name' => 'Tempat Wudhu Perempuan (Belakang)', 'photo' => self::S3.'/facilities/01M0CQGWCMTSMZJS4SD5D7M1X3.jpg', 'description' => null],
                                        ['name' => 'Parkiran Belakang', 'photo' => self::S3.'/facilities/01M0CQNYNXE3NP5K5NHG5JP3CR.jpg', 'description' => null],
                                        ['name' => 'Ruang Masjid Lantai 2', 'photo' => self::S3.'/facilities/01M0CQRCJTJA6SK8KBFXWE43GQ.jpg', 'description' => null],
                                        ['name' => 'Alat Sholat', 'photo' => self::S3.'/facilities/01M17A75E57K13N172M7D1H78R.jpg', 'description' => null],
                                    ],
                                ]],
                                ['key' => 'galeri', 'variant' => 'standar', 'content' => [
                                    'title' => 'Dokumentasi Kegiatan',
                                    'limit' => 8,
                                    'items' => self::galeriItems(),
                                ]],
                                ['key' => 'donasi-progress', 'variant' => 'nurul-huda', 'content' => [
                                    'title' => 'Program Donasi Aktif',
                                    'limit' => 3,
                                    'items' => self::donasiProgressItems(),
                                ]],
                                ['key' => 'agenda', 'variant' => 'poster', 'content' => [
                                    'title' => 'Kajian & Event Terdekat',
                                    'subtitle' => null,
                                    'limit' => null,
                                    'items' => self::agendaItems(),
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
                                    'items' => self::pengurusItems(),
                                ]],
                                $footer,
                            ],
                        ],
                        [
                            'slug' => 'donasi',
                            'name' => 'Donasi',
                            'sections' => [
                                $header,
                                ['key' => 'donasi-progress', 'variant' => 'nurul-huda', 'content' => [
                                    'title' => 'Program Donasi',
                                    'subtitle' => 'Setiap donasi yang masuk dapat dilihat riwayat dan peruntukannya secara transparan.',
                                    'limit' => 9,
                                    'items' => self::donasiProgressItems(),
                                ]],
                                ['key' => 'kalkulator-zakat', 'variant' => 'nurul-huda', 'content' => [
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
                                ['key' => 'laporan-keuangan', 'variant' => 'nurul-huda', 'content' => [
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
                                ['key' => 'agenda', 'variant' => 'poster', 'content' => [
                                    'title' => 'Jadwal Kajian & Event',
                                    'limit' => 10,
                                    'items' => self::agendaItems(),
                                ]],
                                $footer,
                            ],
                        ],
                        [
                            'slug' => 'akad-venue',
                            'name' => 'Akad Venue',
                            'sections' => [
                                $header,
                                ['key' => 'akad-venue', 'variant' => 'nurul-huda', 'content' => [
                                    'hero_title' => 'Akad Nikah Penuh Khidmat',
                                    'hero_subtitle' => 'Langsungkan momen sakral Anda di tempat yang teduh, penuh berkah, dan siap menampung hingga 150 tamu undangan.',
                                    'availability_badge' => 'Terbuka untuk Jamaah',
                                    'infak_note' => 'Tidak ada tarif sewa, jamaah dipersilakan berinfak semampunya.',
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

        if ($admin = User::where('is_admin', true)->first()) {
            app(TemplateSandboxService::class)->sandboxFor($template, $admin);
        }
    }
}
