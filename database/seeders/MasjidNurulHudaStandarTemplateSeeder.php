<?php

namespace Database\Seeders;

use App\Models\OrganizationType;
use App\Models\Template;
use App\Models\User;
use App\Services\TemplateSandboxService;
use Illuminate\Database\Seeder;

/**
 * Seeds the "Masjid & Mushola" template - the NON-exclusive counterpart to
 * MasjidNurulHudaTemplateSeeder, so Masjid/Mushola has a template a Starter/Organization plan
 * can actually auto-pick (see StoreOrganizationRequest::prepareForValidation() - without this,
 * creating an organization of that type left template_id null, since the only masjid template
 * that existed was is_exclusive).
 *
 * Reuses Masjid Nurul Huda's real profile copy (facilities count, kajian cadence, mission
 * statement) rather than inventing a second fictional mosque, but the content itself lives
 * inline here rather than in a shared Samples class: MasjidNurulHudaTemplateSeeder never
 * factored its copy out into one either (see that seeder's doc comment - it's "lifted from
 * [the nurul-huda project's] own Blade views" directly), and CmsSampleDataSeeder's Nurul Huda
 * sample methods (nurulHudaKajianSamples(), nurulHudaOfficerSamples(), ...) are private to that
 * class and keyed to the EXCLUSIVE template's slug specifically - reusing them here would seed
 * this template's organizations with a real committee/kajian roster while the page itself can't
 * render the sections (donasi-progress, laporan-keuangan) that roster exists to support. This
 * template's CMS-backed sections instead fall through to CmsSampleDataSeeder's generic
 * placeholders, same as every non-showcase template.
 *
 * What the standard tier loses, and why: the five premium mosque sections
 * (fasilitas-masjid, donasi-progress, laporan-keuangan, kalkulator-zakat, akad-venue) are gated
 * BOTH by section (`exclusive` in config/page-builder.php) and by their sole variant
 * (`nurul-huda`, `is_exclusive` in SectionVariantSeeder) - unlike the cabang/klinik/portal
 * templates, where only the *variant* was exclusive and a `standar` fallback existed, a masjid
 * on this tier cannot add these sections in ANY form, not even in a lesser layout. So this
 * template doesn't approximate them - it substitutes the closest non-exclusive equivalent:
 *
 *   - donasi-progress (per-program progress bars) -> donasi-zakat-infak (one WhatsApp-driven
 *     donation CTA, no per-program tracking)
 *   - kalkulator-zakat -> folded into that same donasi-zakat-infak CTA's copy
 *   - fasilitas-masjid -> galeri (photos instead of a structured facility list)
 *   - laporan-keuangan, akad-venue -> dropped entirely; no non-exclusive equivalent exists
 *     for either (transparent bookkeeping and venue rental are the two things this tier
 *     genuinely cannot offer - the honest reason to upgrade)
 *   - agenda's `poster` variant (flyer grid) -> agenda/standar (a plain list)
 *
 * Eight unlocked sections, matching Starter's `sections_total` exactly. pengumuman and
 * daftar-berita - both real, both genuinely useful - were the two cut to fit: neither is load-
 * bearing for the substitution argument above, unlike donasi-zakat-infak and galeri, which stand
 * in for section the exclusive tier structurally cannot go without.
 */
class MasjidNurulHudaStandarTemplateSeeder extends Seeder
{
    public const SLUG = 'masjid-nurul-huda-standar';

    private const S3 = 'https://s3.nurul-huda.ambulu.or.id';

    private const HERO_IMAGE = self::S3.'/venue-page/NH.jpg';

    private const ABOUT_IMAGE = self::S3.'/facilities/01M0CQBN9Z92DPB8KC69WCRGA7.jpg';

    /** Narahubung masjid: Tyas Hidayatullah, Sekretaris. */
    public const WHATSAPP = '085213683653';

    public const NARAHUBUNG = 'Tyas Hidayatullah (Sekretaris)';

