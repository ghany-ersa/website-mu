# Product Brief - Platform Website Muhammadiyah

**Status:** Draft untuk product planning dan development  
**Nama kerja:** Website-mu  
**Kategori:** Multi-tenant no-code website builder dan CMS organisasi  
**Tagline kerja:** *Susun. Kelola. Publikasikan.*

## 1. Ringkasan Produk

Website-mu adalah platform no-code untuk membantu organisasi dalam ekosistem Muhammadiyah membuat, mengelola, dan menerbitkan website profesional tanpa kemampuan teknis. Pengguna memilih jenis organisasi dan template, menyusun halaman melalui komponen *drag-and-drop*, mengisi konten, lalu menerbitkan situs ke subdomain atau domain sendiri.

Produk ini tidak diposisikan hanya sebagai pembuat landing page. Arah jangka panjangnya adalah infrastruktur digital bersama yang menghubungkan website, konten, dan struktur organisasi Muhammadiyah dari tingkat induk hingga unit.

> **Visi produk:** setiap organisasi Muhammadiyah memiliki kehadiran digital yang profesional, mudah dikelola, dan saling terhubung.

## 2. Latar Belakang dan Masalah

Organisasi Muhammadiyah, Ortom, Amal Usaha Muhammadiyah (AUM), dan masjid membutuhkan website untuk profil, informasi, layanan, serta publikasi kegiatan. Namun banyak unit menghadapi hambatan berikut.

1. **Keterbatasan tenaga teknis.** Pengurus atau tim media dapat mengelola media sosial, tetapi tidak selalu mampu membangun dan memelihara website.
2. **Biaya dan proses pembuatan tinggi.** Pembuatan website umumnya membutuhkan desainer, pengembang, hosting, domain, dan pemeliharaan terpisah.
3. **Kualitas dan identitas digital tidak konsisten.** Website dibuat sendiri-sendiri dengan desain, struktur informasi, dan pengalaman pengguna yang beragam.
4. **Website statis dan tidak terurus.** Banyak situs berhenti sebagai brosur digital, bukan pusat konten dan layanan organisasi.
5. **Konten dan jaringan organisasi belum terhubung.** Berita atau informasi dari unit belum mudah didistribusikan ke portal induk maupun jaringan di atas/bawahnya.

## 3. Solusi yang Ditawarkan

Platform menyediakan alur terpandu untuk membangun dan mengoperasikan website organisasi:

1. Daftar dan membuat organisasi.
2. Memilih jenis organisasi.
3. Memilih template yang relevan.
4. Menyusun halaman menggunakan komponen siap pakai.
5. Mengelola konten melalui CMS sederhana.
6. Melihat pratinjau desktop dan mobile.
7. Menerbitkan ke subdomain platform atau domain kustom.

Sistem menjaga kemudahan penggunaan tanpa mengorbankan konsistensi desain lewat design system terpandu, komponen yang relevan dengan kebutuhan organisasi, serta bantuan AI untuk struktur dan penulisan konten.

## 4. Target Pengguna

| Segmen | Contoh | Kebutuhan utama |
|---|---|---|
| Struktur Persyarikatan | PDM, PCM, PRM | Profil, berita, program, struktur pengurus, jaringan unit |
| Organisasi Otonom | Pemuda Muhammadiyah, Nasyiatul Aisyiyah, IMM, IPM, Hizbul Wathan, Tapak Suci | Kampanye, kegiatan, rekrutmen, publikasi |
| AUM Pendidikan | Sekolah, madrasah, universitas | Profil, PPDB/PMB, program, berita, kontak |
| AUM Kesehatan dan Sosial | Klinik, rumah sakit, panti asuhan | Layanan, informasi, agenda, donasi |
| Masjid dan Islamic Center | Masjid, mushalla | Jadwal salat, kajian, pengumuman, donasi, lokasi |

**Persona utama:** ketua/pimpinan organisasi, sekretaris, admin, tim media, editor, dan kontributor konten yang tidak memiliki latar belakang pengembangan web.

## 5. Sasaran Produk dan Prinsip Keberhasilan

### Sasaran utama

- Memungkinkan sebuah unit memiliki website pertama yang tayang dalam waktu kurang dari 30 menit.
- Menjadikan pembaruan berita, agenda, dan pengumuman mudah dilakukan oleh tim nonteknis.
- Menjaga kualitas visual dan struktur informasi lintas organisasi.
- Membuka jalan bagi jaringan distribusi konten dan pengelolaan organisasi bertingkat.

