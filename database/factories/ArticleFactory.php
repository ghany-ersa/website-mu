<?php

namespace Database\Factories;

use App\Enums\PublishStatus;
use App\Models\Article;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Article>
 *
 * Uses a fixed pool of the platform's actual published blog posts (copied verbatim from the
 * admin-authored articles in production, cover images included) instead of Faker lorem text or
 * invented copy, since Article is the platform's own public-facing blog (see
 * resources/views/articles/*.blade.php) and its content shows up verbatim in generated
 * pages/screenshots — seeding should reproduce what actually exists, not placeholder samples.
 */
class ArticleFactory extends Factory
{
    /**
     * @var array<int, array{title: string, category: string, cover_image: string, body: string}>
     */
    protected static array $samples = [
        [
            'title' => 'Masjid Muhammadiyah yang Maju Harus Punya Sistem Digital',
            'category' => 'Digitalisasi',
            'cover_image' => 'https://storage.ambulu.or.id/articles/a6e234c3-ba21-43b4-a2fa-d73e07493b5d.webp',
            'body' => <<<'HTML'
                <p>Masjid Muhammadiyah terus bergerak sebagai pusat ibadah, dakwah, pendidikan, sosial, dan pemberdayaan umat. Tapi, sudahkah seluruh informasi, kegiatan, dan pelayanan masjid <strong>terkelola dengan baik di ruang digital? </strong>Hari ini, jamaah dan masyarakat mencari informasi melalui internet.</p><p>Mereka ingin tahu:</p><ol><li><p>Siapa pengurus dan takmir masjid?</p></li><li><p>Di mana lokasi masjid?</p></li><li><p>Kapan jadwal kajian dan kegiatan?</p></li><li><p>Apa saja program sosial dan pemberdayaan?</p></li><li><p>Bagaimana cara menghubungi pengurus?</p></li><li><p>Bagaimana cara memberikan donasi atau berpartisipasi dalam program masjid?</p></li></ol><p><strong>Masjid tidak cukup hanya aktif di dunia nyata. Masjid yang maju juga perlu hadir dan terkelola dengan baik di dunia digital.</strong></p><h2>Kenapa Masjid Perlu Sistem Digital?</h2><h3>Lebih Terbuka dan Profesional</h3><p>Informasi tentang masjid, program, kegiatan, dan layanan dapat disampaikan secara lebih tertata dan mudah diakses oleh jamaah maupun masyarakat.</p><h3>Informasi Tidak Tenggelam</h3><p>Pengumuman di grup WhatsApp atau media sosial bisa dengan cepat tertutup oleh pesan dan postingan baru.</p><p>Dengan website, informasi penting seperti profil masjid, jadwal kegiatan, berita, program, hingga kontak dapat memiliki <strong>rumah digital yang tetap dan mudah ditemukan</strong>.</p><h3>Menjadi Arsip Digital Masjid</h3><p>Dokumentasi kegiatan, kajian, program sosial, pembangunan, dan perjalanan masjid dapat tersimpan dengan lebih baik.</p><p>Apa yang dilakukan hari ini tidak hanya menjadi kenangan, tetapi juga menjadi <strong>jejak digital dan arsip untuk generasi berikutnya</strong>.</p><h3>Mempermudah Pelayanan Jamaah</h3><p>Digitalisasi juga dapat membantu masjid dalam mengelola informasi dan pelayanan.</p><p>Mulai dari:</p><ol><li><p>Profil dan informasi masjid</p></li><li><p>Struktur pengurus dan takmir</p></li><li><p>Jadwal salat dan kegiatan</p></li><li><p>Agenda kajian</p></li><li><p>Berita dan dokumentasi</p></li><li><p>Program sosial</p></li><li><p>Informasi donasi</p></li><li><p>Kontak dan lokasi masjid</p></li></ol><p><strong>Semuanya dapat terhubung dalam satu ekosistem digital.</strong></p><h2>Masjid Maju Tidak Harus Punya Tim IT</h2><p>Masih menganggap digitalisasi masjid itu rumit? Tidak semua masjid harus memiliki programmer atau tim teknologi sendiri. Yang terpenting adalah memulai dari kebutuhan paling dasar: <strong>memiliki sistem digital yang mudah digunakan oleh pengurus dan bermanfaat bagi jamaah. </strong>Dengan <a target="_blank" href="http://website-mu.id" rel="noreferrer noopener"><strong>website-mu.id</strong></a>, masjid dapat mulai membangun rumah digitalnya tanpa harus memulai semuanya dari nol. Cukup siapkan informasi masjid, lalu susun website sesuai kebutuhan.</p><h3>🚀 Mulai Digitalisasi Masjid dalam Kurang dari 30 Menit</h3><p>Tidak perlu menunggu punya tim IT.</p><p>Tidak perlu membuat sistem yang rumit.</p><p>Mulailah dari yang sederhana:</p><p><strong>Profil Masjid. Pengurus. Kegiatan. Kajian. Program. Informasi Jamaah. Kontak.</strong></p><p>Kemudian, kembangkan secara bertahap sesuai kebutuhan. Karena transformasi digital tidak selalu harus dimulai dengan teknologi yang besar.</p><h3>🌐 Saatnya Masjid Muhammadiyah Masuk ke Ruang Digital</h3><p><strong>Masjid Muhammadiyah yang maju bukan hanya ramai kegiatannya, tetapi juga mampu mengelola informasi dan pelayanan secara lebih modern, terbuka, dan terhubung.</strong></p><p>Saatnya membangun sistem digital untuk masjid.</p><p>👉 <strong>Buat website masjid Anda sekarang di </strong><a target="_blank" href="http://website-mu.id" rel="noreferrer noopener"><strong>website-mu.id</strong></a></p>
                HTML,
        ],
        [
            'title' => 'Gerakan 100% PRM Ambulu Go Digital, Satu Ranting, Satu Profil Digital',
            'category' => 'Digitalisasi',
            'cover_image' => 'https://storage.ambulu.or.id/articles/a2453561-1ef2-4cb4-abd9-1c2d64df89c4.webp',
            'body' => <<<'HTML'
                <p>Muhammadiyah tumbuh dari ranting. Di tingkat paling dekat dengan masyarakat inilah dakwah, pendidikan, kegiatan sosial, pemberdayaan, dan berbagai amal usaha terus bergerak. Namun, di tengah masyarakat yang semakin terhubung dengan internet, muncul satu pertanyaan sederhana:</p><p><strong>Sudahkah aktivitas PRM kita mudah ditemukan di ruang digital?</strong></p><p>Masyarakat hari ini terbiasa mencari informasi melalui Google. Ketika ingin mengetahui sebuah organisasi, mereka mencari profil, lokasi, pengurus, kegiatan, program, hingga informasi kontak melalui internet. Karena itu, sudah saatnya PRM tidak hanya aktif di tengah masyarakat, tetapi juga memiliki <strong>identitas dan profil resmi di ruang digital.</strong></p><h2>Dari Ranting, Kita Mulai Digitalisasi</h2><p>Bayangkan jika seluruh PRM se-Cabang Ambulu memiliki profil digital dengan format yang tertata dan mudah ditemukan. Masyarakat dapat mengenal setiap ranting melalui informasi yang lengkap: siapa pengurusnya, di mana lokasinya, apa kegiatannya, program apa yang dijalankan, serta bagaimana cara menghubunginya. Lebih dari sekadar website, profil digital dapat menjadi <strong>rumah informasi dan arsip perjalanan setiap ranting. </strong>Dokumentasi kegiatan yang selama ini tersimpan di galeri HP, grup WhatsApp, atau media sosial dapat mulai dikumpulkan dan menjadi bagian dari sejarah digital organisasi.</p><h2>Kenapa Harus Serempak?</h2><p>Digitalisasi akan menjadi lebih kuat ketika dilakukan bersama. Jika hanya satu atau dua PRM yang memiliki profil digital, manfaatnya masih terbatas. Tetapi ketika seluruh PRM bergerak secara serempak, kita mulai membangun <strong>ekosistem digital Muhammadiyah di Ambulu.</strong></p><p>Satu Cabang memiliki banyak ranting.</p><p>Setiap ranting memiliki identitas digital.</p><p>Dan seluruhnya dapat saling terhubung menjadi satu ekosistem informasi Muhammadiyah yang lebih terbuka, tertata, dan mudah diakses masyarakat.</p><h3>1 PRM = 1 Profil Digital</h3><p>Targetnya sederhana:</p><p><strong>100% PRM se-Cabang Ambulu memiliki profil digital resmi.</strong></p><p>Tidak perlu menunggu memiliki tim IT. Tidak perlu membuat sistem yang rumit. Digitalisasi dapat dimulai dari informasi paling dasar: profil, struktur pengurus, kegiatan, program, lokasi, kontak, dan dokumentasi. Melalui <a target="_blank" href="http://website-mu.id" rel="noreferrer noopener"><strong>website-mu.id</strong></a>, pembuatan profil digital dapat dilakukan dengan lebih sederhana sehingga pengurus dapat fokus pada kegiatan organisasi, sementara kebutuhan digital dapat dibangun secara bertahap.</p><h2>Saatnya PRM Ambulu Go Digital</h2><p>Digitalisasi bukan sekadar mengikuti perkembangan teknologi. Ini adalah langkah untuk memastikan bahwa <strong>gerakan, kontribusi, dan perjalanan PRM dapat dikenal dan diwariskan melalui ruang digital.</strong></p><p>Mari mulai bersama.</p><h3>Gerakan 100% PRM Ambulu Go Digital</h3><p><strong>Saatnya setiap ranting memiliki rumah digitalnya sendiri.</strong></p><p>🌐 <strong>Mulai di </strong><a target="_blank" href="http://website-mu.id" rel="noreferrer noopener"><strong>website-mu.id</strong></a></p>
                HTML,
        ],
        [
            'title' => 'Bangun Website Muhammadiyah dalam Kurang dari 30 Menit',
            'category' => 'Digitalisasi',
            'cover_image' => 'https://storage.ambulu.or.id/articles/a1e9efbb-9ca7-4cdc-b605-a72636d7f56c.webp',
            'body' => <<<'HTML'
                <p>Muhammadiyah aktif bergerak di masyarakat. Tapi, sudahkah aktivitas itu mudah ditemukan di internet? Hari ini, masyarakat mencari informasi lewat Google. Mereka ingin tahu profil PRM atau PCM, siapa pengurusnya, apa kegiatannya, di mana lokasinya, dan bagaimana cara menghubunginya. <strong>Website bisa menjadi rumah digital untuk semua informasi tersebut.</strong></p><h2>Kenapa PRM dan PCM Perlu Website?</h2><p><strong>Lebih kredibel</strong><br>Organisasi terlihat lebih tertata dan mudah dipercaya ketika memiliki website resmi.</p><p><strong>Informasi lebih mudah ditemukan</strong><br>Profil, struktur, kegiatan, berita, dan kontak tidak tenggelam seperti postingan media sosial.</p><p><strong>Menjadi arsip digital</strong><br>Dokumentasi kegiatan dan perjalanan organisasi bisa tersimpan dan diakses kembali kapan saja.</p><h2>Tidak Perlu Jago Coding</h2><p>Masih menganggap membuat website itu rumit? Suara Muhammadiyah Ambulu menghadirkan <a target="_blank" href="http://website-mu.id" rel="noreferrer noopener"><strong>website-mu.id</strong></a>, platform sederhana untuk membantu PRM dan PCM membuat website organisasi tanpa harus memulai dari nol. Cukup siapkan informasi organisasi, lalu susun website sesuai kebutuhan.</p><h3>🚀 Kurang dari 30 Menit</h3><p>Tidak perlu menunggu punya tim IT.<br>Tidak perlu membuat website yang rumit.</p><p><strong>Mulai dari profil, struktur, kegiatan, dan kontak.</strong></p><p>Karena digitalisasi Muhammadiyah bisa dimulai dari langkah sederhana: <strong>punya satu rumah digital yang resmi.</strong></p><h3>🌐 Saatnya PRM dan PCM Punya Website</h3><p><strong>Bikin website resmi Muhammadiyah dalam kurang dari 30 menit.</strong></p><p>👉 <strong>Coba sekarang di </strong><a target="_blank" href="http://website-mu.id" rel="noreferrer noopener"><strong>website-mu.id</strong></a></p>
                HTML,
        ],
        [
            'title' => '5 Alasan Ranting dan Cabang Muhammadiyah Perlu Website Resmi',
            'category' => 'Digitalisasi',
            'cover_image' => 'https://storage.ambulu.or.id/articles/e852d1d4-960e-4b57-8e5e-a523919ac7cb.webp',
            'body' => <<<'HTML'
                <p>Sekarang masyarakat mencari informasi lewat Google. Karena itu, Ranting dan Cabang Muhammadiyah juga perlu punya <strong>rumah digital resmi</strong>. Tidak harus rumit. Yang penting informasinya jelas, mudah ditemukan, dan bisa diakses kapan saja.</p><h2>1. Bikin Organisasi Lebih Kredibel</h2><p>Website membuat PRM dan PCM terlihat lebih tertata dan profesional. Masyarakat bisa langsung melihat profil, struktur pimpinan, kegiatan, hingga kontak resmi organisasi. <strong>Bukan sekadar terlihat digital, tapi menunjukkan bahwa organisasi benar-benar hadir.</strong></p><h2>2. Informasi Tidak Tenggelam</h2><p>Informasi di Instagram atau WhatsApp bisa cepat tertutup oleh postingan baru. Di website, informasi penting seperti sejarah, profil, kegiatan, dan program bisa disimpan dan ditemukan kembali kapan saja. <strong>Media sosial menyebarkan informasi. Website menyimpannya.</strong></p><h2>3. Jadi Pusat Informasi Resmi</h2><p>Daripada masyarakat harus bertanya ke banyak orang, arahkan semuanya ke satu tempat. Profil organisasi, berita, agenda, galeri, alamat, dan kontak bisa dikumpulkan dalam satu website. <strong>Satu link, banyak informasi.</strong></p><h2>4. Menjadi Arsip Digital Organisasi</h2><p>Setiap kegiatan Muhammadiyah adalah bagian dari perjalanan organisasi. Dokumentasi kegiatan, berita, foto, dan program bisa disimpan di website agar tidak hilang begitu saja. <strong>Yang dikerjakan hari ini, bisa dikenang dan ditemukan bertahun-tahun kemudian.</strong></p><h2>5. Tidak Perlu Menunggu Tim IT</h2><p>Membuat website sekarang tidak harus sulit atau mahal. Dengan <a target="_blank" href="http://website-mu.id" rel="noreferrer noopener"><strong>website-mu.id</strong></a>, PRM dan PCM bisa mulai membuat website profil organisasi dengan lebih mudah. Tidak perlu jago coding. Tidak perlu memulai dari nol.</p><h3>🚀 Coba Buat Website Muhammadiyahmu</h3><p>Punya PRM atau PCM yang belum punya website?</p><p><strong>Mulai dari yang sederhana. Masukkan profil, struktur, kegiatan, dan informasi penting organisasi.</strong></p><p>👉 <strong>Coba sekarang di </strong><a target="_blank" href="http://website-mu.id" rel="noreferrer noopener"><strong>website-mu.id</strong></a></p>
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
            'cover_image' => $sample['cover_image'],
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
