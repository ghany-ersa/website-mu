<?php

namespace Database\Seeders;

use App\Models\OrganizationType;
use App\Models\Template;
use App\Models\User;
use App\Services\Samples\SuaraMuhammadiyahAmbuluSamples as Samples;
use App\Services\TemplateSandboxService;
use Illuminate\Database\Seeder;

/**
 * Seeds the "Portal Berita Organisasi (Standar)" template - the NON-exclusive counterpart to
 * SuaraMuhammadiyahAmbuluTemplateSeeder, built from the SAME SuaraMuhammadiyahAmbuluSamples
 * content so both tiers describe one identical outlet and differ only in what the plan buys.
 *
 * A news portal is the use case that loses the most going down a tier, and it is worth being
 * precise about why. The exclusive version leans on `daftar-berita`'s two exclusive variants -
 * `modern` (a featured lead story with a supporting grid) and `ringkas` (a compact list) - and
 * splits coverage across a dedicated Berita page. At standard tier neither variant exists and
 * there is one page, so this template has exactly ONE news layout available: the even
 * `standar` grid, where every story is the same size.
 *
 * Rather than pretend otherwise, the design leans into it. Two `daftar-berita` sections split
 * the coverage by `category_filter` (the same mechanism the exclusive template uses, and one
 * that is NOT plan-gated) - "Kabar Persyarikatan" above, everything else below. That gives a
 * visitor two distinct reading lanes on a single page without any exclusive variant, which is
 * the closest a flat grid gets to an edited front page.
 *
 * What is genuinely lost: the featured-lead hierarchy. On this tier no story can be made to
 * look more important than another, and that is the honest reason a serious outlet upgrades.
 *
 * Nine unlocked sections, inside Starter's 10.
 */
class SuaraMuhammadiyahAmbuluStandarTemplateSeeder extends Seeder
{
    public const SLUG = 'suara-muhammadiyah-ambulu-standar';

