<?php

namespace App\Services\Samples;

/**
 * Single source of truth for Klinik Pratama Aisyiyah Ambulu's real content - contact
 * details, images, and every sample list (services, doctor roster, news, announcements,
 * gallery). Lives in app/ (not database/seeders/) specifically so both sides that need it
 * can depend on it without an inverted dependency:
 *
 *   - Database\Seeders\KlinikAisyiyahAmbuluTemplateSeeder (database/seeders/) uses it to
 *     build the template's structure['pages'] content, seeded once.
 *   - App\Services\CmsSampleDataSeeder (app/Services/) uses it to seed a real organization's
 *     own CMS records (Program, Announcement, GalleryPhoto, ...) - this runs in PRODUCTION
 *     every time an organization is created or a section is added
 *     (Organization::seedPagesFromTemplate(), OrganizationSectionController::store()), so it
 *     cannot depend on database/seeders/, which is seed-only, one-time-migration code.
 *
 * Before this class existed, the same clinic content was duplicated by hand across both of
 * those - a genuine maintenance hazard, since a real detail (e.g. a doctor's hours) had to be
 * updated in two places to stay in sync. Keeping it here means either caller is a thin
 * wrapper: the template seeder shapes this data into `structure['pages']`, and
 * CmsSampleDataSeeder shapes it into CMS table rows - all "what does the clinic actually say"
 * content lives in exactly one place.
 */
class KlinikAisyiyahAmbuluSamples
{
    /** Template slug this content is keyed to - see KlinikAisyiyahAmbuluTemplateSeeder::SLUG. */
    public const TEMPLATE_SLUG = 'klinik-aisyiyah-ambulu-eksklusif';

    /**
     * Klinik Pratama Aisyiyah Ambulu's real contact details - copied onto the seeded
     * organization's own contact fields by OrganizationSeeder, and used as CTA/contact
     * defaults throughout the template structure below.
     */
    public const WHATSAPP = '085234199394';

    public const ADDRESS = 'Jl. Hasanudin III No. 94, Ambulu, Kabupaten Jember, Jawa Timur';

    public const INSTAGRAM = 'https://www.instagram.com/aisyiyahambulu';

    public const TIKTOK = 'https://www.tiktok.com/@klinikaisyiyahambulu';

    /**
     * Google Maps place embed for the clinic's own pin (the /maps/embed?pb=... form the
     * lokasi-peta partial drops straight into an <iframe>). Built from the shared map link the
     * clinic publishes: https://maps.app.goo.gl/hfwJ9yqJbkhz8KrXA
     */
    public const MAP_EMBED = 'https://maps.google.com/maps?q=Klinik%20Pratama%20Aisyiyah%20Ambulu%2C%20Jl.%20Hasanudin%20III%20No.94%2C%20Ambulu%2C%20Jember&t=&z=17&ie=UTF8&iwloc=&output=embed';

    /**
     * Stand-in clinic photography (Unsplash) - the clinic has no public photo library to
     * hotlink the way Masjid Nurul Huda's S3 bucket allowed, so these are clearly generic
     * healthcare images meant to be swapped for the organization's own once it uploads them.
     */
    public const HERO_IMAGE = 'https://images.unsplash.com/photo-1519494026892-80bbd2d6fd0d?auto=format&fit=crop&w=1400&q=80';

    public const ABOUT_IMAGE = 'https://images.unsplash.com/photo-1631217868264-e5b90bb7e133?auto=format&fit=crop&w=1000&q=80';

    public const AMBULANCE_IMAGE = 'https://images.unsplash.com/photo-1587745416684-47953f16f02f?auto=format&fit=crop&w=1000&q=80';

