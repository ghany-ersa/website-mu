<?php

namespace Database\Seeders;

use App\Models\OrganizationType;
use App\Models\Template;
use App\Models\User;
use App\Services\Samples\KlinikAisyiyahAmbuluSamples as Samples;
use App\Services\TemplateSandboxService;
use Illuminate\Database\Seeder;

/**
 * Seeds the "Profil Klinik & Rumah Sakit (Standar)" template - the NON-exclusive counterpart to
 * KlinikAisyiyahAmbuluTemplateSeeder, built from the SAME KlinikAisyiyahAmbuluSamples content so
 * both tiers describe one identical clinic and differ only in what the plan buys.
 *
 * Going down a tier is a harder edit than going up, and this template is where that shows. The
 * exclusive version spends 14 sections across four pages; every plan below Professional allows
 * ONE page (`pages_total`) and at most 10 unlocked sections (Starter; Organization 15), and none
 * of the `modern` variants. So the four pages collapse into one scroll, and the question becomes
 * which of the clinic's material a prospective patient cannot do without:
 *
 *   KEPT - the service catalogue, the doctors' practice hours, the operational notices
 *   (24-hour UGD, BPJS requirements), the WhatsApp contact, and the map. These are the things
 *   someone actually visits a clinic's site to find, and answering them is the site's whole job.
 *
 *   DROPPED - the room/facility gallery and the social-programme section. Both are genuinely
 *   good material, but they persuade rather than inform, and a patient checking whether the UGD
 *   is open tonight is not browsing photographs. They are the first things an upgrade buys back.
 *
 * Ten unlocked sections, so this fits Starter exactly with nothing dropped by
 * Organization::seedPagesFromTemplate() - which silently truncates from the end, hence the
 * ordering: what a patient needs first is first.
 */
class KlinikAisyiyahAmbuluStandarTemplateSeeder extends Seeder
{
    public const SLUG = 'klinik-aisyiyah-ambulu-standar';

