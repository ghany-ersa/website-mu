<?php

namespace Database\Seeders;

use App\Models\OrganizationType;
use App\Models\Template;
use App\Models\User;
use App\Services\TemplateSandboxService;
use Illuminate\Database\Seeder;

/**
 * Seeds the "Sekolah & Madrasah" template - the NON-exclusive counterpart to
 * SdMadyaTemplateSeeder, so Sekolah/Madrasah has a template a Starter or Premium plan can
 * actually use. Without it that type had exactly one template and it was is_exclusive, which
 * meant a school signing up on any lower plan could pick a design it was then blocked from
 * publishing (Organization::planViolations() gates `is_exclusive` at publish time, not at
 * template selection) - the same gap MasjidNurulHudaStandarTemplateSeeder was written to close
 * for Masjid/Mushola.
 *
 * What the standard tier loses, and why:
 *
 *   - Multi-page (6 pages -> 1). pages_total is 1 on starter/premium and 10 on eksklusif, so
 *     this tier is single-page by plan, not by choice. The exclusive tier's dedicated Prestasi,
 *     Akademik, Pengajar, Galeri and Kontak pages collapse into sections on one scrolling page.
 *   - The `sekolahmu` variants (editorial hero, bento prestasi teaser, bento kegiatan, the
 *     sekolahmu roster, the galeri with a video-profile block, the PPDB form with grade
 *     picker and opening hours) are all is_exclusive in SectionVariantSeeder, so every section
 *     here uses `standar`. Notably prestasi/standar is the RICHER of that section's two
 *     variants - it carries the category filter and the search box, where the exclusive
 *     `sekolahmu` one is a fixed three-tile teaser - so the wall of fame is not degraded here,
 *     only relocated onto the single page.
 *   - Pupil profile pages are dropped entirely: `students` is capped at 0 on every plan below
 *     eksklusif (PlanSeeder), so achievements on this tier list no pupil links. That is the one
 *     thing this tier genuinely cannot offer, and the honest reason for a school to upgrade.
 *
 * Eight unlocked sections, matching Starter's `sections_total` exactly (see PlanSeeder), so a
 * Starter school gets the complete template untruncated - Organization::seedPagesFromTemplate()
 * silently drops sections past the limit, so a ninth would simply never appear for the plan this
 * template exists to serve.
 *
 * On copy: every claim here is one a school can verify against its own records - rombel count,
 * accreditation letter, year founded, ekskul list - rather than unverifiable superlatives. The
 * numbers are SD Muhammadiyah 1 Ambulu's own, the same ones the exclusive template uses, so the
 * two tiers describe one school consistently rather than inventing a second fictional one.
 */
class SdMadyaStandarTemplateSeeder extends Seeder
{
    public const SLUG = 'sd-madya-standar';

    /** See SdMadyaTemplateSeeder::ORGANIZATION_TYPE_SLUG - 'Sekolah/Madrasah' slugs with the '/' dropped. */
    private const ORGANIZATION_TYPE_SLUG = 'sekolahmadrasah';

    /** PPDB registration form the school already uses; the hero/CTA link straight to it. */
    public const PPDB_URL = 'https://bit.ly/PPDBSDMADYA';

    /**
     * Pre-resolved from the school's own https://maps.app.goo.gl/JE1radnw6LNuKMacA share link
     * via GoogleMapsEmbedResolver, so the seeded value is already the keyless /maps/embed form
     * the lokasi-peta iframe needs - resolving a short link costs a redirect-following HTTP
     * request, which a seeder should not depend on at run time.
     */
    public const MAP_EMBED = 'https://www.google.com/maps/embed?origin=mfe&pb=!1m2!2m1!1s-8.3530323%2C113.6036492';

    /**
     * The school's actual photography - same two frames SdMadyaTemplateSeeder uses (its
     * HERO_IMAGE and ABOUT_IMAGE, uploaded through that template's sandbox, organization id 11),
     * so both tiers preview the same school rather than two unrelated ones. Every section guards
     * on an empty image, so a dead URL degrades to the empty state rather than a broken layout -
     * see that seeder's doc comment on these being per-ORGANIZATION storage that 404s if the
     * sandbox is ever cleared. Replaces the earlier Unsplash stand-ins.
     */
    private const HERO_IMAGE = 'https://storage.ambulu.or.id/organizations/11/builder/169f07f8-f874-44ef-8e86-37d141145c5e.webp';