    public function run(): void
    {
        // Str::slug('Media/Portal Berita') strips the '/' rather than treating it as a
        // separator, producing 'mediaportal-berita' - see SuaraMuhammadiyahAmbuluTemplateSeeder.
        $organizationType = OrganizationType::where('slug', 'mediaportal-berita')->first();

        $header = ['key' => 'header', 'variant' => 'standar'];
        $footer = ['key' => 'footer', 'variant' => 'standar'];

        $template = Template::updateOrCreate(
            ['slug' => self::SLUG],
            [
                'organization_type_id' => $organizationType?->id,
                'name' => 'Portal Berita Organisasi (Standar)',
                'description' => 'Template satu halaman untuk media dan portal berita organisasi: profil redaksi, dua kanal berita terpisah (kabar persyarikatan dan kabar kaderisasi), tim redaksi, serta kontak untuk kiriman informasi dan siaran pers. Tersedia untuk semua paket.',
                'is_active' => true,
                'is_exclusive' => false,
                // The exclusive Suara template carries `is_featured` for this organization type.
                'is_featured' => false,
                'structure' => [
                    'sample_org_name' => 'Suara Muhammadiyah Ambulu',
                    // Muhammadiyah blue/green as in the exclusive version, but with the platform
                    // default font/radius rather than that template's editorial serif masthead -
                    // the standard tier should read as the standard tier.
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
                                // 1. Identity. An outlet's hero introduces the outlet, not a
                                // headline - the news itself starts two sections down.
                                ['key' => 'hero', 'variant' => 'standar', 'content' => [
                                    'badge' => 'Media PCM Ambulu',
                                    'headline' => 'Suara Muhammadiyah Ambulu',
                                    'subheadline' => 'Kanal media resmi Pimpinan Cabang Muhammadiyah Ambulu - meliput dakwah, pendidikan, kesehatan, dan pemberdayaan umat dari seluruh jaringan Ortom dan Amal Usaha di Ambulu.',
                                    'cta_label' => 'Baca Berita Terkini',
                                    'cta_type' => 'scroll',
                                    'cta_section' => 'daftar-berita',
                                    'cta_secondary_label' => 'Kenali Tim Redaksi',
                                    'cta_secondary_type' => 'scroll',
                                    'cta_secondary_section' => 'struktur-pengurus',
                                    'image' => Samples::HERO_IMAGE,
                                ]],
                                // 2. Lane one: institutional coverage. `category_filter` is a
                                // registry field on daftar-berita and is NOT plan-gated, so
                                // this split works on every plan - see the class doc comment for
                                // why it matters this much here.
                                ['key' => 'daftar-berita', 'variant' => 'standar', 'content' => [
                                    'title' => 'Kabar Persyarikatan',
                                    'category_filter' => 'Organisasi',
                                    'limit' => 4,
                                    'items' => Samples::institutionalItems(),
                                ]],
                                // 3. Lane two: everything else. No filter, so on a live site
                                // this is the general feed beneath the institutional one.
                                ['key' => 'daftar-berita', 'variant' => 'standar', 'content' => [
                                    'title' => 'Kabar Kaderisasi & Ortom',
                                    'category_filter' => null,
                                    'limit' => 4,
                                    'items' => Samples::kaderisasiItems(),
                                ]],
                                // 4. Who is behind the reporting - the credibility section, and
                                // for a media outlet it is not optional.
                                ['key' => 'tentang-organisasi', 'variant' => 'standar', 'content' => [
                                    'title' => 'Media Digitalisasi PCM Ambulu',
                                    'body' => 'Suara Muhammadiyah Ambulu adalah media dan unit digitalisasi Pimpinan Cabang Muhammadiyah Ambulu, hadir untuk mendokumentasikan dan menyebarluaskan kabar dakwah, pendidikan, kesehatan, dan pemberdayaan umat dari seluruh jaringan Ortom dan Amal Usaha di Ambulu kepada warga persyarikatan maupun masyarakat umum.',
                                    'image' => Samples::ABOUT_IMAGE,
                                    'stats' => [
                                        ['value' => '5K+', 'label' => 'Pengikut Instagram'],
                                        ['value' => '2K+', 'label' => 'Pengikut TikTok'],
                                        ['value' => '16', 'label' => 'Tim Redaksi'],
                                    ],
                                ]],
                                // 5. The masthead - all 16 of them. On Starter the `officers`
                                // quota of 3 trims the live CMS records hard (Organization
                                // allows 7, Professional 20), but the template preview always
                                // shows the full redaksi.
                                ['key' => 'struktur-pengurus', 'variant' => 'standar', 'content' => [
                                    'title' => 'Tim Redaksi',
                                    'items' => Samples::timRedaksi(),
                                ]],
                                // 6. The follow ask. An outlet's real conversion is a social
                                // follow, not a form submission.
                                ['key' => 'cta', 'variant' => 'standar', 'content' => [
                                    'title' => 'Ikuti Kanal Media Sosial Kami',
                                    'subtitle' => 'Dapatkan kabar terbaru Suara Muhammadiyah Ambulu setiap hari di Sosial Media kami.',
                                    'cta_label' => 'Ikuti di Instagram',
                                    'cta_type' => 'url',
                                    'cta_url' => Samples::WEBSITE,
                                ]],
                                // 7. Tips and press inquiries - the inbound a newsroom needs.
                                ['key' => 'formulir-kontak', 'variant' => 'standar', 'content' => [
                                    'title' => 'Hubungi Redaksi',
                                    'subtitle' => 'Punya informasi kegiatan, siaran pers, atau ingin berkolaborasi? Hubungi tim redaksi Suara Muhammadiyah Ambulu.',
                                    'wa_number' => Samples::WHATSAPP,
                                    'wa_message' => 'Assalamu\'alaikum, saya ingin menghubungi redaksi Suara Muhammadiyah Ambulu.',
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
