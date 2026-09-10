<?php

namespace App\Services\Samples;

/**
 * Single source of truth for Pimpinan Cabang Aisyiyah Ambulu's real content - contact details,
 * secretariat address, the seven-person pimpinan, and every sample list (program, agenda,
 * berita, galeri, jaringan AUM). Lives in app/ (not database/seeders/) for the same reason as
 * KlinikAisyiyahAmbuluSamples (see that class's doc comment): both
 * Database\Seeders\PcaAmbuluTemplateSeeder (seed-only) and App\Services\CmsSampleDataSeeder
 * (runs in PRODUCTION on every organization creation) need this content, and app/ code must not
 * depend on database/seeders/.
 *
 * Backs a NON-exclusive template aimed at the STARTER plan specifically - see
 * PcaAmbuluTemplateSeeder for what that constrains, and for why several lists here are ordered
 * so their first three entries can stand alone (Starter's officers/programs/agendas quotas are
 * all 3).
 *
 * Editorially this is the counterpart to PcmAmbuluSamples, not a copy of it: where PCM's
 * profile argues from STRUCTURE (a koorbid behind every program), Aisyiyah Ambulu's argues from
 * WORK - a women's movement whose care for the environment and whose economic programs are
 * things warga can see, join, and buy from. The voice throughout is warm and matronly ("ibu-ibu
 * Aisyiyah", "dapur", "halaman rumah") without ever being decorative: every program names a
 * concrete activity, because a progressive women's organization is proven by what it runs, not
 * by how it describes itself.
 */
class PcaAmbuluSamples
{
    /** Template slug this content is keyed to - see PcaAmbuluTemplateSeeder::SLUG. */
    public const TEMPLATE_SLUG = 'pca-ambulu-standar';

    /**
     * PCA Ambulu's narahubung: Uswatun (Sekretaris I). Stored digits-only in the local 08...
     * form the other Samples classes use (the WhatsApp link builders normalize it), not the
     * +62 852-5880-7459 form it was given in.
     */
    public const WHATSAPP = '085258807459';

    /** The person behind self::WHATSAPP, named in CTA copy so warga know who they're texting. */
    public const NARAHUBUNG = 'Uswatun (Sekretaris)';

    /**
     * The secretariat shares an address with Klinik Pratama Aisyiyah Ambulu (see
     * KlinikAisyiyahAmbuluSamples::ADDRESS) - the same Jl. Hasanudin III No. 94 compound, given
     * here in the "Gg. III / Dusun Krajan" form PCA itself uses. Kept as its own constant
     * rather than referencing the clinic's: they're the same place today, but the cabang and
     * its AUM are separate organizations that may not always be co-located.
     */
    public const ADDRESS = 'Jl. Hasanudin Gg. III No. 94, Dusun Krajan, Ambulu, Kabupaten Jember, Jawa Timur';

    /**
     * Google Maps place embed for the secretariat (the /maps/embed?pb=... form the lokasi-peta
     * partial drops straight into an <iframe>). Points at the clinic's pin, which is the same
     * compound and the only one of the two with a published Maps listing.
     */
    public const MAP_EMBED = 'https://maps.google.com/maps?q=Klinik%20Pratama%20Aisyiyah%20Ambulu%2C%20Jl.%20Hasanudin%20III%20No.94%2C%20Ambulu%2C%20Jember&t=&z=17&ie=UTF8&iwloc=&output=embed';

    /**
     * Stand-in photography (Unsplash) - PCA Ambulu has no public photo library to hotlink, so
     * these are clearly generic images meant to be swapped for the cabang's own once it uploads
     * them. Chosen to read as women's community organizing and green/growing things rather than
     * as generic corporate stock, matching what this profile is actually about.
     */
    public const HERO_IMAGE = 'https://images.unsplash.com/photo-1591901206069-ed60c4429a2e?auto=format&fit=crop&w=1400&q=80';

    public const ABOUT_IMAGE = 'https://images.unsplash.com/photo-1466692476868-aef1dfb1e735?auto=format&fit=crop&w=1000&q=80';

