<?php

namespace Database\Seeders;

use App\Models\OrganizationType;
use App\Models\Template;
use App\Models\User;
use App\Services\Samples\PcaAmbuluSamples as Samples;
use App\Services\TemplateSandboxService;
use Illuminate\Database\Seeder;

/**
 * Seeds the "Profil Cabang Aisyiyah (Standar)" template - the second NON-exclusive template,
 * modeled on Pimpinan Cabang Aisyiyah Ambulu, and the first one aimed squarely at the STARTER
 * plan. It shares PcmAmbuluTemplateSeeder's structural constraints (single page, `standar`
 * variants only, no `exclusive` section keys - see that seeder's doc comment for why each is
 * forced), but differs from it in the one way that matters for the cheapest tier:
 *
 * EVERY sample list here is ordered so its first few entries survive Starter's quotas
 * (officers 3, programs 3, agendas 3, gallery_photos 3, posts 5). CmsSampleDataSeeder truncates
 * from the END of each list, so on Starter a cabang keeps the ketua and both wakil, the three
 * green/economic programs, the three busiest recurring agendas, and all five news stories -
 * i.e. the specific subset that still tells the whole story. PcmAmbuluTemplateSeeder was
 * written for the Organization plan and is merely tolerant of Starter; this one is designed
 * for it. Ten unlocked sections is exactly Starter's `sections_total`, so nothing is dropped.
 *
 * Content is PCA Ambulu's real profile: the seven-person pimpinan, the secretariat on
 * Jl. Hasanudin Gg. III No. 94 Dusun Krajan, Uswatun as narahubung, and its Amal Usaha (Klinik
 * Pratama Aisyiyah Ambulu plus TK ABA 1-4) - all pulled from App\Services\Samples\
 * PcaAmbuluSamples (see that class's doc comment for why the content lives there and not here).
 *
 * The editorial idea, and the reason this reads nothing like the PCM template despite sharing
 * its components: PCM argues from STRUCTURE (a koorbid behind every program); Aisyiyah Ambulu
 * argues from WORK. This is a women's movement that is progressive in the most literal sense -
 * it runs a bank sampah, turns household yards into kebun gizi, and helps ibu-ibu build real
 * businesses - so the page leads with what the ibu-ibu DO, and the pimpinan list arrives after
 * as the answer to "who is behind all this". The voice is deliberately warm and matronly
 * (dapur, halaman rumah, ibu-ibu) while every claim stays concrete, because a progressive
 * women's organization is proven by its activities, not by adjectives about itself.
 */
class PcaAmbuluTemplateSeeder extends Seeder
{
    public const SLUG = Samples::TEMPLATE_SLUG;

