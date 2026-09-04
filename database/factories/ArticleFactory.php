<?php

namespace Database\Factories;

use App\Enums\PublishStatus;
use App\Models\Article;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Article>
 *
 * Uses a fixed pool of realistic Website-Mu blog topics (product updates, digitalization tips
 * for Muhammadiyah organizations, case studies) instead of Faker lorem text, since Article is
 * the platform's own public-facing blog (see resources/views/articles/*.blade.php) and its
 * copy shows up verbatim in generated pages/screenshots.
 */
class ArticleFactory extends Factory
{
    /**
     * @var array<int, array{title: string, category: string, body: string}>
     */
    protected static array $samples = [
        [
            'title' => '5 Alasan Ranting dan Cabang Muhammadiyah Perlu Website Resmi',
            'category' => 'Tips Digitalisasi',
            'body' => <<<'HTML'
                <p>Banyak PRM dan PCM masih mengandalkan grup WhatsApp dan media sosial pribadi pengurus untuk menyampaikan informasi kegiatan. Cara ini rawan hilang saat pergantian pengurus dan sulit ditemukan jamaah baru.</p>
                <h2>1. Identitas resmi yang mudah ditemukan</h2>
                <p>Website dengan subdomain resmi membuat jamaah, donatur, dan calon pengurus baru bisa memverifikasi keaslian informasi dengan mudah, tanpa tergantung satu akun media sosial.</p>
                <h2>2. Informasi kegiatan tidak tenggelam</h2>
                <p>Berbeda dengan linimasa media sosial yang terus bergulir, halaman agenda dan berita di website tetap dapat diakses kapan pun dibutuhkan.</p>
                <h2>3. Memudahkan transparansi program dan donasi</h2>
                <p>Laporan kegiatan, program unggulan, hingga informasi rekening donasi dapat disajikan secara terstruktur dan mudah diverifikasi.</p>
                <p>Dengan Website-Mu, pengurus tidak perlu keahlian coding untuk membangun dan mengelola website resmi organisasinya sendiri.</p>
                HTML,
        ],
        [
            'title' => 'Cara Mudah Mengelola Konten Website Organisasi Tanpa Coding',
            'category' => 'Tips Digitalisasi',
            'body' => <<<'HTML'
                <p>Salah satu kendala utama organisasi dalam mengelola website adalah kebutuhan akan tenaga teknis. Website-Mu dirancang agar pengurus non-teknis tetap bisa mengelola kontennya sendiri.</p>
                <h2>Editor drag-and-drop</h2>
                <p>Susun halaman dengan menambah, memindah, dan mengatur ulang komponen seperti galeri, agenda, dan profil pengurus tanpa menulis satu baris kode pun.</p>
                <h2>CMS sederhana untuk konten rutin</h2>
                <p>Berita, artikel, dan agenda kegiatan dapat diperbarui melalui panel admin yang ringkas, sehingga siapa pun di kepengurusan dapat dilatih menggunakannya dalam hitungan menit.</p>
                <p>Fokuskan waktu pengurus pada substansi program, bukan pada urusan teknis pengelolaan website.</p>
                HTML,
        ],
        [
            'title' => 'Studi Kasus: PCM Ambulu Terbitkan Website dalam Waktu Kurang dari Sehari',
            'category' => 'Studi Kasus',
            'body' => <<<'HTML'
                <p>Pimpinan Cabang Muhammadiyah (PCM) Ambulu menjadi salah satu contoh organisasi yang berhasil menerbitkan website resminya dalam waktu singkat menggunakan Website-Mu.</p>
                <h2>Tantangan sebelum menggunakan Website-Mu</h2>
                <p>Sebelumnya, informasi kegiatan PCM Ambulu tersebar di beberapa grup WhatsApp dan akun media sosial pribadi pengurus, sehingga sulit diakses oleh warga baru maupun donatur dari luar daerah.</p>
                <h2>Proses pembuatan website</h2>
                <p>Pengurus memilih template organisasi Muhammadiyah, menyesuaikan warna dan logo, lalu mengisi profil, agenda, dan berita menggunakan editor drag-and-drop bawaan platform.</p>
                <h2>Hasil</h2>
                <p>Dalam waktu kurang dari sehari, website resmi PCM Ambulu berhasil diterbitkan lengkap dengan profil organisasi, program kerja, dan galeri kegiatan.</p>
                HTML,
        ],
        [
            'title' => 'Mengenal Fitur Publikasi Domain Kustom di Website-Mu',
            'category' => 'Produk',
            'body' => <<<'HTML'
                <p>Selain subdomain gratis, Website-Mu juga mendukung penggunaan domain kustom milik organisasi sendiri, seperti pcmambulu.or.id.</p>
                <h2>Kenapa domain kustom penting?</h2>
                <p>Domain sendiri memperkuat identitas resmi organisasi dan memudahkan warga mengingat alamat website tanpa embel-embel nama platform.</p>
                <h2>Cara menghubungkan domain</h2>
                <p>Pengurus cukup mengarahkan pengaturan DNS domain ke Website-Mu melalui panduan yang tersedia di halaman pengaturan organisasi, tanpa perlu bantuan teknis dari pihak ketiga.</p>
                <p>Fitur ini tersedia mulai paket Organization ke atas.</p>
                HTML,
        ],
        [
            'title' => 'Panduan Menyusun Profil Organisasi yang Informatif',
            'category' => 'Tips Digitalisasi',
            'body' => <<<'HTML'
                <p>Halaman profil adalah salah satu halaman yang paling banyak dikunjungi pengunjung website organisasi. Berikut beberapa hal yang sebaiknya dicantumkan.</p>
                <h2>Sejarah singkat</h2>
                <p>Ceritakan latar belakang berdirinya organisasi secara ringkas namun informatif.</p>
                <h2>Visi, misi, dan struktur kepengurusan</h2>
                <p>Cantumkan visi misi terkini beserta susunan pengurus aktif agar warga mengetahui siapa yang dapat dihubungi untuk keperluan tertentu.</p>
                <h2>Program unggulan</h2>
                <p>Sorot program kerja yang menjadi fokus utama organisasi pada periode berjalan.</p>
                <p>Semua elemen ini dapat disusun dengan cepat menggunakan komponen profil bawaan Website-Mu.</p>
                HTML,
        ],
        [
            'title' => 'Kabar Muhammadiyah: Digitalisasi Ranting dan Cabang Semakin Meluas',
            'category' => 'Kabar Muhammadiyah',
            'body' => <<<'HTML'
                <p>Semakin banyak Pimpinan Ranting dan Pimpinan Cabang Muhammadiyah di berbagai daerah yang mulai beralih ke website resmi sebagai kanal informasi utama bagi warga dan simpatisan.</p>
                <h2>Dorongan dari kebutuhan transparansi</h2>
                <p>Kebutuhan akan transparansi program dan pengelolaan donasi menjadi salah satu pendorong utama percepatan digitalisasi ini.</p>
                <h2>Dukungan platform no-code</h2>
                <p>Kehadiran platform seperti Website-Mu mempermudah organisasi tingkat ranting dan cabang yang umumnya tidak memiliki tenaga IT khusus untuk tetap memiliki kehadiran digital yang layak.</p>
                HTML,
        ],
        [
            'title' => 'Checklist Sebelum Menerbitkan Website Organisasi Anda',
            'category' => 'Tips Digitalisasi',
            'body' => <<<'HTML'
                <p>Sebelum menekan tombol terbitkan, ada baiknya pengurus memeriksa beberapa hal berikut agar website siap diakses publik.</p>
                <h2>1. Periksa informasi kontak</h2>
                <p>Pastikan nomor telepon, alamat, dan tautan media sosial sudah benar dan aktif.</p>
                <h2>2. Lengkapi halaman profil dan program</h2>
                <p>Hindari halaman kosong atau masih berisi konten contoh dari template.</p>
                <h2>3. Uji tampilan di perangkat mobile</h2>
                <p>Sebagian besar pengunjung akan mengakses website melalui ponsel, sehingga tampilan mobile harus diprioritaskan.</p>
                <p>Setelah semua poin terpenuhi, website siap diterbitkan ke subdomain atau domain resmi organisasi.</p>
                HTML,
        ],
        [
            'title' => 'Mengelola Galeri Kegiatan agar Lebih Menarik Dikunjungi',
            'category' => 'Tips Digitalisasi',
            'body' => <<<'HTML'
                <p>Galeri foto kegiatan menjadi salah satu komponen yang paling sering dilihat pengunjung untuk menilai keaktifan sebuah organisasi.</p>
                <h2>Pilih foto dengan kualitas baik</h2>
                <p>Gunakan foto beresolusi cukup dan pencahayaan yang jelas agar kegiatan terlihat profesional.</p>
                <h2>Kelompokkan berdasarkan kegiatan</h2>
                <p>Mengelompokkan foto per kegiatan memudahkan pengunjung menelusuri dokumentasi acara tertentu.</p>
                <p>Fitur galeri dengan lightbox di Website-Mu memungkinkan pengunjung melihat pratinjau foto secara penuh langsung dari halaman kegiatan.</p>
                HTML,
        ],
    ];

    protected static int $nextSampleIndex = 0;

    /**
     * Define the model's default state.
     *
     * Cycles through the sample pool in order (rather than picking randomly) so seeding N
     * articles, with N <= count(self::$samples), never produces duplicate titles/slugs.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $sample = self::$samples[self::$nextSampleIndex % count(self::$samples)];
        self::$nextSampleIndex++;

        return [
            'author_id' => null,
            'title' => $sample['title'],
            'slug' => str($sample['title'])->slug().'-'.fake()->unique()->numberBetween(1, 100000),
            'category' => $sample['category'],
            'cover_image' => null,
            'body' => $sample['body'],
            'status' => PublishStatus::Draft,
            'published_at' => null,
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PublishStatus::Published,
            'published_at' => now(),
        ]);
    }
}