    /**
     * The seven-person Pimpinan Cabang Aisyiyah Ambulu. The cabang's own list gave several
     * posts twice (two wakil, two sekretaris, two bendahara) with no ordinals and a skipped
     * number; per the cabang's confirmation those are numbered I/II here and reordered into the
     * conventional ketua -> wakil -> sekretaris -> bendahara sequence.
     *
     * NOTE on plan limits: Starter allows only 3 officers, so a cabang on that plan gets this
     * list TRUNCATED to Yayuk, Indayati, and Istiqomah by CmsSampleDataSeeder::sampleCount().
     * That truncation is the reason the order matters - the three that survive are the ketua
     * and both her wakil, which still reads as a leadership line rather than a random subset.
     * The template's own preview always shows all seven, and adding the rest is a normal CMS
     * edit (or a plan upgrade: Organization allows 7, exactly this list).
     *
     * Names are given without the "Bu" honorific, matching how PcmAmbuluSamples dropped "Bpk." -
     * the role column already carries the formality, and officer cards read cleaner without it.
     *
     * @return array<int, array{name: string, role: string}>
     */
    public static function pimpinanCabang(): array
    {
        return [
            ['name' => 'Yayuk', 'role' => 'Ketua'],
            ['name' => 'Indayati', 'role' => 'Wakil Ketua I'],
            ['name' => 'Istiqomah', 'role' => 'Wakil Ketua II'],
            ['name' => 'Uswatun', 'role' => 'Sekretaris I'],
            ['name' => 'Titis', 'role' => 'Sekretaris II'],
            ['name' => 'Atik', 'role' => 'Bendahara I'],
            ['name' => 'Faroha', 'role' => 'Bendahara II'],
        ];
    }

    /**
     * The cabang's flagship programs. Ordered so the FIRST THREE are the ones that carry the
     * profile's whole argument - a women's movement that is green and economically productive -
     * because Starter's `programs` quota is 3 and those three are what a Starter cabang keeps as
     * real Program records. The remaining three (education, health, dakwah) round out the
     * preview and arrive with a plan upgrade or a manual add.
     *
     * Six items also fills program-unggulan/standar's 3-column grid evenly at two rows.
     *
     * @return array<int, array{title: string, description: string, icon: string}>
     */
    public static function programItems(): array
    {
        return [
            [
                'title' => 'Aisyiyah Peduli Lingkungan',
                'description' => 'Gerakan bank sampah, pemilahan sampah rumah tangga, dan pengolahan sampah organik menjadi kompos bersama ibu-ibu di setiap ranting.',
                'icon' => '♻️',
            ],
            [
                'title' => 'Kebun Gizi & Tanaman Keluarga',
                'description' => 'Memanfaatkan halaman rumah untuk sayur, tanaman obat keluarga, dan bibit gratis - pangan sehat yang dimulai dari dapur sendiri.',
                'icon' => '🌿',
            ],
            [
                'title' => 'UMKM Ibu Berdaya',
                'description' => 'Pendampingan usaha rumahan warga: pelatihan produksi, pengemasan, perizinan, hingga pemasaran daring bagi ibu-ibu wirausaha.',
                'icon' => '🧺',
            ],
            [
                'title' => 'TK ABA & Pendidikan Anak Usia Dini',
                'description' => 'Pembinaan mutu TK ABA 1 hingga 4 serta pendampingan guru dan orang tua dalam mendidik anak usia dini.',
                'icon' => '🎨',
            ],
            [
                'title' => 'Ibu Sehat, Keluarga Sehat',
                'description' => 'Posyandu balita dan lansia, penyuluhan gizi, serta baksos kesehatan bersama Klinik Pratama Aisyiyah Ambulu.',
                'icon' => '🩺',
            ],
            [
                'title' => 'Pengajian & Bina Keluarga Sakinah',
                'description' => 'Kajian rutin ibu-ibu, tadarus, dan pembinaan keluarga sakinah sebagai ruang tumbuh bersama warga Aisyiyah.',
                'icon' => '🕌',
            ],
        ];
    }