### Prinsip desain produk

- **Sederhana secara default:** pengguna diberi pilihan yang jelas, bukan kanvas kosong yang membingungkan.
- **Terpandu, bukan membatasi:** fleksibilitas tersedia dalam pagar design system.
- **Relevan secara kontekstual:** template dan komponen dibuat untuk kebutuhan organisasi Muhammadiyah.
- **Mobile-first:** situs dan editor mendukung penggunaan perangkat mobile.
- **Siap bertumbuh:** fondasi mendukung tenant, role, domain, dan jaringan organisasi.

## 6. UX Flow Utama

```text
DAFTAR / MASUK
      ↓
BUAT ORGANISASI
      ↓
PILIH JENIS ORGANISASI
      ↓
PILIH TEMPLATE
      ↓
ISI IDENTITAS & BRAND
      ↓
SUSUN HALAMAN (DRAG & DROP)
      ↓
EDIT / KELOLA KONTEN
      ↓
PREVIEW DESKTOP & MOBILE
      ↓
PUBLISH
      ↓
SUBDOMAIN PLATFORM / DOMAIN KUSTOM
```

### Alur onboarding yang direkomendasikan

- Pengguna memilih tipe organisasi di awal agar platform dapat menyarankan struktur halaman dan komponen yang tepat.
- Template diisi dengan konten contoh yang jelas ditandai untuk diganti.
- Checklist onboarding menunjukkan progres: logo, profil, halaman utama, kontak, dan publish.
- Setelah publish, dashboard mengarahkan pengguna ke tugas rutin: menulis berita, menambah agenda, dan melihat statistik dasar.

## 7. Template Library

Template adalah titik awal yang siap dipublikasikan, bukan sekadar variasi visual. Setiap template memiliki struktur halaman, konten contoh, dan komponen yang sesuai dengan jenis organisasi.

### Kategori awal

- **Persyarikatan:** PDM, PCM, PRM.
- **Ortom:** Pemuda Muhammadiyah, Nasyiatul Aisyiyah, IMM, IPM, Hizbul Wathan, Tapak Suci.
- **AUM Pendidikan:** sekolah, madrasah, perguruan tinggi.
- **AUM Kesehatan dan Sosial:** klinik, rumah sakit, panti, layanan sosial.
- **Masjid:** masjid, mushalla, Islamic Center.

### Contoh struktur template masjid

- Hero dan ajakan utama.
- Jadwal salat.
- Pengumuman penting.
- Jadwal kajian dan agenda.
- Profil masjid dan imam/khatib.
- Donasi, zakat, infak, QRIS atau tautan Lazismu.
- Galeri, lokasi, dan kontak.

**Catatan pengembangan lanjutan (kegiatan masjid):** entitas Agenda pada CMS (lihat bagian 12) saat ini masih generik (tanggal, lokasi, narahubung, deskripsi, pendaftaran/tautan). Untuk kebutuhan masjid, pertimbangkan penambahan:
- Field pengisi/ustadz dan topik/kitab yang dibahas.
- Kategori kegiatan (kajian rutin, agenda tahunan/musiman seperti Ramadhan/Qurban/Muharram, kegiatan sosial).
- Dukungan jadwal berulang (recurring), misalnya kajian tiap Ahad, bukan hanya tanggal tunggal.
- Keterkaitan Agenda dengan Galeri, agar dokumentasi kegiatan yang sudah lewat bisa tampil otomatis sebagai bukti aktivitas masjid.

## 8. Component Library

Component library adalah inti pengalaman builder. Komponen harus bersifat modular, responsif, dan konsisten dengan brand settings organisasi.

| Kelompok | Komponen awal |
|---|---|
| Basic | heading, teks, gambar, video, tombol, divider, spacer, kolom/layout |
| Navigasi | header, menu, breadcrumb, footer, CTA bar |
| Organisasi | logo/identitas, sambutan ketua, profil, struktur pengurus, program kerja, layanan, daftar AUM/Ortom |
| Konten | daftar berita, artikel unggulan, agenda, pengumuman, galeri, video |
| Muhammadiyah-spesifik | profil organisasi, kajian, kalender kegiatan, jadwal salat, zakat/infak/donasi, QRIS, integrasi/tautan Lazismu |
| Konversi & kontak | formulir kontak, pendaftaran, lokasi/peta, FAQ, CTA |

Setiap komponen menyediakan beberapa variasi layout agar situs tetap bervariasi tanpa memerlukan desain dari nol.

