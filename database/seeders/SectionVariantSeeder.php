<?php

namespace Database\Seeders;

use App\Models\SectionVariant;
use Illuminate\Database\Seeder;

/**
 * Seeds the section_variants table - the registry of which Blade view renders each section
 * variant, and whether picking it requires Organization::canUseExclusiveTemplates(). This is the
 * one-time data migration off config/page-builder.php's old `variants`/`default_variant` keys
 * (now removed from that file) plus the ongoing workflow for adding a brand-new variant: write
 * the Blade view, then add one entry here.
 */
class SectionVariantSeeder extends Seeder
{
    public function run(): void
    {
        $sections = [
            'hero' => [
                'standar' => ['view' => 'templates.sections.hero.standar', 'exclusive' => false, 'default' => true],
                'masjidmu' => ['view' => 'templates.sections.hero.masjidmu', 'exclusive' => true],
                'modern' => ['view' => 'templates.sections.hero.modern', 'exclusive' => true],
                'headline-berita' => ['view' => 'templates.sections.hero.headline-berita', 'exclusive' => true],
                'sekolahmu' => ['view' => 'templates.sections.hero.sekolahmu', 'exclusive' => true],
            ],
            'header' => [
                'standar' => ['view' => 'templates.sections.header.standar', 'exclusive' => false, 'default' => true],
            ],
            'footer' => [
                'standar' => ['view' => 'templates.sections.footer.standar', 'exclusive' => false, 'default' => true],
            ],
            'tentang-organisasi' => [
                'standar' => ['view' => 'templates.sections.tentang-organisasi.standar', 'exclusive' => false, 'default' => true],
                'modern' => ['view' => 'templates.sections.tentang-organisasi.modern', 'exclusive' => true],
                'sekolahmu' => ['view' => 'templates.sections.tentang-organisasi.sekolahmu', 'exclusive' => true],
            ],
            'sambutan-ketua' => [
                'standar' => ['view' => 'templates.sections.sambutan-ketua.standar', 'exclusive' => false, 'default' => true],
                'modern' => ['view' => 'templates.sections.sambutan-ketua.modern', 'exclusive' => true],
            ],
            'struktur-pengurus' => [
                'standar' => ['view' => 'templates.sections.struktur-pengurus.standar', 'exclusive' => false, 'default' => true],
                'modern' => ['view' => 'templates.sections.struktur-pengurus.modern', 'exclusive' => true],
                'sekolahmu' => ['view' => 'templates.sections.struktur-pengurus.sekolahmu', 'exclusive' => true],
            ],
            'program-unggulan' => [
                'standar' => ['view' => 'templates.sections.program-unggulan.standar', 'exclusive' => false, 'default' => true],
                'modern' => ['view' => 'templates.sections.program-unggulan.modern', 'exclusive' => true],
                'sekolahmu' => ['view' => 'templates.sections.program-unggulan.sekolahmu', 'exclusive' => true],
            ],
            'layanan' => [
                'standar' => ['view' => 'templates.sections.layanan.standar', 'exclusive' => false, 'default' => true],
            ],
            'jaringan-aum-ortom' => [
                'standar' => ['view' => 'templates.sections.jaringan-aum-ortom.standar', 'exclusive' => false, 'default' => true],
            ],
            'daftar-berita' => [
                'standar' => ['view' => 'templates.sections.daftar-berita.standar', 'exclusive' => false, 'default' => true],
                'modern' => ['view' => 'templates.sections.daftar-berita.modern', 'exclusive' => true],
                'ringkas' => ['view' => 'templates.sections.daftar-berita.ringkas', 'exclusive' => true],
            ],
            'agenda' => [
                'standar' => ['view' => 'templates.sections.agenda.standar', 'exclusive' => false, 'default' => true],
                'poster' => ['view' => 'templates.sections.agenda.poster', 'exclusive' => true],
            ],
            'pengumuman' => [
                'standar' => ['view' => 'templates.sections.pengumuman.standar', 'exclusive' => false, 'default' => true],
            ],
            'galeri' => [
                'standar' => ['view' => 'templates.sections.galeri.standar', 'exclusive' => false, 'default' => true],
                'sekolahmu' => ['view' => 'templates.sections.galeri.sekolahmu', 'exclusive' => true],
            ],
            'jadwal-praktik' => [
                'standar' => ['view' => 'templates.sections.jadwal-praktik.standar', 'exclusive' => false, 'default' => true],
            ],
            'donasi-zakat-infak' => [
                'standar' => ['view' => 'templates.sections.donasi-zakat-infak.standar', 'exclusive' => false, 'default' => true],
            ],
            'kegiatan' => [
                'standar' => ['view' => 'templates.sections.kegiatan.standar', 'exclusive' => false, 'default' => true],
                'sekolahmu' => ['view' => 'templates.sections.kegiatan.sekolahmu', 'exclusive' => true],
            ],
            'testimoni' => [
                'standar' => ['view' => 'templates.sections.testimoni.standar', 'exclusive' => false, 'default' => true],
                'sekolahmu' => ['view' => 'templates.sections.testimoni.sekolahmu', 'exclusive' => true],
            ],
            'prestasi' => [
                'standar' => ['view' => 'templates.sections.prestasi.standar', 'exclusive' => false, 'default' => true],
                'sekolahmu' => ['view' => 'templates.sections.prestasi.sekolahmu', 'exclusive' => true],
            ],
            'formulir-kontak' => [
                'standar' => ['view' => 'templates.sections.formulir-kontak.standar', 'exclusive' => false, 'default' => true],
                'sekolahmu' => ['view' => 'templates.sections.formulir-kontak.sekolahmu', 'exclusive' => true],
            ],
            'lokasi-peta' => [
                'standar' => ['view' => 'templates.sections.lokasi-peta.standar', 'exclusive' => false, 'default' => true],
            ],
            'fasilitas-masjid' => [
                'masjidmu' => ['view' => 'templates.sections.fasilitas-masjid.masjidmu', 'exclusive' => true, 'default' => true],
            ],
            'donasi-progress' => [
                'masjidmu' => ['view' => 'templates.sections.donasi-progress.masjidmu', 'exclusive' => true, 'default' => true],
            ],
            'laporan-keuangan' => [
                'masjidmu' => ['view' => 'templates.sections.laporan-keuangan.masjidmu', 'exclusive' => true, 'default' => true],
            ],
            'kalkulator-zakat' => [
                'masjidmu' => ['view' => 'templates.sections.kalkulator-zakat.masjidmu', 'exclusive' => true, 'default' => true],
            ],
            'akad-venue' => [
                'masjidmu' => ['view' => 'templates.sections.akad-venue.masjidmu', 'exclusive' => true, 'default' => true],
            ],
            'cta' => [
                'standar' => ['view' => 'templates.sections.cta.standar', 'exclusive' => false, 'default' => true],
                'modern' => ['view' => 'templates.sections.cta.modern', 'exclusive' => true],
                'newsletter' => ['view' => 'templates.sections.cta.newsletter', 'exclusive' => true],
                'sekolahmu' => ['view' => 'templates.sections.cta.sekolahmu', 'exclusive' => true],
            ],
        ];

        foreach ($sections as $sectionKey => $variants) {
            foreach ($variants as $variantKey => $meta) {
                SectionVariant::updateOrCreate(
                    ['section_key' => $sectionKey, 'variant_key' => $variantKey],
                    [
                        'view' => $meta['view'],
                        'is_exclusive' => $meta['exclusive'],
                        'is_default' => $meta['default'] ?? false,
                    ]
                );
            }
        }
    }
}
