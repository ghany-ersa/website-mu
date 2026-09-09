<?php

namespace App\Services\Samples;

/**
 * Single source of truth for Suara Muhammadiyah Ambulu's real content - contact details and
 * every sample list (news, editorial team). Lives in app/ (not database/seeders/) for the
 * same reason as KlinikAisyiyahAmbuluSamples (see that class's doc comment): both
 * Database\Seeders\SuaraMuhammadiyahAmbuluTemplateSeeder (seed-only) and
 * App\Services\CmsSampleDataSeeder (runs in PRODUCTION on every organization creation) need
 * this content, and app/ code must not depend on database/seeders/.
 */
class SuaraMuhammadiyahAmbuluSamples
{
    /** Template slug this content is keyed to - see SuaraMuhammadiyahAmbuluTemplateSeeder::SLUG. */
    public const TEMPLATE_SLUG = 'suara-muhammadiyah-ambulu-eksklusif';

    /**
     * Suara Muhammadiyah Ambulu's real contact details - copied onto the seeded organization's
     * own contact fields by OrganizationSeeder, and used as CTA/contact defaults throughout the
     * template structure below.
     */
    public const WHATSAPP = '085183220977';

    public const INSTAGRAM = 'https://www.instagram.com/suaramuhammadiyahambulu';

    public const TIKTOK = 'https://www.tiktok.com/@suaramuhammadiyahambulu';

    /**
     * The redaksi's own pre-existing site, distinct from its website-mu.id-hosted profile.
     * Kept here as background reference only - not linked anywhere in
     * SuaraMuhammadiyahAmbuluTemplateSeeder's structure, per the organization's own request
     * not to publish it on the seeded site.
     */
    public const WEBSITE = 'https://lynk.id/suaramuhammadiyahambulu';

    /**
     * Stand-in editorial/newsroom photography (Unsplash) - Suara Muhammadiyah Ambulu's own
     * photo library isn't reachable from this app, so these are clearly generic images meant
     * to be swapped for the redaksi's own once it uploads them.
     */
    public const HERO_IMAGE = 'https://images.unsplash.com/photo-1495020689067-958852a7765e?auto=format&fit=crop&w=1400&q=80';

    public const ABOUT_IMAGE = 'https://images.unsplash.com/photo-1504711434969-e33886168f5c?auto=format&fit=crop&w=1000&q=80';

    /**
     * The 16-member editorial team, exactly as given by the organization. `role` groups
     * multiple people under the same desk (e.g. two Graphic Designers) the way
     * struktur-pengurus/modern.blade.php expects: a flat {name, role} list, one card per
     * person, ordered by how the redaksi introduces itself (pimpinan first, then desk by
     * desk). Fina was listed on both Social Media and Journalist in the organization's own
     * roster; per their own call she's placed on Jurnalis only, keeping Social Media Lead to
     * Ardava alone.
     *
     * @return array<int, array{name: string, role: string}>
     */
    public static function timRedaksi(): array
    {
        return [
            ['name' => 'Bagas', 'role' => 'Pemimpin Redaksi'],
            ['name' => 'Iim', 'role' => 'Sekretaris'],
            ['name' => 'Figa', 'role' => 'Bendahara'],
            ['name' => 'Ghany', 'role' => 'Business Development'],
            ['name' => 'Aam', 'role' => 'People Development'],
            ['name' => 'Ardava', 'role' => 'Social Media Lead'],
            ['name' => 'Mazfiar', 'role' => 'Humas'],
            ['name' => 'Ikhsan', 'role' => 'Jurnalis'],
            ['name' => 'Fina', 'role' => 'Jurnalis'],
            ['name' => 'Radin', 'role' => 'Graphic Designer'],
            ['name' => 'Falus', 'role' => 'Graphic Designer'],
            ['name' => 'Rayhan', 'role' => 'Graphic Designer'],
            ['name' => 'Richard', 'role' => 'Graphic Designer'],
            ['name' => 'Parama', 'role' => 'Video Editor'],
            ['name' => 'Ajeng', 'role' => 'Video Editor'],
            ['name' => 'Meydiano', 'role' => 'Video Editor'],
        ];
    }

