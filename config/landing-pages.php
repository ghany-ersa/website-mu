<?php

/*
|--------------------------------------------------------------------------
| Per-segment SEO landing pages
|--------------------------------------------------------------------------
|
| The homepage pitches every organization type at once, so it can only rank for broad terms
| ("website muhammadiyah"). Each entry here becomes its own page at /{path}, owning one keyword
| cluster with a focused title, H1, and FAQ. Routes, the sitemap, and the marketing footer all
| loop over this array, so adding a segment is one entry here and nothing else - same rule as
| config/page-builder.php.
|
| Copy rules that apply to every entry:
|   - Only promise what the app actually ships. `exclusive` on a feature mirrors either the
|     section's `exclusive` flag in config/page-builder.php or an is_exclusive-only variant in
|     SectionVariantSeeder, so the badge never sells a premium feature as included everywhere.
|   - meta_title stays under ~60 characters and leads with the target keyword.
|   - faq items render visibly AND as FAQPage JSON-LD from the same array; Google treats
|     structured data that doesn't match visible text as a guideline violation.
|   - `icon` is a key into the icon map in resources/views/landing/segment.blade.php.
|   - Klinik copy avoids superlatives and outcome promises ("terbaik", "pasti sembuh"): health
|     facility publicity in Indonesia is regulated, and tenants will echo the pitch we give them.
|
*/

