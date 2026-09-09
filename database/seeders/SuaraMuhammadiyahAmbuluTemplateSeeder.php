<?php

namespace Database\Seeders;

use App\Models\OrganizationType;
use App\Models\Template;
use App\Services\Samples\SuaraMuhammadiyahAmbuluSamples as Samples;
use Illuminate\Database\Seeder;

/**
 * Seeds the "Suara Muhammadiyah Ambulu (Eksklusif)" template - a news/media-portal template
 * for a cabang's own digitalisasi outlet, distinct from every other seeded template (which
 * models an organization's own institutional site). Same overall shape as
 * KlinikAisyiyahAmbuluTemplateSeeder: multi-page, its own seeder rather than an entry in
 * TemplateSeeder::exclusiveTemplates() (deliberately single-page), and pulling all its real
 * content from a single App\Services\Samples\* class shared with CmsSampleDataSeeder (see
 * that class's doc comment for why the content can't live directly in this file).
 *
 * Content is Suara Muhammadiyah Ambulu's real profile: WhatsApp, Instagram, TikTok, and its
 * 16-member editorial team (see Samples::WEBSITE's doc comment - the redaksi's own pre-
 * existing site is background reference only, deliberately not linked anywhere on this seeded
 * site). Three pages (Beranda, Berita, Kontak): Beranda opens with an identity hero
 * introducing the outlet itself (who it is, who runs it - not a news headline), then a short
 * "Kabar Terkini" teaser, a fuller "about" section, and the full editorial team; Berita is the
 * full news listing across TWO daftar-berita sections in different variants/layouts (`modern`
 * featured for institutional coverage, `standar` grid for kaderisasi coverage - see
 * Samples::institutionalItems()/kaderisasiItems() for why the split is by category_filter, not
 * a slice); Kontak surfaces WhatsApp and Instagram for tips or press inquiries.
 *
 * Section total across all 3 pages is 9 unlocked sections, comfortably under the Professional
 * plan's sections_total limit (25) - this template is is_exclusive, so only Professional-plan
 * organizations can pick it (see Organization::canUseExclusiveTemplates()).
 */
class SuaraMuhammadiyahAmbuluTemplateSeeder extends Seeder
{
    public const SLUG = Samples::TEMPLATE_SLUG;