    /**
     * The cabang's Amal Usaha, as PCA Ambulu itself lists them: the clinic plus TK ABA 1-4.
     * Each TK is its own row rather than one "TK ABA 1-4" entry - jaringan-aum-ortom/standar
     * renders a card per row, and four cards make the cabang's actual footprint in early
     * childhood education visible in a way one card does not.
     *
     * Not plan-limited: 'jaringan-aum-ortom' has no PlanLimitService quota (see
     * CmsSampleDataSeeder::seedNetworks()), so all five survive even on Starter.
     *
     * @return array<int, array{name: string, type: string}>
     */
    public static function jaringanItems(): array
    {
        return [
            ['name' => 'Klinik Pratama Aisyiyah Ambulu', 'type' => 'AUM Kesehatan'],
            ['name' => 'TK ABA 1 Ambulu', 'type' => 'AUM Pendidikan'],
            ['name' => 'TK ABA 2 Ambulu', 'type' => 'AUM Pendidikan'],
            ['name' => 'TK ABA 3 Ambulu', 'type' => 'AUM Pendidikan'],
            ['name' => 'TK ABA 4 Ambulu', 'type' => 'AUM Pendidikan'],
        ];
    }

    /**
     * Recurring cabang activities for the `agenda` section AND CmsSampleDataSeeder's Agenda
     * records. `days` is an offset from seeding time (that's the shape seedAgendas() expects),
     * so a freshly seeded site always shows upcoming - never stale - dates.
     *
     * First three are what a Starter cabang keeps (`agendas` quota 3), so the recurring pengajian,
     * the bank sampah pickup, and the posyandu lead - the three that best show a cabang whose
     * calendar is actually busy.
     *
     * @return array<int, array{title: string, days: int, location: string, description: string}>
     */
    public static function agendaItems(): array
    {
        return [
            [
                'title' => 'Pengajian Rutin Ibu-Ibu Aisyiyah',
                'days' => 4,
                'location' => 'Aula PCA Ambulu, Jl. Hasanudin Gg. III',
                'description' => '<p>Kajian rutin terbuka untuk seluruh anggota Aisyiyah dan ibu-ibu warga sekitar.</p>',
            ],
            [
                'title' => 'Setor Bank Sampah Ranting',
                'days' => 9,
                'location' => 'Halaman Sekretariat PCA Ambulu',
                'description' => '<p>Penimbangan dan setor sampah terpilah dari rumah warga. Hasilnya masuk tabungan anggota.</p>',
            ],
            [
                'title' => 'Posyandu Balita & Lansia',
                'days' => 16,
                'location' => 'Balai Dusun Krajan, Ambulu',
                'description' => '<p>Penimbangan balita, pemeriksaan lansia, dan penyuluhan gizi bersama kader Aisyiyah.</p>',
            ],
            [
                'title' => 'Pelatihan Pengemasan Produk UMKM',
                'days' => 23,
                'location' => 'Aula PCA Ambulu, Jl. Hasanudin Gg. III',
                'description' => '<p>Pelatihan kemasan, label, dan pemasaran daring bagi ibu-ibu pelaku usaha rumahan.</p>',
            ],
        ];
    }

    /**
     * agendaItems() reshaped into the {title, date_day, date_month, date_year, location, time}
     * form agenda/standar.blade.php renders in template-preview context (where there's no
     * organization to query Agenda rows from). Derived here rather than hand-typed so the two
     * lists can never drift apart, and computed from `now()` at seed time so a preview shows
     * plausible upcoming dates instead of a hardcoded year that ages out.
     *
     * Times differ from PcmAmbuluSamples' fixed 18:00: these are ibu-ibu daytime activities
     * (pengajian and posyandu run mid-morning), so the hour is per-item rather than shared.
     *
     * @return array<int, array<string, string>>
     */
    public static function agendaPreviewItems(): array
    {
        return array_map(function (array $item) {
            $date = now()->addDays($item['days'])->setTime(8, 0);

            return [
                'title' => $item['title'],
                'date_day' => $date->format('d'),
                'date_month' => $date->translatedFormat('M'),
                'date_year' => $date->format('Y'),
                'location' => $item['location'],
                'time' => $date->format('H:i'),
            ];
        }, self::agendaItems());
    }

