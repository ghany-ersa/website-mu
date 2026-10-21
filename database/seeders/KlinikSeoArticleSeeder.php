<?php

namespace Database\Seeders;

/**
 * Cluster around /website-klinik: "cara membuat website klinik" and "jadwal praktik dokter
 * online". See SeoArticleClusterSeeder for the shared rules.
 *
 * Health-facility publicity in Indonesia is regulated, so these articles steer tenants away
 * from superlatives and outcome promises, and say plainly that the platform has no queue or
 * medical-record system - a clinic acting on an overstated pitch is the risk worth avoiding.
 */
class KlinikSeoArticleSeeder extends SeoArticleClusterSeeder
{
    public const SLUGS = [
        'website' => 'cara-membuat-website-klinik-tanpa-coding',
        'jadwal' => 'jadwal-praktik-dokter-online-di-website-klinik',
    ];

    protected function category(): string
    {
        return 'Klinik';
    }

    protected function articles(): array
    {
        $website = self::articlePath('website');
        $jadwal = self::articlePath('jadwal');

        return [
            [
                'slug' => self::SLUGS['website'],
                'title' => 'Cara Membuat Website Klinik Tanpa Coding: Panduan untuk Klinik Pratama dan Klinik Aisyiyah',
                'body' => <<<HTML
                    <p>Cara membuat website klinik kini bisa dikerjakan sendiri oleh admin klinik tanpa programmer. Panduan ini membahas informasi yang perlu ada di website klinik pratama, klinik Aisyiyah, maupun rumah sakit, serta langkah menerbitkannya.</p>
                    <h2>Mengapa Klinik Perlu Website?</h2>
                    <p>Sebelum datang, pasien biasanya mencari tahu lewat ponsel: jam buka, dokter yang praktik hari ini, dan layanan apa saja yang tersedia. Kalau informasinya tidak ditemukan, mereka menelepon, mengirim pesan WhatsApp, atau memilih fasilitas kesehatan lain yang informasinya lebih jelas.</p>
                    <p>Website klinik menjawab pertanyaan-pertanyaan itu kapan saja, sehingga petugas pendaftaran tidak perlu menjawab hal yang sama berulang kali.</p>
                    <h2>Informasi yang Perlu Ada di Website Klinik</h2>
                    <ul>
                        <li><p><strong>Jadwal praktik dokter</strong>: nama dokter, spesialisasi, hari, dan jam praktik.</p></li>
                        <li><p><strong>Daftar layanan</strong>: poli umum, poli gigi, KIA, laboratorium, farmasi, dan lainnya.</p></li>
                        <li><p><strong>Jam operasional</strong>, termasuk layanan di hari libur bila ada.</p></li>
                        <li><p><strong>Profil klinik</strong>: sejarah, visi misi, dan fasilitas.</p></li>
                        <li><p><strong>Program kesehatan</strong>: posyandu, senam sehat, pemeriksaan kesehatan gratis.</p></li>
                        <li><p><strong>Pengumuman</strong>: perubahan jadwal dokter dan libur hari raya.</p></li>
                        <li><p><strong>Kontak dan lokasi</strong>: nomor WhatsApp admin, alamat, dan peta.</p></li>
                    </ul>
                    <h2>Langkah Membuat Website Klinik</h2>
                    <h3>1. Pilih template website klinik</h3>
                    <p>Gunakan template yang sudah menyediakan bagian jadwal dokter dan daftar layanan. Lihat contohnya di halaman <a href="/website-klinik">website klinik</a> website-mu.id.</p>
                    <h3>2. Sesuaikan identitas klinik</h3>
                    <p>Unggah logo, pilih warna, dan gunakan foto fasilitas asli klinik Anda, bukan foto stok, agar pasien mengenali tempatnya saat datang.</p>
                    <h3>3. Isi jadwal dokter dan layanan</h3>
                    <p>Masukkan data dokter, jadwal, dan layanan lewat formulir di dashboard. Tidak ada kode yang perlu disentuh.</p>
                    <h3>4. Tunjuk penanggung jawab pembaruan</h3>
                    <p>Jadwal yang tidak diperbarui justru merugikan pasien. Tetapkan satu atau dua petugas yang bertanggung jawab memperbarui jadwal dan pengumuman.</p>
                    <h3>5. Terbitkan dan hubungkan ke Google Maps</h3>
                    <p>Cantumkan alamat website di profil Google Maps klinik, papan nama, dan kartu pasien, sehingga pasien yang mencari klinik di sekitarnya bisa langsung menemukan informasinya.</p>
                    <h2>Perhatikan Etika Publikasi Layanan Kesehatan</h2>
                    <p>Publikasi fasilitas pelayanan kesehatan di Indonesia diatur, antara lain melalui Peraturan Menteri Kesehatan Nomor 1787/MENKES/PER/XII/2010 tentang Iklan dan Publikasi Pelayanan Kesehatan. Prinsip praktisnya:</p>
                    <ul>
                        <li><p>Sampaikan informasi yang <strong>faktual</strong>: layanan, jadwal, dan fasilitas yang benar-benar tersedia.</p></li>
                        <li><p>Hindari klaim superlatif seperti "terbaik" atau "paling lengkap".</p></li>
                        <li><p>Jangan menjanjikan kesembuhan atau hasil pengobatan tertentu.</p></li>
                    </ul>
                    <p>Pastikan isi website diperiksa oleh penanggung jawab klinik dan sesuai dengan ketentuan terbaru yang berlaku.</p>
                    <h2>Mulai Sekarang</h2>
                    <p>Informasi yang jelas adalah bagian dari pelayanan. Pelajari juga <a href="{$jadwal}">cara menampilkan jadwal praktik dokter online di website klinik</a>, atau langsung <a href="/website-klinik">buat website klinik di website-mu.id</a>, coba gratis tanpa coding.</p>
                    HTML,
            ],
            [
                'slug' => self::SLUGS['jadwal'],
                'title' => 'Jadwal Praktik Dokter Online: Cara Klinik Mengurangi Pertanyaan Berulang dari Pasien',
                'body' => <<<HTML
                    <p>Jadwal praktik dokter online di website klinik membantu pasien merencanakan kunjungan tanpa harus menelepon dulu. Artikel ini membahas cara menyajikan jadwal dokter yang jelas, akurat, dan mudah ditemukan di Google.</p>
                    <h2>Pertanyaan yang Paling Sering Diterima Klinik</h2>
                    <p>Tanyakan pada petugas pendaftaran klinik mana pun, jawabannya hampir sama: "Dokter gigi praktik hari apa?", "Hari Minggu buka?", "Jam berapa dokter anak datang?". Pertanyaan-pertanyaan ini menyita waktu petugas, padahal jawabannya selalu sama setiap minggu.</p>
                    <p>Jadwal praktik yang dipublikasikan online menjawabnya sekali untuk semua pasien.</p>
                    <h2>Cara Menyajikan Jadwal Praktik Dokter yang Baik</h2>
                    <h3>1. Lengkapi informasi setiap dokter</h3>
                    <p>Cantumkan nama lengkap dengan gelar, spesialisasi atau layanan, hari praktik, dan jam praktik. Contoh: "drg. Nama Dokter - Poli Gigi - Senin, Rabu, Jumat - 16.00-20.00 WIB".</p>
                    <h3>2. Kelompokkan berdasarkan layanan</h3>
                    <p>Pasien biasanya mencari berdasarkan kebutuhan, misalnya poli gigi atau KIA, bukan nama dokter. Urutkan jadwal sesuai layanan agar mudah dipindai.</p>
                    <h3>3. Selalu perbarui saat ada perubahan</h3>
                    <p>Jadwal yang salah lebih merugikan daripada tidak ada jadwal sama sekali. Ubah jadwal begitu ada dokter cuti atau pergantian shift, dan umumkan perubahan mendadak lewat fitur pengumuman.</p>
                    <h3>4. Tampilkan jam operasional dan hari libur</h3>
                    <p>Selain jadwal dokter, tampilkan jam buka klinik dan informasi libur hari raya, termasuk layanan darurat bila tersedia.</p>
                    <h3>5. Sediakan jalur bertanya</h3>
                    <p>Untuk hal yang tidak tercantum, sediakan tombol WhatsApp admin di website. Pasien bertanya lebih spesifik, dan petugas tidak lagi menjawab pertanyaan dasar.</p>
                    <h2>Menampilkan Jadwal Dokter di Website Klinik</h2>
                    <p>Di <a href="/website-klinik">website klinik website-mu.id</a>, jadwal praktik dokter diisi dan diubah dari dashboard, lalu langsung tampil di website. Pasien juga bisa menghubungi admin lewat WhatsApp dari halaman yang sama.</p>
                    <p>Perlu dicatat, website-mu.id saat ini berfungsi sebagai website informasi klinik, bukan sistem antrean atau rekam medis. Bila klinik Anda memakai sistem antrean tersendiri, website tetap bisa menjadi pintu informasi utama yang mengarahkan pasien ke sana.</p>
                    <h2>Bonus: Jadwal Dokter Membantu SEO Klinik</h2>
                    <p>Pasien sering mencari "dokter gigi di [nama kecamatan]" atau "klinik buka hari Minggu di [nama kota]". Halaman jadwal yang memuat nama layanan, hari, dan lokasi klinik membantu website Anda muncul pada pencarian seperti itu.</p>
                    <h2>Kesimpulan</h2>
                    <p>Jadwal praktik dokter online yang akurat menghemat waktu petugas dan memudahkan pasien. Belum punya website klinik? Ikuti <a href="{$website}">panduan cara membuat website klinik tanpa coding</a>, atau langsung <a href="/website-klinik">coba gratis di website-mu.id</a>.</p>
                    HTML,
            ],
        ];
    }
}