## 9. Drag-and-Drop Editor

Editor menggunakan tiga area utama:

```text
┌──────────────────┬──────────────────────────────┬──────────────────┐
│ PANEL KOMPONEN   │ KANVAS HALAMAN               │ PANEL PROPERTI   │
│                  │                              │                  │
│ Sections         │ Header                       │ Konten           │
│ Blocks           │ Hero                         │ Layout           │
│ Saved blocks     │ Berita / agenda / galeri     │ Style terbatas   │
│ Pages            │ Footer                       │ Visibility       │
└──────────────────┴──────────────────────────────┴──────────────────┘
```

### Kemampuan editor MVP

- Tambah, hapus, duplikasi, urutkan, dan simpan section.
- Drag-and-drop section atau komponen ke kanvas.
- Edit teks langsung pada kanvas serta memilih media dari library.
- Pengaturan layout dan spacing yang aman.
- Preview responsif untuk desktop, tablet, dan mobile.
- Undo/redo dan status perubahan belum dipublikasikan.
- Draft, preview, dan publish per halaman.

## 10. Guided Design System

Platform tidak memberi kebebasan desain tanpa batas. Pengguna non-desainer membutuhkan batasan yang membantu mereka menghasilkan situs yang baik secara konsisten.

### Brand Settings

- Logo organisasi.
- Nama organisasi dan singkatan.
- Warna primer dan sekunder.
- Pilihan pasangan font yang dikurasi.
- Gaya tombol.
- Border radius dan karakter visual.

Setelah konfigurasi dilakukan, semua komponen secara otomatis mengikuti token brand tersebut. Pengguna dapat mengubah isi dan memilih variasi komponen, tetapi tidak perlu mengatur warna, tipografi, dan spacing satu per satu.

### Guardrails

- Palet dan pasangan font terbatas namun cukup fleksibel.
- Grid, ukuran, kontras, dan responsivitas ditangani oleh sistem.
- Peringatan untuk gambar beresolusi rendah, teks terlalu panjang, atau kontras tidak memadai.
- Template dan section terkurasi agar struktur informasi tetap sehat.

## 11. AI Co-Pilot

AI berperan sebagai pendamping penyusunan situs dan konten, bukan pengganti kontrol pengguna.

### Use case prioritas

- Membuat struktur halaman berdasarkan jenis dan tujuan organisasi.
- Menghasilkan draf headline, subheadline, profil singkat, dan CTA.
- Mengubah catatan kegiatan menjadi berita yang rapi.
- Meringkas artikel panjang untuk kartu berita atau media sosial.
- Membuat deskripsi program, layanan, agenda, dan pengumuman.
- Memberi saran komponen atau section yang masih kurang.

### Contoh prompt pengguna

> “Saya pengurus PCM Ambulu dan ingin website untuk memperkenalkan organisasi, program unggulan, berita, dan AUM.”

Output yang disarankan AI: hero, tentang organisasi, program unggulan, berita, agenda, daftar AUM/Ortom, CTA, dan kontak.

### Prinsip AI

- Semua keluaran AI selalu dapat diedit sebelum dipublikasikan.
- AI tidak mengarang klaim, data, atau identitas organisasi; pengguna diminta memverifikasi detail faktual.
- Gaya bahasa dapat disesuaikan: formal organisasi, informatif, atau persuasif.

## 12. Content Management System (CMS)

Platform harus mendukung situs yang terus diperbarui, bukan hanya landing page statis.

### Entitas CMS minimum

- **Pages:** Home, Tentang, Program, Kontak, dan halaman custom.
- **Posts/berita:** judul, penulis, kategori, gambar, isi, status, tanggal publikasi.
- **Agenda:** tanggal, lokasi, narahubung, deskripsi, pendaftaran/tautan.
- **Pengumuman:** judul, masa tampil, prioritas, isi.
- **Galeri:** album, media, caption.
- **Data organisasi:** pengurus, AUM, Ortom, program, layanan.
- **Media library:** unggah, folder, alt text, dan penggunaan aset.

Komponen halaman dapat terhubung ke koleksi CMS, misalnya section “Berita Terbaru” otomatis menampilkan post terbaru yang sudah diterbitkan.

## 13. Domain dan Subdomain

Setiap tenant mendapat subdomain segera setelah situs dibuat, misalnya:

```text
pcmambulu.platform.id
sdmuhammadiyah1ambulu.platform.id
```

Paket berbayar dapat menghubungkan domain kustom, misalnya:

