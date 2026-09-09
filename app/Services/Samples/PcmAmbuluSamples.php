<?php

namespace App\Services\Samples;

/**
 * Single source of truth for Pimpinan Cabang Muhammadiyah Ambulu's real content - contact
 * details, secretariat address, the nine-person pimpinan harian, and every sample list
 * (program, agenda, berita, jaringan AUM/Ortom). Lives in app/ (not database/seeders/) for the
 * same reason as KlinikAisyiyahAmbuluSamples (see that class's doc comment): both
 * Database\Seeders\PcmAmbuluTemplateSeeder (seed-only) and App\Services\CmsSampleDataSeeder
 * (runs in PRODUCTION on every organization creation) need this content, and app/ code must
 * not depend on database/seeders/.
 *
 * Unlike the Klinik and Suara Muhammadiyah samples, this content backs a NON-exclusive
 * template - see PcmAmbuluTemplateSeeder for why that constrains it to a single page, eight
 * unlocked sections, and `standar` variants only.
 */
class PcmAmbuluSamples
{
    /** Template slug this content is keyed to - see PcmAmbuluTemplateSeeder::SLUG. */
    public const TEMPLATE_SLUG = 'pcm-ambulu-standar';

    /**
     * PCM Ambulu's narahubung: Beni Hendarto (Sekretaris), the cabang's designated
     * contact person - so every WhatsApp CTA on this template reaches the secretariat rather
     * than an individual pimpinan's personal line. Stored digits-only in the local 08... form
     * the other Samples classes use (the WhatsApp link builders normalize it), not the
     * +62 823-3258-3556 form it was given in.
     */
    public const WHATSAPP = '082332583556';

    /** The person behind self::WHATSAPP, named in CTA copy so warga know who they're texting. */
    public const NARAHUBUNG = 'Beni Hendarto (Sekretaris)';

    public const ADDRESS = 'Jl. dr. Soetomo No. 15, Krajan, Ambulu, Kabupaten Jember, Jawa Timur';

    /**
     * Google Maps place embed for the secretariat (the /maps/embed?pb=... form the lokasi-peta
     * partial drops straight into an <iframe>), built from the address above as a search query
     * rather than a shared place pin - PCM Ambulu has no published Maps listing to link, so
     * this centers on the street address and should be replaced with the cabang's own pin once
     * it claims one.
     */
    public const MAP_EMBED = 'https://maps.google.com/maps?q=Jl.%20dr.%20Soetomo%20No.15%20Krajan%20Ambulu%20Jember&t=&z=17&ie=UTF8&iwloc=&output=embed';

    /**
     * Stand-in photography (Unsplash) - PCM Ambulu has no public photo library to hotlink the
     * way Masjid Nurul Huda's S3 bucket allowed, so these are clearly generic community/
     * organizational images meant to be swapped for the cabang's own once it uploads them.
     */
    public const HERO_IMAGE = 'https://images.unsplash.com/photo-1517486808906-6ca8b3f04846?auto=format&fit=crop&w=1400&q=80';

    public const ABOUT_IMAGE = 'https://images.unsplash.com/photo-1524178232363-1fb2b075b655?auto=format&fit=crop&w=1000&q=80';

    /**
     * The nine-person Pimpinan Harian PCM Ambulu, exactly as the cabang lists itself: ketua,
     * sekretaris, the six koordinator bidang in the order given, then bendahara last. Flat
     * {name, role} shape is what struktur-pengurus/standar.blade.php renders (one card per
     * person) and what CmsSampleDataSeeder::seedOfficers() inserts as Officer rows.
     *
     * NOTE on plan limits: the Starter plan allows only 3 officers and Organization 7, so a
     * cabang on those plans gets this list TRUNCATED to the first 3/7 entries by
     * CmsSampleDataSeeder::sampleCount() - which is exactly why ketua and sekretaris lead the
     * list and the bendahara, though conventionally named alongside them, sits ninth here as
     * the cabang itself ordered it. A cabang that wants its full harian visible needs the
     * Professional plan (20 officers); the template's own preview always shows all nine.
     *
     * @return array<int, array{name: string, role: string}>
     */
    public static function pimpinanHarian(): array
    {
        return [
            ['name' => 'Zainal Arifin', 'role' => 'Ketua'],
            ['name' => 'Beni Hendarto', 'role' => 'Sekretaris'],
            ['name' => "Moh. Naf'an", 'role' => 'Bendahara'],
            ['name' => 'Romadhoni Sholeh', 'role' => 'Koorbid Sosial dan Pengkaderan'],
            ['name' => 'Tyas Hidayatulloh', 'role' => 'Koorbid Tabligh'],
            ['name' => 'Mansur Syahrowi', 'role' => 'Koorbid Wakaf'],
            ['name' => "Imam Musta'id", 'role' => 'Koorbid Dikdasmen'],
            ['name' => 'Suhartono', 'role' => 'Koorbid Kesehatan'],
            ['name' => 'Syaikur Rokhman', 'role' => 'Koorbid Ekonomi'],
        ];
    }

