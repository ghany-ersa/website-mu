<?php

namespace Database\Seeders;

use App\Models\OrganizationType;
use App\Models\Template;
use App\Models\User;
use App\Services\Samples\KlinikAisyiyahAmbuluSamples as Samples;
use App\Services\TemplateSandboxService;
use Illuminate\Database\Seeder;

/**
 * Seeds the "Klinik Pratama Aisyiyah Ambulu (Eksklusif)" template - currently the ONLY
 * seeded template in the app while template/org sample data is rebuilt from scratch,
 * starting with AUM Kesehatan (see DatabaseSeeder, TemplateSeeder::run() is temporarily
 * empty). Modeled on MasjidNurulHudaTemplateSeeder's pattern - its own seeder rather than an
 * entry in TemplateSeeder::exclusiveTemplates() because it's MULTI-page, unlike every
 * template that helper produces (deliberately single-page). structure['pages'][0] is the
 * home page, matching Organization::seedPagesFromTemplate()'s is_home => $pageIndex === 0
 * assumption.
 *
 * Content is Klinik Pratama Aisyiyah Ambulu's real profile: address, WhatsApp, Instagram,
 * service lines, and doctor roster - all pulled from App\Services\Samples\
 * KlinikAisyiyahAmbuluSamples, the single source of truth for the clinic's content (also
 * used by CmsSampleDataSeeder to seed the organization's own CMS records - see that class's
 * doc comment for why the content lives there and not here). Four pages (Beranda, Layanan,
 * Jadwal Dokter, Kontak), using the `modern` exclusive section variants, a full service
 * catalogue, a clinic profile, a room/facility gallery, and a dedicated contact page.
 *
 * Section total across all 4 pages is 14 unlocked sections, under the Professional plan's
 * sections_total limit (25) - this template is is_exclusive, so only Professional-plan
 * organizations can pick it (see Organization::canUseExclusiveTemplates()).
 *
 * Deliberately does NOT use the five premium *mosque* sections (fasilitas-masjid,
 * donasi-progress, laporan-keuangan, kalkulator-zakat, sewa-aula): those are gated to mosque
 * use cases and none of them models a clinic's needs. The exclusivity here comes from the
 * multi-page structure and the exclusive section *variants* instead.
 */
class KlinikAisyiyahAmbuluTemplateSeeder extends Seeder
{
    public const SLUG = Samples::TEMPLATE_SLUG;