    public function run(): void
    {
        // Str::slug('Klinik/Rumah Sakit') strips the '/' - see KlinikAisyiyahAmbuluTemplateSeeder.
        $organizationType = OrganizationType::where('slug', 'klinikrumah-sakit')->first();

        $header = ['key' => 'header', 'variant' => 'standar'];
        $footer = ['key' => 'footer', 'variant' => 'standar'];

        $template = Template::updateOrCreate(
            ['slug' => self::SLUG],
            [
                'organization_type_id' => $organizationType?->id,
                'name' => 'Profil Klinik & Rumah Sakit (Standar)',
                'description' => 'Template satu halaman untuk klinik, rumah sakit, dan AUM Kesehatan: profil layanan, jadwal praktik dokter, informasi pelayanan, kabar dan edukasi kesehatan, serta kontak dan peta lokasi. Tersedia untuk semua paket.',
                'is_active' => true,
                'is_exclusive' => false,
                // The exclusive Klinik template carries `is_featured` for this organization type.
                'is_featured' => false,
                'structure' => [
                    'sample_org_name' => 'Klinik Pratama Aisyiyah Ambulu',
                    // Aisyiyah green led, as in the exclusive version, but with the platform
                    // default font/radius - the standard tier should look like the standard
                    // tier, so the exclusive template's serif/sharp identity stays distinct.
                    'brand' => [
                        'primary' => '#079C4E',
                        'secondary' => '#2C368B',
                        'font' => 'Plus Jakarta Sans',
                        'radius' => 'rounded',
                    ],
                    'pages' => [
                        [
                            'slug' => 'home',
                            'name' => 'Beranda',
                            'sections' => [
                                $header,
                                // 1. The two things a patient needs immediately: that the UGD is
                                // open, and a way to call. Unlike the cabang profiles, whose
                                // hero CTAs scroll, this one dials straight into WhatsApp - a
                                // clinic visitor may be in a hurry.
                                ['key' => 'hero', 'variant' => 'standar', 'content' => [
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
                                // 2. What the clinic treats. High, because it is the most
                                // common reason for the visit.
                                ['key' => 'layanan', 'variant' => 'standar', 'content' => [
                                    'title' => 'Layanan Klinik',
                                    'items' => Samples::layananItems(),
                                ]],
                                // 3. When to come, and for whom.
                                ['key' => 'jadwal-praktik', 'variant' => 'standar', 'content' => [
                                    'title' => 'Jadwal Praktik Dokter',
                                    'doctors' => Samples::jadwalDokter(),
                                ]],
                                // 4. The standing operational notices - what to bring for BPJS,
                                // that hours can change. Practical, not promotional.
                                ['key' => 'pengumuman', 'variant' => 'standar', 'content' => [
                                    'title' => 'Informasi Pelayanan',
                                    'items' => Samples::pengumumanItems(),
                                ]],
                                // 5. Who runs this clinic and why it is affordable.
                                ['key' => 'tentang-organisasi', 'variant' => 'standar', 'content' => [
                                    'title' => 'Amal Usaha Kesehatan Aisyiyah di Ambulu',
                                    'body' => 'Klinik Pratama Aisyiyah Ambulu adalah amal usaha bidang kesehatan milik Pimpinan Cabang Aisyiyah Ambulu. Kami hadir sebagai layanan kesehatan yang terjangkau dan dekat dengan masyarakat, menerima pasien umum maupun peserta BPJS Kesehatan, dengan unit gawat darurat dan rawat inap yang siaga 24 jam.',
                                    'image' => Samples::ABOUT_IMAGE,
                                    'stats' => [
                                        ['value' => '24 Jam', 'label' => 'UGD & Rawat Inap'],
                                        ['value' => '5', 'label' => 'Layanan Poli'],
                                        ['value' => 'Gratis', 'label' => 'Layanan Ambulans'],
                                    ],
                                ]],
                                // 6. The free ambulance and the baksos - what the clinic does
                                // beyond paid service lines.
                                ['key' => 'program-unggulan', 'variant' => 'standar', 'content' => [
                                    'title' => 'Program Sosial Klinik',
                                    'items' => Samples::programItems(),
                                ]],
                                // 7. News and health education.
                                ['key' => 'daftar-berita', 'variant' => 'standar', 'content' => [
                                    'title' => 'Kabar & Edukasi Kesehatan',
                                    'limit' => 3,
                                    'items' => Samples::beritaItems(),
                                ]],
                                // 8. The urgent ask, restated now that the visitor has read
                                // enough to know what the clinic offers.
                                ['key' => 'cta', 'variant' => 'standar', 'content' => [
                                    'title' => 'Butuh Penanganan Segera?',
                                    'subtitle' => 'UGD kami siaga 24 jam dan ambulans tersedia gratis bagi warga yang membutuhkan.',
                                    'cta_label' => 'Hubungi Klinik Sekarang',
                                    'cta_type' => 'whatsapp',
                                    'cta_wa_number' => Samples::WHATSAPP,
                                    'cta_wa_message' => 'Assalamu\'alaikum, saya membutuhkan bantuan layanan Klinik Pratama Aisyiyah Ambulu.',
                                ]],
                                // 9. Registration and scheduling questions.
                                ['key' => 'formulir-kontak', 'variant' => 'standar', 'content' => [
                                    'title' => 'Hubungi Klinik',
                                    'subtitle' => 'Untuk pendaftaran, konsultasi jadwal, atau permintaan ambulans, hubungi kami langsung via WhatsApp.',
                                    'wa_number' => Samples::WHATSAPP,
                                    'wa_message' => 'Assalamu\'alaikum, saya ingin bertanya seputar layanan Klinik Pratama Aisyiyah Ambulu.',
                                ]],
                                // 10. How to get there - the last thing needed, and the one a
                                // patient opens on the way.
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
