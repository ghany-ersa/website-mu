<?php

namespace Database\Seeders;

use App\Models\OrganizationType;
use App\Models\Template;
use App\Models\User;
use App\Services\Samples\PcmAmbuluSamples as Samples;
use App\Services\TemplateSandboxService;
use Illuminate\Database\Seeder;

/**
 * Seeds the "Profil Cabang Muhammadiyah (Eksklusif)" template - the Professional-tier
 * counterpart to PcmAmbuluTemplateSeeder, built from the SAME PcmAmbuluSamples content so the
 * two tiers describe one identical cabang and differ only in what the plan buys.
 *
 * That framing is the point. A cabang comparing the two should see the same PCM Ambulu twice,
 * not two different organizations, so the upgrade decision is about presentation and capacity
 * rather than about content it would have to rewrite. What Professional actually adds here:
 *
 *   - FOUR pages instead of one (`pages_total` is 10 on Professional, 1 below it), so the
 *     material that had to be compressed into a single scroll gets room: profil, program,
 *     kabar, and kontak each become their own page.
 *   - EXCLUSIVE variants - hero/modern, tentang-organisasi/modern, struktur-pengurus/modern,
 *     program-unggulan/modern, daftar-berita/modern + ringkas, cta/modern + newsletter. These
 *     are gated by SectionVariantSeeder's is_exclusive flag, so they are literally unavailable
 *     to the standard template.
 *   - 16 unlocked sections, comfortably inside Professional's sections_total (25) and far past
 *     what Starter/Organization allow (10/15).
 *
 * Deliberately NOT used: the five premium mosque sections (fasilitas-masjid, donasi-progress,
 * laporan-keuangan, kalkulator-zakat, sewa-aula). They are gated to mosque use cases and none
 * models a cabang's needs - same reasoning as KlinikAisyiyahAmbuluTemplateSeeder's note.
 *
 * Editorially this keeps the standard template's argument (structure as the story: a koorbid
 * behind every program) but lets each beat breathe on its own page, and adds the one thing a
 * single page had no room for - a dedicated program page where each bidang's work is presented
 * at length rather than as one card in a six-card grid.
 */
class PcmAmbuluEksklusifTemplateSeeder extends Seeder
{
    public const SLUG = 'pcm-ambulu-eksklusif';

