<?php

namespace Database\Seeders;

use App\Models\OrganizationType;
use App\Models\Template;
use App\Models\User;
use App\Services\Samples\PcaAmbuluSamples as Samples;
use App\Services\TemplateSandboxService;
use Illuminate\Database\Seeder;

/**
 * Seeds the "Profil Cabang Aisyiyah (Eksklusif)" template - the Professional-tier counterpart to
 * PcaAmbuluTemplateSeeder, built from the SAME PcaAmbuluSamples content so both tiers describe
 * one identical cabang and differ only in what the plan buys (see
 * PcmAmbuluEksklusifTemplateSeeder's doc comment for the full reasoning behind that pairing).
 *
 * What Professional adds over the standard PCA template: four pages instead of one, the
 * exclusive `modern`/`ringkas`/`newsletter` variants, and 15 unlocked sections against
 * Professional's sections_total of 25.
 *
 * The page split follows this cabang's own argument rather than PCM's. Aisyiyah Ambulu argues
 * from WORK, so its second page is "Program & Kegiatan" - the environmental and economic
 * programs presented at length, with the agenda and the photo gallery that prove they happen,
 * all three of which the single-page version had to ration. Structure comes last, on the kontak
 * page, exactly as it does at standard tier: this is a movement introduced by what its ibu-ibu
 * do, not by who chairs it.
 *
 * Deliberately NOT used: the five premium mosque sections - gated to mosque use cases, and none
 * models a cabang Aisyiyah's needs.
 */
class PcaAmbuluEksklusifTemplateSeeder extends Seeder
{
    public const SLUG = 'pca-ambulu-eksklusif';

