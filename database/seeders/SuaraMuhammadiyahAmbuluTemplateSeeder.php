<?php

namespace Database\Seeders;

use App\Models\OrganizationType;
use App\Models\Template;
use App\Models\User;
use App\Services\Samples\SuaraMuhammadiyahAmbuluSamples as Samples;
use App\Services\TemplateSandboxService;
use Illuminate\Database\Seeder;

/**
 * Seeds the "Media Progresif" template - a news/media-portal template
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

        $template = Template::updateOrCreate(
            ['slug' => self::SLUG],
            [
                'organization_type_id' => $organizationType?->id,
                'name' => 'Media Berkemajuan',
                'description' => 'Beranda memperkenalkan profil dan tim redaksi lengkap dengan berita terkini, halaman Berita menampung seluruh arsip pemberitaan, dan halaman Kontak menghubungkan pembaca lewat WhatsApp dan media sosial redaksi.',
                'is_active' => true,
                'is_exclusive' => true,
                // The featured template for the Media/Portal Berita type - one per type, now that each
                // has both a standard and an exclusive variant to choose between.
                'is_featured' => true,
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
                        'logo' => 'https://storage.ambulu.or.id/organizations/7/brand/133c8afb-9b33-4234-b362-8dbc6e020618.webp',
                    ],
                    // Read by Organization::phone()/whatsapp()/etc. as the fallback an
                    // organization on this template shows before it fills in its own contact
                    // fields - see those methods on the model. Not just sandbox-editor plumbing.
                    'contact' => [
                        'email' => Samples::EMAIL,
                        'whatsapp' => Samples::WHATSAPP,
                        'address' => Samples::ADDRESS,
                        'instagram_url' => Samples::INSTAGRAM,
                        'facebook_url' => Samples::FACEBOOK,
                        'tiktok_url' => Samples::TIKTOK,
                        'youtube_url' => Samples::YOUTUBE,
                    ],
                    'pages' => [
                        [
                            'slug' => 'home',
                            'name' => 'Beranda',
                            'sections' => [
                                $header,
                                // `cta_section`/`cta_secondary_section` point at daftar-berita and
                                // struktur-pengurus by KEY - "Simpan ke Template" has TWICE now
                                // written raw numeric section ids here instead ('99'/'101' as of
                                // this writing), an artifact of how the sandbox's scroll-anchor
                                // picker serializes a selection. SectionAnchor::href() only
                                // resolves a key, so an id would 404 the scroll on any other
                                // organization - restored to keys here. This is a builder bug,
                                // not a seeder one, and will keep recurring on every re-save of
                                // this hero's CTAs in the sandbox editor until fixed at the
                                // source; re-check this field whenever resyncing this template
                                // from the admin's "Simpan ke Template".
                                ['key' => 'hero', 'variant' => 'modern', 'content' => [
                                    'badge' => 'Media Ambulu',
                                    'headline' => 'Suara Muhammadiyah Ambulu',
                                    'subheadline' => 'Kanal media resmi Muhammadiyah Ambulu - meliput dakwah, pendidikan, kesehatan, dan pemberdayaan umat dari seluruh jaringan Ortom dan Amal Usaha di Ambulu, dikelola oleh tim redaksi 16 orang.',
                                    'cta_label' => 'Baca Berita Terkini',
                                    'cta_type' => 'scroll',
                                    'cta_section' => 'daftar-berita',
                                    'cta_secondary_label' => 'Kenali Tim Redaksi',
                                    'cta_secondary_type' => 'scroll',
                                    'cta_secondary_section' => 'struktur-pengurus',
                                    'image' => 'https://storage.ambulu.or.id/organizations/7/builder/a0bfef2f-3a8f-4e70-b32e-b53d85135dd9.webp',
                                ]],
                                ['key' => 'daftar-berita', 'variant' => 'ringkas', 'content' => [
                                    'title' => 'Kabar Terkini',
                                    'limit' => 6,
                                    'items' => array_slice(Samples::beritaItems(), 0, 6),
                                ]],
                                ['key' => 'tentang-organisasi', 'variant' => 'modern', 'content' => [
                                    'title' => 'Media Muhammadiyah Ambulu',
                                    'body' => 'Suara Muhammadiyah Ambulu adalah media dan unit digitalisasi Muhammadiyah Ambulu, hadir untuk mendokumentasikan dan menyebarluaskan kabar dakwah, pendidikan, kesehatan, dan pemberdayaan umat dari seluruh jaringan Ortom dan Amal Usaha di Ambulu kepada warga persyarikatan maupun masyarakat umum.',
                                    'image' => 'https://storage.ambulu.or.id/organizations/7/builder/804a27a1-cf7f-4c14-a5fa-7d5d964ee4ea.webp',
                                    'stats' => [
                                        ['value' => '5k+', 'label' => 'Pengikut Instagram'],
                                        ['value' => '2K+', 'label' => 'Pengikut TikTok'],
                                        ['value' => '16', 'label' => 'Tim Redaksi'],
                                    ],
                                ]],
                                ['key' => 'struktur-pengurus', 'variant' => 'modern', 'content' => [
                                    'title' => 'Tim Redaksi',
                                    'items' => Samples::timRedaksi(),
                                ]],
                                ['key' => 'cta', 'variant' => 'newsletter', 'content' => [
                                    'title' => 'Jangan Lewatkan Kabar Muhammadiyah Ambulu',
                                    'subtitle' => 'Ikuti Sosial Media kami untuk update setiap hari.',
                                    'cta_label' => 'Ikuti Kami',
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
                                    'limit' => 10,
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
                                    'subtitle' => 'Dapatkan kabar terbaru Suara Muhammadiyah Ambulu setiap hari di Sosial Media kami.',
                                    'cta_label' => 'Ikuti di Instagram',
                                    'cta_type' => 'url',
                                    'cta_url' => Samples::WEBSITE,
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