    public function run(): void
    {
        // Named for the movement, not the tier ('Aisyiyah', not 'Pimpinan Cabang Aisyiyah') -
        // see OrganizationTypeSeeder. One type serves PDA/PCA/PRA alike.
        $organizationType = OrganizationType::where('slug', 'aisyiyah')->first();

        $header = ['key' => 'header', 'variant' => 'standar'];
        $footer = ['key' => 'footer', 'variant' => 'standar'];

        $template = Template::updateOrCreate(
            ['slug' => self::SLUG],
            [
                'organization_type_id' => $organizationType?->id,
                'name' => 'Profil Cabang Aisyiyah (Standar)',
                'description' => 'Template satu halaman untuk Pimpinan Cabang/Ranting Aisyiyah: profil cabang, program lingkungan dan pemberdayaan ekonomi perempuan, agenda kegiatan ibu-ibu, galeri kegiatan, kabar cabang, struktur pimpinan, Amal Usaha (klinik dan TK ABA), serta kontak sekretariat. Tersedia untuk semua paket, dirancang pas untuk paket Starter.',
                'is_active' => true,
                'is_exclusive' => false,
                'is_featured' => true,
                'structure' => [
                    'sample_org_name' => 'PCA Ambulu',
                    // Aisyiyah green led with Muhammadiyah blue secondary - Aisyiyah's own
                    // pairing, and the same order KlinikAisyiyahAmbuluTemplateSeeder uses for
                    // the ortom. Doubles as the environmental signal this profile is built
                    // around. Platform-default font/radius, as befits the standard tier (the
                    // exclusive templates keep the serif/sharp identity to stay visibly
                    // distinct).
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
                                // 1. Identity, led by the work rather than the institution.
                                // Primary CTA scrolls to the programs - the whole argument of
                                // the page - and the WhatsApp ask waits until formulir-kontak.
                                ['key' => 'hero', 'variant' => 'standar', 'content' => [
                                    'badge' => 'Pimpinan Cabang Aisyiyah Ambulu',
                                    'headline' => 'Perempuan Berkemajuan, Bumi Terjaga, Keluarga Berdaya',
                                    'subheadline' => 'Ibu-ibu Aisyiyah Ambulu bergerak dari rumah ke rumah: memilah sampah, menanam kebun gizi di halaman sendiri, dan menumbuhkan usaha rumahan - merawat lingkungan sekaligus memandirikan keluarga.',
                                    'cta_label' => 'Lihat Program Kami',
                                    'cta_type' => 'scroll',
                                    'cta_section' => 'program-unggulan',
                                    'cta_secondary_label' => 'Agenda Kegiatan',
                                    'cta_secondary_type' => 'scroll',
                                    'cta_secondary_section' => 'agenda',
                                    'image' => Samples::HERO_IMAGE,
                                ]],
                                // 2. Who we are. `stats` are countable facts the cabang can
                                // state without bookkeeping (5 AUM, 4 TK ABA, 7 pimpinan)
                                // rather than the registry's default "10+ Tahun / 100+ Anggota"
                                // guesses a cabang would have to verify or leave wrong.
                                ['key' => 'tentang-organisasi', 'variant' => 'standar', 'content' => [
                                    'title' => 'Tentang Aisyiyah Ambulu',
                                    'body' => 'Pimpinan Cabang Aisyiyah Ambulu adalah gerakan perempuan Muhammadiyah di Kecamatan Ambulu, Kabupaten Jember. Sejak dulu Aisyiyah percaya bahwa perubahan besar dimulai dari hal-hal yang dekat: dapur yang sehat, halaman yang hijau, sampah yang terpilah, dan ibu yang punya penghasilan sendiri. Dari sanalah kami bergerak - merawat lingkungan, memberdayakan ekonomi keluarga, mengasuh anak usia dini lewat TK ABA, dan menjaga kesehatan warga bersama Klinik Pratama Aisyiyah Ambulu.',
                                    'image' => Samples::ABOUT_IMAGE,
                                    'stats' => [
                                        ['value' => '5', 'label' => 'Amal Usaha'],
                                        ['value' => '4', 'label' => 'TK ABA Binaan'],
                                        ['value' => '7', 'label' => 'Pimpinan Cabang'],
                                    ],
                                ]],
                                // 3. The work itself, placed high on purpose. On Starter only
                                // the first three survive as Program records - which is exactly
                                // the lingkungan/ekonomi trio this profile is built on.
                                ['key' => 'program-unggulan', 'variant' => 'standar', 'content' => [
                                    'title' => 'Program Unggulan Aisyiyah Ambulu',
                                    'items' => Samples::programItems(),
                                ]],
                                // 4. Those programs on a calendar - and an open invitation.
                                // The subtitle does real work here: it turns a schedule into a
                                // "come join us", which is how a cabang actually recruits.
                                ['key' => 'agenda', 'variant' => 'standar', 'content' => [
                                    'title' => 'Agenda Kegiatan Ibu-Ibu',
                                    'subtitle' => 'Terbuka untuk seluruh anggota Aisyiyah dan ibu-ibu warga sekitar. Datang saja, tidak perlu mendaftar.',
                                    'limit' => 4,
                                    'items' => Samples::agendaPreviewItems(),
                                ]],
                                // 5. Proof, in pictures. A bank sampah and a kebun gizi are
                                // things you have to SEE - which is why a galeri earns a slot
                                // on a page this tight, where the PCM profile spent that slot
                                // on a sambutan instead.
                                ['key' => 'galeri', 'variant' => 'standar', 'content' => [
                                    'title' => 'Galeri Kegiatan',
                                    'limit' => 6,
                                    'items' => Samples::kegiatanPhotos(),
                                ]],
                                // 6. Proof, in words. `limit` 5 matches the sample count and
                                // Starter's `posts` quota, so nothing is truncated here.
                                ['key' => 'daftar-berita', 'variant' => 'standar', 'content' => [
                                    'title' => 'Kabar Aisyiyah Ambulu',
                                    'limit' => 5,
                                    'items' => Samples::beritaItems(),
                                ]],
                                // 7. Only now: who is behind all of it. Deliberately AFTER the
                                // work - the inverse of the PCM template, where the structure
                                // is the argument and therefore leads.
                                ['key' => 'struktur-pengurus', 'variant' => 'standar', 'content' => [
                                    'title' => 'Pimpinan Cabang Aisyiyah Ambulu',
                                    'items' => Samples::pimpinanCabang(),
                                ]],
                                // 8. The Amal Usaha - a clinic and four TK ABA. Concrete
                                // institutions the cabang runs, which is the strongest possible
                                // close to the "what we do" half of the page.
                                ['key' => 'jaringan-aum-ortom', 'variant' => 'standar', 'content' => [
                                    'title' => 'Amal Usaha Aisyiyah Ambulu',
                                    'items' => Samples::jaringanItems(),
                                ]],
                                // 9. The ask. Names the narahubung rather than showing a bare
                                // number - an ibu texting an organization wants to know who
                                // picks up.
                                ['key' => 'formulir-kontak', 'variant' => 'standar', 'content' => [
                                    'title' => 'Hubungi Aisyiyah Ambulu',
                                    'subtitle' => 'Ingin ikut kegiatan, menitipkan sampah terpilah, atau mengajak kerja sama program? Hubungi narahubung kami, '.Samples::NARAHUBUNG.'.',
                                    'wa_number' => Samples::WHATSAPP,
                                    'wa_message' => 'Assalamu\'alaikum, saya ingin bertanya seputar kegiatan Aisyiyah Ambulu.',
                                ]],
                                // 10. Where to find them. Same compound as Klinik Pratama
                                // Aisyiyah Ambulu - see Samples::MAP_EMBED.
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