    public function run(): void
    {
        // Str::slug('Klinik/Rumah Sakit') strips the '/' rather than treating it as a separator,
        // producing 'klinikrumah-sakit' (see OrganizationTypeSeeder, and the same quirk noted in
        // SuaraMuhammadiyahAmbuluTemplateSeeder) - not the 'klinik-rumah-sakit' a naive reading
        // of the name would suggest. This type was named 'AUM Kesehatan' (slug 'aum-kesehatan')
        // until the type list was reworded to name the institution rather than its category.
        $organizationType = OrganizationType::where('slug', 'klinikrumah-sakit')->first();

        $header = ['key' => 'header', 'variant' => 'standar'];
        $footer = ['key' => 'footer', 'variant' => 'standar'];

        $template = Template::updateOrCreate(
            ['slug' => self::SLUG],
            [
                'organization_type_id' => $organizationType?->id,
                'name' => 'Klinik Pratama Aisyiyah (Eksklusif)',
                'description' => 'Template eksklusif multi-halaman untuk klinik dan AUM Kesehatan: beranda dengan layanan unggulan dan profil klinik, halaman katalog layanan lengkap dengan galeri fasilitas dan ruangan, jadwal praktik dokter tersendiri, serta halaman kontak dengan peta lokasi. Khusus paket dengan entitlement template eksklusif.',
                'is_active' => true,
                'is_exclusive' => true,
                'is_featured' => true,
                'structure' => [
                    'sample_org_name' => 'Klinik Pratama Aisyiyah Ambulu',
                    // Aisyiyah green led, Muhammadiyah blue as the secondary - the reverse of the
                    // platform default pairing, so the exclusive tier reads as its own identity.
                    // Serif `Lora` + `sharp` radius match the other exclusive templates.
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
                                    'badge' => 'Menerima Pasien Umum & BPJS',
                                    'headline' => 'Sehat Bersama, Melayani dengan Ikhlas',
                                    'subheadline' => 'Klinik Pratama Aisyiyah Ambulu melayani UGD, rawat jalan, dan rawat inap 24 jam bagi warga Ambulu dan sekitarnya - lengkap dengan poli gigi, laboratorium, dan ambulans gratis.',
                                    'cta_label' => 'Hubungi Klinik',
                                    'cta_type' => 'whatsapp',
                                    'cta_wa_number' => Samples::WHATSAPP,
                                    'cta_wa_message' => 'Assalamu\'alaikum, saya ingin bertanya seputar layanan Klinik Pratama Aisyiyah Ambulu.',
                                    'cta_secondary_label' => 'Jadwal Praktik Dokter',
                                    'cta_secondary_type' => 'scroll',
                                    'cta_secondary_section' => 'jadwal-praktik',
                                    'image' => Samples::HERO_IMAGE,
                                ]],
                                ['key' => 'tentang-organisasi', 'variant' => 'modern', 'content' => [
                                    'title' => 'Amal Usaha Kesehatan Aisyiyah di Ambulu',
                                    'body' => 'Klinik Pratama Aisyiyah Ambulu adalah amal usaha bidang kesehatan milik Pimpinan Cabang Aisyiyah Ambulu. Kami hadir sebagai layanan kesehatan yang terjangkau dan dekat dengan masyarakat, menerima pasien umum maupun peserta BPJS Kesehatan, dengan unit gawat darurat dan rawat inap yang siaga 24 jam.',
                                    'image' => Samples::ABOUT_IMAGE,
                                    'stats' => [
                                        ['value' => '24 Jam', 'label' => 'UGD & Rawat Inap'],
                                        ['value' => '5', 'label' => 'Layanan Poli'],
                                        ['value' => 'Gratis', 'label' => 'Layanan Ambulans'],
                                    ],
                                ]],
                                ['key' => 'layanan', 'variant' => 'standar', 'content' => [
                                    'title' => 'Layanan Unggulan',
                                    'items' => Samples::layananItems(),
                                ]],
                                ['key' => 'jadwal-praktik', 'variant' => 'standar', 'content' => [
                                    'title' => 'Jadwal Praktik Dokter',
                                    'doctors' => Samples::jadwalDokter(),
                                ]],
                                ['key' => 'daftar-berita', 'variant' => 'modern', 'content' => [
                                    'title' => 'Kabar & Edukasi Kesehatan',
                                    'items' => [...Samples::beritaItems(), Samples::extraBeritaItem()],
                                ]],
                                ['key' => 'cta', 'variant' => 'modern', 'content' => [
                                    'title' => 'Butuh Penanganan Segera?',
                                    'subtitle' => 'UGD kami siaga 24 jam dan ambulans tersedia gratis bagi warga yang membutuhkan.',
                                    'cta_label' => 'Hubungi Klinik Sekarang',
                                    'cta_type' => 'whatsapp',
                                    'cta_wa_number' => Samples::WHATSAPP,
                                    'cta_wa_message' => 'Assalamu\'alaikum, saya membutuhkan bantuan layanan Klinik Pratama Aisyiyah Ambulu.',
                                ]],
                                $footer,
                            ],
                        ],
                        [
                            'slug' => 'layanan',
                            'name' => 'Layanan',
                            'sections' => [
                                $header,
                                ['key' => 'layanan', 'variant' => 'standar', 'content' => [
                                    'title' => 'Katalog Layanan Klinik',
                                    'items' => Samples::layananItems(),
                                ]],
                                ['key' => 'program-unggulan', 'variant' => 'modern', 'content' => [
                                    'title' => 'Program Sosial Klinik',
                                    'items' => Samples::programItems(),
                                ]],
                                // Room/facility photos, previously on the removed 'kegiatan' page's
                                // galeri section (which was about activities, not the clinic
                                // itself) - moved here since a prospective patient browsing
                                // 'Layanan' is exactly who wants to see what the clinic's rooms
                                // look like before visiting.
                                ['key' => 'galeri', 'variant' => 'standar', 'content' => [
                                    'title' => 'Fasilitas & Ruangan Klinik',
                                    'limit' => 8,
                                    'items' => Samples::ruanganPhotos(),
                                ]],
                                ['key' => 'cta', 'variant' => 'standar', 'content' => [
                                    'title' => 'Ada Pertanyaan Seputar Layanan?',
                                    'subtitle' => 'Tim kami siap membantu menjelaskan prosedur, biaya, dan alur pendaftaran BPJS.',
                                    'cta_label' => 'Tanya via WhatsApp',
                                    'cta_type' => 'whatsapp',
                                    'cta_wa_number' => Samples::WHATSAPP,
                                    'cta_wa_message' => 'Assalamu\'alaikum, saya ingin bertanya seputar layanan Klinik Pratama Aisyiyah Ambulu.',
                                ]],
                                $footer,
                            ],
                        ],
                        [
                            'slug' => 'jadwal-dokter',
                            'name' => 'Jadwal Dokter',
                            'sections' => [
                                $header,
                                ['key' => 'jadwal-praktik', 'variant' => 'standar', 'content' => [
                                    'title' => 'Jadwal Praktik Dokter',
                                    'doctors' => Samples::jadwalDokter(),
                                ]],
                                ['key' => 'pengumuman', 'variant' => 'standar', 'content' => [
                                    'title' => 'Informasi Pelayanan',
                                    'items' => Samples::pengumumanItems(),
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
                                    'title' => 'Hubungi Klinik',
                                    'subtitle' => 'Untuk pendaftaran, konsultasi jadwal, atau permintaan ambulans, hubungi kami langsung via WhatsApp.',
                                    'wa_number' => Samples::WHATSAPP,
                                    'wa_message' => 'Assalamu\'alaikum, saya ingin bertanya seputar layanan Klinik Pratama Aisyiyah Ambulu.',
                                ]],
                                ['key' => 'lokasi-peta', 'variant' => 'standar', 'content' => [
                                    'title' => 'Lokasi Klinik',
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
