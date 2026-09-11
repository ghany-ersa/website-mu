<?php

namespace Database\Seeders;

use App\Models\OrganizationType;
use App\Models\Template;
use App\Models\User;
use App\Services\Samples\SuaraMuhammadiyahAmbuluSamples as Samples;
use App\Services\TemplateSandboxService;
use Illuminate\Database\Seeder;

/**
 * Seeds the "Portal Berita Organisasi" template - the NON-exclusive counterpart to
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
                'name' => 'Berita Organisasi',
                'description' => 'Perkenalkan profil redaksi dan pisahkan kabar persyarikatan dari kabar kaderisasi dalam dua kanal berita, tampilkan tim redaksi, dan buka jalur kontak untuk kiriman informasi serta siaran pers.',
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
                        'radius' => 'full',
                        'logo' => 'https://storage.ambulu.or.id/organizations/8/brand/d94af499-afed-4383-8fb7-5ca2e67a3f77.webp',
                    ],
                    // See SuaraMuhammadiyahAmbuluTemplateSeeder's matching block for why this
                    // exists and what reads it.
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
                                // 1. Identity. An outlet's hero introduces the outlet, not a
                                // headline - the news itself starts two sections down. Scroll
                                // targets are KEYS (daftar-berita/struktur-pengurus), not the raw
                                // numeric section ids "Simpan ke Template" has repeatedly written
                                // here - see SuaraMuhammadiyahAmbuluTemplateSeeder's matching
                                // comment on that builder bug.
                                ['key' => 'hero', 'variant' => 'standar', 'content' => [
                                    'badge' => 'Media Ambulu',
                                    'headline' => 'Suara Muhammadiyah Ambulu',
                                    'subheadline' => 'Kanal media resmi Muhammadiyah Ambulu - meliput dakwah, pendidikan, kesehatan, dan pemberdayaan umat dari seluruh jaringan Ortom dan Amal Usaha di Ambulu.',
                                    'cta_label' => 'Baca Berita Terkini',
                                    'cta_type' => 'scroll',
                                    'cta_section' => 'daftar-berita',
                                    'cta_secondary_label' => 'Kenali Tim Redaksi',
                                    'cta_secondary_type' => 'scroll',
                                    'cta_secondary_section' => 'struktur-pengurus',
                                    'image' => 'https://storage.ambulu.or.id/organizations/8/builder/e8dad3da-cb85-48db-b923-b5312a2b6216.webp',
                                ]],
                                // 2. Who is behind the reporting - the credibility section, and
                                // for a media outlet it is not optional. Moved ahead of the news
                                // lanes by the admin's own drag-reorder in the builder (was
                                // originally after both daftar-berita sections) - trust the
                                // outlet before reading it, rather than the other way round.
                                ['key' => 'tentang-organisasi', 'variant' => 'standar', 'content' => [
                                    'title' => 'Media Digitalisasi PCM Ambulu',
                                    'body' => 'Suara Muhammadiyah Ambulu adalah media dan unit digitalisasi Pimpinan Cabang Muhammadiyah Ambulu, hadir untuk mendokumentasikan dan menyebarluaskan kabar dakwah, pendidikan, kesehatan, dan pemberdayaan umat dari seluruh jaringan Ortom dan Amal Usaha di Ambulu kepada warga persyarikatan maupun masyarakat umum.',
                                    'image' => 'https://storage.ambulu.or.id/organizations/8/builder/47952257-280e-4a34-b9bb-b080e2444e4b.webp',
                                    'stats' => [
                                        ['value' => '5K+', 'label' => 'Pengikut Instagram'],
                                        ['value' => '2K+', 'label' => 'Pengikut TikTok'],
                                        ['value' => '16', 'label' => 'Tim Redaksi'],
                                    ],
                                ]],
                                // 3. Lane one: institutional coverage. `category_filter` is a
                                // registry field on daftar-berita and is NOT plan-gated, so
                                // this split works on every plan - see the class doc comment for
                                // why it matters this much here.
                                ['key' => 'daftar-berita', 'variant' => 'standar', 'content' => [
                                    'title' => 'Kabar Persyarikatan',
                                    'category_filter' => 'Organisasi',
                                    'limit' => 3,
                                    'items' => Samples::institutionalItems(),
                                ]],
                                // 4. The masthead - all 16 of them. On Starter the `officers`
                                // quota of 3 trims the live CMS records hard (Organization
                                // allows 7, Professional 20), but the template preview always
                                // shows the full redaksi. Sits between the two news lanes (the
                                // admin's own reorder) rather than after both.
                                ['key' => 'struktur-pengurus', 'variant' => 'standar', 'content' => [
                                    'title' => 'Tim Redaksi',
                                    'items' => Samples::timRedaksi(),
                                ]],
                                // 5. Lane two: everything else. No filter, so on a live site
                                // this is the general feed the institutional lane doesn't cover.
                                ['key' => 'daftar-berita', 'variant' => 'standar', 'content' => [
                                    'title' => 'Kabar Kaderisasi & Ortom',
                                    'category_filter' => null,
                                    'limit' => 3,
                                    'items' => Samples::kaderisasiItems(),
                                ]],
                                // 6. The follow ask. An outlet's real conversion is a social
                                // follow, not a form submission.
                                ['key' => 'cta', 'variant' => 'standar', 'content' => [
                                    'title' => 'Ikuti Kanal Media Sosial Kami',
                                    'subtitle' => 'Dapatkan kabar terbaru Suara Muhammadiyah Ambulu setiap hari di Sosial Media kami.',
                                    'cta_label' => 'Ikuti Kami',
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