    public function run(): void
    {
        // Named for the movement, not the tier - see OrganizationTypeSeeder.
        $organizationType = OrganizationType::where('slug', 'aisyiyah')->first();

        $header = ['key' => 'header', 'variant' => 'standar'];
        $footer = ['key' => 'footer', 'variant' => 'standar'];

        $template = Template::updateOrCreate(
            ['slug' => self::SLUG],
            [
                'organization_type_id' => $organizationType?->id,
                'name' => 'Profil Cabang Aisyiyah (Eksklusif)',
                'description' => 'Template eksklusif multi-halaman untuk Pimpinan Cabang/Ranting Aisyiyah: beranda dengan profil gerakan perempuan dan kabar terkini, halaman program lingkungan dan pemberdayaan ekonomi lengkap dengan agenda dan galeri kegiatan, halaman kabar cabang, serta halaman kontak dengan struktur pimpinan, amal usaha, dan peta sekretariat. Khusus paket dengan entitlement template eksklusif.',
                'is_active' => true,
                'is_exclusive' => true,
                // The standard-tier PCA template carries `is_featured` for this organization
                // type - one featured template per type keeps the flag meaningful.
                'is_featured' => false,
                'structure' => [
                    'sample_org_name' => 'PCA Ambulu',
                    // Aisyiyah green led with Muhammadiyah blue secondary, as at standard tier,
                    // but with the serif `Lora` + `sharp` radius that marks every exclusive
                    // template.
                    'brand' => [
                        'primary' => '#079C4E',
                        'secondary' => '#2C368B',
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
                                    'badge' => 'Pimpinan Cabang Aisyiyah Ambulu',
                                    'headline' => 'Perempuan Berkemajuan, Bumi Terjaga, Keluarga Berdaya',
                                    'subheadline' => 'Ibu-ibu Aisyiyah Ambulu bergerak dari rumah ke rumah: memilah sampah, menanam kebun gizi di halaman sendiri, dan menumbuhkan usaha rumahan - merawat lingkungan sekaligus memandirikan keluarga.',
                                    'cta_label' => 'Lihat Program Kami',
                                    'cta_type' => 'scroll',
                                    'cta_section' => 'program-unggulan',
                                    'cta_secondary_label' => 'Kabar Terkini',
                                    'cta_secondary_type' => 'scroll',
                                    'cta_secondary_section' => 'daftar-berita',
                                    'image' => Samples::HERO_IMAGE,
                                ]],
                                ['key' => 'tentang-organisasi', 'variant' => 'modern', 'content' => [
                                    'title' => 'Tentang Aisyiyah Ambulu',
                                    'body' => 'Pimpinan Cabang Aisyiyah Ambulu adalah gerakan perempuan Muhammadiyah di Kecamatan Ambulu, Kabupaten Jember. Sejak dulu Aisyiyah percaya bahwa perubahan besar dimulai dari hal-hal yang dekat: dapur yang sehat, halaman yang hijau, sampah yang terpilah, dan ibu yang punya penghasilan sendiri. Dari sanalah kami bergerak - merawat lingkungan, memberdayakan ekonomi keluarga, mengasuh anak usia dini lewat TK ABA, dan menjaga kesehatan warga bersama Klinik Pratama Aisyiyah Ambulu.',
                                    'image' => Samples::ABOUT_IMAGE,
                                    'stats' => [
                                        ['value' => '5', 'label' => 'Amal Usaha'],
                                        ['value' => '4', 'label' => 'TK ABA Binaan'],
                                        ['value' => '7', 'label' => 'Pimpinan Cabang'],
                                    ],
                                ]],
                                // Teaser only - the full program treatment is the next page's
                                // whole reason to exist.
                                ['key' => 'program-unggulan', 'variant' => 'modern', 'content' => [
                                    'title' => 'Yang Kami Kerjakan',
                                    'items' => array_slice(Samples::programItems(), 0, 3),
                                ]],
                                ['key' => 'daftar-berita', 'variant' => 'ringkas', 'content' => [
                                    'title' => 'Kabar Terkini',
                                    'limit' => 3,
                                    'items' => array_slice(Samples::beritaItems(), 0, 3),
                                ]],
                                ['key' => 'cta', 'variant' => 'modern', 'content' => [
                                    'title' => 'Mari Bergerak Bersama Ibu-Ibu Aisyiyah',
                                    'subtitle' => 'Ikut pengajian, setor sampah terpilah, atau belajar usaha bersama - semua terbuka untuk ibu-ibu warga Ambulu.',
                                    'cta_label' => 'Hubungi Kami',
                                    'cta_type' => 'whatsapp',
                                    'cta_wa_number' => Samples::WHATSAPP,
                                    'cta_wa_message' => 'Assalamu\'alaikum, saya ingin bertanya seputar kegiatan Aisyiyah Ambulu.',
                                ]],
                                $footer,
                            ],
                        ],
                        [
                            'slug' => 'program',
                            'name' => 'Program & Kegiatan',
                            'sections' => [
                                $header,
                                // All six programs, then the two things that prove they run -
                                // a calendar and photographs. At standard tier these three had
                                // to share one crowded scroll with everything else.
                                ['key' => 'program-unggulan', 'variant' => 'modern', 'content' => [
                                    'title' => 'Program Unggulan Aisyiyah Ambulu',
                                    'items' => Samples::programItems(),
                                ]],
                                ['key' => 'agenda', 'variant' => 'standar', 'content' => [
                                    'title' => 'Agenda Kegiatan Ibu-Ibu',
                                    'subtitle' => 'Terbuka untuk seluruh anggota Aisyiyah dan ibu-ibu warga sekitar. Datang saja, tidak perlu mendaftar.',
                                    'limit' => 4,
                                    'items' => Samples::agendaPreviewItems(),
                                ]],
                                ['key' => 'galeri', 'variant' => 'standar', 'content' => [
                                    'title' => 'Galeri Kegiatan',
                                    'limit' => 6,
                                    'items' => Samples::kegiatanPhotos(),
                                ]],
                                $footer,
                            ],
                        ],
                        [
                            'slug' => 'kabar',
                            'name' => 'Kabar',
                            'sections' => [
                                $header,
                                ['key' => 'daftar-berita', 'variant' => 'modern', 'content' => [
                                    'title' => 'Kabar Aisyiyah Ambulu',
                                    'limit' => null,
                                    'items' => Samples::beritaItems(),
                                ]],
                                ['key' => 'cta', 'variant' => 'newsletter', 'content' => [
                                    'title' => 'Ikuti Kabar Aisyiyah Ambulu',
                                    'subtitle' => 'Hubungi kami untuk mendapat informasi kegiatan terbaru dari cabang.',
                                    'cta_label' => 'Hubungi Kami',
                                    'cta_type' => 'whatsapp',
                                    'cta_wa_number' => Samples::WHATSAPP,
                                    'cta_wa_message' => 'Assalamu\'alaikum, saya ingin mendapat informasi kegiatan Aisyiyah Ambulu.',
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
                                    'title' => 'Hubungi Aisyiyah Ambulu',
                                    'subtitle' => 'Ingin ikut kegiatan, menitipkan sampah terpilah, atau mengajak kerja sama program? Hubungi narahubung kami, '.Samples::NARAHUBUNG.'.',
                                    'wa_number' => Samples::WHATSAPP,
                                    'wa_message' => 'Assalamu\'alaikum, saya ingin bertanya seputar kegiatan Aisyiyah Ambulu.',
                                ]],
                                // Structure last, as at standard tier - this cabang is
                                // introduced by its work, and answers "who is behind it" only
                                // once a visitor has reason to ask.
                                ['key' => 'struktur-pengurus', 'variant' => 'modern', 'content' => [
                                    'title' => 'Pimpinan Cabang Aisyiyah Ambulu',
                                    'items' => Samples::pimpinanCabang(),
                                ]],
                                ['key' => 'jaringan-aum-ortom', 'variant' => 'standar', 'content' => [
                                    'title' => 'Amal Usaha Aisyiyah Ambulu',
                                    'items' => Samples::jaringanItems(),
                                ]],
                                ['key' => 'lokasi-peta', 'variant' => 'standar', 'content' => [
                                    'title' => 'Sekretariat PCA Ambulu',
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