    /**
     * Kabar cabang for the `daftar-berita` section AND CmsSampleDataSeeder's Post records - same
     * stories in both places. Each reports one of the programs above actually happening, so the
     * news list reads as evidence rather than filler. Five items matches Starter's `posts` quota
     * (5) exactly, so nothing is truncated on the plan this template targets.
     *
     * @return array<int, array{title: string, category: string, date: string, image: string, body: string}>
     */
    public static function beritaItems(): array
    {
        return [
            [
                'title' => 'Bank Sampah Aisyiyah Ambulu Kumpulkan Satu Ton Sampah Terpilah',
                'category' => 'Lingkungan',
                'date' => '28 Agt 2026',
                'image' => 'https://images.unsplash.com/photo-1532996122724-e3c354a0b15b?auto=format&fit=crop&w=700&q=80',
                'body' => 'Setoran rutin ibu-ibu dari empat ranting menembus satu ton sampah terpilah. Hasil penjualan masuk ke tabungan anggota dan kas kegiatan cabang.',
            ],
            [
                'title' => 'Ibu-Ibu Aisyiyah Sulap Halaman Rumah Jadi Kebun Gizi Keluarga',
                'category' => 'Lingkungan',
                'date' => '21 Agt 2026',
                'image' => 'https://images.unsplash.com/photo-1416879595882-3373a0480b5b?auto=format&fit=crop&w=700&q=80',
                'body' => 'Bibit sayur dan tanaman obat keluarga dibagikan gratis kepada anggota. Panen pertama sudah masuk dapur warga.',
            ],
            [
                'title' => 'Pelatihan Kemasan Dorong UMKM Ibu Rumahan Naik Kelas',
                'category' => 'Ekonomi',
                'date' => '14 Agt 2026',
                'image' => 'https://images.unsplash.com/photo-1556909212-d5b604d0c90d?auto=format&fit=crop&w=700&q=80',
                'body' => 'Puluhan pelaku usaha rumahan belajar mengemas, memberi label, dan memasarkan produknya secara daring.',
            ],
            [
                'title' => 'Posyandu Aisyiyah Layani Ratusan Balita dan Lansia',
                'category' => 'Kesehatan',
                'date' => '07 Agt 2026',
                'image' => 'https://images.unsplash.com/photo-1576091160550-2173dba999ef?auto=format&fit=crop&w=700&q=80',
                'body' => 'Bekerja sama dengan Klinik Pratama Aisyiyah Ambulu, kader menggelar penimbangan balita dan pemeriksaan lansia.',
            ],
            [
                'title' => 'TK ABA Ambulu Ajak Anak Menanam dan Memilah Sampah Sejak Dini',
                'category' => 'Pendidikan',
                'date' => '31 Jul 2026',
                'image' => 'https://images.unsplash.com/photo-1587616211892-f743fcca64f9?auto=format&fit=crop&w=700&q=80',
                'body' => 'Empat TK ABA di bawah PCA Ambulu memasukkan kegiatan menanam dan memilah sampah ke dalam pembelajaran harian.',
            ],
        ];
    }

    /**
     * Activity photos for the `galeri` section. Shape is {image, caption} - what
     * galeri/standar.blade.php renders directly, and what
     * CmsSampleDataSeeder::toGalleryPhotoSamples() adapts into GalleryPhoto rows.
     *
     * Starter's `gallery_photos` quota is 3, so only the first three become real records - hence
     * the green/economic trio leads, the same three the programs and berita lead with.
     *
     * @return array<int, array{image: string, caption: string}>
     */
    public static function kegiatanPhotos(): array
    {
        return [
            ['image' => 'https://images.unsplash.com/photo-1532996122724-e3c354a0b15b?auto=format&fit=crop&w=600&q=80', 'caption' => 'Setor bank sampah ranting'],
            ['image' => 'https://images.unsplash.com/photo-1416879595882-3373a0480b5b?auto=format&fit=crop&w=600&q=80', 'caption' => 'Kebun gizi di halaman rumah'],
            ['image' => 'https://images.unsplash.com/photo-1556909212-d5b604d0c90d?auto=format&fit=crop&w=600&q=80', 'caption' => 'Pelatihan UMKM ibu-ibu'],
            ['image' => 'https://images.unsplash.com/photo-1576091160550-2173dba999ef?auto=format&fit=crop&w=600&q=80', 'caption' => 'Posyandu balita dan lansia'],
            ['image' => 'https://images.unsplash.com/photo-1591901206069-ed60c4429a2e?auto=format&fit=crop&w=600&q=80', 'caption' => 'Pengajian rutin Aisyiyah'],
            ['image' => 'https://images.unsplash.com/photo-1587616211892-f743fcca64f9?auto=format&fit=crop&w=600&q=80', 'caption' => 'Kegiatan anak TK ABA'],
        ];
    }
}
