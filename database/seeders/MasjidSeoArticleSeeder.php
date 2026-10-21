<?php

namespace Database\Seeders;

use App\Enums\PublishStatus;
use App\Models\Article;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Three blog articles forming an SEO content cluster around /website-masjid: each targets one
 * long-tail query ("cara membuat website masjid", "laporan keuangan masjid", "donasi masjid
 * online"), links to the other two, and links to the landing page with a keyword anchor.
 *
 * Run by hand, not from DatabaseSeeder (which mirrors the articles already in production):
 *   php artisan db:seed --class=MasjidSeoArticleSeeder --force
 *
 * firstOrCreate by slug, so re-running never overwrites an admin's later edits. Seeded as Draft
 * so the copy is reviewed (and given a cover image) in the admin before going public.
 *
 * Links are root-relative ("/website-masjid") rather than route() output: the body is stored
 * HTML, and an absolute URL would bake in whatever APP_URL the seeder happened to run under.
 * The opening paragraph doubles as the meta description (articles/show.blade.php takes the
 * first 160 characters of the body), so each one leads with its target keyword.
 */
class MasjidSeoArticleSeeder extends Seeder
{
    public const SLUGS = [
        'website' => 'cara-membuat-website-masjid-tanpa-coding',
        'keuangan' => 'cara-membuat-laporan-keuangan-masjid-transparan',
        'donasi' => 'cara-mengelola-donasi-dan-wakaf-masjid-online',
    ];

    public function run(): void
    {
        $author = User::where('email', 'admin@website-mu.id')->first();

        foreach ($this->articles() as $article) {
            Article::firstOrCreate(
                ['slug' => $article['slug']],
                $article + [
                    'author_id' => $author?->id,
                    'category' => 'Masjid',
                    'cover_image' => null,
                    'status' => PublishStatus::Draft,
                    'published_at' => null,
                ],
            );
        }
    }

    /**
     * @return array<int, array{slug: string, title: string, body: string}>
     */
    private function articles(): array
    {
        $website = '/berita/'.self::SLUGS['website'];
        $keuangan = '/berita/'.self::SLUGS['keuangan'];
        $donasi = '/berita/'.self::SLUGS['donasi'];

        return [
            [
                'slug' => self::SLUGS['website'],
                'title' => 'Cara Membuat Website Masjid Tanpa Coding: Panduan Lengkap untuk Takmir',
                'body' => <<<HTML
                    <p>Cara membuat website masjid kini tidak lagi butuh programmer atau biaya jutaan rupiah. Dengan platform no-code, takmir bisa menerbitkan website resmi masjid dalam kurang dari 30 menit. Panduan ini membahas langkah demi langkahnya.</p>
                    <h2>Mengapa Masjid Perlu Website Sendiri?</h2>
                    <p>Media sosial dan grup WhatsApp memang ramai, tetapi informasinya cepat tenggelam. Pengumuman petugas Jumat minggu lalu sudah tertimbun ratusan pesan, dan jamaah baru di sekitar masjid tidak tahu harus mencari informasi ke mana.</p>
                    <p>Website masjid menjadi <strong>wajah resmi</strong> yang bisa ditemukan lewat Google kapan saja. Di sana jamaah bisa melihat jadwal kajian, profil takmir, program donasi, hingga laporan keuangan. Semua di satu alamat yang mudah dibagikan.</p>
                    <h2>Apa Saja yang Perlu Ada di Website Masjid?</h2>
                    <p>Sebelum mulai, siapkan dulu isi yang paling sering dicari jamaah:</p>
                    <ul>
                        <li><p><strong>Profil masjid</strong>: sejarah singkat, visi, dan foto bangunan.</p></li>
                        <li><p><strong>Susunan takmir</strong>: ketua, sekretaris, bendahara, dan seksi-seksi.</p></li>
                        <li><p><strong>Jadwal kajian dan agenda</strong>: kajian rutin, pengajian ahad pagi, kegiatan Ramadan.</p></li>
                        <li><p><strong>Pengumuman</strong>: petugas Jumat, jadwal Idul Fitri dan Idul Adha, kerja bakti.</p></li>
                        <li><p><strong>Fasilitas</strong>: tempat wudhu, perpustakaan, TPA, aula, parkir.</p></li>
                        <li><p><strong>Program donasi dan wakaf</strong> beserta progresnya.</p></li>
                        <li><p><strong>Laporan keuangan</strong> kas masjid per bulan.</p></li>
                        <li><p><strong>Lokasi dan kontak</strong>: peta, alamat, dan nomor WhatsApp takmir.</p></li>
                    </ul>
                    <p>Tidak harus lengkap di hari pertama. Mulailah dari profil, takmir, jadwal kajian, dan lokasi, lalu lengkapi sedikit demi sedikit.</p>
                    <h2>Langkah Membuat Website Masjid</h2>
                    <h3>1. Pilih template website masjid</h3>
                    <p>Template menentukan tampilan dan susunan halaman. Pilih yang sudah dirancang khusus untuk masjid, sehingga bagian seperti jadwal kajian, fasilitas, dan donasi sudah tersedia tanpa perlu dirakit sendiri. Anda bisa melihat contohnya di halaman <a href="/website-masjid">website masjid</a> website-mu.id.</p>
                    <h3>2. Sesuaikan identitas masjid</h3>
                    <p>Unggah logo, pilih warna yang sesuai karakter masjid, lalu ganti nama dan foto utama. Langkah ini membuat website langsung terasa milik masjid Anda, bukan template umum.</p>
                    <h3>3. Isi konten lewat formulir</h3>
                    <p>Di platform no-code, setiap bagian diisi lewat formulir sederhana: nama dan jabatan takmir, judul dan waktu kajian, foto kegiatan. Tidak ada kode yang perlu disentuh.</p>
                    <h3>4. Ajak pengurus lain ikut mengelola</h3>
                    <p>Website masjid yang hidup adalah yang rutin diperbarui. Tambahkan sekretaris, bendahara, atau seksi dakwah sebagai pengelola agar pembaruan tidak bergantung pada satu orang.</p>
                    <h3>5. Terbitkan dan bagikan</h3>
                    <p>Setelah diterbitkan, bagikan alamat website ke grup WhatsApp jamaah, cantumkan di spanduk dan brosur, serta tempel kode QR-nya di papan pengumuman masjid.</p>
                    <h2>Tips Agar Website Masjid Ramai Dikunjungi</h2>
                    <ul>
                        <li><p><strong>Perbarui jadwal kajian setiap pekan.</strong> Ini konten yang paling sering dicari jamaah.</p></li>
                        <li><p><strong>Tampilkan laporan keuangan secara rutin.</strong> Transparansi membangun kepercayaan. Baca panduan <a href="{$keuangan}">cara membuat laporan keuangan masjid yang transparan</a>.</p></li>
                        <li><p><strong>Buka program donasi dengan target yang jelas.</strong> Lihat <a href="{$donasi}">cara mengelola donasi dan wakaf masjid secara online</a>.</p></li>
                        <li><p><strong>Unggah dokumentasi kegiatan.</strong> Foto kajian dan bakti sosial menunjukkan masjid yang hidup.</p></li>
                    </ul>
                    <h2>Mulai Sekarang</h2>
                    <p>Setiap masjid punya keunggulan yang layak dikenal lebih luas. Website adalah cara paling mudah untuk menunjukkannya kepada jamaah dan masyarakat. <a href="/website-masjid">Buat website masjid Anda di website-mu.id</a>, coba gratis dan rancang sendiri tanpa coding.</p>
                    HTML,
            ],
            [
                'slug' => self::SLUGS['keuangan'],
                'title' => 'Cara Membuat Laporan Keuangan Masjid yang Transparan dan Dipercaya Jamaah',
                'body' => <<<HTML
                    <p>Laporan keuangan masjid yang transparan adalah kunci kepercayaan jamaah dan donatur. Panduan ini menjelaskan cara menyusun laporan kas masjid yang rapi, mudah dipahami, dan bisa dipublikasikan secara rutin.</p>
                    <h2>Mengapa Transparansi Keuangan Masjid Penting?</h2>
                    <p>Dana masjid adalah amanah umat. Setiap rupiah infak, sedekah, dan wakaf yang diterima perlu dipertanggungjawabkan. Ketika jamaah bisa melihat sendiri ke mana dana digunakan, mereka lebih yakin untuk terus berinfak, dan takmir terhindar dari prasangka.</p>
                    <p>Pengumuman saldo kas setiap Jumat sudah menjadi tradisi baik. Namun pengumuman lisan hanya didengar jamaah yang hadir, dan rinciannya tidak bisa dilihat ulang. Laporan tertulis yang dipublikasikan melengkapi tradisi tersebut.</p>
                    <h2>Komponen Laporan Keuangan Masjid</h2>
                    <p>Laporan kas masjid yang sederhana namun lengkap setidaknya memuat:</p>
                    <ol>
                        <li><p><strong>Periode laporan</strong>, misalnya bulanan.</p></li>
                        <li><p><strong>Saldo awal</strong>, yaitu sisa kas dari periode sebelumnya.</p></li>
                        <li><p><strong>Pemasukan</strong> per kategori: kotak amal Jumat, infak harian, donasi program, wakaf, dan lainnya.</p></li>
                        <li><p><strong>Pengeluaran</strong> per kategori: listrik dan air, honor petugas kebersihan, kegiatan kajian, perawatan bangunan, santunan sosial.</p></li>
                        <li><p><strong>Saldo akhir</strong>, yaitu saldo awal ditambah pemasukan dikurangi pengeluaran.</p></li>
                    </ol>
                    <p>Pisahkan dana yang terikat, seperti donasi untuk pembangunan atau wakaf, dari kas operasional. Dana terikat hanya boleh dipakai sesuai tujuan yang diniatkan donatur. Untuk masjid yang lebih besar, format laporan bisa mengacu pada ISAK 35 tentang penyajian laporan keuangan entitas berorientasi nonlaba.</p>
                    <h2>Langkah Menyusun Laporan Keuangan Masjid</h2>
                    <h3>1. Catat setiap transaksi di hari yang sama</h3>
                    <p>Bendahara mencatat tanggal, kategori, keterangan, dan nominal setiap uang masuk atau keluar. Pencatatan yang ditunda sering berakhir lupa.</p>
                    <h3>2. Simpan bukti transaksi</h3>
                    <p>Nota, kuitansi, dan bukti transfer disimpan rapi per bulan. Bukti ini dibutuhkan saat laporan diperiksa.</p>
                    <h3>3. Hitung kotak amal bersama saksi</h3>
                    <p>Pembukaan dan penghitungan kotak amal sebaiknya dilakukan minimal dua orang, lalu hasilnya ditandatangani bersama.</p>
                    <h3>4. Rekap di akhir bulan</h3>
                    <p>Jumlahkan pemasukan dan pengeluaran per kategori, lalu cocokkan saldo akhir dengan uang tunai dan saldo rekening masjid.</p>
                    <h3>5. Publikasikan kepada jamaah</h3>
                    <p>Tempel ringkasan di papan pengumuman, umumkan saat Jumat, dan yang paling awet: <strong>tampilkan di website masjid</strong> agar bisa dilihat kapan saja, termasuk oleh donatur yang tinggal jauh.</p>
                    <h2>Menampilkan Laporan Keuangan di Website Masjid</h2>
                    <p>Dengan fitur laporan keuangan di <a href="/website-masjid">website masjid website-mu.id</a>, bendahara cukup mencatat pemasukan dan pengeluaran per bulan dari dashboard. Laporan otomatis tersusun rapi per periode di website, tanpa perlu membuat file atau desain terpisah setiap bulan.</p>
                    <p>Transparansi keuangan juga memperkuat program penggalangan dana. Donatur yang melihat laporan rutin lebih percaya untuk ikut program berikutnya. Pelajari <a href="{$donasi}">cara mengelola donasi dan wakaf masjid secara online</a>.</p>
                    <h2>Kesimpulan</h2>
                    <p>Laporan keuangan masjid yang baik tidak harus rumit: catat setiap transaksi, simpan buktinya, rekap setiap bulan, lalu publikasikan. Kalau masjid Anda belum punya website, ikuti <a href="{$website}">panduan cara membuat website masjid tanpa coding</a>.</p>
                    HTML,
            ],
            [
                'slug' => self::SLUGS['donasi'],
                'title' => 'Cara Mengelola Donasi dan Wakaf Masjid secara Online agar Donatur Percaya',
                'body' => <<<HTML
                    <p>Donasi masjid online membuka pintu kebaikan yang lebih luas: jamaah perantau, alumni, dan donatur dari kota lain bisa ikut berkontribusi. Panduan ini membahas cara mengelola program donasi dan wakaf masjid secara online dengan amanah dan transparan.</p>
                    <h2>Kenapa Donasi Masjid Perlu Hadir Online?</h2>
                    <p>Kotak amal hanya menjangkau jamaah yang datang ke masjid. Padahal banyak orang yang ingin membantu pembangunan atau kegiatan masjid kampung halamannya, tetapi tidak tahu programnya, berapa kebutuhannya, dan ke mana menyalurkannya.</p>
                    <p>Program donasi yang dipublikasikan online menjawab ketiga pertanyaan itu sekaligus.</p>
                    <h2>Ciri Program Donasi Masjid yang Dipercaya</h2>
                    <ul>
                        <li><p><strong>Tujuan yang spesifik.</strong> "Wakaf pembangunan lantai dua" lebih meyakinkan daripada "donasi masjid".</p></li>
                        <li><p><strong>Target dana yang jelas.</strong> Donatur ingin tahu berapa kebutuhan sebenarnya.</p></li>
                        <li><p><strong>Progres yang diperbarui.</strong> Angka terkumpul yang bergerak menunjukkan program benar-benar berjalan.</p></li>
                        <li><p><strong>Rekening atas nama masjid atau lembaga</strong>, bukan rekening pribadi pengurus.</p></li>
                        <li><p><strong>Laporan penggunaan dana</strong> setelah program berjalan atau selesai.</p></li>
                    </ul>
                    <h2>Langkah Mengelola Donasi dan Wakaf Masjid Online</h2>
                    <h3>1. Tentukan program dan targetnya</h3>
                    <p>Pisahkan setiap kebutuhan menjadi program tersendiri, misalnya wakaf pembangunan, wakaf Al-Qur'an, atau santunan anak yatim. Tetapkan target nominal dan, jika perlu, batas waktunya.</p>
                    <h3>2. Siapkan jalur penyaluran yang resmi</h3>
                    <p>Gunakan rekening atas nama masjid. Untuk zakat, koordinasikan dengan Lazismu setempat agar penyalurannya sesuai ketentuan. Untuk wakaf, pastikan ada nazhir yang bertanggung jawab sesuai Undang-Undang Nomor 41 Tahun 2004 tentang Wakaf.</p>
                    <h3>3. Publikasikan di website masjid</h3>
                    <p>Tampilkan setiap program lengkap dengan foto, deskripsi, target, dan progress bar. Halaman program bisa langsung dibagikan ke grup WhatsApp dan media sosial.</p>
                    <h3>4. Catat setiap donasi yang masuk</h3>
                    <p>Setiap kali donasi diterima, bendahara mencatat nama donatur (atau "Hamba Allah"), nominal, dan tanggalnya. Di <a href="/website-masjid">website masjid website-mu.id</a>, angka terkumpul dan persentase progres langsung diperbarui begitu donasi dicatat.</p>
                    <h3>5. Laporkan penggunaan dana</h3>
                    <p>Tutup program dengan laporan: berapa yang terkumpul, untuk apa dana digunakan, dan dokumentasi hasilnya. Laporan ini menjadi modal kepercayaan untuk program berikutnya. Lihat <a href="{$keuangan}">cara membuat laporan keuangan masjid yang transparan</a>.</p>
                    <h2>Lengkapi dengan Kalkulator Zakat</h2>
                    <p>Banyak jamaah ingin berzakat tetapi ragu menghitungnya. Kalkulator zakat di website masjid membantu jamaah menghitung zakat maal berdasarkan nisab emas, lalu menghubungkannya langsung ke amil masjid lewat WhatsApp.</p>
                    <h2>Kesimpulan</h2>
                    <p>Donasi masjid online yang dipercaya dibangun dari tiga hal: tujuan yang jelas, progres yang terbuka, dan laporan yang rutin. Belum punya website masjid? Ikuti <a href="{$website}">panduan cara membuat website masjid tanpa coding</a>, atau langsung <a href="/website-masjid">coba gratis di website-mu.id</a>.</p>
                    HTML,
            ],
        ];
    }
}