```text
pcmambulu.or.id
sdmuh1ambulu.sch.id
namamasjid.or.id
```

Platform menangani SSL, provisioning subdomain, panduan DNS domain kustom, dan status verifikasi domain agar pengguna tidak perlu memahami deployment.

## 14. Multi-Tenant Architecture

Produk dibangun sebagai SaaS multi-tenant: satu platform melayani banyak organisasi dengan data, pengguna, konten, domain, dan pengaturan yang terisolasi per tenant.

```text
Platform
├── Tenant: PDM Jember
├── Tenant: PCM Ambulu
├── Tenant: SD Muhammadiyah 1 Ambulu
├── Tenant: Pemuda Muhammadiyah Ambulu
└── Tenant: Masjid Muhammadiyah
```

### Ruang data tenant

```text
Tenant
├── Website dan pages
├── Theme / brand settings
├── Content dan media
├── Users dan roles
├── Domain
├── Subscription
└── Analytics
```

### Pertimbangan arsitektur awal

- Tenant ID wajib pada seluruh data bisnis.
- Otorisasi harus memeriksa tenant dan role di setiap operasi.
- Media dan domain dipetakan ke tenant secara eksplisit.
- Arsitektur perlu mendukung relasi parent–child untuk Organization Network pada fase berikutnya.
- Publikasi harus mendukung cache/CDN, HTTPS, dan isolasi konfigurasi tiap domain.

## 15. Role Management

Pengelolaan situs organisasi dilakukan bersama-sama. Role minimum:

| Role | Kewenangan |
|---|---|
| Owner | Kepemilikan organisasi, subscription, domain, seluruh pengaturan dan akses |
| Admin | Mengelola situs, pengguna, konten, dan pengaturan operasional |
| Editor | Membuat, mengedit, menjadwalkan, serta menerbitkan konten dan halaman sesuai izin |
| Author | Membuat dan mengedit draf konten sendiri; publikasi memerlukan persetujuan |

Pengembangan lanjutan dapat menambahkan approval workflow, role kustom, dan audit log.

## 16. Template Marketplace

Marketplace adalah pengembangan jangka panjang untuk memperluas variasi dan ekosistem produk.

- Designer atau mitra dapat membuat template khusus, misalnya template sekolah Muhammadiyah atau masjid.
- Organisasi dapat memasang template atau section siap pakai ke tenant mereka.
- Marketplace dapat berkembang bertahap: **template → component/block → plugin/integrasi**.
- Semua aset marketplace harus mengikuti standar responsivitas, aksesibilitas, keamanan, dan kompatibilitas design system.

Pada tahap awal, library bersifat terkurasi oleh tim platform untuk menjaga kualitas.

## 17. Monetisasi

Model yang direkomendasikan adalah freemium, dengan harga final ditentukan setelah validasi willingness-to-pay dan biaya infrastruktur.

| Paket | Kisaran awal | Cakupan |
|---|---:|---|
| Free | Rp0 | 1 situs, subdomain, batas halaman, template dasar, basic CMS, branding platform |
| Organization | Rp50–100 ribu/bulan | domain kustom, halaman lebih banyak, CMS penuh, tanpa branding, analytics dasar, storage lebih besar |
| Professional | Rp150–300 ribu/bulan | beberapa editor, AI content, analytics lanjutan, komponen lebih banyak, backup, prioritas dukungan |
| Paket Persyarikatan | Custom | pengelolaan banyak tenant, dashboard jaringan, paket dan dukungan organisasi |

Potensi pendapatan tambahan: biaya setup/onboarding, domain dan storage tambahan, template premium, layanan implementasi, serta integrasi khusus.

## 18. Organization Network

Organization Network adalah pembeda strategis jangka panjang: organisasi induk dapat melihat dan menghubungkan unit di bawahnya tanpa menghilangkan kemandirian tiap tenant.

```text
PDM Jember
├── PCM Ambulu
│   ├── PRM A
│   ├── PRM B
│   └── PRM C
├── PCM Wuluhan
└── AUM
    ├── SD Muhammadiyah
    ├── SMP Muhammadiyah
    └── Klinik
```

### Kapabilitas target

- Relasi organisasi induk dan unit.
- Direktori unit dan situs dalam jaringan.
- Dashboard induk untuk status situs, aktivitas, dan kebutuhan bantuan.
- Distribusi konten: berita dari unit dapat dibagikan ke portal induk atau sebaliknya, dengan kontrol persetujuan.
- Template, brand guideline, dan komponen resmi yang dapat dibagikan dari tingkat induk.
- Analitik agregat yang menghormati hak akses tenant.