    /**
     * News/kabar items for the template's `daftar-berita` sections AND CmsSampleDataSeeder's
     * Post records - same stories in both places. Deliberately tagged into exactly two
     * categories, 'Organisasi' and 'Kaderisasi', rather than a wider set: the Berita page
     * shows two daftar-berita sections filtered by category_filter (see
     * SuaraMuhammadiyahAmbuluTemplateSeeder), and that filter only supports one positive exact
     * match with no negation - so a clean, non-duplicating split against LIVE CMS data (not
     * just this static list) needs the categories to already be exactly the two groups the
     * page displays. 'Organisasi' covers institutional/AUM/Ortom program coverage (cabang
     * meetings, Lazismu, klinik, kajian, sekolah, panti asuhan, koperasi); 'Kaderisasi' covers
     * youth/cadre training specifically.
     *
     * @return array<int, array{title: string, category: string, date: string, image: string, body: string}>
     */
    public static function beritaItems(): array
    {
        return [
            [
                'title' => 'PCM Ambulu Gelar Rapat Persiapan Musyawarah Cabang',
                'category' => 'Organisasi',
                'date' => '18 Agt 2026',
                'image' => 'https://images.unsplash.com/photo-1591115765373-5207764f72e7?auto=format&fit=crop&w=700&q=80',
                'body' => 'Pimpinan Cabang Muhammadiyah Ambulu menggelar rapat koordinasi lintas Ortom dan AUM untuk mematangkan agenda musyawarah cabang mendatang.',
            ],
            [
                'title' => 'Lazismu Ambulu Salurkan Bantuan untuk Korban Bencana di Jember Selatan',
                'category' => 'Organisasi',
                'date' => '12 Agt 2026',
                'image' => 'https://images.unsplash.com/photo-1593113598332-cd288d649433?auto=format&fit=crop&w=700&q=80',
                'body' => 'Bantuan logistik dan tenaga medis dikerahkan Lazismu Ambulu bersama MDMC untuk membantu warga terdampak di wilayah selatan Jember.',
            ],
            [
                'title' => 'Klinik Pratama Aisyiyah Ambulu Gelar Baksos Kesehatan Gratis',
                'category' => 'Organisasi',
                'date' => '07 Agt 2026',
                'image' => 'https://images.unsplash.com/photo-1585036156171-384164a8c675?auto=format&fit=crop&w=700&q=80',
                'body' => 'Puluhan warga mengikuti pemeriksaan kesehatan gratis yang digelar amal usaha kesehatan Aisyiyah Ambulu di Balai Desa.',
            ],
            [
                'title' => 'Kajian Ahad Pagi Bahas Fikih Muamalah bagi Pelaku UMKM Muhammadiyah',
                'category' => 'Organisasi',
                'date' => '02 Agt 2026',
                'image' => 'https://images.unsplash.com/photo-1547347298-4074fc3086f0?auto=format&fit=crop&w=700&q=80',
                'body' => 'Kajian rutin pekan ini menghadirkan pembahasan fikih muamalah yang relevan bagi para pelaku usaha kecil di lingkungan Muhammadiyah Ambulu.',
            ],
            [
                'title' => 'SD Muhammadiyah 1 Ambulu Raih Juara Umum Lomba Sains se-Kecamatan',
                'category' => 'Organisasi',
                'date' => '28 Jul 2026',
                'image' => 'https://images.unsplash.com/photo-1503676260728-1c00da094a0b?auto=format&fit=crop&w=700&q=80',
                'body' => 'Prestasi membanggakan ditorehkan siswa-siswi SD Muhammadiyah 1 Ambulu dalam ajang olimpiade sains tingkat kecamatan.',
            ],
            [
                'title' => 'Pemuda Muhammadiyah Ambulu Adakan Pelatihan Jurnalistik Dasar',
                'category' => 'Kaderisasi',
                'date' => '21 Jul 2026',
                'image' => 'https://images.unsplash.com/photo-1552664730-d307ca884978?auto=format&fit=crop&w=700&q=80',
                'body' => 'Pelatihan ini menjadi bagian dari upaya kaderisasi jurnalis muda Muhammadiyah di lingkungan Ambulu.',
            ],
            [
                'title' => 'Aisyiyah Ambulu Gelar Pengajian Rutin dan Santunan Yatim Piatu',
                'category' => 'Organisasi',
                'date' => '16 Jul 2026',
                'image' => 'https://images.unsplash.com/photo-1602880655958-8d51a3d1e83c?auto=format&fit=crop&w=700&q=80',
                'body' => 'Puluhan anak yatim piatu menerima santunan dalam pengajian rutin yang digelar Pimpinan Cabang Aisyiyah Ambulu.',
            ],
            [
                'title' => 'Panti Asuhan Muhammadiyah Ambulu Terima Bantuan Sembako dari Warga',
                'category' => 'Organisasi',
                'date' => '09 Jul 2026',
                'image' => 'https://images.unsplash.com/photo-1593113646773-028c64a8f1b8?auto=format&fit=crop&w=700&q=80',
                'body' => 'Donasi sembako dari warga dan simpatisan disalurkan langsung untuk kebutuhan sehari-hari anak asuh di Panti Asuhan Muhammadiyah Ambulu.',
            ],
            [
                'title' => 'IMM dan IPM Ambulu Gelar Diskusi Publik Kepemimpinan Muda Berkemajuan',
                'category' => 'Kaderisasi',
                'date' => '03 Jul 2026',
                'image' => 'https://images.unsplash.com/photo-1531482615713-2afd69097998?auto=format&fit=crop&w=700&q=80',
                'body' => 'Diskusi lintas Ortom ini mengangkat tema kepemimpinan muda berkemajuan, dihadiri kader IMM dan IPM se-Ambulu.',
            ],
            [
                'title' => 'Koperasi Syariah Muhammadiyah Ambulu Dorong Ekonomi UMKM Warga',
                'category' => 'Organisasi',
                'date' => '26 Jun 2026',
                'image' => 'https://images.unsplash.com/photo-1556740738-b6a63e27c4df?auto=format&fit=crop&w=700&q=80',
                'body' => 'Pembiayaan syariah tanpa riba disalurkan koperasi milik Muhammadiyah Ambulu untuk mendorong pertumbuhan usaha mikro warga.',
            ],
            [
                'title' => 'Hizbul Wathan Ambulu Gelar Perkemahan Kader Penggalang',
                'category' => 'Kaderisasi',
                'date' => '19 Jun 2026',
                'image' => 'https://images.unsplash.com/photo-1504280390367-361c6d9f38f4?auto=format&fit=crop&w=700&q=80',
                'body' => 'Perkemahan akhir pekan ini melatih kedisiplinan dan jiwa kepanduan kader penggalang Hizbul Wathan se-Ambulu.',
            ],
            [
                'title' => 'Tapak Suci Ambulu Buka Pendidikan Dasar Kader Pendekar Baru',
                'category' => 'Kaderisasi',
                'date' => '12 Jun 2026',
                'image' => 'https://images.unsplash.com/photo-1555597673-b21d5c935865?auto=format&fit=crop&w=700&q=80',
                'body' => 'Puluhan siswa mengikuti pendidikan dasar kader Tapak Suci Putera Muhammadiyah sebagai jenjang awal sebelum resmi menjadi anggota.',
            ],
            [
                'title' => 'Nasyiatul Aisyiyah Ambulu Latih Kader Muda Fasilitator Kajian',
                'category' => 'Kaderisasi',
                'date' => '05 Jun 2026',
                'image' => 'https://images.unsplash.com/photo-1544717305-2782549b5136?auto=format&fit=crop&w=700&q=80',
                'body' => 'Pelatihan ini menyiapkan kader muda Nasyiatul Aisyiyah agar mampu memfasilitasi kajian keputrian di tingkat ranting.',
            ],
            [
                'title' => 'Darul Arqam Dasar Bekali Kader Baru Muhammadiyah Ambulu dengan Ideologi Persyarikatan',
                'category' => 'Kaderisasi',
                'date' => '29 Mei 2026',
                'image' => 'https://images.unsplash.com/photo-1524178232363-1fb2b075b655?auto=format&fit=crop&w=700&q=80',
                'body' => 'Pelatihan wajib bagi kader baru ini membekali pemahaman ideologi Muhammadiyah sebelum aktif di Ortom maupun Amal Usaha.',
            ],
            [
                'title' => 'PDM Jember Kunjungi PCM Ambulu, Bahas Sinergi Program Dakwah Kawasan Selatan',
                'category' => 'Organisasi',
                'date' => '22 Mei 2026',
                'image' => 'https://images.unsplash.com/photo-1560439514-4e9645039924?auto=format&fit=crop&w=700&q=80',
                'body' => 'Kunjungan silaturahmi Pimpinan Daerah Muhammadiyah Jember ke PCM Ambulu menghasilkan kesepakatan sinergi program dakwah di wilayah selatan.',
            ],
            [
                'title' => 'Masjid Al-Furqan Binaan Muhammadiyah Ambulu Rampungkan Renovasi Menara',
                'category' => 'Organisasi',
                'date' => '15 Mei 2026',
                'image' => 'https://images.unsplash.com/photo-1542379653-b198e0eeaa46?auto=format&fit=crop&w=700&q=80',
                'body' => 'Renovasi menara Masjid Al-Furqan rampung setelah dua bulan pengerjaan, didukung donasi warga dan simpatisan Muhammadiyah Ambulu.',
            ],
            [
                'title' => 'MDMC Ambulu Gelar Simulasi Tanggap Bencana bagi Warga Pesisir',
                'category' => 'Organisasi',
                'date' => '08 Mei 2026',
                'image' => 'https://images.unsplash.com/photo-1518709268805-4e9042af2176?auto=format&fit=crop&w=700&q=80',
                'body' => 'Simulasi evakuasi dan pertolongan pertama digelar MDMC Ambulu untuk meningkatkan kesiapsiagaan warga pesisir menghadapi bencana.',
            ],
            [
                'title' => 'SMP Muhammadiyah Ambulu Luncurkan Program Tahfidz Intensif',
                'category' => 'Organisasi',
                'date' => '01 Mei 2026',
                'image' => 'https://images.unsplash.com/photo-1580582932707-520aed937b7b?auto=format&fit=crop&w=700&q=80',
                'body' => 'Program tahfidz intensif diluncurkan untuk mendorong siswa SMP Muhammadiyah Ambulu menghafal Al-Qur\'an sejak usia dini.',
            ],
            [
                'title' => 'IPM Ambulu Gelar Sekolah Kader Taruna Melati I',
                'category' => 'Kaderisasi',
                'date' => '24 Apr 2026',
                'image' => 'https://images.unsplash.com/photo-1509062522246-3755977927d7?auto=format&fit=crop&w=700&q=80',
                'body' => 'Sekolah Kader Taruna Melati I diikuti puluhan pelajar sebagai jenjang perkaderan dasar Ikatan Pelajar Muhammadiyah se-Ambulu.',
            ],
            [
                'title' => 'Lazismu Ambulu Buka Program Beasiswa untuk Anak Yatim dan Dhuafa',
                'category' => 'Organisasi',
                'date' => '17 Apr 2026',
                'image' => 'https://images.unsplash.com/photo-1523240795612-9a054b0db644?auto=format&fit=crop&w=700&q=80',
                'body' => 'Program beasiswa pendidikan bagi anak yatim dan dhuafa resmi dibuka Lazismu Ambulu untuk tahun ajaran mendatang.',
            ],
            [
                'title' => 'Aisyiyah Ambulu Adakan Pelatihan Kewirausahaan bagi Ibu Rumah Tangga',
                'category' => 'Organisasi',
                'date' => '10 Apr 2026',
                'image' => 'https://images.unsplash.com/photo-1556740758-90de374c12ad?auto=format&fit=crop&w=700&q=80',
                'body' => 'Pelatihan kewirausahaan digelar Pimpinan Cabang Aisyiyah Ambulu untuk membekali ibu rumah tangga keterampilan usaha rumahan.',
            ],
            [
                'title' => 'Hizbul Wathan Ambulu Ikuti Jambore Daerah di Jember',
                'category' => 'Kaderisasi',
                'date' => '03 Apr 2026',
                'image' => 'https://images.unsplash.com/photo-1504196606672-aef5c9cefc92?auto=format&fit=crop&w=700&q=80',
                'body' => 'Kontingen Hizbul Wathan Ambulu tampil dalam Jambore Daerah, mengikuti berbagai lomba kepanduan tingkat Jawa Timur.',
            ],
            [
                'title' => 'Klinik Pratama Aisyiyah Ambulu Perluas Layanan Poli Gigi',
                'category' => 'Organisasi',
                'date' => '27 Mar 2026',
                'image' => 'https://images.unsplash.com/photo-1629909613654-28e377c37b09?auto=format&fit=crop&w=700&q=80',
                'body' => 'Layanan poli gigi resmi dibuka Klinik Pratama Aisyiyah Ambulu untuk melengkapi fasilitas kesehatan bagi warga sekitar.',
            ],
            [
                'title' => 'Nasyiatul Aisyiyah Ambulu Gelar Bakti Sosial di Panti Jompo',
                'category' => 'Organisasi',
                'date' => '20 Mar 2026',
                'image' => 'https://images.unsplash.com/photo-1509099395029-9df6205f8f11?auto=format&fit=crop&w=700&q=80',
                'body' => 'Kunjungan dan bakti sosial digelar Nasyiatul Aisyiyah Ambulu untuk menghibur dan berbagi kebutuhan pokok bagi lansia panti jompo.',
            ],
            [
                'title' => 'Tapak Suci Ambulu Raih Medali Emas di Kejuaraan Pencak Silat Daerah',
                'category' => 'Kaderisasi',
                'date' => '13 Mar 2026',
                'image' => 'https://images.unsplash.com/photo-1555597408-26bc6bf03390?auto=format&fit=crop&w=700&q=80',
                'body' => 'Atlet muda Tapak Suci Putera Muhammadiyah Ambulu berhasil menyabet medali emas dalam kejuaraan pencak silat tingkat daerah.',
            ],
            [
                'title' => 'Koperasi Syariah Muhammadiyah Ambulu Gelar RAT Tahun Buku 2025',
                'category' => 'Organisasi',
                'date' => '06 Mar 2026',
                'image' => 'https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?auto=format&fit=crop&w=700&q=80',
                'body' => 'Rapat Anggota Tahunan membahas capaian usaha dan pembagian sisa hasil usaha koperasi syariah milik Muhammadiyah Ambulu.',
            ],
            [
                'title' => 'Panti Asuhan Muhammadiyah Ambulu Gelar Buka Puasa Bersama Anak Yatim',
                'category' => 'Organisasi',
                'date' => '27 Feb 2026',
                'image' => 'https://images.unsplash.com/photo-1547153760-18fc86324498?auto=format&fit=crop&w=700&q=80',
                'body' => 'Acara buka puasa bersama digelar untuk mempererat kekeluargaan antara pengasuh, anak asuh, dan donatur Panti Asuhan Muhammadiyah Ambulu.',
            ],
            [
                'title' => 'Pemuda Muhammadiyah Ambulu Gelar Turnamen Futsal Antar Ranting',
                'category' => 'Kaderisasi',
                'date' => '20 Feb 2026',
                'image' => 'https://images.unsplash.com/photo-1517649763962-0c623066013b?auto=format&fit=crop&w=700&q=80',
                'body' => 'Turnamen futsal antar ranting digelar Pemuda Muhammadiyah Ambulu untuk mempererat silaturahmi sekaligus menjaring bibit atlet muda.',
            ],
            [
                'title' => 'SD Muhammadiyah 1 Ambulu Resmikan Perpustakaan Digital',
                'category' => 'Organisasi',
                'date' => '13 Feb 2026',
                'image' => 'https://images.unsplash.com/photo-1521587760476-6c12a4b040da?auto=format&fit=crop&w=700&q=80',
                'body' => 'Perpustakaan digital diresmikan untuk memudahkan siswa SD Muhammadiyah 1 Ambulu mengakses bahan bacaan secara daring.',
            ],
            [
                'title' => 'IMM Ambulu Adakan Diskusi Publik Bertema Ekonomi Kerakyatan',
                'category' => 'Kaderisasi',
                'date' => '06 Feb 2026',
                'image' => 'https://images.unsplash.com/photo-1560439514-07d4d84fbe74?auto=format&fit=crop&w=700&q=80',
                'body' => 'Diskusi publik menghadirkan akademisi dan praktisi membahas penguatan ekonomi kerakyatan di kalangan kader Ikatan Mahasiswa Muhammadiyah.',
            ],
            [
                'title' => 'Lazismu Ambulu Gelar Layanan Kesehatan Keliling ke Desa Terpencil',
                'category' => 'Organisasi',
                'date' => '30 Jan 2026',
                'image' => 'https://images.unsplash.com/photo-1576091160399-112ba8d25d1d?auto=format&fit=crop&w=700&q=80',
                'body' => 'Layanan kesehatan keliling menjangkau desa terpencil di wilayah Ambulu, memberi pemeriksaan dan obat gratis bagi warga.',
            ],
            [
                'title' => 'PCM Ambulu Gelar Musyawarah Cabang, Tetapkan Program Kerja Lima Tahun',
                'category' => 'Organisasi',
                'date' => '23 Jan 2026',
                'image' => 'https://images.unsplash.com/photo-1517048676732-d65bc937f952?auto=format&fit=crop&w=700&q=80',
                'body' => 'Musyawarah Cabang menetapkan susunan pimpinan dan program kerja lima tahun ke depan bagi Pimpinan Cabang Muhammadiyah Ambulu.',
            ],
            [
                'title' => 'Fortasi Ranting Bekali Siswa Baru dengan Nilai-Nilai Kemuhammadiyahan',
                'category' => 'Kaderisasi',
                'date' => '16 Jan 2026',
                'image' => 'https://images.unsplash.com/photo-1523050854058-8df90110c9f1?auto=format&fit=crop&w=700&q=80',
                'body' => 'Forum Taaruf Siswa memperkenalkan nilai-nilai kemuhammadiyahan kepada siswa baru di sekolah-sekolah Muhammadiyah se-Ambulu.',
            ],
            [
                'title' => 'Aisyiyah Ambulu Salurkan Bantuan Alat Belajar untuk PAUD Binaan',
                'category' => 'Organisasi',
                'date' => '09 Jan 2026',
                'image' => 'https://images.unsplash.com/photo-1490373892916-969e2ee94d38?auto=format&fit=crop&w=700&q=80',
                'body' => 'Bantuan alat peraga dan buku belajar disalurkan untuk mendukung kegiatan belajar mengajar di PAUD binaan Aisyiyah Ambulu.',
            ],
            [
                'title' => 'Tapak Suci dan Hizbul Wathan Gelar Latihan Gabungan Jelang Milad Muhammadiyah',
                'category' => 'Kaderisasi',
                'date' => '02 Jan 2026',
                'image' => 'https://images.unsplash.com/photo-1517649763962-0c623066013b?auto=format&fit=crop&w=700&q=80',
                'body' => 'Latihan gabungan digelar sebagai persiapan penampilan Tapak Suci dan Hizbul Wathan pada perayaan Milad Muhammadiyah mendatang.',
            ],
            [
                'title' => 'PCM Ambulu Gelar Perayaan Milad Muhammadiyah ke-114 Tingkat Cabang',
                'category' => 'Organisasi',
                'date' => '26 Des 2025',
                'image' => 'https://images.unsplash.com/photo-1531058020387-3be344556be6?auto=format&fit=crop&w=700&q=80',
                'body' => 'Perayaan Milad Muhammadiyah ke-114 tingkat cabang dimeriahkan pawai, panggung gembira, dan santunan bagi kaum dhuafa se-Ambulu.',
            ],
            [
                'title' => 'Klinik Pratama Aisyiyah Ambulu Adakan Penyuluhan Gizi Balita',
                'category' => 'Organisasi',
                'date' => '19 Des 2025',
                'image' => 'https://images.unsplash.com/photo-1576765607924-3f7b8c172eae?auto=format&fit=crop&w=700&q=80',
                'body' => 'Penyuluhan gizi balita digelar Klinik Pratama Aisyiyah Ambulu bekerja sama dengan Posyandu setempat guna menekan angka stunting.',
            ],
            [
                'title' => 'Nasyiatul Aisyiyah Ambulu Ikuti Pelatihan Kader Fasilitator Keluarga Sakinah',
                'category' => 'Kaderisasi',
                'date' => '12 Des 2025',
                'image' => 'https://images.unsplash.com/photo-1544717305-2782549b5136?auto=format&fit=crop&w=700&q=80',
                'body' => 'Pelatihan fasilitator keluarga sakinah diikuti kader Nasyiatul Aisyiyah Ambulu untuk mendukung program bimbingan pranikah di tingkat ranting.',
            ],
            [
                'title' => 'Koperasi Syariah Muhammadiyah Ambulu Luncurkan Layanan Pembiayaan UMKM Digital',
                'category' => 'Organisasi',
                'date' => '05 Des 2025',
                'image' => 'https://images.unsplash.com/photo-1556742111-a301076d9d18?auto=format&fit=crop&w=700&q=80',
                'body' => 'Layanan pembiayaan digital diluncurkan agar pelaku UMKM binaan Muhammadiyah Ambulu lebih mudah mengakses modal usaha syariah.',
            ],
            [
                'title' => 'IPM dan IMM Ambulu Gelar Baksos Akhir Tahun untuk Warga Terdampak Banjir',
                'category' => 'Kaderisasi',
                'date' => '28 Nov 2025',
                'image' => 'https://images.unsplash.com/photo-1509099395029-9df6205f8f11?auto=format&fit=crop&w=700&q=80',
                'body' => 'Bakti sosial akhir tahun digelar kader IPM dan IMM Ambulu untuk membantu warga yang terdampak banjir di beberapa desa binaan.',
            ],
        ];
    }

    /**
     * Institutional coverage: everything in beritaItems() NOT tagged 'Kaderisasi'. Positive
     * filter (not "everything except Kaderisasi" against the live table) so
     * SuaraMuhammadiyahAmbuluTemplateSeeder's 'Kabar Persyarikatan' section can pass this same
     * list to `category_filter => 'Organisasi'` and mean it, both in template preview and once
     * a real organization has its own Post records.
     *
     * @return array<int, array{title: string, category: string, date: string, image: string, body: string}>
     */
    public static function institutionalItems(): array
    {
        return array_values(array_filter(
            self::beritaItems(),
            fn (array $item) => $item['category'] === 'Organisasi',
        ));
    }

    /**
     * Kaderisasi (cadre/youth training) coverage - the other half of the 'Kabar Kaderisasi &
     * Ortom' section's disjoint split from institutionalItems() above.
     *
     * @return array<int, array{title: string, category: string, date: string, image: string, body: string}>
     */
    public static function kaderisasiItems(): array
    {
        return array_values(array_filter(
            self::beritaItems(),
            fn (array $item) => $item['category'] === 'Kaderisasi',
        ));
    }
}