    public function run(): void
    {
        // Named for the movement, not the tier - see OrganizationTypeSeeder.
        $organizationType = OrganizationType::where('slug', 'muhammadiyah')->first();

        $header = ['key' => 'header', 'variant' => 'standar'];
        $footer = ['key' => 'footer', 'variant' => 'standar'];

        $template = Template::updateOrCreate(
            ['slug' => self::SLUG],
            [
                'organization_type_id' => $organizationType?->id,
                'name' => 'Profil Cabang Muhammadiyah (Eksklusif)',
                'description' => 'Template eksklusif multi-halaman untuk Pimpinan Cabang/Ranting Muhammadiyah: beranda dengan sambutan dan pimpinan harian, halaman program per bidang lengkap dengan agenda, halaman kabar cabang, serta halaman kontak dengan jaringan Ortom/AUM dan peta sekretariat. Khusus paket dengan entitlement template eksklusif.',
                'is_active' => true,
                'is_exclusive' => true,
                // The standard-tier PCM template carries the `is_featured` flag for this
                // organization type - one featured template per type keeps the flag meaningful
                // now that every type has two.
                'is_featured' => false,
                'structure' => [
                    'sample_org_name' => 'PCM Ambulu',
                    // Muhammadiyah blue/green as always, but with the serif `Lora` + `sharp`
                    // radius that marks every exclusive template - the same cabang, visibly
                    // dressed differently from its standard-tier twin.
                    'brand' => [
                        'primary' => '#2C368B',
                        'secondary' => '#079C4E',
                        'font' => 'Lora',
                        'radius' => 'sharp',
                    ],
                    'pages' => [
                        [
                            'slug' => 'home',
                            'name' => 'Beranda',
                            'sections' => [
                                $header,
                                ['key' => 'hero', 'variant' => 'modern', 'content' => [
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
                                ['key' => 'tentang-organisasi', 'variant' => 'modern', 'content' => [
                                    'title' => 'Tentang PCM Ambulu',
                                    'body' => 'Pimpinan Cabang Muhammadiyah Ambulu adalah penyelenggara gerakan Muhammadiyah di tingkat cabang Kecamatan Ambulu, Kabupaten Jember. Bersama ranting, Ortom, dan Amal Usaha di lingkungannya, cabang menggerakkan dakwah, pengkaderan, pendidikan, kesehatan, wakaf, dan pemberdayaan ekonomi warga - dijalankan oleh pimpinan harian dengan enam koordinator bidang yang masing-masing memegang program nyata.',
                                    'image' => Samples::ABOUT_IMAGE,
                                    'stats' => [
                                        ['value' => '6', 'label' => 'Bidang Garapan'],
                                        ['value' => '9', 'label' => 'Pimpinan Harian'],
                                        ['value' => '6', 'label' => 'Ortom & Amal Usaha'],
                                    ],
                                ]],
                                ['key' => 'sambutan-ketua', 'variant' => 'modern', 'content' => [
                                    'nama' => 'Zainal Arifin',
                                    'jabatan' => 'Ketua Pimpinan Cabang Muhammadiyah Ambulu',
                                    'sambutan' => 'Assalamu\'alaikum warahmatullahi wabarakatuh. Selamat datang di laman resmi Pimpinan Cabang Muhammadiyah Ambulu. Melalui laman ini kami membuka pintu selebar-lebarnya bagi warga persyarikatan dan masyarakat umum untuk mengenal program, agenda, dan amal usaha yang kami jalankan. Muhammadiyah Ambulu berkomitmen menghadirkan gerakan dakwah yang berkemajuan - membina kader, memakmurkan masjid, menjaga amanah wakaf, memajukan pendidikan dan kesehatan, serta memberdayakan ekonomi warga. Mari bersama-sama kita gerakkan kebaikan untuk Ambulu.',
                                ]],
                                ['key' => 'struktur-pengurus', 'variant' => 'modern', 'content' => [
                                    'title' => 'Pimpinan Harian PCM Ambulu',
                                    'items' => Samples::pimpinanHarian(),
                                ]],
                                // Teaser only - the full six-program treatment lives on the
                                // Program page, which is the whole reason that page exists.
                                ['key' => 'daftar-berita', 'variant' => 'ringkas', 'content' => [
                                    'title' => 'Kabar Terkini',
                                    'limit' => 3,
                                    'items' => array_slice(Samples::beritaItems(), 0, 3),
                                ]],
                                ['key' => 'cta', 'variant' => 'modern', 'content' => [
                                    'title' => 'Mari Bergerak Bersama Muhammadiyah Ambulu',
                                    'subtitle' => 'Terbuka untuk warga persyarikatan maupun masyarakat umum yang ingin ikut kegiatan atau berkolaborasi.',
                                    'cta_label' => 'Hubungi Sekretariat',
                                    'cta_type' => 'whatsapp',
                                    'cta_wa_number' => Samples::WHATSAPP,
                                    'cta_wa_message' => 'Assalamu\'alaikum, saya ingin bertanya seputar kegiatan Pimpinan Cabang Muhammadiyah Ambulu.',
                                ]],
                                $footer,
                            ],
                        ],
                        [
                            'slug' => 'program',
                            'name' => 'Program',
                            'sections' => [
                                $header,
                                // The payoff of having a second page: all six bidang programs
                                // in the modern layout, then the calendar that proves they run.
                                ['key' => 'program-unggulan', 'variant' => 'modern', 'content' => [
                                    'title' => 'Program Unggulan per Bidang',
                                    'items' => Samples::programItems(),
                                ]],
                                ['key' => 'agenda', 'variant' => 'standar', 'content' => [
                                    'title' => 'Agenda Kegiatan Cabang',
                                    'subtitle' => 'Kegiatan rutin dan terjadwal yang terbuka untuk warga persyarikatan maupun masyarakat umum.',
                                    'limit' => 4,
                                    'items' => Samples::agendaPreviewItems(),
                                ]],
                                ['key' => 'cta', 'variant' => 'standar', 'content' => [
                                    'title' => 'Ingin Ikut Kegiatan Cabang?',
                                    'subtitle' => 'Sebagian besar agenda terbuka untuk umum. Hubungi kami untuk memastikan jadwal terbaru.',
                                    'cta_label' => 'Tanya Jadwal via WhatsApp',
                                    'cta_type' => 'whatsapp',
                                    'cta_wa_number' => Samples::WHATSAPP,
                                    'cta_wa_message' => 'Assalamu\'alaikum, saya ingin bertanya seputar agenda kegiatan PCM Ambulu.',
                                ]],
                                $footer,
                            ],
                        ],
                        [
                            'slug' => 'kabar',
                            'name' => 'Kabar',
                            'sections' => [
                                $header,
                                // Full listing, unlimited - the home page's ringkas teaser is
                                // what keeps this from being redundant with it.
                                ['key' => 'daftar-berita', 'variant' => 'modern', 'content' => [
                                    'title' => 'Kabar Cabang',
                                    'limit' => null,
                                    'items' => Samples::beritaItems(),
                                ]],
                                ['key' => 'cta', 'variant' => 'newsletter', 'content' => [
                                    'title' => 'Jangan Lewatkan Kabar Muhammadiyah Ambulu',
                                    'subtitle' => 'Hubungi sekretariat untuk mendapat informasi kegiatan cabang terbaru.',
                                    'cta_label' => 'Hubungi Kami',
                                    'cta_type' => 'whatsapp',
                                    'cta_wa_number' => Samples::WHATSAPP,
                                    'cta_wa_message' => 'Assalamu\'alaikum, saya ingin mendapat informasi kegiatan PCM Ambulu.',
                                ]],
                                $footer,
                            ],
                        ],
                        [
                            'slug' => 'kontak',
                            'name' => 'Kontak',
                            'sections' => [
                                $header,
                                ['key' => 'formulir-kontak', 'variant' => 'standar', 'content' => [
                                    'title' => 'Hubungi Sekretariat PCM Ambulu',
                                    'subtitle' => 'Untuk undangan, kerja sama program, atau informasi kegiatan, hubungi narahubung cabang '.Samples::NARAHUBUNG.'.',
                                    'wa_number' => Samples::WHATSAPP,
                                    'wa_message' => 'Assalamu\'alaikum, saya ingin bertanya seputar kegiatan Pimpinan Cabang Muhammadiyah Ambulu.',
                                ]],
                                ['key' => 'jaringan-aum-ortom', 'variant' => 'standar', 'content' => [
                                    'title' => 'Ortom & Amal Usaha di Ambulu',
                                    'items' => Samples::jaringanItems(),
                                ]],
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