    /**
     * The cabang's flagship programs, written so each one maps directly onto a koorbid in
     * pimpinanHarian() above - the profile's whole argument is that PCM Ambulu's structure
     * isn't decorative, every bidang has a program warga can point at. Ordered to match the
     * koorbid ordering (sosial/pengkaderan, tabligh, wakaf, dikdasmen, kesehatan, ekonomi).
     *
     * Six items is deliberate: it fills program-unggulan/standar's 3-column grid evenly at two
     * rows, and the Organization plan's `programs` limit (5) trims it to five real Program
     * records - still a full row plus two, not a lonely single card. The template's own preview
     * always shows all six.
     *
     * @return array<int, array{title: string, description: string, icon: string}>
     */
    public static function programItems(): array
    {
        return [
            [
                'title' => 'Kaderisasi Muda Ambulu',
                'description' => 'Pengajian kader, Darul Arqam, dan pendampingan Ortom (IPM, IMM, Pemuda, Nasyiah) untuk menyiapkan penerus persyarikatan di Ambulu.',
                'icon' => '🌱',
            ],
            [
                'title' => 'Tabligh & Dakwah Jamaah',
                'description' => 'Kajian rutin, khutbah terjadwal, dan pembinaan takmir masjid serta mushola di seluruh ranting Ambulu.',
                'icon' => '🕌',
            ],
            [
                'title' => 'Tertib Wakaf & Aset',
                'description' => 'Pendataan, sertifikasi, dan pengamanan legal tanah wakaf milik persyarikatan agar aman untuk generasi berikutnya.',
                'icon' => '📜',
            ],
            [
                'title' => 'Sekolah Muhammadiyah Unggul',
                'description' => 'Pembinaan mutu dan tata kelola AUM Pendidikan di Ambulu, dari sekolah dasar hingga menengah.',
                'icon' => '🎓',
            ],
            [
                'title' => 'Ambulu Sehat',
                'description' => 'Baksos kesehatan, posyandu, dan penguatan layanan AUM Kesehatan bersama Aisyiyah untuk warga yang membutuhkan.',
                'icon' => '🩺',
            ],
            [
                'title' => 'Ekonomi Jamaah',
                'description' => 'Pemberdayaan UMKM warga, pengelolaan Lazismu, dan penguatan usaha milik persyarikatan.',
                'icon' => '🤝',
            ],
        ];
    }

    /**
     * The cabang's affiliated Ortom and Amal Usaha. `type` is rendered as the small label under
     * each name by jaringan-aum-ortom/standar.blade.php. These are the standing Ortom every PCM
     * has plus the AUM categories Ambulu actually runs - a cabang adopting this template edits
     * the names to its own units rather than adding rows from scratch.
     *
     * Not plan-limited: 'jaringan-aum-ortom' has no PlanLimitService quota (see
     * CmsSampleDataSeeder::seedNetworks()), so all six survive on every plan - though that
     * seeder still writes its own generic placeholders rather than this list (see
     * PcmAmbuluTemplateSeeder's note on that gap).
     *
     * @return array<int, array{name: string, type: string}>
     */
    public static function jaringanItems(): array
    {
        return [
            ['name' => 'Pimpinan Cabang Aisyiyah Ambulu', 'type' => 'Ortom'],
            ['name' => 'Pemuda Muhammadiyah Ambulu', 'type' => 'Ortom'],
            ['name' => 'Nasyiatul Aisyiyah Ambulu', 'type' => 'Ortom'],
            ['name' => 'Ikatan Pelajar Muhammadiyah Ambulu', 'type' => 'Ortom'],
            ['name' => 'Perguruan Muhammadiyah Ambulu', 'type' => 'AUM Pendidikan'],
            ['name' => 'Klinik Pratama Aisyiyah Ambulu', 'type' => 'AUM Kesehatan'],
        ];
    }