    /**
     * The clinic's advertised service lines ("Poli umum | Rawat Luka | Poli Gigi | KB Suntik |
     * Laboratorium", plus the 24-hour UGD/rawat inap and free ambulance it leads with). Used
     * both as the template's `layanan` section content and as the organization's own
     * `layanan`-type Program records.
     *
     * @return array<int, array{title: string, description: string, icon: string}>
     */
    public static function layananItems(): array
    {
        return [
            ['title' => 'UGD 24 Jam', 'description' => 'Unit gawat darurat siaga sepanjang hari untuk penanganan cepat kondisi darurat.', 'icon' => '🚑'],
            ['title' => 'Rawat Inap & Rawat Jalan', 'description' => 'Perawatan menginap dan kunjungan berobat harian dengan pendampingan tenaga medis.', 'icon' => '🛏️'],
            ['title' => 'Poli Umum', 'description' => 'Pemeriksaan dan konsultasi dokter umum untuk pasien umum maupun BPJS.', 'icon' => '🩺'],
            ['title' => 'Poli Gigi', 'description' => 'Pemeriksaan, perawatan, dan tindakan kesehatan gigi bersama dokter gigi.', 'icon' => '🦷'],
            ['title' => 'Rawat Luka & KB Suntik', 'description' => 'Perawatan luka serta layanan keluarga berencana suntik oleh bidan dan perawat.', 'icon' => '💉'],
            ['title' => 'Laboratorium', 'description' => 'Pemeriksaan laboratorium penunjang diagnosis dengan hasil cepat.', 'icon' => '🔬'],
        ];
    }

    /**
     * The clinic's social programs - what it does beyond paid service lines. Used both as the
     * template's `program-unggulan` section content and as the organization's own
     * `program`-type Program records.
     *
     * @return array<int, array{title: string, description: string, icon: string}>
     */
    public static function programItems(): array
    {
        return [
            ['title' => 'Ambulans Gratis', 'description' => 'Layanan antar-jemput pasien tanpa biaya bagi warga yang membutuhkan.', 'icon' => '🚑'],
            ['title' => 'Baksos Kesehatan', 'description' => 'Pemeriksaan dan pengobatan gratis berkala di desa-desa sekitar Ambulu.', 'icon' => '🤝'],
            ['title' => 'Posyandu & Penyuluhan', 'description' => 'Pendampingan posyandu balita dan lansia bersama kader Aisyiyah.', 'icon' => '👶'],
        ];
    }

    /**
     * The clinic's real doctor roster and practice hours. `schedule` is one line because
     * jadwal-praktik/standar.blade.php renders it as a single string.
     *
     * @return array<int, array{name: string, specialty: string, schedule: string}>
     */
    public static function jadwalDokter(): array
    {
        return [
            ['name' => 'dr. Idfian Fenata Adi A.', 'specialty' => 'Dokter Umum', 'schedule' => 'Senin - Jumat, 07.30 - 13.00 WIB'],
            ['name' => 'dr. Kholisatul Widad', 'specialty' => 'Dokter Umum', 'schedule' => 'Senin - Jumat, 15.00 - 18.00 WIB'],
            ['name' => 'dr. Faisal Akbar', 'specialty' => 'Dokter Umum', 'schedule' => 'Senin - Jumat, 18.00 - 21.00 WIB'],
            ['name' => 'drg. Cahyohadi, M.Kes', 'specialty' => 'Dokter Gigi', 'schedule' => 'Senin - Jumat, 17.00 - 21.00 WIB'],
        ];
    }

    /**
     * Stand-in room/facility photography (Unsplash) for the 'Layanan' page's galeri section -
     * same rationale as HERO_IMAGE/ABOUT_IMAGE: the clinic has no public photo library yet, so
     * these are clearly generic clinic-interior images meant to be swapped for the
     * organization's own once it uploads real photos of its rooms. Shape matches what the
     * template's galeri section content and CmsSampleDataSeeder's gallery seeding each need:
     * `image`/`caption` for the template, `photo`/`caption` for the CMS seeder.
     *
     * @return array<int, array{image: string, caption: string}>
     */
    public static function ruanganPhotos(): array
    {
        return [
            ['image' => 'https://images.unsplash.com/photo-1516549655169-df83a0774514?auto=format&fit=crop&w=600&q=80', 'caption' => 'Ruang UGD'],
            ['image' => 'https://images.unsplash.com/photo-1519494026892-80bbd2d6fd0d?auto=format&fit=crop&w=600&q=80', 'caption' => 'Ruang Rawat Inap'],
            ['image' => 'https://images.unsplash.com/photo-1629909613654-28e377c37b09?auto=format&fit=crop&w=600&q=80', 'caption' => 'Ruang Tunggu Poli Umum'],
            ['image' => 'https://images.unsplash.com/photo-1606811841689-23dfddce3e95?auto=format&fit=crop&w=600&q=80', 'caption' => 'Ruang Poli Gigi'],
            ['image' => 'https://images.unsplash.com/photo-1579154204601-01588f351e67?auto=format&fit=crop&w=600&q=80', 'caption' => 'Ruang Laboratorium'],
            ['image' => self::AMBULANCE_IMAGE, 'caption' => 'Ambulans Klinik'],
        ];
    }