    /**
     * Uploaded through this template's own sandbox editor (organization id 10) - a separate
     * file from the exclusive template's logo even though both show the same masjid, because
     * each template gets its own sandbox. Per-ORGANIZATION storage, so it 404s if that sandbox
     * is cleared; only ever read back by TemplateSandboxService::sandboxFor().
     */
    private const LOGO = 'https://storage.ambulu.or.id/organizations/10/brand/9c547178-919a-45f5-878b-74e20bb988c7.webp';

    public const ADDRESS = 'Jl. Raya Suyitman No.178, Sumberan, Ambulu, Kec. Ambulu, Kabupaten Jember';

    /** Google Maps place embed (the /maps/embed?pb=... form lokasi-peta drops into an iframe). */
    public const MAP_EMBED = 'https://www.google.com/maps/embed?origin=mfe&pb=!1m2!2m1!1s-8.342512%2C113.607005';

    public function run(): void
    {
        // Str::slug('Masjid/Mushola') strips the '/' rather than treating it as a separator,
        // producing 'masjidmushola' - see OrganizationTypeSeeder.
        $organizationType = OrganizationType::where('slug', 'masjidmushola')->first();

        $header = ['key' => 'header', 'variant' => 'standar'];
        $footer = ['key' => 'footer', 'variant' => 'standar'];

        $template = Template::updateOrCreate(
            ['slug' => self::SLUG],
            [
                'organization_type_id' => $organizationType?->id,
                'name' => 'Masjid Umat',
                'description' => 'Bagikan jadwal kajian dan galeri kegiatan, buka jalur donasi & zakat lewat WhatsApp, kenalkan pengurus, dan tampilkan kontak dengan peta lokasi - satu halaman profil masjid yang siap dikunjungi jamaah.',
                'is_active' => true,
                'is_exclusive' => false,
                // The exclusive Masjid Nurul Huda template carries `is_featured` for this type.
                'is_featured' => false,
                'structure' => [
                    'sample_org_name' => 'Masjid Nurul Huda',
                    // Same identity as the exclusive template (primary #2c368B, accent #1e79cc)
                    // but with the platform default font/radius rather than that template's
                    // plain-sans/rounded-2xl treatment - the standard tier should read as the
                    // standard tier.
                    'brand' => [
                        'primary' => '#2c368B',
                        'secondary' => '#1e79cc',
                        'font' => 'Plus Jakarta Sans',
                        'radius' => 'sharp',
                        'logo' => self::LOGO,
                    ],
                    // Same contact block as the exclusive template - see
                    // MasjidNurulHudaTemplateSeeder::contact() for why the email and socials are
                    // Suara Muhammadiyah's while the WhatsApp and address are the masjid's own.
                    'contact' => [
                        'email' => 'mediamu.ambulu@gmail.com',
                        'whatsapp' => '6285213683653',
                        'address' => self::ADDRESS,
                        'instagram_url' => 'https://www.instagram.com/suaramuhammadiyahambulu',
                        'facebook_url' => 'https://www.facebook.com/share/14sYGQiqc4L/',
                        'tiktok_url' => 'https://www.tiktok.com/@suaramuhammadiyahambulu',
                        'youtube_url' => 'https://www.youtube.com/@suaramuhammadiyahabl',
                    ],
                    'pages' => [
                        [
                            'slug' => 'home',
                            'name' => 'Beranda',
                            'sections' => [
                                $header,
                                // 1. Identity, same headline as the exclusive tier.
                                ['key' => 'hero', 'variant' => 'standar', 'content' => [
                                    'badge' => 'Terbuka untuk seluruh jamaah',
                                    'headline' => 'Masjid Nurul Huda Ambulu',
                                    'subheadline' => 'Pusat ibadah dan kegiatan umat yang transparan dalam pengelolaan dana dan terbuka untuk seluruh jamaah.',
                                    'cta_label' => 'Jadwal Kajian',
                                    'cta_type' => 'scroll',
                                    'cta_section' => 'agenda',
                                    'cta_secondary_label' => 'Donasi & Zakat',
                                    'cta_secondary_type' => 'scroll',
                                    'cta_secondary_section' => 'donasi-zakat-infak',
                                    'image' => self::HERO_IMAGE,
                                ]],
                                // 2. Profile - same stats the exclusive tier leads with, since
                                // they don't depend on any exclusive section to be true.
                                ['key' => 'tentang-organisasi', 'variant' => 'standar', 'content' => [
                                    'title' => 'Pusat Ibadah & Kegiatan Umat',
                                    'body' => 'Masjid Nurul Huda adalah rumah ibadah sekaligus pusat kegiatan keagamaan, pendidikan, dan sosial bagi masyarakat sekitar. Kami berkomitmen mengelola dana umat secara transparan dan menghadirkan kegiatan yang bermanfaat bagi jamaah.',
                                    'image' => self::ABOUT_IMAGE,
                                    'stats' => [
                                        ['value' => '13', 'label' => 'Fasilitas Masjid'],
                                        ['value' => '4x', 'label' => 'Kajian Rutin/Bulan'],
                                        ['value' => '100%', 'label' => 'Dana Transparan'],
                                    ],
                                ]],
                                // 3. When to come - agenda/standar (a plain list) in place of
                                // the exclusive tier's agenda/poster (a flyer grid).
                                // `items` carries a `poster` key the `standar` variant ignores -
                                // it is the superset shape TemplateSandboxService::export()
                                // writes so the same list still works if a Professional-plan
                                // masjid later switches this section to the `poster` variant.
                                ['key' => 'agenda', 'variant' => 'standar', 'content' => [
                                    'title' => 'Jadwal Kajian & Event',
                                    'subtitle' => 'Kajian rutin dan kegiatan masjid terbuka untuk seluruh jamaah.',
                                    'limit' => 4,
                                    'items' => MasjidNurulHudaTemplateSeeder::agendaItems(),
                                ]],
                                // 4. What it looks like - galeri stands in for the exclusive
                                // tier's structured fasilitas-masjid list, which this plan
                                // cannot add in any variant (see class doc comment).
                                ['key' => 'galeri', 'variant' => 'standar', 'content' => [
                                    'title' => 'Dokumentasi Kegiatan & Fasilitas',
                                    'limit' => 8,
                                    'items' => MasjidNurulHudaTemplateSeeder::galeriItems(),
                                ]],
                                // 5. The ask - donasi-zakat-infak's one WhatsApp CTA folds in
                                // what donasi-progress + kalkulator-zakat did on the exclusive
                                // tier: no per-program tracking or nisab math, but still a real
                                // path to give.
                                ['key' => 'donasi-zakat-infak', 'variant' => 'standar', 'content' => [
                                    'title' => 'Donasi, Zakat & Infak',
                                    'body' => 'Salurkan donasi, zakat, dan infak Anda melalui takmir masjid. Setiap donasi dikelola secara transparan untuk kemakmuran masjid dan kemaslahatan jamaah.',
                                    'wa_number' => self::WHATSAPP,
                                    'wa_message' => 'Assalamu\'alaikum, saya ingin berdonasi untuk Masjid Nurul Huda. Mohon informasi caranya ya.',
                                ]],
                                // 6. Who runs the masjid.
                                ['key' => 'struktur-pengurus', 'variant' => 'standar', 'content' => [
                                    'title' => 'Pengurus Masjid Nurul Huda',
                                    'items' => MasjidNurulHudaTemplateSeeder::pengurusItems(),
                                ]],
                                // 7. Direct contact - names the narahubung, same as every other
                                // standard-tier template's formulir-kontak.
                                ['key' => 'formulir-kontak', 'variant' => 'standar', 'content' => [
                                    'title' => 'Hubungi Takmir Masjid',
                                    'subtitle' => 'Untuk pertanyaan seputar kegiatan, donasi, atau kajian, hubungi narahubung kami, '.self::NARAHUBUNG.'.',
                                    'wa_number' => self::WHATSAPP,
                                    'wa_message' => 'Assalamu\'alaikum, saya ingin bertanya seputar kegiatan Masjid Nurul Huda.',
                                ]],
                                // 8. Where to find it.
                                ['key' => 'lokasi-peta', 'variant' => 'standar', 'content' => [
                                    'title' => 'Lokasi Masjid',
                                    'address' => self::ADDRESS,
                                    'map_embed' => self::MAP_EMBED,
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