    /**
     * Recurring cabang activities for the `agenda` section AND CmsSampleDataSeeder's Agenda
     * records. `days` is an offset from seeding time (that's the shape seedAgendas() expects),
     * so a freshly seeded site always shows upcoming - never stale - dates; the template's own
     * preview needs the pre-rendered date_day/date_month/date_year trio instead, which
     * agendaPreviewItems() below derives from these.
     *
     * @return array<int, array{title: string, days: int, location: string, description: string}>
     */
    public static function agendaItems(): array
    {
        return [
            [
                'title' => 'Pengajian Ahad Pagi Pimpinan dan Warga',
                'days' => 5,
                'location' => 'Masjid Cabang, Krajan Ambulu',
                'description' => '<p>Kajian rutin terbuka untuk seluruh warga persyarikatan dan masyarakat umum.</p>',
            ],
            [
                'title' => 'Rapat Koordinasi Pimpinan Harian dan Koorbid',
                'days' => 12,
                'location' => 'Kantor PCM Ambulu, Jl. dr. Soetomo No. 15',
                'description' => '<p>Evaluasi program tiap bidang dan penetapan agenda cabang bulan berikutnya.</p>',
            ],
            [
                'title' => 'Baksos Kesehatan Bersama Aisyiyah',
                'days' => 20,
                'location' => 'Balai Desa Ambulu',
                'description' => '<p>Pemeriksaan kesehatan gratis bagi warga bekerja sama dengan AUM Kesehatan Ambulu.</p>',
            ],
            [
                'title' => 'Darul Arqam Dasar Kader Muda',
                'days' => 28,
                'location' => 'Perguruan Muhammadiyah Ambulu',
                'description' => '<p>Pelatihan kader dasar bagi anggota Ortom se-Cabang Ambulu.</p>',
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
     * @return array<int, array<string, string>>
     */
    public static function agendaPreviewItems(): array
    {
        return array_map(function (array $item) {
            $date = now()->addDays($item['days'])->setTime(18, 0);

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
     * Kabar cabang for the `daftar-berita` section AND CmsSampleDataSeeder's Post records -
     * same stories in both places. Each maps to one of the programs above, so the news list
     * reads as evidence the programs are running rather than as filler.
     *
     * @return array<int, array{title: string, category: string, date: string, image: string, body: string}>
     */
    public static function beritaItems(): array
    {
        return [
            [
                'title' => 'Musyawarah Cabang Tetapkan Sembilan Pimpinan Harian PCM Ambulu',
                'category' => 'Organisasi',
                'date' => '24 Agt 2026',
                'image' => 'https://images.unsplash.com/photo-1591115765373-5207764f72e7?auto=format&fit=crop&w=700&q=80',
                'body' => 'Musyawarah Cabang menetapkan susunan Pimpinan Harian PCM Ambulu beserta enam koordinator bidang untuk periode berjalan.',
            ],
            [
                'title' => 'Sertifikasi Tanah Wakaf Ranting Rampung Bertahap',
                'category' => 'Wakaf',
                'date' => '17 Agt 2026',
                'image' => 'https://images.unsplash.com/photo-1450101499163-c8848c66ca85?auto=format&fit=crop&w=700&q=80',
                'body' => 'Bidang Wakaf melaporkan penyelesaian berkas sertifikasi sejumlah bidang tanah wakaf milik persyarikatan di Ambulu.',
            ],
            [
                'title' => 'Darul Arqam Dasar Cetak Puluhan Kader Muda Ambulu',
                'category' => 'Kaderisasi',
                'date' => '09 Agt 2026',
                'image' => 'https://images.unsplash.com/photo-1524178232363-1fb2b075b655?auto=format&fit=crop&w=700&q=80',
                'body' => 'Puluhan kader dari IPM, IMM, dan Pemuda Muhammadiyah mengikuti pelatihan kader dasar tingkat cabang.',
            ],
            [
                'title' => 'Baksos Kesehatan Cabang Layani Ratusan Warga',
                'category' => 'Sosial',
                'date' => '02 Agt 2026',
                'image' => 'https://images.unsplash.com/photo-1585036156171-384164a8c675?auto=format&fit=crop&w=700&q=80',
                'body' => 'Bekerja sama dengan Aisyiyah dan AUM Kesehatan, cabang menggelar pemeriksaan dan pengobatan gratis bagi warga.',
            ],
        ];
    }
}
