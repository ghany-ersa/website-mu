<?php

namespace Database\Seeders;

use App\Models\OrganizationType;
use App\Models\Template;
use App\Models\User;
use App\Services\Samples\KlinikAisyiyahAmbuluSamples as Samples;
use App\Services\TemplateSandboxService;
use Illuminate\Database\Seeder;

/**
 * Seeds the "Klinik & Rumah Sakit" template - the NON-exclusive counterpart to
 * KlinikAisyiyahAmbuluTemplateSeeder, built from the SAME KlinikAisyiyahAmbuluSamples content so
 * both tiers describe one identical clinic and differ only in what the plan buys.
 *
 * Going down a tier is a harder edit than going up, and this template is where that shows. The
 * exclusive version spends 14 sections across four pages; every plan below Professional allows
 * ONE page (`pages_total`) and at most 8 unlocked sections on Starter (Organization 15), and none
 * of the `modern` variants. So the four pages collapse into one scroll, and the question becomes
 * which of the clinic's material a prospective patient cannot do without:
 *
 *   KEPT - the service catalogue, the doctors' practice hours, the social programmes, kabar/
 *   edukasi, the WhatsApp contact, and the map. These are the things someone actually visits a
 *   clinic's site to find, and answering them is the site's whole job.
 *
 *   DROPPED - the standing pengumuman list and the closing `cta` section. pengumuman is real
 *   content but secondary to the service catalogue it repeats in spirit; `cta` restated an ask
 *   the hero and formulir-kontak already made twice, so cutting it costs nothing a patient needs.
 *
 * Eight unlocked sections, so this fits Starter exactly with nothing dropped by
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
                'name' => 'Rumah Sehat',
                'description' => 'Tampilkan katalog layanan dan jadwal praktik dokter, informasi pelayanan pasien, kabar dan edukasi kesehatan, hingga kontak dan peta lokasi - semua dalam satu halaman profil klinik yang mudah ditemukan pasien.',
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
                        'radius' => 'sharp',
                        'logo' => 'https://storage.ambulu.or.id/organizations/6/brand/bda79c51-7293-4634-8dc7-9be52dcc9090.webp',
                    ],
                    // See KlinikAisyiyahAmbuluTemplateSeeder's matching block for why this
                    // exists and what reads it.
                    'contact' => [
                        'whatsapp' => Samples::WHATSAPP,
                        'address' => Samples::ADDRESS,
                        'instagram_url' => Samples::INSTAGRAM,
                        'tiktok_url' => Samples::TIKTOK,
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
                                // See KlinikAisyiyahAmbuluTemplateSeeder's matching hero image
                                // comment - this is a real photo uploaded through the sandbox
                                // editor (organization id 6), not Samples::HERO_IMAGE.
                                ['key' => 'hero', 'variant' => 'standar', 'content' => [
                                    'badge' => 'Menerima Pasien Umum & BPJS',
                                    'headline' => 'Sehat Bersama, Melayani dengan Ikhlas',
                                    'subheadline' => 'Klinik Pratama Aisyiyah Ambulu melayani UGD, rawat jalan, dan rawat inap 24 jam bagi warga Ambulu dan sekitarnya - lengkap dengan poli gigi, laboratorium, dan ambulans gratis.',
                                    'cta_label' => 'Hubungi Klinik',
                                    'cta_type' => 'whatsapp',
                                    'cta_wa_number' => Samples::WHATSAPP,
                                    'cta_wa_message' => 'Assalamu\'alaikum, saya ingin bertanya seputar layanan Klinik Pratama Aisyiyah Ambulu.',
                                    'cta_secondary_label' => 'Jadwal Praktik',
                                    'cta_secondary_type' => 'scroll',
                                    'cta_secondary_section' => 'jadwal-praktik',
                                    'image' => 'https://storage.ambulu.or.id/organizations/6/builder/8c9b738c-4ec9-4cb6-aa93-69a0e4c5182c.webp',
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
                                // 4. Who runs this clinic and why it is affordable.
                                ['key' => 'tentang-organisasi', 'variant' => 'standar', 'content' => [
                                    'title' => 'Amal Usaha Kesehatan Aisyiyah di Ambulu',
                                    'body' => 'Klinik Pratama Aisyiyah Ambulu adalah amal usaha bidang kesehatan milik Pimpinan Cabang Aisyiyah Ambulu. Kami hadir sebagai layanan kesehatan yang terjangkau dan dekat dengan masyarakat, menerima pasien umum maupun peserta BPJS Kesehatan, dengan unit gawat darurat dan rawat inap yang siaga 24 jam.',
                                    'image' => 'https://storage.ambulu.or.id/organizations/6/builder/d86132f0-f5b3-49d4-93b6-e3ed414be0ca.webp',
                                    'stats' => [
                                        ['value' => '24 Jam', 'label' => 'UGD & Rawat Inap'],
                                        ['value' => '5', 'label' => 'Layanan Poli'],
                                        ['value' => 'Gratis', 'label' => 'Layanan Ambulans'],
                                    ],
                                ]],
                                // 5. The free ambulance and the baksos - what the clinic does
                                // beyond paid service lines.
                                ['key' => 'program-unggulan', 'variant' => 'standar', 'content' => [
                                    'title' => 'Program Sosial Klinik',
                                    'items' => Samples::programItems(),
                                ]],
                                // 6. News and health education.
                                ['key' => 'daftar-berita', 'variant' => 'standar', 'content' => [
                                    'title' => 'Kabar & Edukasi Kesehatan',
                                    'limit' => 3,
                                    'items' => Samples::beritaItems(),
                                ]],
                                // 7. Registration and scheduling questions.
                                ['key' => 'formulir-kontak', 'variant' => 'standar', 'content' => [
                                    'title' => 'Hubungi Klinik',
                                    'subtitle' => 'Untuk pendaftaran, konsultasi jadwal, atau permintaan ambulans, hubungi kami langsung via WhatsApp.',
                                    'wa_number' => Samples::WHATSAPP,
                                    'wa_message' => 'Assalamu\'alaikum, saya ingin bertanya seputar layanan Klinik Pratama Aisyiyah Ambulu.',
                                ]],
                                // 8. How to get there - the last thing needed, and the one a
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