## 19. Positioning dan Pesan Utama

### Positioning

> **Platform digital untuk membangun, mengelola, dan menghubungkan website seluruh ekosistem Muhammadiyah.**

Versi sederhana untuk komunikasi pemasaran:

> **Bikin website organisasi Muhammadiyah tanpa coding.**

### Tagline alternatif

- **Susun. Kelola. Publikasikan.**
- **Website organisasi, dibuat lebih mudah.**
- **Setiap organisasi punya cerita. Kami bantu menghadirkannya secara digital.**

## 20. Roadmap Produk

### Phase 1 - MVP Website Builder

Tujuan: membuktikan bahwa unit organisasi nonteknis dapat membuat dan menerbitkan website sendiri.

- Authentication dan onboarding organisasi.
- Pemilihan jenis organisasi dan template.
- Builder halaman berbasis section/component.
- Component library dasar dan template awal.
- Brand settings / guided design system dasar.
- Preview responsif.
- Media upload dan media library dasar.
- CMS dasar: pages, berita, agenda, pengumuman.
- Subdomain publishing.
- Domain kustom.
- Role dasar: Owner, Admin, Editor, Author.

**Exit criteria:** situs organisasi dapat dibuat, diisi, dikelola, dan dipublikasikan end-to-end tanpa bantuan developer.

### Phase 2 - AI Content Assistant

Tujuan: mempercepat penyusunan struktur situs dan produksi konten.

- AI page/section generator.
- AI copy assistant untuk profil, berita, program, dan CTA.
- Rewrite, summary, dan tone adjustment.
- Saran kelengkapan konten serta kontrol review pengguna.

### Phase 3 - Organization Network

Tujuan: menghubungkan organisasi dan unit dalam struktur Persyarikatan.

- Parent–child organization relationship.
- Directory dan dashboard jaringan.
- Content syndication dengan approval.
- Shared template, guideline, dan komponen resmi.

### Phase 4 - Marketplace

Tujuan: memperluas variasi desain dan memperkuat ekosistem creator/mitra.

- Template marketplace.
- Component/section marketplace.
- Kurasi, review, dan mekanisme instalasi aset.
- Monetisasi template premium.

### Phase 5 - Integrasi Ekosistem Muhammadiyah

Tujuan: menjadikan platform sebagai fondasi layanan digital organisasi.

- Integrasi data atau direktori ekosistem yang disetujui.
- Integrasi donasi, Lazismu, QRIS, dan layanan relevan.
- Integrasi formulir, pendaftaran, atau layanan AUM.
- API dan plugin framework untuk partner terverifikasi.

## 21. Ukuran Keberhasilan Awal

- Waktu median dari registrasi hingga publish.
- Persentase tenant yang berhasil publish dalam 7 hari pertama.
- Jumlah situs aktif dan halaman yang diterbitkan.
- Aktivitas konten bulanan: berita, agenda, pengumuman.
- Retensi admin/editor organisasi.
- Persentase pengguna yang beralih dari subdomain ke domain kustom/paket berbayar.
- Kepuasan pengguna terhadap kemudahan editor dan kualitas hasil situs.

## 22. Risiko dan Mitigasi Awal

| Risiko | Mitigasi |
|---|---|
| Builder terlalu kompleks untuk pengguna awal | Mulai dari section terkurasi, onboarding terpandu, dan opsi template-first |
| Desain situs tidak konsisten | Terapkan design tokens, guardrails, dan variasi komponen yang terbatas namun cukup |
| Situs tidak diperbarui setelah publish | Dashboard tugas rutin, CMS sederhana, notifikasi, dan kalender konten |
| Biaya dukungan tinggi | Dokumentasi, template khusus per segmen, serta alur self-service yang kuat |
| Kompleksitas jaringan organisasi terlalu dini | Pisahkan sebagai fase setelah MVP dan validasi kebutuhan tenant independen |

## 23. Keputusan Produk yang Direkomendasikan

Mulailah dengan **template-first website builder + basic CMS** untuk beberapa segmen paling jelas, misalnya PCM/PRM, sekolah, dan masjid. Fokus keberhasilan awal bukan pada jumlah fitur, tetapi pada kemampuan pengguna nonteknis untuk menerbitkan situs yang layak, konsisten, dan terus diperbarui. AI, jaringan organisasi, serta marketplace menjadi pengungkit pertumbuhan setelah alur dasar terbukti dipakai.

