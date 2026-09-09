<?php

namespace Database\Seeders;

use App\Models\OrganizationType;
use App\Models\Template;
use App\Models\User;
use App\Services\Samples\PcmAmbuluSamples as Samples;
use App\Services\TemplateSandboxService;
use Illuminate\Database\Seeder;

/**
 * Seeds the "Profil Cabang Muhammadiyah (Standar)" template - the first NON-exclusive template
 * in the app, modeled on Pimpinan Cabang Muhammadiyah Ambulu. Every other seeded template
 * (Klinik Aisyiyah, Suara Muhammadiyah, Masjid Nurul Huda) is is_exclusive and therefore
 * Professional-only; this one is what a cabang on the Starter or Organization plan actually
 * gets, which is the entire reason its shape differs from theirs:
 *
 *   - SINGLE page. Starter and Organization both cap `pages_total` at 1 (see PlanSeeder), and
 *     the builder's page switcher is Professional-only - a multi-page structure here would
 *     seed pages the owner cannot see or edit.
 *   - TEN unlocked sections, which fits every plan: Starter allows 10 and Organization 15
 *     (raised from 5/8 by the 2026_09_10 raise_sections_total_plan_limits migration precisely
 *     because the old caps couldn't fit a complete cabang profile). header/footer are `locked`
 *     (config/page-builder.php) and don't count toward that limit, so the count is the ten
 *     between them. Organization::seedPagesFromTemplate() silently DROPS sections past the
 *     limit walking backward from the end, so the order below is still chosen defensively -
 *     what identifies the cabang first, what merely enriches it last - leaving Starter exactly
 *     at its cap and Organization five sections of headroom to add more.
 *   - `standar` variants ONLY, and no `exclusive` section keys. SectionVariantSeeder marks
 *     modern/ringkas/newsletter/poster is_exclusive, and the five premium mosque sections are
 *     gated at the section level too - picking any of them would make this template unusable
 *     by the very plans it exists for.
 *
 * Content is PCM Ambulu's real profile: the nine-person pimpinan harian, the secretariat on
 * Jl. dr. Soetomo No. 15 Krajan, and Beni Hendarto as narahubung - all pulled from
 * App\Services\Samples\PcmAmbuluSamples (see that class's doc comment for why the content lives
 * there and not here).
 *
 * The editorial idea, since a standard-plan profile has no exclusive layouts to lean on: make
 * the STRUCTURE the story. Six of the nine pimpinan are koordinator bidang, so the program
 * section names one flagship program per koorbid, the agenda shows those programs actually
 * running on dates, and the berita list reports them as they happen - a visitor scrolling the
 * single page reads "here is who leads us -> here is what each of them runs -> here is it
 * happening -> here is proof it happened", which is a far more progressive profile than the
 * usual sambutan-and-sejarah page, using only components every plan already has.
 */
class PcmAmbuluTemplateSeeder extends Seeder
{
    public const SLUG = Samples::TEMPLATE_SLUG;