return [

    'segments' => [

        'masjid' => [
            'path' => 'website-masjid',
            'organization_type_slug' => 'masjidmushola',
            'name' => 'Website Masjid',
            'meta_title' => 'Website Masjid Profesional Tanpa Coding - website-mu.id',
            'meta_description' => 'Buat website masjid yang membanggakan jamaah: laporan keuangan transparan, program donasi & wakaf, jadwal kajian, kalkulator zakat, dan profil takmir. Tanpa coding, siap terbit kurang dari 30 menit.',
            'hero' => [
                'badge' => 'Khusus Masjid & Mushola',
                'title' => 'Website Masjid yang',
                'highlight' => 'Membanggakan Jamaah.',
                'body' => 'Setiap masjid punya keunggulan: kajian yang hidup, program wakaf yang terus tumbuh, kas yang dikelola amanah. Tampilkan semuanya dalam satu website resmi masjid - tanpa coding, siap terbit kurang dari 30 menit.',
                'primary_cta' => 'Buat Website Masjid Sekarang',
                'secondary_cta' => 'Lihat Contoh Website Masjid',
                'mockup_url' => 'masjid-anda.website-mu.id',
                'image_alt' => 'Contoh website masjid buatan website-mu.id',
                'floating_label' => 'Kas Bulan Ini',
                'floating_text' => 'Laporan Terbuka untuk Jamaah',
                'stat_label' => 'Fitur Khas Masjid',
            ],
            'why' => [
                'badge' => 'Mengapa Masjid Perlu Website?',
                'title' => 'Keunggulan Masjid Anda Layak Dikenal Lebih Luas',
                'body' => 'Jamaah hari ini mencari masjid lewat ponsel. Website masjid menjadi wajah resmi yang menumbuhkan kepercayaan - jauh sebelum mereka melangkah masuk.',
                'points' => [
                    ['title' => 'Kepercayaan Donatur Tumbuh', 'body' => 'Laporan keuangan yang terbuka adalah bukti amanah. Donatur yang melihat dananya tercatat rapi cenderung kembali berinfak.'],
                    ['title' => 'Dakwah Menjangkau Generasi Muda', 'body' => 'Jadwal kajian dan kegiatan yang mudah ditemukan di Google membawa jamaah baru, termasuk anak muda dan pendatang di sekitar masjid.'],
                    ['title' => 'Informasi Tidak Tenggelam', 'body' => 'Pengumuman di grup WhatsApp cepat tertimbun. Di website, info penting tetap tersimpan dan bisa dibagikan ulang kapan saja.'],
                ],
            ],
            'features' => [
                'badge' => 'Fitur Website Masjid',
                'title' => 'Semua yang Dibutuhkan Takmir, dalam Satu Website',
                'body' => 'Dirancang bersama pengurus masjid, bukan template website umum yang dipaksakan.',
                'items' => [
                    ['icon' => 'report', 'exclusive' => true, 'title' => 'Laporan Keuangan Transparan', 'body' => 'Pemasukan dan pengeluaran kas masjid tampil rapi per bulan. Jamaah dan donatur bisa melihat sendiri ke mana infaknya digunakan.'],
                    ['icon' => 'heart', 'exclusive' => true, 'title' => 'Program Donasi & Wakaf', 'body' => 'Wakaf pembangunan, wakaf Al-Qur\'an, bakti sosial - lengkap dengan target dan progress bar yang terus bergerak setiap donasi dicatat.'],
                    ['icon' => 'calculator', 'exclusive' => true, 'title' => 'Kalkulator Zakat', 'body' => 'Jamaah menghitung zakat maal sendiri berdasarkan nisab emas, lalu langsung terhubung ke WhatsApp amil masjid untuk konsultasi.'],
                    ['icon' => 'building', 'exclusive' => true, 'title' => 'Fasilitas Masjid', 'body' => 'Pamerkan ruang utama, tempat wudhu, perpustakaan, TPA, hingga area parkir - kebanggaan yang selama ini hanya diketahui jamaah tetap.'],
                    ['icon' => 'calendar', 'exclusive' => true, 'title' => 'Akad Nikah di Masjid', 'body' => 'Kenalkan aula masjid untuk akad nikah dan acara jamaah, dengan ketentuan infak yang bisa Anda tulis sendiri - tanpa kesan menyewakan rumah Allah.'],
                    ['icon' => 'book', 'exclusive' => false, 'title' => 'Jadwal Kajian & Agenda', 'body' => 'Kajian rutin, pengajian ahad pagi, dan kegiatan Ramadan tersusun dalam agenda yang mudah dibagikan ke grup WhatsApp jamaah.'],
                    ['icon' => 'megaphone', 'exclusive' => false, 'title' => 'Pengumuman & Berita', 'body' => 'Info petugas Jumat, jadwal Idul Adha, hingga liputan kegiatan - tersimpan rapi, tidak tenggelam di riwayat chat grup.'],
                    ['icon' => 'map', 'exclusive' => false, 'title' => 'Galeri, Takmir & Lokasi', 'body' => 'Dokumentasi kegiatan, susunan pengurus takmir, serta peta lokasi agar musafir dan jamaah baru mudah menemukan masjid Anda.'],
                ],
            ],
            'templates' => [
                'badge' => 'Contoh Website Masjid',
                'title' => 'Pilih Template Website Masjid',
                'body' => 'Buka preview untuk melihat contoh website masjid yang sudah terisi lengkap, lalu gunakan sebagai titik awal masjid Anda.',
                'link_label' => 'Lihat Semua Template Masjid',
            ],
            'steps' => [
                'badge' => 'Cara Membuat Website Masjid',
                'title' => 'Tiga Langkah, Masjid Anda Online',
                'items' => [
                    ['title' => 'Pilih Template Masjid', 'body' => 'Pilih desain yang paling mencerminkan karakter masjid Anda, lalu sesuaikan warna dan logonya.'],
                    ['title' => 'Isi Profil Masjid', 'body' => 'Masukkan susunan takmir, jadwal kajian, fasilitas, program donasi, dan foto kegiatan lewat formulir sederhana.'],
                    ['title' => 'Terbitkan & Bagikan', 'body' => 'Website masjid tayang di alamat sendiri dan siap dibagikan ke grup WhatsApp jamaah.'],
                ],
            ],
            'faq' => [
                'title' => 'Seputar Website Masjid',
                'items' => [
                    ['q' => 'Apakah saya perlu bisa coding untuk membuat website masjid?', 'a' => 'Tidak sama sekali. Anda cukup memilih template masjid, mengisi profil seperti nama takmir, jadwal kajian, dan foto kegiatan lewat formulir, lalu menerbitkannya. Semuanya dikelola dari dashboard tanpa menyentuh kode.'],
                    ['q' => 'Berapa lama waktu yang dibutuhkan untuk membuat website masjid?', 'a' => 'Target kami kurang dari 30 menit sampai website masjid Anda tayang. Template sudah berisi struktur halaman lengkap, sehingga Anda tinggal mengganti isinya dengan data masjid sendiri.'],
                    ['q' => 'Apakah website masjid bisa dicoba gratis?', 'a' => 'Bisa. Anda dapat mendaftar dan merancang website masjid secara gratis terlebih dahulu. Pilihan paket langganan dapat dilihat di dashboard setelah mendaftar.'],
                    ['q' => 'Bagaimana cara menampilkan laporan keuangan masjid di website?', 'a' => 'Bendahara atau takmir mencatat pemasukan dan pengeluaran kas per bulan dari dashboard. Laporan otomatis tersusun rapi di website, sehingga jamaah dan donatur dapat memantau penggunaan dana masjid secara transparan.'],
                    ['q' => 'Apakah jamaah bisa melihat perkembangan donasi dan wakaf?', 'a' => 'Bisa. Setiap program donasi atau wakaf memiliki target dana dan progress bar. Setiap kali takmir mencatat donasi yang masuk, angka terkumpul dan persentasenya langsung diperbarui di website.'],
                    ['q' => 'Apa alamat website masjid yang akan saya dapatkan?', 'a' => 'Website masjid Anda tayang di subdomain website-mu.id, misalnya nama-masjid.website-mu.id, sehingga mudah diingat dan dibagikan ke jamaah.'],
                    ['q' => 'Apakah pengelolaan website bisa dibagi dengan pengurus takmir lain?', 'a' => 'Bisa. Anda dapat menambahkan pengurus lain sebagai anggota, sehingga sekretaris, bendahara, atau seksi dakwah bisa ikut memperbarui konten sesuai tugasnya.'],
                    ['q' => 'Apakah website-mu.id khusus untuk masjid Muhammadiyah?', 'a' => 'website-mu.id dirancang untuk ekosistem Muhammadiyah, termasuk masjid dan mushola ranting, cabang, maupun masjid di lingkungan amal usaha Muhammadiyah, dengan fitur yang disesuaikan untuk kebutuhan takmir.'],
                ],
            ],
            'cta' => [
                'badge' => 'Digitalisasi Masjid',
                'title' => 'Saatnya Masjid Anda Tampil Membanggakan',
                'body' => 'Coba gratis dan rancang website masjid Anda sendiri sekarang. Jamaah akan bangga membagikannya.',
            ],
        ],

        'sekolah' => [
            'path' => 'website-sekolah',
            'organization_type_slug' => 'sekolahmadrasah',
            'name' => 'Website Sekolah',
            'meta_title' => 'Website Sekolah Muhammadiyah Tanpa Coding - website-mu.id',
            'meta_description' => 'Buat website sekolah Muhammadiyah yang meyakinkan calon wali murid: profil sekolah, prestasi siswa, kegiatan & ekstrakurikuler, hingga formulir pendaftaran via WhatsApp. Tanpa coding, siap terbit kurang dari 30 menit.',
            'hero' => [
                'badge' => 'Khusus Sekolah & Madrasah',
                'title' => 'Website Sekolah yang',
                'highlight' => 'Dipercaya Orang Tua.',
                'body' => 'Orang tua mencari sekolah lewat Google sebelum datang survei. Tampilkan prestasi siswa, kegiatan, dan keunggulan sekolah Anda dalam satu website resmi - tanpa coding, siap terbit kurang dari 30 menit.',
                'primary_cta' => 'Buat Website Sekolah Sekarang',
                'secondary_cta' => 'Lihat Contoh Website Sekolah',
                'mockup_url' => 'sekolah-anda.website-mu.id',
                'image_alt' => 'Contoh website sekolah Muhammadiyah buatan website-mu.id',
                'floating_label' => 'Pendaftaran Dibuka',
                'floating_text' => 'Calon Wali Murid Langsung Chat',
                'stat_label' => 'Fitur Khas Sekolah',
            ],
            'why' => [
                'badge' => 'Mengapa Sekolah Perlu Website?',
                'title' => 'Sekolah Pilihan Dimulai dari Pencarian Google',
                'body' => 'Sebelum mendaftarkan anak, orang tua membandingkan sekolah dari layar ponsel. Website sekolah adalah kesan pertama yang menentukan apakah mereka datang survei.',
                'points' => [
                    ['title' => 'Kesan Pertama yang Meyakinkan', 'body' => 'Profil, visi misi, dan fasilitas yang tersaji rapi menunjukkan sekolah yang dikelola dengan sungguh-sungguh.'],
                    ['title' => 'Prestasi Jadi Bukti Mutu', 'body' => 'Juara lomba, hafalan Al-Qur\'an, dan karya siswa adalah bukti nyata kualitas pendidikan - tampilkan, jangan hanya disimpan di lemari piala.'],
                    ['title' => 'Satu Pintu Informasi Sekolah', 'body' => 'Pengumuman, agenda, dan berita sekolah tersimpan di satu alamat resmi, tidak tercecer di banyak grup kelas.'],
                ],
            ],
            'features' => [
                'badge' => 'Fitur Website Sekolah',
                'title' => 'Semua yang Dibutuhkan Sekolah, dalam Satu Website',
                'body' => 'Dirancang untuk kebutuhan sekolah dan madrasah Muhammadiyah, dari profil hingga penerimaan murid baru.',
                'items' => [
                    ['icon' => 'star', 'exclusive' => false, 'title' => 'Prestasi Sekolah & Siswa', 'body' => 'Deretan juara lomba, olimpiade, tahfidz, dan penghargaan sekolah tersaji rapi lengkap dengan statistik pencapaian.'],
                    ['icon' => 'user', 'exclusive' => true, 'title' => 'Profil Siswa Berprestasi', 'body' => 'Setiap siswa berprestasi punya halaman profil sendiri - kebanggaan yang bisa dibagikan orang tua ke keluarga besar.'],
                    ['icon' => 'chat', 'exclusive' => true, 'title' => 'Formulir Pendaftaran via WhatsApp', 'body' => 'Calon wali murid mengisi nama dan kelas tujuan, lalu pesan pendaftaran langsung terkirim ke WhatsApp panitia.'],
                    ['icon' => 'heart', 'exclusive' => false, 'title' => 'Testimoni Wali Murid', 'body' => 'Cerita orang tua dan alumni menjadi rekomendasi paling dipercaya bagi calon wali murid.'],
                    ['icon' => 'academic', 'exclusive' => false, 'title' => 'Kegiatan & Ekstrakurikuler', 'body' => 'Program unggulan, pembiasaan ibadah, Hizbul Wathan, Tapak Suci, hingga ekstrakurikuler lain tampil menarik.'],
                    ['icon' => 'users', 'exclusive' => false, 'title' => 'Guru & Struktur Sekolah', 'body' => 'Kenalkan kepala sekolah, guru, dan tenaga kependidikan agar orang tua tahu siapa yang mendidik anaknya.'],
                    ['icon' => 'newspaper', 'exclusive' => false, 'title' => 'Berita, Agenda & Pengumuman', 'body' => 'Kabar kegiatan, jadwal ujian, dan pengumuman libur sekolah tersimpan rapi di satu tempat.'],
                    ['icon' => 'photo', 'exclusive' => false, 'title' => 'Galeri & Lokasi', 'body' => 'Dokumentasi kegiatan dan peta lokasi sekolah memudahkan calon wali murid datang survei.'],
                ],
            ],
            'templates' => [
                'badge' => 'Contoh Website Sekolah',
                'title' => 'Pilih Template Website Sekolah',
                'body' => 'Buka preview untuk melihat contoh website sekolah yang sudah terisi lengkap, lalu gunakan sebagai titik awal sekolah Anda.',
                'link_label' => 'Lihat Semua Template Sekolah',
            ],
            'steps' => [
                'badge' => 'Cara Membuat Website Sekolah',
                'title' => 'Tiga Langkah, Sekolah Anda Online',
                'items' => [
                    ['title' => 'Pilih Template Sekolah', 'body' => 'Pilih desain yang sesuai karakter sekolah Anda, lalu sesuaikan warna dan logo sekolah.'],
                    ['title' => 'Isi Profil Sekolah', 'body' => 'Masukkan visi misi, data guru, prestasi, kegiatan, dan foto sekolah lewat formulir sederhana.'],
                    ['title' => 'Terbitkan & Bagikan', 'body' => 'Cantumkan alamat website di brosur penerimaan murid baru, spanduk, dan grup wali murid.'],
                ],
            ],
            'faq' => [
                'title' => 'Seputar Website Sekolah',
                'items' => [
                    ['q' => 'Apakah perlu keahlian IT untuk membuat website sekolah?', 'a' => 'Tidak. Operator sekolah atau guru cukup memilih template, mengisi profil sekolah, data guru, prestasi, dan kegiatan lewat formulir, lalu menerbitkannya. Tidak ada kode yang perlu disentuh.'],
                    ['q' => 'Berapa lama waktu yang dibutuhkan untuk membuat website sekolah?', 'a' => 'Target kami kurang dari 30 menit sampai website sekolah Anda tayang. Template sudah berisi struktur halaman lengkap, sehingga Anda tinggal mengganti isinya dengan data sekolah sendiri.'],
                    ['q' => 'Apakah website sekolah bisa dicoba gratis?', 'a' => 'Bisa. Anda dapat mendaftar dan merancang website sekolah secara gratis terlebih dahulu. Pilihan paket langganan dapat dilihat di dashboard setelah mendaftar.'],
                    ['q' => 'Apakah website sekolah bisa dipakai untuk penerimaan murid baru?', 'a' => 'Bisa untuk promosi dan pendaftaran awal. Formulir pendaftaran mengirim data calon murid langsung ke WhatsApp panitia, sehingga panitia bisa menindaklanjuti satu per satu. Website-mu.id bukan sistem seleksi SPMB/PPDB online yang lengkap.'],
                    ['q' => 'Bagaimana cara menampilkan prestasi siswa?', 'a' => 'Prestasi dicatat dari dashboard lengkap dengan nama lomba, tingkat, dan tahunnya, lalu otomatis tampil di website. Siswa berprestasi juga bisa memiliki halaman profil sendiri.'],
                    ['q' => 'Apa alamat website sekolah yang akan saya dapatkan?', 'a' => 'Website sekolah Anda tayang di subdomain website-mu.id, misalnya nama-sekolah.website-mu.id, sehingga mudah dicantumkan di brosur dan spanduk.'],
                    ['q' => 'Apakah website bisa dikelola beberapa guru atau operator?', 'a' => 'Bisa. Anda dapat menambahkan guru atau operator lain sebagai anggota, sehingga pembaruan berita dan prestasi tidak bergantung pada satu orang.'],
                    ['q' => 'Jenjang sekolah apa saja yang bisa memakai website-mu.id?', 'a' => 'website-mu.id dirancang untuk sekolah dan madrasah di lingkungan Muhammadiyah dan Aisyiyah, mulai dari TK dan PAUD, SD/MI, SMP/MTs, hingga SMA/SMK/MA.'],
                ],
            ],
            'cta' => [
                'badge' => 'Digitalisasi Sekolah',
                'title' => 'Saatnya Sekolah Anda Ditemukan Calon Wali Murid',
                'body' => 'Coba gratis dan rancang website sekolah Anda sendiri sekarang, sebelum musim penerimaan murid baru dimulai.',
            ],
        ],

        'klinik' => [
            'path' => 'website-klinik',
            'organization_type_slug' => 'klinikrumah-sakit',
            'name' => 'Website Klinik',
            'meta_title' => 'Website Klinik & Rumah Sakit Tanpa Coding - website-mu.id',
            'meta_description' => 'Buat website klinik pratama, klinik Aisyiyah, atau rumah sakit Muhammadiyah: jadwal praktik dokter, daftar layanan, edukasi kesehatan, dan kontak WhatsApp. Tanpa coding, siap terbit kurang dari 30 menit.',
            'hero' => [
                'badge' => 'Khusus Klinik & Rumah Sakit',
                'title' => 'Website Klinik yang',
                'highlight' => 'Memudahkan Pasien.',
                'body' => 'Pasien mencari jadwal dokter dan layanan lewat ponsel sebelum datang. Sajikan informasi klinik yang jelas dan selalu terbaru dalam satu website resmi - tanpa coding, siap terbit kurang dari 30 menit.',
                'primary_cta' => 'Buat Website Klinik Sekarang',
                'secondary_cta' => 'Lihat Contoh Website Klinik',
                'mockup_url' => 'klinik-anda.website-mu.id',
                'image_alt' => 'Contoh website klinik buatan website-mu.id',
                'floating_label' => 'Hari Ini',
                'floating_text' => 'Jadwal Dokter Selalu Terbaru',
                'stat_label' => 'Fitur Khas Klinik',
            ],
            'why' => [
                'badge' => 'Mengapa Klinik Perlu Website?',
                'title' => 'Pasien Mencari Informasi Sebelum Datang',
                'body' => 'Jam buka, jadwal dokter, dan jenis layanan adalah hal pertama yang dicari pasien. Website klinik menjawabnya kapan saja, tanpa menunggu telepon diangkat.',
                'points' => [
                    ['title' => 'Pertanyaan Berulang Berkurang', 'body' => 'Jadwal praktik dan jam layanan yang tampil jelas di website mengurangi pertanyaan yang sama di telepon dan WhatsApp.'],
                    ['title' => 'Layanan Dikenal Masyarakat', 'body' => 'Banyak warga tidak tahu klinik di dekatnya punya layanan gigi, KIA, atau laboratorium. Website membuatnya mudah ditemukan.'],
                    ['title' => 'Edukasi Kesehatan sebagai Dakwah', 'body' => 'Artikel kesehatan dan program promotif seperti posyandu dan senam sehat memperluas manfaat klinik bagi masyarakat sekitar.'],
                ],
            ],
            'features' => [
                'badge' => 'Fitur Website Klinik',
                'title' => 'Informasi Lengkap untuk Pasien, dalam Satu Website',
                'body' => 'Dirancang untuk klinik dan rumah sakit di lingkungan Muhammadiyah dan Aisyiyah.',
                'items' => [
                    ['icon' => 'clock', 'exclusive' => false, 'title' => 'Jadwal Praktik Dokter', 'body' => 'Nama dokter, spesialisasi, dan jam praktik tampil rapi dan bisa diperbarui kapan saja dari dashboard.'],
                    ['icon' => 'clipboard', 'exclusive' => false, 'title' => 'Daftar Layanan', 'body' => 'Poli umum, gigi, KIA, laboratorium, farmasi - setiap layanan dijelaskan agar pasien tahu ke mana harus datang.'],
                    ['icon' => 'building', 'exclusive' => false, 'title' => 'Profil & Fasilitas Klinik', 'body' => 'Sejarah, visi misi, dan fasilitas klinik tersaji lengkap dengan statistik layanan.'],
                    ['icon' => 'sparkles', 'exclusive' => false, 'title' => 'Program Kesehatan', 'body' => 'Posyandu, senam sehat, pemeriksaan gratis, dan program promotif lain tampil sebagai bagian dari pelayanan klinik.'],
                    ['icon' => 'newspaper', 'exclusive' => false, 'title' => 'Artikel Edukasi Kesehatan', 'body' => 'Bagikan tips kesehatan dan kabar klinik secara rutin - konten yang juga membantu klinik ditemukan di Google.'],
                    ['icon' => 'megaphone', 'exclusive' => false, 'title' => 'Pengumuman', 'body' => 'Perubahan jadwal dokter, libur hari raya, dan info layanan baru tersampaikan cepat ke pasien.'],
                    ['icon' => 'chat', 'exclusive' => false, 'title' => 'Kontak WhatsApp', 'body' => 'Pasien bisa langsung menghubungi admin klinik lewat WhatsApp dari halaman website.'],
                    ['icon' => 'map', 'exclusive' => false, 'title' => 'Galeri & Lokasi', 'body' => 'Foto fasilitas dan peta lokasi memudahkan pasien baru menemukan klinik Anda.'],
                ],
            ],
            'templates' => [
                'badge' => 'Contoh Website Klinik',
                'title' => 'Pilih Template Website Klinik',
                'body' => 'Buka preview untuk melihat contoh website klinik yang sudah terisi lengkap, lalu gunakan sebagai titik awal klinik Anda.',
                'link_label' => 'Lihat Semua Template Klinik',
            ],
            'steps' => [
                'badge' => 'Cara Membuat Website Klinik',
                'title' => 'Tiga Langkah, Klinik Anda Online',
                'items' => [
                    ['title' => 'Pilih Template Klinik', 'body' => 'Pilih desain yang sesuai identitas klinik Anda, lalu sesuaikan warna dan logonya.'],
                    ['title' => 'Isi Informasi Layanan', 'body' => 'Masukkan jadwal dokter, daftar layanan, jam buka, dan foto fasilitas lewat formulir sederhana.'],
                    ['title' => 'Terbitkan & Bagikan', 'body' => 'Cantumkan alamat website di papan nama, kartu pasien, dan Google Maps klinik Anda.'],
                ],
            ],
            'faq' => [
                'title' => 'Seputar Website Klinik',
                'items' => [
                    ['q' => 'Apakah perlu keahlian IT untuk membuat website klinik?', 'a' => 'Tidak. Admin klinik cukup memilih template, mengisi jadwal dokter, layanan, dan profil klinik lewat formulir, lalu menerbitkannya. Tidak ada kode yang perlu disentuh.'],
                    ['q' => 'Berapa lama waktu yang dibutuhkan untuk membuat website klinik?', 'a' => 'Target kami kurang dari 30 menit sampai website klinik Anda tayang. Template sudah berisi struktur halaman lengkap, sehingga Anda tinggal mengganti isinya dengan data klinik sendiri.'],
                    ['q' => 'Apakah website klinik bisa dicoba gratis?', 'a' => 'Bisa. Anda dapat mendaftar dan merancang website klinik secara gratis terlebih dahulu. Pilihan paket langganan dapat dilihat di dashboard setelah mendaftar.'],
                    ['q' => 'Bagaimana cara memperbarui jadwal praktik dokter?', 'a' => 'Jadwal dokter diubah dari dashboard dan langsung tampil di website. Untuk perubahan mendadak, gunakan juga fitur pengumuman agar pasien segera tahu.'],
                    ['q' => 'Apakah pasien bisa mendaftar antrean online lewat website?', 'a' => 'Saat ini website-mu.id belum menyediakan sistem antrean atau rekam medis. Pasien dapat langsung menghubungi admin klinik lewat WhatsApp dari website untuk bertanya atau membuat janji.'],
                    ['q' => 'Apa yang perlu diperhatikan saat mengisi konten website klinik?', 'a' => 'Isi website sepenuhnya Anda yang menentukan. Pastikan informasinya faktual dan sesuai ketentuan publikasi pelayanan kesehatan yang berlaku, misalnya tidak memakai klaim berlebihan seperti "terbaik" atau menjanjikan kesembuhan.'],
                    ['q' => 'Apa alamat website klinik yang akan saya dapatkan?', 'a' => 'Website klinik Anda tayang di subdomain website-mu.id, misalnya nama-klinik.website-mu.id, yang bisa dicantumkan di Google Maps dan kartu pasien.'],
                    ['q' => 'Fasilitas kesehatan apa saja yang bisa memakai website-mu.id?', 'a' => 'website-mu.id dirancang untuk fasilitas kesehatan di lingkungan Muhammadiyah dan Aisyiyah, seperti klinik pratama, klinik utama, balai pengobatan, rumah bersalin, hingga rumah sakit.'],
                ],
            ],
            'cta' => [
                'badge' => 'Digitalisasi Klinik',
                'title' => 'Saatnya Klinik Anda Mudah Ditemukan Pasien',
                'body' => 'Coba gratis dan rancang website klinik Anda sendiri sekarang. Pasien akan terbantu sejak sebelum mereka datang.',
            ],
        ],

    ],

];