    public function run(): void
    {
        // Str::slug('Media/Portal Berita') strips the '/' rather than treating it as a
        // separator, producing 'mediaportal-berita' (see OrganizationTypeSeeder) - not the
        // 'media-portal-berita' a naive reading of the name would suggest.
        $organizationType = OrganizationType::where('slug', 'mediaportal-berita')->first();

        $header = ['key' => 'header', 'variant' => 'standar'];
        $footer = ['key' => 'footer', 'variant' => 'standar'];

        Template::updateOrCreate(
            ['slug' => self::SLUG],
            [
                'organization_type_id' => $organizationType?->id,
                'name' => 'Suara Muhammadiyah Ambulu (Eksklusif)',
                'description' => 'Template eksklusif portal berita untuk media digitalisasi cabang: beranda yang mengenalkan profil dan tim redaksi lengkap dengan berita terkini, halaman berita lengkap, dan halaman kontak untuk WhatsApp dan media sosial redaksi. Khusus paket dengan entitlement template eksklusif.',
                'is_active' => true,
                'is_exclusive' => true,
                'structure' => [
                    'sample_org_name' => 'Suara Muhammadiyah Ambulu',
                    // Muhammadiyah blue/green, matching the persyarikatan it reports on. Serif
                    // `Lora` + `sharp` radius match the other exclusive templates - an
                    // editorial, masthead-like identity rather than the platform default.
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
                                    'badge' => 'Media PCM Ambulu',
                                    'headline' => 'Suara Muhammadiyah Ambulu',
                                    'subheadline' => 'Kanal media resmi Pimpinan Cabang Muhammadiyah Ambulu - meliput dakwah, pendidikan, kesehatan, dan pemberdayaan umat dari seluruh jaringan Ortom dan Amal Usaha di Ambulu, dikelola oleh tim redaksi 16 orang.',
                                    'cta_label' => 'Baca Berita Terkini',
                                    'cta_type' => 'scroll',
                                    'cta_section' => 'daftar-berita',
                                    'cta_secondary_label' => 'Kenali Tim Redaksi',
                                    'cta_secondary_type' => 'scroll',
                                    'cta_secondary_section' => 'struktur-pengurus',
                                    'image' => Samples::HERO_IMAGE,
                                ]],
                                ['key' => 'daftar-berita', 'variant' => 'ringkas', 'content' => [
                                    'title' => 'Kabar Terkini',
                                    'limit' => 6,
                                    'items' => array_slice(Samples::beritaItems(), 0, 6),
                                ]],
                                ['key' => 'tentang-organisasi', 'variant' => 'modern', 'content' => [
                                    'title' => 'Media Digitalisasi PCM Ambulu',
                                    'body' => 'Suara Muhammadiyah Ambulu adalah media dan unit digitalisasi Pimpinan Cabang Muhammadiyah Ambulu, hadir untuk mendokumentasikan dan menyebarluaskan kabar dakwah, pendidikan, kesehatan, dan pemberdayaan umat dari seluruh jaringan Ortom dan Amal Usaha di Ambulu kepada warga persyarikatan maupun masyarakat umum.',
                                    'image' => Samples::ABOUT_IMAGE,
                                    'stats' => [
                                        ['value' => '5K+', 'label' => 'Pengikut Instagram'],
                                        ['value' => '2K+', 'label' => 'Pengikut TikTok'],
                                        ['value' => '10+', 'label' => 'Kabar Berita'],
                                    ],
                                ]],
                                ['key' => 'struktur-pengurus', 'variant' => 'modern', 'content' => [
                                    'title' => 'Tim Redaksi',
                                    'items' => Samples::timRedaksi(),
                                ]],
                                ['key' => 'cta', 'variant' => 'newsletter', 'content' => [
                                    'title' => 'Jangan Lewatkan Kabar Muhammadiyah Ambulu',
                                    'subtitle' => 'Ikuti Sosial Media kami untuk update setiap hari.',
                                    'cta_label' => 'Ikuti',
                                    'cta_type' => 'url',
                                    'cta_url' => Samples::WEBSITE,
                                ]],
                                $footer,
                            ],
                        ],
                        [
                            'slug' => 'berita',
                            'name' => 'Berita',
                            'sections' => [
                                $header,
                                ['key' => 'daftar-berita', 'variant' => 'modern', 'content' => [
                                    'title' => 'Kabar Persyarikatan',
                                    'category_filter' => 'Organisasi',
                                    'limit' => 5,
                                    'items' => Samples::institutionalItems(),
                                ]],
                                ['key' => 'daftar-berita', 'variant' => 'ringkas', 'content' => [
                                    'title' => 'Kabar Kaderisasi & Ortom',
                                    'category_filter' => null,
                                    'limit' => null,
                                    'items' => Samples::beritaItems(),
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
                                    'title' => 'Hubungi Redaksi',
                                    'subtitle' => 'Punya informasi kegiatan, siaran pers, atau ingin berkolaborasi? Hubungi tim redaksi Suara Muhammadiyah Ambulu.',
                                    'wa_number' => Samples::WHATSAPP,
                                    'wa_message' => 'Assalamu\'alaikum, saya ingin menghubungi redaksi Suara Muhammadiyah Ambulu.',
                                ]],
                                // No dedicated "channel links" section exists in the component
                                // vocabulary, so Instagram is surfaced here as this cta's link;
                                // TikTok isn't (cta only carries one URL) but still renders as a
                                // footer icon once the organization's own instagram_url/
                                // tiktok_url are set (see OrganizationSeeder). The redaksi's own
                                // site (sm-ambulu.vercel.app) isn't linked anywhere on the
                                // seeded site - it's real-world background only, informing the
                                // sample copy's tone, not a URL this template publishes.
                                ['key' => 'cta', 'variant' => 'standar', 'content' => [
                                    'title' => 'Ikuti Kanal Media Sosial Kami',
                                    'subtitle' => 'Dapatkan kabar terbaru Suara Muhammadiyah Ambulu setiap hari di Instagram dan TikTok kami.',
                                    'cta_label' => 'Ikuti di Instagram',
                                    'cta_type' => 'url',
                                    'cta_url' => Samples::INSTAGRAM,
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