    public function run(): void
    {
        $organizationType = OrganizationType::where('slug', 'pimpinan-cabang-muhammadiyah')->first();

        $header = ['key' => 'header', 'variant' => 'standar'];
        $footer = ['key' => 'footer', 'variant' => 'standar'];

        $template = Template::updateOrCreate(
            ['slug' => self::SLUG],
            [
                'organization_type_id' => $organizationType?->id,
                'name' => 'Profil Cabang Muhammadiyah (Standar)',
                'description' => 'Template satu halaman untuk Pimpinan Cabang/Ranting Muhammadiyah: profil cabang, struktur pimpinan harian lengkap dengan koordinator bidang, program unggulan per bidang, agenda kegiatan, kabar cabang, jaringan Ortom dan Amal Usaha, serta kontak sekretariat. Tersedia untuk semua paket.',
                'is_active' => true,
                'is_exclusive' => false,
                'is_featured' => true,
                'structure' => [
                    'sample_org_name' => 'PCM Ambulu',
                    // Muhammadiyah blue primary with green secondary - the persyarikatan's own
                    // pairing, unmodified. `Plus Jakarta Sans` + `rounded` are the platform
                    // defaults rather than the exclusive templates' serif/sharp identity:
                    // this is the standard tier and should look like it, so the exclusive
                    // templates keep a visibly distinct look to sell.
                    'brand' => [
                        'primary' => '#2C368B',
                        'secondary' => '#079C4E',
                        'font' => 'Plus Jakarta Sans',
                        'radius' => 'rounded',
                    ],
                    'pages' => [
                        [
                            'slug' => 'home',
                            'name' => 'Beranda',
                            'sections' => [
                                $header,
                                // 1. Identity. Both CTAs are scroll targets rather than one
                                // WhatsApp link: a cabang profile's job is to get a visitor
                                // reading, and the WhatsApp ask lands later, at formulir-kontak,
                                // once there's a reason to make it.
                                ['key' => 'hero', 'variant' => 'standar', 'content' => [
                                    'badge' => 'Pimpinan Cabang Muhammadiyah Ambulu',
                                    'headline' => 'Berkemajuan, Berdaya, Bermanfaat untuk Ambulu',
                                    'subheadline' => 'Pimpinan Cabang Muhammadiyah Ambulu bergerak di enam bidang - pengkaderan, tabligh, wakaf, pendidikan, kesehatan, dan ekonomi - bersama Ortom dan Amal Usaha untuk warga Ambulu, Jember.',
                                    'cta_label' => 'Lihat Program Cabang',
                                    'cta_type' => 'scroll',
                                    'cta_section' => 'program-unggulan',
                                    'cta_secondary_label' => 'Kenali Pimpinan Kami',
                                    'cta_secondary_type' => 'scroll',
                                    'cta_secondary_section' => 'struktur-pengurus',
                                    'image' => Samples::HERO_IMAGE,
                                ]],
                                // 2. Who we are. `stats` are structural facts the cabang can
                                // state without bookkeeping (six bidang, nine pimpinan, and its
                                // Ortom+AUM count) rather than the registry's default "10+
                                // Tahun Berdiri / 100+ Anggota" guesses, which a cabang would
                                // have to either verify or quietly leave wrong.
                                ['key' => 'tentang-organisasi', 'variant' => 'standar', 'content' => [
                                    'title' => 'Tentang PCM Ambulu',
                                    'body' => 'Pimpinan Cabang Muhammadiyah Ambulu adalah penyelenggara gerakan Muhammadiyah di tingkat cabang Kecamatan Ambulu, Kabupaten Jember. Bersama ranting, Ortom, dan Amal Usaha di lingkungannya, cabang menggerakkan dakwah, pengkaderan, pendidikan, kesehatan, wakaf, dan pemberdayaan ekonomi warga - dijalankan oleh pimpinan harian dengan enam koordinator bidang yang masing-masing memegang program nyata.',
                                    'image' => Samples::ABOUT_IMAGE,
                                    'stats' => [
                                        ['value' => '6', 'label' => 'Bidang Garapan'],
                                        ['value' => '9', 'label' => 'Pimpinan Harian'],
                                        ['value' => '6', 'label' => 'Ortom & Amal Usaha'],
                                    ],
                                ]],
                                // 3. The ketua's own voice, between "what this cabang is" and
                                // "who runs it" - the conventional opening of a persyarikatan
                                // profile, kept because it's the one section that makes the page
                                // sound like a person rather than an org chart. Sits after
                                // tentang-organisasi, not before it, so a first-time visitor
                                // learns what PCM Ambulu is before being addressed by its ketua.
                                ['key' => 'sambutan-ketua', 'variant' => 'standar', 'content' => [
                                    'nama' => 'Zainal Arifin',
                                    'jabatan' => 'Ketua Pimpinan Cabang Muhammadiyah Ambulu',
                                    'sambutan' => 'Assalamu\'alaikum warahmatullahi wabarakatuh. Selamat datang di laman resmi Pimpinan Cabang Muhammadiyah Ambulu. Melalui laman ini kami membuka pintu selebar-lebarnya bagi warga persyarikatan dan masyarakat umum untuk mengenal program, agenda, dan amal usaha yang kami jalankan. Muhammadiyah Ambulu berkomitmen menghadirkan gerakan dakwah yang berkemajuan - membina kader, memakmurkan masjid, menjaga amanah wakaf, memajukan pendidikan dan kesehatan, serta memberdayakan ekonomi warga. Mari bersama-sama kita gerakkan kebaikan untuk Ambulu.',
                                ]],
                                // 4. The nine pimpinan. Placed before the programs on purpose:
                                // each koorbid's name here is what makes the program below it
                                // read as someone's responsibility rather than a wish list.
                                ['key' => 'struktur-pengurus', 'variant' => 'standar', 'content' => [
                                    'title' => 'Pimpinan Harian PCM Ambulu',
                                    'items' => Samples::pimpinanHarian(),
                                ]],
                                // 5. One flagship program per koorbid - see
                                // Samples::programItems() for the mapping.
                                ['key' => 'program-unggulan', 'variant' => 'standar', 'content' => [
                                    'title' => 'Program Unggulan per Bidang',
                                    'items' => Samples::programItems(),
                                ]],
                                // 6. Those programs on a calendar.
                                ['key' => 'agenda', 'variant' => 'standar', 'content' => [
                                    'title' => 'Agenda Kegiatan Cabang',
                                    'subtitle' => 'Kegiatan rutin dan terjadwal yang terbuka untuk warga persyarikatan maupun masyarakat umum.',
                                    'limit' => 4,
                                    'items' => Samples::agendaPreviewItems(),
                                ]],
                                // 7. Those programs having happened. `limit` 4 matches the
                                // sample count so the standar grid fills two even rows; a
                                // cabang that publishes more posts gets a "Muat Lebih Banyak"
                                // button rather than an ever-growing page.
                                ['key' => 'daftar-berita', 'variant' => 'standar', 'content' => [
                                    'title' => 'Kabar Cabang',
                                    'limit' => 3,
                                    'items' => Samples::beritaItems(),
                                ]],
                                // 8. The cabang isn't alone. Also the section that tells a
                                // visiting Ortom/AUM this platform is where their own site
                                // would sit.
                                ['key' => 'jaringan-aum-ortom', 'variant' => 'standar', 'content' => [
                                    'title' => 'Ortom & Amal Usaha di Ambulu',
                                    'items' => Samples::jaringanItems(),
                                ]],
                                // 9. The ask, at the bottom where it belongs. Named narahubung
                                // in the subtitle rather than a bare number - a warga texting
                                // an organization wants to know who picks up. No separate `cta`
                                // section above it: formulir-kontak already carries the same
                                // WhatsApp action, and a standard-tier page earns attention by
                                // being substantive, not by asking twice.
                                ['key' => 'formulir-kontak', 'variant' => 'standar', 'content' => [
                                    'title' => 'Hubungi Sekretariat PCM Ambulu',
                                    'subtitle' => 'Untuk undangan, kerja sama program, atau informasi kegiatan, hubungi narahubung cabang '.Samples::NARAHUBUNG.'.',
                                    'wa_number' => Samples::WHATSAPP,
                                    'wa_message' => 'Assalamu\'alaikum, saya ingin bertanya seputar kegiatan Pimpinan Cabang Muhammadiyah Ambulu.',
                                ]],
                                // 10. Where to actually find them. Pairs with the section above
                                // rather than repeating the address inside its subtitle - a
                                // visitor who wants to visit wants a map, not a line of text.
                                ['key' => 'lokasi-peta', 'variant' => 'standar', 'content' => [
                                    'title' => 'Sekretariat PCM Ambulu',
                                    'address' => Samples::ADDRESS,
                                    'map_embed' => Samples::MAP_EMBED,
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