    /**
     * News/edukasi items for the template's `daftar-berita` section AND
     * CmsSampleDataSeeder's Post records - same three stories in both places.
     *
     * @return array<int, array{title: string, category: string, date: string, image: string, body: string}>
     */
    public static function beritaItems(): array
    {
        return [
            [
                'title' => 'Klinik Aisyiyah Ambulu Sediakan Ambulans Gratis bagi Warga',
                'category' => 'Layanan',
                'date' => '14 Agt 2026',
                'image' => self::AMBULANCE_IMAGE,
                'body' => 'Layanan ambulans gratis dapat diakses warga Ambulu dan sekitarnya dengan menghubungi nomor layanan klinik.',
            ],
            [
                'title' => 'Baksos Pemeriksaan Kesehatan Gratis di Balai Desa Ambulu',
                'category' => 'Sosial',
                'date' => '07 Agt 2026',
                'image' => 'https://images.unsplash.com/photo-1585036156171-384164a8c675?auto=format&fit=crop&w=700&q=80',
                'body' => 'Puluhan warga mengikuti pemeriksaan tensi, gula darah, dan konsultasi dokter umum tanpa biaya.',
            ],
            [
                'title' => 'Tips Menjaga Kesehatan Gigi Anak Sejak Dini',
                'category' => 'Edukasi',
                'date' => '30 Jul 2026',
                'image' => 'https://images.unsplash.com/photo-1606811841689-23dfddce3e95?auto=format&fit=crop&w=700&q=80',
                'body' => 'Dokter gigi klinik membagikan panduan sederhana merawat gigi anak di rumah.',
            ],
        ];
    }

    /**
     * A fourth story ('Layanan Laboratorium Kini Buka Setiap Hari Kerja') that the template's
     * home-page news section shows but which isn't part of beritaItems() above - that method
     * feeds CmsSampleDataSeeder's Post samples too, and is capped to 3 there. Kept separate so
     * the template's richer 4-item preview doesn't force a 4th real Post record on every
     * seeded organization regardless of its plan's `posts` limit.
     *
     * @return array{title: string, category: string, date: string, image: string}
     */
    public static function extraBeritaItem(): array
    {
        return [
            'title' => 'Layanan Laboratorium Kini Buka Setiap Hari Kerja',
            'category' => 'Layanan',
            'date' => '21 Jul 2026',
            'image' => 'https://images.unsplash.com/photo-1579154204601-01588f351e67?auto=format&fit=crop&w=700&q=80',
        ];
    }

    /**
     * The 'Informasi Pelayanan' notices, for both the template's `pengumuman` section content
     * and CmsSampleDataSeeder's Announcement records. All three are standing operational
     * notices (24-hour service, BPJS registration requirements, doctor schedule caveat) rather
     * than time-limited announcements, so `ongoing => true` tells
     * CmsSampleDataSeeder::seedAnnouncements() to leave them without a "Berlaku hingga ..."
     * expiry date.
     *
     * @return array<int, array{title: string, priority: string, body: string, ongoing: bool}>
     */
    public static function pengumumanItems(): array
    {
        return [
            ['title' => 'UGD, Rawat Jalan, dan Rawat Inap Melayani 24 Jam', 'priority' => 'Tinggi', 'body' => 'Unit gawat darurat klinik siaga sepanjang hari, termasuk akhir pekan dan hari libur.', 'ongoing' => true],
            ['title' => 'Pendaftaran Pasien BPJS', 'priority' => 'Sedang', 'body' => 'Pasien BPJS harap membawa kartu BPJS aktif dan KTP saat mendaftar di loket.', 'ongoing' => true],
            ['title' => 'Jadwal Dokter Dapat Berubah Sewaktu-waktu', 'priority' => 'Sedang', 'body' => 'Mohon konfirmasi terlebih dahulu melalui WhatsApp klinik sebelum datang berobat.', 'ongoing' => true],
        ];
    }
}
