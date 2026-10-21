<?php

namespace Database\Seeders;

/**
 * Cluster around /website-sekolah: "cara membuat website sekolah" and "promosi penerimaan murid
 * baru / SPMB / PPDB". See SeoArticleClusterSeeder for the shared rules.
 *
 * The enrolment article deliberately says the platform's form is not a full SPMB selection
 * system - it only forwards a message to the committee's WhatsApp (formulir-kontak/sekolahmu).
 */
class SekolahSeoArticleSeeder extends SeoArticleClusterSeeder
{
    public const SLUGS = [
        'website' => 'cara-membuat-website-sekolah-muhammadiyah-tanpa-coding',
        'spmb' => 'strategi-website-sekolah-untuk-promosi-penerimaan-murid-baru',
    ];

    protected function category(): string
    {
        return 'Sekolah';
    }

    protected function articles(): array
    {
        $website = self::articlePath('website');
        $spmb = self::articlePath('spmb');

        return [
            [
                'slug' => self::SLUGS['website'],
                'title' => 'Cara Membuat Website Sekolah Muhammadiyah Tanpa Coding: Panduan untuk Kepala Sekolah dan Operator',
                'body' => <<<HTML
                    <p>Cara membuat website sekolah Muhammadiyah kini cukup dikerjakan operator atau guru tanpa keahlian pemrograman. Panduan ini membahas isi yang wajib ada dan langkah menerbitkan website sekolah dalam kurang dari 30 menit.</p>
                    <h2>Mengapa Sekolah Muhammadiyah Perlu Website Resmi?</h2>
                    <p>Hampir semua orang tua kini mencari informasi sekolah lewat Google sebelum datang survei. Sekolah yang tidak punya website, atau websitenya tidak diperbarui bertahun-tahun, kalah bersaing dengan sekolah lain yang informasinya lebih mudah ditemukan.</p>
                    <p>Website juga menjadi <strong>arsip resmi</strong> sekolah: prestasi, kegiatan, dan pengumuman tersimpan rapi, tidak hilang di grup WhatsApp kelas yang terus berganti setiap tahun ajaran.</p>
                    <h2>Isi yang Wajib Ada di Website Sekolah</h2>
                    <ul>
                        <li><p><strong>Profil sekolah</strong>: sejarah, visi misi, akreditasi, dan keunggulan.</p></li>
                        <li><p><strong>Kepala sekolah dan guru</strong>: orang tua ingin tahu siapa yang mendidik anaknya.</p></li>
                        <li><p><strong>Prestasi</strong>: juara lomba, olimpiade, tahfidz, dan penghargaan sekolah.</p></li>
                        <li><p><strong>Program unggulan dan ekstrakurikuler</strong>: pembiasaan ibadah, Hizbul Wathan, Tapak Suci, dan lainnya.</p></li>
                        <li><p><strong>Testimoni wali murid dan alumni</strong>.</p></li>
                        <li><p><strong>Berita, agenda, dan pengumuman</strong> sekolah.</p></li>
                        <li><p><strong>Galeri kegiatan</strong> dan <strong>lokasi</strong> lengkap dengan peta.</p></li>
                        <li><p><strong>Informasi pendaftaran</strong> murid baru dan kontak panitia.</p></li>
                    </ul>
                    <h2>Langkah Membuat Website Sekolah</h2>
                    <h3>1. Pilih template website sekolah</h3>
                    <p>Gunakan template yang memang dirancang untuk sekolah, sehingga bagian prestasi, guru, kegiatan, dan pendaftaran sudah tersedia. Lihat contohnya di halaman <a href="/website-sekolah">website sekolah</a> website-mu.id.</p>
                    <h3>2. Sesuaikan identitas sekolah</h3>
                    <p>Unggah logo sekolah, pilih warna, lalu ganti foto utama dengan foto gedung atau kegiatan siswa terbaik Anda.</p>
                    <h3>3. Isi data lewat formulir</h3>
                    <p>Masukkan data guru, prestasi, kegiatan, dan profil sekolah lewat formulir sederhana di dashboard. Tidak ada kode yang perlu disentuh.</p>
                    <h3>4. Bagi tugas pengelolaan</h3>
                    <p>Tambahkan guru atau operator lain sebagai pengelola. Misalnya, wakasek kesiswaan memperbarui prestasi dan humas memperbarui berita.</p>
                    <h3>5. Terbitkan dan promosikan</h3>
                    <p>Cantumkan alamat website di brosur, spanduk, kop surat, dan profil Google Maps sekolah. Bagikan juga ke grup wali murid.</p>
                    <h2>Tips Agar Website Sekolah Mudah Ditemukan di Google</h2>
                    <ul>
                        <li><p><strong>Gunakan nama lengkap sekolah</strong> secara konsisten, misalnya "SD Muhammadiyah 1 Ambulu", sesuai yang diketik orang tua di Google.</p></li>
                        <li><p><strong>Unggah berita kegiatan secara rutin</strong>, minimal dua kali sebulan.</p></li>
                        <li><p><strong>Perbarui prestasi setiap ada lomba</strong>. Konten baru memberi sinyal bahwa website aktif.</p></li>
                        <li><p><strong>Hubungkan dengan Google Maps</strong> agar sekolah muncul di pencarian lokal.</p></li>
                    </ul>
                    <p>Website sekolah paling berdampak menjelang musim penerimaan murid baru. Baca <a href="{$spmb}">strategi website sekolah untuk promosi penerimaan murid baru</a>.</p>
                    <h2>Mulai Sekarang</h2>
                    <p>Prestasi dan keunggulan sekolah Anda layak diketahui lebih banyak orang tua. <a href="/website-sekolah">Buat website sekolah di website-mu.id</a>, coba gratis dan rancang sendiri tanpa coding.</p>
                    HTML,
            ],
            [
                'slug' => self::SLUGS['spmb'],
                'title' => 'Strategi Website Sekolah untuk Promosi Penerimaan Murid Baru (SPMB/PPDB)',
                'body' => <<<HTML
                    <p>Website sekolah adalah alat promosi penerimaan murid baru yang bekerja 24 jam. Artikel ini membahas strategi menyiapkan website sekolah menjelang SPMB (sebelumnya dikenal sebagai PPDB) agar lebih banyak calon wali murid mendaftar.</p>
                    <h2>Bagaimana Orang Tua Memilih Sekolah Hari Ini?</h2>
                    <p>Sebagian besar orang tua memulai dengan pencarian Google seperti "SD Muhammadiyah terdekat" atau "sekolah Islam terbaik di [nama kota]". Mereka membuka beberapa website, membandingkan, lalu memilih dua atau tiga sekolah untuk didatangi.</p>
                    <p>Artinya, <strong>website menentukan apakah sekolah Anda masuk daftar survei atau tidak</strong>. Brosur dan spanduk tetap penting, tetapi keputusan awal kini sering terjadi di layar ponsel.</p>
                    <h2>Lima Hal yang Dicari Calon Wali Murid</h2>
                    <ol>
                        <li><p><strong>Keunggulan dan program sekolah.</strong> Apa yang membedakan sekolah Anda: tahfidz, bilingual, sekolah ramah anak, atau pendidikan karakter Islami?</p></li>
                        <li><p><strong>Bukti prestasi.</strong> Daftar juara dan karya siswa adalah bukti mutu yang paling meyakinkan.</p></li>
                        <li><p><strong>Suasana sekolah.</strong> Foto kegiatan, fasilitas, dan lingkungan belajar.</p></li>
                        <li><p><strong>Cerita orang tua lain.</strong> Testimoni wali murid dan alumni.</p></li>
                        <li><p><strong>Informasi pendaftaran yang jelas.</strong> Jadwal, syarat, biaya, dan kontak panitia.</p></li>
                    </ol>
                    <h2>Strategi Menyiapkan Website Sebelum Penerimaan Murid Baru</h2>
                    <h3>Mulai tiga bulan sebelumnya</h3>
                    <p>Google butuh waktu untuk mengindeks dan menaikkan peringkat halaman. Website yang baru dibuat seminggu sebelum pendaftaran dibuka sering belum muncul di pencarian.</p>
                    <h3>Tampilkan prestasi terbaru di halaman depan</h3>
                    <p>Prestasi tahun ini lebih meyakinkan daripada daftar panjang prestasi lama. Sertakan foto siswa dan tingkat lombanya.</p>
                    <h3>Kumpulkan testimoni wali murid</h3>
                    <p>Minta 3 sampai 5 orang tua menuliskan pengalaman mereka. Testimoni yang spesifik, misalnya "anak saya kini terbiasa salat dhuha", lebih kuat daripada pujian umum.</p>
                    <h3>Pasang formulir pendaftaran awal</h3>
                    <p>Permudah calon wali murid menyatakan minat. Di <a href="/website-sekolah">website sekolah website-mu.id</a>, formulir pendaftaran mengirim nama calon murid dan kelas tujuan langsung ke WhatsApp panitia, sehingga panitia bisa segera menindaklanjuti. Formulir ini untuk pendaftaran awal dan promosi, bukan pengganti sistem seleksi SPMB resmi bila sekolah Anda memakainya.</p>
                    <h3>Umumkan jadwal lewat pengumuman dan agenda</h3>
                    <p>Jadwal pendaftaran, tes, dan pengumuman hasil sebaiknya ada di website, bukan hanya di grup WhatsApp, agar orang tua yang baru mengenal sekolah juga bisa membacanya.</p>
                    <h3>Sebarkan alamat website di semua materi promosi</h3>
                    <p>Cetak alamat website dan kode QR di brosur, spanduk, dan unggahan media sosial, sehingga semua jalur promosi bermuara ke satu informasi resmi.</p>
                    <h2>Kesimpulan</h2>
                    <p>Website sekolah yang siap sebelum musim penerimaan murid baru membantu sekolah masuk daftar pertimbangan orang tua sejak awal. Belum punya website sekolah? Ikuti <a href="{$website}">panduan cara membuat website sekolah Muhammadiyah tanpa coding</a>, atau langsung <a href="/website-sekolah">coba gratis di website-mu.id</a>.</p>
                    HTML,
            ],
        ];
    }
}