    private const ABOUT_IMAGE = 'https://storage.ambulu.or.id/organizations/11/builder/178c48cf-b1f2-44e4-b505-11b37b3c1a18.webp';

    public function run(): void
    {
        $organizationType = OrganizationType::where('slug', self::ORGANIZATION_TYPE_SLUG)->first();

        $header = ['key' => 'header', 'variant' => 'standar'];
        $footer = ['key' => 'footer', 'variant' => 'standar'];

        $template = Template::updateOrCreate(
            ['slug' => self::SLUG],
            [
                'organization_type_id' => $organizationType?->id,
                'name' => 'Sekolah Kita',
                'description' => 'Satu halaman profil sekolah: identitas dan akreditasi, prestasi siswa dengan filter kategori, kegiatan dan ekstrakurikuler, profil guru, galeri, serta pendaftaran lewat WhatsApp dan peta lokasi.',
                'thumbnail_path' => 'templates/thumbnails/sekolah-standar.png',
                'is_active' => true,
                'is_exclusive' => false,
                // The exclusive SD Madya template is the featured one for this type.
                'is_featured' => false,
                'structure' => [
                    'sample_org_name' => 'SD Muhammadiyah 1 Ambulu',
                    // Same navy/gold identity as the exclusive tier, but with the platform
                    // default font rather than that template's Inter/Newsreader pairing - the
                    // `sekolahmu` font pair is what gives the exclusive design its editorial
                    // feel, and the standard tier should read as the standard tier.
                    'brand' => [
                        'primary' => '#002447',
                        'secondary' => '#E9C349',
                        'font' => 'Plus Jakarta Sans',
                        'radius' => 'soft',
                        // Uploaded through this template's own sandbox editor (organization id
                        // 12), same as the other templates' logos. Per-ORGANIZATION storage, so
                        // it 404s if that sandbox is ever cleared; header/standar.blade.php falls
                        // back to a text wordmark when this is blank.
                        'logo' => 'https://storage.ambulu.or.id/organizations/12/brand/e8756aef-3c87-40a3-99e4-289322ce48a8.webp',
                    ],
                    /*
                     * SD Muhammadiyah 1 Ambulu's real contact details and social accounts.
                     * Narahubung: Beni Hendarto, S.Pd.I (Kepala Sekolah).
                     *
                     * Stored on the TEMPLATE, which means these are shown in template-preview
                     * context and inherited by this template's sandbox only - a real organization
                     * created from this template never picks them up (see Organization::phone()'s
                     * doc comment on why template contact info must not leak onto an unrelated
                     * organization's live site).
                     *
                     * whatsapp is bare digits with the country code, no '+' or separators, which
                     * is the shape wa.me links and formulir-kontak's wa_number both expect.
                     */
                    'contact' => [
                        'phone' => '082332583556',
                        'whatsapp' => '6282332583556',
                        'address' => 'Ambulu, Kabupaten Jember, Jawa Timur',
                        'instagram_url' => 'https://instagram.com/sd.madya.ambulu',
                        'facebook_url' => 'https://www.facebook.com/madya.ambulu.5',
                        'tiktok_url' => 'https://tiktok.com/@sd.madya.ambulu',
                        'youtube_url' => 'https://www.youtube.com/@sdmadyaambulu7957',
                    ],
                    'pages' => [
                        [
                            'slug' => 'home',
                            'name' => 'Beranda',
                            'sections' => [
                                $header,
                                // 1. Who this is. Both CTAs scroll to sections that exist on
                                // this page - there is no second page to link to on this tier.
                                ['key' => 'hero', 'variant' => 'standar', 'content' => [
                                    'badge' => 'Menerima peserta didik baru',
                                    'headline' => 'SD Muhammadiyah 1 Ambulu',
                                    'subheadline' => 'Sekolah dasar Muhammadiyah di Ambulu, Jember. Terakreditasi A, dengan 12 rombongan belajar dan 9 ekstrakurikuler.',
                                    'cta_label' => 'Daftar PPDB',
                                    'cta_type' => 'url',
                                    'cta_url' => self::PPDB_URL,
                                    'cta_secondary_label' => 'Lihat Prestasi',
                                    'cta_secondary_type' => 'scroll',
                                    'cta_secondary_section' => 'prestasi',
                                    'image' => self::HERO_IMAGE,
                                ]],
                                // 2. Profile. Same stats as the exclusive tier's prestasi strip,
                                // all checkable against the school's own records.
                                ['key' => 'tentang-organisasi', 'variant' => 'standar', 'content' => [
                                    'title' => 'Tentang Sekolah',
                                    'body' => 'SD Muhammadiyah 1 Ambulu menyelenggarakan pendidikan dasar dengan kurikulum nasional yang dipadukan pembiasaan ibadah harian. Sekolah berada di bawah Majelis Pendidikan Dasar dan Menengah Pimpinan Cabang Muhammadiyah Ambulu.',
                                    'image' => self::ABOUT_IMAGE,
                                    'stats' => [
                                        ['value' => '12', 'label' => 'Rombongan Belajar'],
                                        ['value' => 'A', 'label' => 'Akreditasi'],
                                        ['value' => '1978', 'label' => 'Berdiri Sejak'],
                                    ],
                                ]],
                                // 3. Prestasi. The `standar` variant is the filterable wall of
                                // fame - richer than the exclusive tier's three-tile teaser -
                                // so this is the one section the lower tier does not lose.
                                // No `limit`: the filter and search only make sense over the
                                // full list.
                                ['key' => 'prestasi', 'variant' => 'standar', 'content' => [
                                    'label' => 'Prestasi',
                                    'title' => 'Prestasi Siswa',
                                    'subtitle' => 'Daftar prestasi siswa, dari tingkat kecamatan hingga nasional. Gunakan filter kategori untuk melihat bidang tertentu.',
                                ]],
                                // 4. Kegiatan - reads `kegiatan`-type Program rows, the same
                                // CMS resource the exclusive tier's Akademik page uses.
                                ['key' => 'kegiatan', 'variant' => 'standar', 'content' => [
                                    'title' => 'Kegiatan & Ekstrakurikuler',
                                    'subtitle' => 'Pembiasaan harian dan kegiatan pengembangan diri yang diikuti siswa.',
                                ]],
                                // 5. Who teaches. struktur-pengurus/standar renders plain
                                // name/role cards; the exclusive tier's `sekolahmu` variant
                                // adds the subject and photo treatment.
                                ['key' => 'struktur-pengurus', 'variant' => 'standar', 'content' => [
                                    'title' => 'Tim Pengajar',
                                    'subtitle' => 'Guru kelas dan guru mata pelajaran yang mendampingi siswa setiap hari.',
                                ]],
                                // 6. What it looks like.
                                ['key' => 'galeri', 'variant' => 'standar', 'content' => [
                                    'title' => 'Galeri Kegiatan',
                                    'subtitle' => 'Dokumentasi kegiatan belajar, ekstrakurikuler, dan acara sekolah.',
                                    'limit' => 8,
                                ]],
                                // 7. How to enrol. The exclusive tier has a PPDB form with a
                                // grade picker and opening hours (formulir-kontak/sekolahmu);
                                // this tier uses the plain WhatsApp form, so the grade goes in
                                // the prefilled message instead.
                                ['key' => 'formulir-kontak', 'variant' => 'standar', 'content' => [
                                    'title' => 'Pendaftaran & Informasi',
                                    'subtitle' => 'Untuk pendaftaran peserta didik baru atau pertanyaan lain, hubungi kami lewat WhatsApp pada jam sekolah.',
                                    'wa_message' => 'Assalamu\'alaikum, saya ingin bertanya tentang pendaftaran peserta didik baru di {org_name}.',
                                ]],
                                // 8. Where to find it.
                                ['key' => 'lokasi-peta', 'variant' => 'standar', 'content' => [
                                    'title' => 'Lokasi Sekolah',
                                    'map_embed' => self::MAP_EMBED,
                                ]],
                                $footer,
                            ],
                        ],
                    ],
                ],
            ],
        );

        // Same as the other template seeders: give the template its sandbox up front so an admin
        // can open it in the builder without the designer having to create one on first visit.
        if ($admin = User::where('is_admin', true)->first()) {
            app(TemplateSandboxService::class)->sandboxFor($template, $admin);
        }
    }
}
