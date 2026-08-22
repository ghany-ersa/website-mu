# Product Brief — Platform Website Muhammadiyah

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

- **Persyarikatan:** PDM, PCM, PRM — satu template bersama (`persyarikatan-standar`), karena ketiganya berbagi struktur dan tujuan yang sama sebagai pimpinan wilayah/cabang/ranting.
- **Ortom:** Pemuda Muhammadiyah, Nasyiatul Aisyiyah, IMM, IPM, Hizbul Wathan, Tapak Suci — **setiap Ortom punya template tersendiri**, bukan satu template generik. Lihat "Template per Ortom" di bawah.
- **AUM Pendidikan:** sekolah, madrasah, perguruan tinggi.
- **AUM Kesehatan dan Sosial:** klinik, rumah sakit, panti, layanan sosial.
- **Masjid:** masjid, mushalla, Islamic Center.

### Template per Ortom

Setiap Ortom memiliki karakter, audiens, dan tujuan komunikasi yang berbeda, sehingga template generik tidak memadai. Setiap template Ortom disusun dengan tujuan utama yang sama: **menonjolkan citra organisasi, menampilkan program kerja, dan mengajak pengunjung menjadi anggota baru** — namun dengan penekanan konten dan warna identitas yang berbeda per organisasi.

| Ortom | Fokus konten | Warna identitas (draf, belum terverifikasi resmi) |
|---|---|---|
| Nasyiatul Aisyiyah (NA) | Perempuan muda berkemajuan: kajian keputrian, kepemimpinan perempuan, pemberdayaan ekonomi | Ungu |
| Pemuda Muhammadiyah | Kaderisasi pemuda, aksi sosial, kewirausahaan | Hijau + oranye |
| Tapak Suci | Prestasi bela diri, latihan rutin, ujian kenaikan tingkat | Merah + kuning emas |
| Hizbul Wathan (HW) | Kepanduan: perkemahan, kepemimpinan pandu, pendidikan karakter | Cokelat + hijau |
| IMM | Gerakan intelektual mahasiswa: kajian ilmiah, kaderisasi, advokasi | Hitam + merah maroon |
| IPM | Organisasi pelajar: kepemimpinan pelajar, literasi, seni/ekstrakurikuler | Hijau + biru |

> **Catatan:** kode warna di atas adalah dugaan berdasarkan asosiasi umum terhadap identitas visual masing-masing organisasi, **bukan hasil verifikasi dari pedoman brand resmi**. Warna perlu dikonfirmasi ke masing-masing Ortom sebelum dipakai sebagai default produksi.

### Contoh struktur template masjid

- Hero dan ajakan utama.
- Jadwal salat.
- Pengumuman penting.
- Jadwal kajian dan agenda.
- Profil masjid dan imam/khatib.
- Donasi, zakat, infak, QRIS atau tautan Lazismu.
- Galeri, lokasi, dan kontak.

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

Warna primer dan sekunder diisi otomatis dari default template yang dipilih (mis. warna identitas khas tiap Ortom, lihat §7 "Template per Ortom"), sehingga tenant langsung mendapat tampilan yang relevan dengan identitas organisasinya sejak awal — namun tetap dapat diubah oleh pengguna di Brand Settings jika diperlukan.

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

### Phase 1 — MVP Website Builder

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

### Phase 2 — AI Content Assistant

Tujuan: mempercepat penyusunan struktur situs dan produksi konten.

- AI page/section generator.
- AI copy assistant untuk profil, berita, program, dan CTA.
- Rewrite, summary, dan tone adjustment.
- Saran kelengkapan konten serta kontrol review pengguna.

### Phase 3 — Organization Network

Tujuan: menghubungkan organisasi dan unit dalam struktur Persyarikatan.

- Parent–child organization relationship.
- Directory dan dashboard jaringan.
- Content syndication dengan approval.
- Shared template, guideline, dan komponen resmi.

### Phase 4 — Marketplace

Tujuan: memperluas variasi desain dan memperkuat ekosistem creator/mitra.

- Template marketplace.
- Component/section marketplace.
- Kurasi, review, dan mekanisme instalasi aset.
- Monetisasi template premium.

### Phase 5 — Integrasi Ekosistem Muhammadiyah

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

## 24. Status Implementasi & Catatan Lintas Sesi

> Section ini adalah sumber kebenaran untuk *apa yang sudah nyata ada di kode*, bukan pengulangan roadmap di §20. Update section ini setiap sesi kerja mengubah model, migration, route, atau keputusan arsitektur — supaya sesi berikutnya tidak perlu re-derive dari `git log`/`git diff` dari nol. **Catatan:** `CLAUDE.md` masih menyebut repo sebagai "skeleton Laravel 13 tanpa route/controller/model custom" — deskripsi itu sudah usang begitu Phase 1 mulai berjalan (lihat di bawah) dan perlu disinkronkan.

### 24.1 Progres nyata vs Roadmap Phase 1 (§20)

| Item Phase 1 | Status | Catatan |
|---|---|---|
| Authentication dan onboarding organisasi | Sebagian (checklist diperluas 2026-08-22) | Login/register bawaan Laravel Breeze-style ada (`resources/views/auth`). Alur pilih-tipe-lalu-buat sudah ada dari sesi sebelumnya (§24.1 baris Template). Checklist "Langkah Awal" di dashboard organisasi (`Organization::onboardingChecklist()`) kini **4 item** (Atur Brand, Isi Kontak, Susun Halaman, Publish Situs) — item "Susun Halaman" baru ditambah sesi ini (lihat §24.4), mengarah ke `organizations.builder.edit` dan dianggap selesai begitu minimal 1 page organisasi punya minimal 1 section tersimpan (`$this->pages()->whereHas('sections')->exists()`), bukan sekadar "page ada" (selalu true otomatis lewat `ensureHomePageExists()`, jadi tidak informatif). Item "Tambah Pengurus" yang sempat ada di sesi lampau sengaja **tetap tidak dikembalikan** — pengurus tetap dikelola dari dalam builder lewat link "Kelola Pengurus →" di section `struktur-pengurus`. Status selesai tetap dihitung otomatis dari data yang sudah ada, bukan tabel progress baru. **Bukan** wizard multi-step terpisah — checklist muncul di dashboard existing setelah organisasi dibuat, tidak mengubah form create. Item "Publish Situs" kini toggle sungguhan (lihat baris Subdomain publishing) |
| Pemilihan jenis organisasi dan template | Selesai untuk MVP (tahap awal) | Sejak 2026-08-17, form `organizations/create.blade.php` hanya minta pilih jenis organisasi — `template_id` otomatis diisi dari template aktif milik jenis itu lewat `StoreOrganizationRequest::prepareForValidation()` (lihat §24.2). Seluruh 12 jenis organisasi kini punya tepat 1 template aktif (`TemplateSeeder`) sehingga auto-select tidak pernah gagal diam-diam. Flow lama pilih template dulu via `templates.use` tetap ada sebagai jalur alternatif |
| Builder halaman berbasis section/component | Sebagian (dibangun 2026-08-17, disederhanakan jadi 1 halaman per organisasi pada tanggal yang sama, canvas jadi iframe terisolasi pada tanggal yang sama juga) | Model `OrganizationPage`/`OrganizationSection` + editor tiga-panel mobile-first (`organizations/{organization}/builder`) sudah ada: add/remove/duplicate/reorder section, edit konten via form, draft/publish. **Tahap awal ini sengaja dibatasi satu halaman** — `Organization::ensureHomePageExists()` hanya meng-clone halaman pertama template (kalau ada) atau membuat "Beranda" kosong; tidak ada UI buat/hapus halaman meski route/controller-nya dipertahankan untuk nanti (lihat §24.4). **Canvas preview kini `<iframe>` terisolasi** (`organizations.builder.canvas` route, `organizations/builder/canvas.blade.php`) yang merender dalam warna brand organisasi sungguhan — lihat §24.4 untuk detail refactor. CMS binding sudah selesai (lihat baris CMS). Belum ada: media upload di form konten untuk field selain image/photo/galeri (field `stats`/`times` masih read-only) |
| Component library dasar dan template awal | Sebagian | `admin/templates` dengan `templates/sections` (Blade partials) ada untuk tim platform menyusun template. Sejak builder dibangun, 21 partial ini **juga** dipakai langsung sebagai component library tenant (lihat `config/page-builder.php` untuk registry key→field), bukan lagi cuma alat admin |
| Brand settings / guided design system dasar | Selesai untuk MVP (font + border radius ditambah 2026-08-22) | Migration `organizations.primary_color`/`secondary_color`/`logo`/`font_family`/`border_radius`. Halaman standalone `organizations/{org}/brand` (`OrganizationBrandController`) dengan color picker native + text input hex + picker logo (reuse media library) + dropdown font + preset radius (Tajam/Lembut/Bulat) + pratinjau langsung. **Auto-copy dari template saat organisasi dibuat**: `StoreOrganizationRequest::prepareForValidation()` menyalin `template.structure.brand.primary/secondary` ke `organizations.primary_color/secondary_color` kalau belum diisi eksplisit — sesuai PRD §6. `Organization::primaryColor()`/`secondaryColor()`/`fontFamily()`/`borderRadius()` jadi effective-value accessor dengan fallback chain identik: kolom sendiri → `template.structure.brand` → default platform (`#2C368B`/`#079C4E`/`Plus Jakarta Sans`/`soft`) — pola sama persis dengan `templates/preview.blade.php`. **Warna, font, dan radius kini benar-benar terlihat efeknya di canvas builder & preview template** — token font/radius baru didefinisikan di `config/branding.php` (whitelist `fonts`/`radii`, divalidasi via `Rule::in()` di controller) dan di-inject sebagai `tailwind.config.theme.extend.fontFamily.sans`/`borderRadius.brand` di **dua dokumen kembar yang wajib dijaga sinkron**: `organizations/pages/_document.blade.php` (builder canvas + situs publik) dan `templates/preview.blade.php` (preview template mentah). **Refactor besar sesi ini**: seluruh 19 kemunculan class radius statis (`rounded-2xl`/`xl`/`lg`/`3xl`/`r-xl`) di 21 file `templates/sections/*.blade.php` diganti ke `rounded-brand`/`rounded-r-brand` supaya pilihan radius organisasi benar-benar terlihat di semua section (`rounded-full` sengaja tidak disentuh — itu bentuk sirkular permanen, bukan token brand). **Logo kini juga dipakai**: `header.blade.php`/`footer.blade.php` menampilkan `organizations.logo` sebagai `<img>` menggantikan inisial nama saat logo terisi, fallback ke inisial saat kosong; logo juga dipakai sebagai favicon dan gambar hero sebagai og:image. Gaya tombol dari PRD §6 masih belum dikerjakan — scope sengaja dibatasi warna+logo+font+radius |
| Preview responsif | Sebagian | `TemplatePreviewController` + `templates/preview.blade.php` menampilkan preview template statis; builder (`organizations/builder/edit.blade.php`) menampilkan preview live hasil susunan tenant di kanvas tengah dan sudah mobile-responsive sejak 2026-08-17 (lihat §24.4) |
| Media upload dan media library dasar | Sebagian (dibangun 2026-08-17) | Model `Media` (`organization_id`, disk `public`, dimensi) + `MediaController` (`index`/`store`/`destroy`, scoped per organisasi lewat `Organization::media()`) + `MediaPolicy`. Builder punya picker modal (upload + pilih dari library) untuk field bertipe gambar (`image`, `photo`) dan untuk `galeri.items` sebagai daftar gambar repeatable. Field `items` untuk struktur-pengurus/program-unggulan/dll tetap dialihkan ke CMS terpisah (lihat baris CMS) — **field generik `stats`/`times` kini dapat repeater form sendiri** (lihat baris CMS untuk detail), jadi bukan lagi kotak "segera hadir"
| CMS dasar: pages, berita, agenda, pengumuman | Sebagian (diperluas 2026-08-17) | Pages sudah ada lewat `OrganizationPage` (§24.1 baris Builder). Berita/Agenda/Pengumuman: model `Post`/`Agenda`/`Announcement` (masing-masing `belongsTo Organization` langsung, bukan grandchild) + `PostPolicy`/`AgendaPolicy`/`AnnouncementPolicy` (pola membership sama dengan `MediaPolicy`) + controller CRUD dengan halaman standalone di luar builder, ditautkan dari kartu "Konten" di dashboard organisasi. Section `daftar-berita`/`agenda`/`pengumuman` **auto-bind** ke koleksi CMS terkait saat render sebagai halaman tenant (query `published()` scope, urut terbaru, dibatasi `content.limit`) sesuai PRD §12 ("section Berita Terbaru otomatis menampilkan post terbaru yang sudah diterbitkan"). **Data organisasi (pengurus/program/layanan/jaringan AUM-Ortom) baru ditambah pada sesi yang sama**: model `Officer` (struktur-pengurus), `Program` (program-unggulan **dan** layanan — satu tabel dibedakan kolom `type`, karena kedua section berbagi partial `program-unggulan.blade.php` yang sama persis), `OrganizationNetwork` (jaringan-aum-ortom) — masing-masing dengan CRUD standalone + auto-bind, `Officer` juga dapat drag-and-drop reorder (SortableJS, pola sama dengan section builder). **Galeri kini juga entitas CMS penuh** (sesi terpisah tanggal yang sama, lihat §24.4): model `GalleryPhoto` (url/caption/order, CRUD standalone + reorder, pola identik `Officer`) menggantikan `galeri.items` yang sebelumnya cuma daftar URL gambar manual di JSON section — **bukan** struktur album/caption dua-level yang disebut PRD §12 ("album, media, caption"), sengaja disederhanakan jadi flat per-organisasi (semua foto dalam satu pool, tanpa pengelompokan album) atas keputusan eksplisit; album bisa ditambah nanti sebagai lapisan di atas `GalleryPhoto` kalau memang dibutuhkan. **Field generik `stats`/`times` kini bisa diisi langsung dari builder** (2026-08-22, lihat §24.4): `content['stats']` (section `tentang-organisasi`, maks 3 item `{value, label}`, Alpine repeater add/hapus) dan `content['times']` (section `jadwal-salat`, 5 baris tetap Subuh–Isya, label terkunci, hanya jam yang diisi) — keduanya bukan entitas CMS terpisah (tidak ada tabel baru), tetap tersimpan sebagai JSON di `organization_sections.content` seperti field lain, `OrganizationSectionController::update()` tidak perlu diubah karena dot-notation request sudah generik menangani array. **Masih belum ada:** folder/alt text di media library |
| Subdomain publishing | Selesai untuk MVP (dibangun 2026-08-17, lihat §24.4) | Mekanisme Laravel-side lengkap: toggle publish di dashboard (`OrganizationController::publish()`, `Organization::publish()`) benar-benar mengubah `status` dan menstempel `published_at` (kolom yang sebelumnya ada tapi mati total). Route `Route::domain('{organization_slug}.'.config('tenancy.domain'))` (baru, `routes/web.php`) + `OrganizationSiteController` merender situs organisasi publik tanpa auth, hanya untuk organisasi berstatus Published (draft 404 identik dengan slug yang belum pernah ada — tidak bocor keberadaannya). DNS wildcard dan SSL **bukan** bagian dari kode ini — itu setup sekali di sisi hosting (dikonfirmasi kompatibel dengan Laravel Cloud, wildcard subdomain+SSL native mereka), env var `TENANT_DOMAIN` yang menyalakan seluruh mekanisme begitu DNS diarahkan. Domain kustom (bagian PRD §13 yang terpisah) tetap belum dikerjakan |
| Domain kustom | Belum mulai | — |
| Role dasar: Owner, Admin, Editor, Author | Selesai untuk MVP | **Koreksi dari catatan sesi sebelumnya:** role sudah pakai `App\Enums\OrganizationRole` (enum, bukan string bebas) dengan `canManageMembers()`, dan `App\Policies\OrganizationPolicy` sudah menegakkannya (`view`/`update` untuk semua member, `manageMembers`/`delete` untuk Owner/Admin atau Owner). Builder baru menggunakan policy `update` yang sama, tidak menambah role baru |

**Yang sudah ada di luar urutan roadmap:** admin panel untuk mengelola template (`App\Http\Controllers\Admin\TemplateController`, `resources/views/admin`) — ini infrastruktur sisi platform-team, bukan sisi tenant, jadi tidak persis masuk kategori manapun di §20. Juga ada feature test di repo ini (`tests/Feature/OrganizationBuilderTest.php`, `tests/Feature/OrganizationStoreTest.php`) — sebelumnya tidak ada test untuk Organization/Template sama sekali.

### 24.2 Keputusan arsitektur yang sudah de-facto diambil

Ini keputusan yang sudah *terjadi* di kode (bisa dilihat dari migration/model), tapi belum ditulis eksplisit sebagai keputusan di §14. Dicatat di sini supaya tidak dianggap "belum diputuskan" lagi di sesi berikutnya:

- **Multi-tenancy: shared database, shared schema.** Bukan database-per-tenant. Isolasi tenant dilakukan lewat foreign key `organization_id` di tabel-tabel terkait (lihat `organization_user`), bukan lewat koneksi DB terpisah atau schema terpisah. Konsekuensi: setiap query/model yang menyentuh data tenant **wajib** scoping eksplisit ke `organization_id` (global scope Eloquent atau middleware) begitu tabel konten (pages, posts, dst) mulai dibuat — ini belum ada, jadi jadi risiko kalau builder halaman ditambah tanpa scoping dari awal.
- **Role disimpan sebagai string bebas di pivot**, bukan enum/tabel roles terpisah. `organization_user.role` (migration `2026_08_16_162542`) — cocok untuk MVP tapi tidak type-safe. Perlu diputuskan: tetap string + validasi di controller, atau migrasi ke Laravel enum cast / package permission (spatie/laravel-permission) saat role approval workflow (§15) mulai dikerjakan.
- **Template dan Organization sudah dipisah sejak awal** (`template_id` nullable, `nullOnDelete` di `organizations`) — organisasi bisa ada tanpa template terpasang. Ini konsisten dengan §7 (template sebagai titik awal, bukan keharusan permanen).
- **Slug organisasi unik global** (`organizations.slug` unique, bukan unique per sesuatu) — relevan untuk §13 (subdomain: `{slug}.platform.id`) sekaligus berarti slug harus direbut di awal pembuatan organisasi, termasuk untuk organisasi dalam jaringan berjenjang (§18) yang mungkin punya nama mirip.
- **Konten halaman tenant: `organization_pages` + `organization_sections` (dari sesi 2026-08-17).** Section per halaman disimpan sebagai baris ternormalisasi (bukan JSON blob seperti `templates.structure`), tapi kolom `content` tiap section tetap JSON bebas — supaya semua 21 partial section (`resources/views/templates/sections/*.blade.php`) langsung reuse tanpa rewrite, dan supaya field seperti `items`/`stats` bisa diisi CMS di masa depan tanpa migration baru. Struktur di-clone dari `template.structure` ke tabel milik organisasi saat kunjungan pertama ke builder (`Organization::seedPagesFromTemplate()`), bukan lewat artisan backfill command.
- **`organization_sections` tidak punya `organization_id` langsung** — hanya `organization_page_id`. Section tidak pernah di-query lintas-tenant secara langsung, jadi cukup dicapai lewat `organization_pages`. **Konsekuensi penting:** route Laravel `scopeBindings()` tidak bisa otomatis memvalidasi kepemilikan `{section}` terhadap `{organization}` di URL langsung (`organizations/{organization}/sections/{section}`) karena section adalah *grandchild*, bukan *child* langsung — `Organization` tidak punya method `sections()`. Route seperti ini divalidasi manual di `OrganizationSectionController::ensureBelongsToOrganization()`. Kalau menambah tabel baru yang juga grandchild dari `Organization` (pola serupa), ingat pola ini, jangan asumsikan `scopeBindings()` otomatis aman.
- **Editor builder pakai Alpine.js + SortableJS via CDN**, bukan masuk ke pipeline Vite — konsisten dengan pola CDN yang sudah dipakai `layouts/app.blade.php` dan `templates/preview.blade.php`. Ini keputusan sadar untuk menghindari yak-shaving menyatukan dua pipeline aset yang sudah didokumentasikan sebagai inkonsistensi di CLAUDE.md, bukan solusi permanen — perlu direvisit kalau builder makin kompleks (butuh state management reaktif, bukan cuma toggle panel).

### 24.3 Open question: verifikasi warna resmi Ortom (§7)

Tabel warna identitas per Ortom di §7 eksplisit ditandai "draf, belum terverifikasi resmi". Ini **task terbuka**, bukan sekadar catatan:

- [ ] Konfirmasi kode warna resmi (hex, bukan cuma nama warna) untuk NA, Pemuda Muhammadiyah, Tapak Suci, HW, IMM, IPM — idealnya dari pedoman brand/PUEBI masing-masing Ortom tingkat pusat.
- [ ] Setelah terverifikasi, update tabel §7 dan tandai catatan "belum diverifikasi" dihapus.
- [ ] Nilai hex yang terverifikasi perlu dituangkan sebagai default `primary_color`/`secondary_color` saat field brand token ditambahkan ke `organizations` (lihat gap "Brand settings" di §24.1) — jangan hardcode di kode tanpa sumber di PRD ini.

Sampai terverifikasi, jangan gunakan warna di §7 sebagai warna default produksi tanpa disclaimer ke pengguna.

### 24.4 Changelog sesi kerja

Ringkas per sesi: apa yang dikerjakan, keputusan yang diambil, apa yang masih pending. Tambahkan entri baru di atas (paling baru di atas), jangan hapus entri lama.

- **2026-08-22 (menyelesaikan 3 gap Phase 1 tersisa: onboarding, brand font+radius, repeater stats/times — kecuali domain kustom & multi-halaman, sengaja di-skip atas permintaan eksplisit user)** — Onboarding: `Organization::onboardingChecklist()` dapat key ke-4, `content`, dihitung dari `$this->pages()->whereHas('sections')->exists()` (bukan tabel progress baru, konsisten pola 3 item lama) — item dashboard baru "Susun Halaman" mengarah ke `organizations.builder.edit`. Item "Tambah Pengurus" yang sebelumnya sempat ditolak eksplisit **tetap tidak** dikembalikan. Brand settings: migration baru `font_family`/`border_radius` (nullable) + `config/branding.php` (whitelist 4 font — Plus Jakarta Sans/Inter/Poppins/Lora — dan 3 token radius — sharp/soft/full — dengan nilai CSS & Google Fonts URL per opsi) + `Organization::fontFamily()`/`borderRadius()` mereplikasi persis pola fallback 3-tingkat `primaryColor()`/`secondaryColor()` (kolom sendiri → `template.structure.brand` → default platform). `OrganizationBrandController::update()` tambah `Rule::in()` untuk kedua field. Form `brand/edit.blade.php` dapat dropdown font + preset radius (button group) + preview live yang ikut menyesuaikan (Alpine `fontStack()`/`radiusValue()`). **Kritis untuk supaya pilihan benar-benar terlihat** (bukan cuma tersimpan di DB, ini alasan utama kenapa item ini sebelumnya "Sebagian"): kedua dokumen kembar `organizations/pages/_document.blade.php` dan `templates/preview.blade.php` (sudah saling dijaga sinkron sejak sesi Subdomain Publishing untuk warna) diperluas inject `tailwind.config.theme.extend.fontFamily.sans`/`borderRadius.brand` dari token efektif, plus **refactor 19 kemunculan class radius statis** (`rounded-2xl`/`xl`/`lg`/`3xl`/`r-xl`) di seluruh 21 file `templates/sections/*.blade.php` ke `rounded-brand`/`rounded-r-brand` (via `perl -pi` word-boundary replace, `sed` BSD gagal karena tidak mendukung `\b`) — `rounded-full` sengaja tidak disentuh (bentuk sirkular permanen, bukan token brand yang bisa diubah pengguna). **Bug ditemukan & diperbaiki saat verifikasi manual sebelum commit:** `{{ $font['stack'] }}` di dalam `<style>body{font-family:...}</style>` ter-escape Blade jadi HTML entity (`&quot;Plus Jakarta Sans&quot;`, merusak CSS) karena `{{ }}` meng-escape HTML padahal context-nya CSS — diperbaiki ke `{!! !!}` di kedua dokumen (nilai berasal dari config internal, bukan input user, jadi aman). Repeater stats/times: `resources/views/organizations/builder/edit.blade.php` — 2 cabang baru sebelum fallback generik "segera hadir" (yang tetap ada untuk field array lain yang belum ditangani): `stats` (khusus section `tentang-organisasi`, Alpine repeater `x-for` dengan add/hapus baris, dibatasi maks 3 sesuai grid 3-kolom section itu) dan `times` (khusus section `jadwal-salat`, 5 baris Blade loop biasa bukan Alpine array — label Subuh–Isya dikunci via hidden input, user cuma isi jam) — keputusan batas ini dikonfirmasi eksplisit dengan user sebelum implementasi. `OrganizationSectionController::update()` **tidak diubah** — dot-notation `$request->input("content.$field")` sudah generik menangani array asal nama input HTML mengikuti struktur `content[stats][0][value]` dst. Test: `OrganizationBrandTest` diperluas (assert checklist 4-key, test baru font/radius update + reject nilai di luar whitelist), 71/72 total suite lulus (1 kegagalan `ExampleTest` bawaan Laravel adalah pre-existing/tidak terkait, dikonfirmasi via `git stash` sebelum sesi ini juga sudah gagal). **Perbaikan tambahan di luar 3 gap tapi ditemukan lewat code-review multi-agent terhadap seluruh working tree** (sesi lain berjalan paralel di repo yang sama membangun halaman detail tenant publik post/agenda/pengumuman — bug di bawah ada di kode sesi itu, bukan kode sesi ini, tapi diperbaiki langsung atas permintaan user karena termasuk potensial): (1) `route('tenant.posts.show'/'tenant.announcements.show'/'tenant.agendas.show', ...)` dipanggil unconditional di `daftar-berita.blade.php`/`pengumuman.blade.php`/`agenda.blade.php` — route itu cuma terdaftar kalau `TENANT_DOMAIN` terisi (lihat guard di `routes/web.php` sesi Subdomain Publishing), padahal section yang sama dirender juga oleh builder canvas (route berbeda, tidak ke-guard sama) — kalau `TENANT_DOMAIN` kosong, builder canvas crash `RouteNotFoundException` untuk organisasi manapun yang punya section berita/agenda/pengumuman; diperbaiki dengan `Route::has()` guard, fallback `'#'` (pola sama dengan `$item['url'] ?? '#'` section lain). (2) `_article-jsonld.blade.php` — `json_encode(..., JSON_UNESCAPED_SLASHES)` dipakai bareng `{!! !!}` di dalam `<script type="application/ld+json">`; `JSON_UNESCAPED_SLASHES` justru mematikan proteksi default PHP terhadap `</script>` di string ter-encode, jadi judul post berisi `</script><script>...` bisa keluar dari tag dan inject JS ke halaman publik — diperbaiki: ganti flag ke `JSON_HEX_TAG | JSON_HEX_AMP` (meng-escape `<`/`>`/`&` jadi unicode escape, `</script>` tidak bisa lagi terbentuk literal).
- **2026-08-17 (perbaikan: dua sumber publish yang tidak sinkron)** — Dipicu laporan user tepat setelah sesi Subdomain Publishing (entri di bawah): toggle "Publikasikan" di dashboard organisasi (`Organization::status`) dan tombol "Publish"/"Tarik ke draf" di header builder (`OrganizationPage::published_at`, fitur yang sudah ada sejak sesi builder, mendahului sesi Subdomain Publishing) adalah **dua kontrol terpisah yang saling tidak tahu** — situs publik (`OrganizationSiteController`) cuma baca `Organization::status`, tidak pernah baca `OrganizationPage::published_at`, jadi tombol builder terlihat berfungsi (UI berubah "Live"/toggle) tapi sama sekali tidak memengaruhi apakah situs publik bisa diakses. **Keputusan (dikonfirmasi eksplisit dengan user):** satu sumber kebenaran saja — builder hanya dukung 1 halaman per organisasi (§24.1 baris Builder), jadi "halaman published" dan "organisasi published" pada dasarnya konsep yang sama, tidak perlu 2 kontrol. Tombol Publish/Tarik-ke-draf di header `organizations/builder/edit.blade.php` **dihapus**, diganti indikator read-only "Live" (titik hijau berdenyut) yang muncul kalau `$organization->status === Published` — builder sekarang cuma *menampilkan* status, tidak lagi mengubahnya; publish murni lewat dashboard (`organizations.publish`). `OrganizationPageController::update()` kehilangan cabang `publish`/`unpublish` yang menulis `$page->published_at` (kode mati begitu form pemicunya dihapus). **Kolom `organization_pages.published_at` sengaja dibiarkan ada di skema** (dorman, tidak dihapus lewat migration) — konsisten dengan bagaimana `organizations.published_at` sendiri sempat dorman sebelum sesi Subdomain Publishing; tidak ada urgensi membersihkan kolom DB yang tidak dipakai. Test: `OrganizationBuilderTest` (9 test) tetap lulus tanpa perubahan setelah tombol dihapus, membuktikan tidak ada test yang bergantung pada perilaku lama itu.
- **2026-08-17 (Subdomain publishing)** — Item Phase 1 terakhir yang sebelumnya 0% ("Belum mulai") kini selesai untuk MVP: mekanisme Laravel-side untuk mempublikasikan situs organisasi ke `{slug}.{tenant domain}`. **Keputusan arsitektur kunci — satu aplikasi, dua "wajah" berdasarkan `Host` header, bukan dua deployment**: `website-mu.id` (platform) dan `{slug}.website-mu.id` (situs tenant) sama-sama masuk ke proses Laravel yang sama; `Route::domain()` mencabangkan berdasarkan host request, tidak ada server/repo terpisah. Config baru `config/tenancy.php` (`'domain' => env('TENANT_DOMAIN')`) — var terpisah dari `APP_URL` sengaja (APP_URL bawa scheme dan dipakai signed URL/mail, jangan dioverload). **Route tenant di-guard `if ($tenantDomain)`** di `routes/web.php` — kalau `TENANT_DOMAIN` kosong, route itu benar-benar **tidak terdaftar** (bukan cuma tidak match), jadi `php artisan serve` lokal tanpa env ini berperilaku identik dengan sebelum sesi ini, dibuktikan lewat `php artisan route:list --name=tenant.home` yang literally kosong tanpa env var itu. `Organization::publish(bool $published = true)` — method eksplisit (bukan model event/mutator, konsisten gaya `ensureHomePageExists()`) yang menstempel `published_at` **hanya sekali** saat publish pertama kali; unpublish/republish berikutnya tidak menyentuhnya lagi — jadi kolom itu berarti "pertama kali live", bukan "sedang live sejak". `OrganizationStatus` enum **tetap biner** (Draft/Published, tidak ditambah case baru) — "belum pernah publish" vs "pernah publish lalu di-unpublish" sudah cukup dibedakan lewat `published_at` null vs terisi, tidak perlu case ketiga. `OrganizationSiteController::show()` — controller publik baru, filter status ada di query lookup itu sendiri (`where('status', Published)->firstOrFail()`) supaya organisasi Draft 404 dengan cara yang **identik** dengan slug yang belum pernah diklaim (tidak membocorkan bahwa slug itu terpakai tapi belum publish); sengaja **tanpa** `$this->authorize(...)` karena begitu Published, visibilitasnya publik tanpa syarat, beda dengan builder yang selalu cek membership. **View di-refactor supaya tidak duplikat:** scaffold `<html>` yang sebelumnya inline di `organizations/builder/canvas.blade.php` (Tailwind CDN config, warna brand, favicon, og:image dari sesi-sesi sebelumnya, reveal-scroll script) diekstrak ke `organizations/pages/_document.blade.php` baru, dipakai bareng oleh `canvas.blade.php` (builder iframe) dan `organizations/public/show.blade.php` (situs publik, baru) — keduanya jadi wrapper tipis, sengaja **tetap 2 file terpisah** (bukan 1 entry point tunggal) supaya `canvas.blade.php` bebas nambah concern khusus builder (mis. overlay edit-mode nanti) tanpa risiko bocor ke situs publik, dan sebaliknya. **Tidak ada perubahan sama sekali** ke `_render.blade.php` atau partial `templates/sections/*.blade.php` manapun — auto-binding CMS (Post/Agenda/Announcement/GalleryPhoto/Officer/dst) yang sudah pakai pola `isset($organization)` sejak sesi-sesi sebelumnya otomatis jalan juga di rute publik tanpa kerja tambahan, dibuktikan test `test_public_render_shows_cms_bound_gallery_content`. **Dashboard**: baris checklist "Publish Situs" yang sebelumnya hardcode disabled "Segera hadir" sekarang toggle sungguhan (`organizations.publish` route, `confirm()`-guarded, pola sama dengan hapus member/organisasi) — label berganti "Publikasikan"/"Jadikan Draft" sesuai status, menampilkan URL live begitu published; link "Lihat Situs →" juga ditambah di header dashboard, keduanya di-guard ganda (`status === Published && $tenantDomain`) supaya tidak pernah tampil link mati di local dev tanpa `TENANT_DOMAIN`. **Verifikasi manual end-to-end** (bukan cuma test otomatis): `php artisan serve` di port terpisah + `curl --resolve {slug}.website-mu.test:{port}:127.0.0.1` (pengganti `/etc/hosts` yang butuh sudo, tidak tersedia di sesi non-interaktif) — dikonfirmasi 3 skenario: organisasi Published render 200 dengan konten section sungguhan, organisasi Draft 404 di subdomain yang sama, domain telanjang (`127.0.0.1:{port}/` tanpa subdomain) tetap serve `welcome.blade.php` tanpa terpengaruh. `phpunit.xml` dapat `TENANT_DOMAIN=website-mu.test` permanen di env test (supaya route ter-registrasi utuh untuk seluruh suite — config tidak bisa diubah di tengah test run karena route sudah ter-daftar saat boot, bukan saat request). Test baru `tests/Feature/OrganizationPublishingTest.php` (7 test: akses publik situs Published, 404 situs Draft, akses publik tanpa auth sama sekali, konten CMS ter-bind di rute publik, toggle publish/unpublish + regresi "stempel sekali saja", non-member ditolak 403, label toggle dan URL live tampil benar di dashboard sesuai status). **Sengaja di luar cakupan sesi ini** (dikonfirmasi eksplisit dengan user sebelum mulai): domain kustom (fase PRD §13 terpisah), setup DNS wildcard/SSL sungguhan di hosting (dikonfirmasi kompatibel dengan Laravel Cloud lewat riset — wildcard subdomain+SSL native mereka via DCV delegation pre-verification, tapi setup itu di panel Laravel Cloud, bukan kode), migrasi Tailwind CDN ke compiled CSS untuk performa publik (tetap CDN untuk sekarang, konsisten pola CDN yang sudah ada di seluruh codebase), UI multi-halaman publish (builder tetap satu halaman per organisasi), scheduled publishing, sitemap/robots.txt, analytics.
- **2026-08-17 (Galeri jadi entitas CMS: `GalleryPhoto`)** — Melengkapi item Phase 1 "CMS dasar" yang sebelumnya secara eksplisit dicatat belum selesai (§24.1 baris CMS, dan entri media library di bawah): "Galeri sebagai entitas CMS penuh (album/caption), bukan cuma daftar gambar". Migration `gallery_photos` (`organization_id`, `url`, `caption`, `order`) + model `GalleryPhoto` + `GalleryPhotoPolicy` (viewAny/create/update/delete, pola membership sama persis dengan `OfficerPolicy`) + `OrganizationGalleryController` (CRUD + `reorder`, juga clone langsung dari `OrganizationOfficerController`) + view standalone `organizations/gallery/{index,form}.blade.php` (drag-and-drop reorder SortableJS di index, picker foto dari media library di form — pola sama persis dengan Officer). **Keputusan struktur data yang diambil secara sadar, menyimpang dari kata "album" di PRD §12:** dibangun sebagai flat per-organisasi (`GalleryPhoto belongsTo Organization` langsung, tanpa tabel `GalleryAlbum` perantara) alih-alih struktur album-berisi-foto dua level yang disebut PRD ("album, media, caption") — atas pilihan eksplisit user, bukan default asisten. Konsekuensi: semua foto organisasi ada di satu pool tanpa pengelompokan; kalau nanti "album" (misal per acara/tahun) benar-benar dibutuhkan, itu perlu tabel `GalleryAlbum` baru dengan `GalleryPhoto belongsTo GalleryAlbum` (bukan langsung ke `Organization` lagi) — perubahan struktural, bukan sekadar tambah kolom, jadi rencanakan sebagai migration+relasi baru saat itu tiba, bukan diselipkan di `GalleryPhoto` yang sudah ada. **Relasi dinamai `Organization::photos()`, bukan `galleryPhotos()`** — beda dari rencana awal — karena `scopeBindings()` pada grup route `organizations.gallery.*` me-resolve nested route-model-binding `{photo}` lewat pencocokan nama relasi ke nama parameter route persis (bukan ke nama URL segment `gallery`), sama seperti `officers()`/`programs()`/`networks()` untuk resource mereka masing-masing — kalau relasi tetap bernama `galleryPhotos()` sementara parameter route `{photo}`, binding gagal dengan `BadMethodCallException: Call to undefined method Organization::photos()` (ditemukan lewat test yang gagal sebelum commit, lihat pola serupa di §24.2 soal grandchild binding, ini kasus berbeda — sama-sama child langsung tapi nama relasi harus cocok literal dengan nama parameter). **Section `galeri.blade.php` auto-bind** ke `Organization::photos()` (query `take($limit)`, default 8, field baru `limit` ditambahkan ke registry `config/page-builder.php`) mengikuti pola `daftar-berita`/`agenda`/`pengumuman` persis (`isset($organization)` fallback ke `content['items']` sample array untuk konteks `templates/preview.blade.php`) — foto kini render dengan caption overlay (gradient hitam di bagian bawah tiap gambar) kalau `caption` terisi. Builder properties panel: field `galeri.items` yang sebelumnya repeater manual (tambah/hapus gambar langsung dari media picker dalam form section) **dihapus total**, diganti link "Kelola Galeri →" — pola sama dengan 7 section CMS-bound lain yang sudah ada duluan (`daftar-berita`, `agenda`, `pengumuman`, `struktur-pengurus`, `program-unggulan`, `layanan`, `jaringan-aum-ortom`). Kartu "Konten" baru ("Galeri", jumlah foto) ditambah di dashboard `organizations/show.blade.php`, sejajar Berita/Agenda/Pengumuman. Test baru: `tests/Feature/OrganizationOrgDataTest.php` diperluas (CRUD + reorder foto galeri, forbidden untuk non-member, 404 lintas-organisasi, auto-binding tervalidasi via `assertSee` URL foto dan caption di response canvas builder).
- **2026-08-17 (favicon dari logo, og:image dari hero)** — Lanjutan permintaan sesi sebelumnya (logo di header/footer, lihat entri di bawah): logo dipakai juga sebagai favicon publik, dan gambar hero section dipakai sebagai og:image, keduanya di `<head>` `organizations/builder/canvas.blade.php` — **satu-satunya** dokumen HTML dengan `<head>` sungguhan untuk situs organisasi yang ada di codebase ini sampai sekarang (bukan halaman builder/`edit.blade.php` yang cuma UI chrome, dan bukan `templates/preview.blade.php` yang tidak punya `$organization` di scope). Favicon: `<link rel="icon" href="{{ $organization->logo }}">`, dirender kondisional `@if ($organization->logo)`, tidak ada fallback ke favicon platform default kalau logo kosong (browser jatuh ke favicon bawaan/tidak ada, dianggap cukup — belum ada kebutuhan favicon default platform yang eksplisit). Og:image: diambil dari `$page->sections->firstWhere('key', 'hero')?->content['image'] ?? null` — `$page->sections` sudah eager-loaded dari `OrganizationBuilderController::canvas()` jadi tidak perlu query tambahan; kalau section `hero` tidak ada di halaman itu atau field `image`-nya kosong, tag `og:image` tidak dirender sama sekali (tidak ada fallback ke gambar lain). `og:title` juga ditambah sekalian (nama organisasi) karena og:image tanpa og:title kurang berguna untuk keperluan share link, meski tidak diminta eksplisit — dianggap bagian wajar dari "menerapkan og:image" yang sudah diminta. **Keputusan yang sengaja ditunda:** deteksi hero image ini hardcode ke section berkey `hero` di halaman yang sedang dibuka (`$page`, bukan selalu home page) — kalau builder nanti mendukung banyak halaman per organisasi (§24.1 baris Builder, saat ini masih dibatasi 1 halaman), setiap halaman akan generate og:image dari hero section miliknya sendiri, bukan selalu dari homepage; ini konsisten dengan urutan render saat ini, jadi tidak dianggap masalah, hanya dicatat supaya jelas kalau nanti perilakunya terasa "salah" untuk halaman non-home. Test baru: `tests/Feature/OrganizationBuilderTest.php` (favicon+og:image muncul dengan URL benar saat logo dan hero.image terisi; keduanya sama sekali tidak dirender saat logo/hero.image kosong).
- **2026-08-17 (logo diterapkan ke header/footer)** — Logo organisasi tersimpan sejak sesi Brand Settings tapi tidak pernah dipakai di mana pun (dicatat sebagai gap di §24.1 baris Brand settings dan entri iframe-refactor di bawah). `templates/sections/header.blade.php`/`footer.blade.php` ditambah `$orgLogo = $organization->logo ?? null` (pola `??` sama dengan `$orgName` yang sudah ada di kedua partial, aman dipakai di 2 konteks: render halaman tenant sungguhan yang selalu passing `$organization`, dan `templates/preview.blade.php` yang TIDAK passing `$organization` sama sekali — `??` jatuh ke `null` di situ, bukan error). Header: `<img>` logo menggantikan lingkaran inisial nama saat `$orgLogo` terisi; footer: logo kecil ditambah di sebelah nama organisasi (elemen baru, sebelumnya footer cuma teks nama+copyright). **Belum dikerjakan:** logo tidak dipakai di tempat lain (og:image, favicon, dll) — scope sesi ini sengaja dibatasi ke 2 partial yang sudah render nama organisasi. Test baru: `tests/Feature/OrganizationBuilderTest.php::test_uploaded_logo_renders_in_header_and_footer_on_canvas` (assert URL logo muncul di response canvas builder untuk section `header` dan `footer`). Verifikasi manual: cek route `templates.preview` (tanpa organisasi) tetap render tanpa error meski `$organization` undefined di scope itu.
- **2026-08-17 (checklist onboarding: item Kontak, item Pengurus dihapus)** — PRD §6 merekomendasikan checklist onboarding 5 item (logo, profil, halaman utama, kontak, publish); implementasi sebelumnya cuma 3 (brand, officers, published) dan "kontak" belum bisa dideteksi sama sekali karena `organizations` tidak punya kolom kontak apa pun. Migration baru menambah `organizations.phone`/`email`/`whatsapp` (nullable string). `Organization::onboardingChecklist()` dapat item baru `contact`, selesai kalau salah satu dari ketiganya terisi (bukan wajib semua — organisasi bisa pilih channel yang relevan buat mereka). **Keputusan lokasi form:** field kontak ditaruh di halaman Brand Settings yang sudah ada (`OrganizationBrandController`/`organizations/brand/edit.blade.php`), bukan controller/route/view baru — dipilih karena tidak ada halaman "edit profil organisasi" sama sekali di codebase ini (name/region/description cuma diisi sekali saat create, tidak ada route edit), jadi bikin controller baru untuk 3 field kontak dianggap berlebihan. Konsekuensinya `OrganizationBrandController` kini menangani brand (warna+logo) DAN kontak, bukan cuma brand murni — kalau nanti field profil lain (name/region/description) butuh UI edit juga, pertimbangkan rename controller/route jadi lebih generik saat itu, jangan terus menumpuk di "Brand". Validasi: `phone`/`whatsapp` cuma `string|max:30` (tidak divalidasi format nomor, sengaja dibiarkan bebas karena format Indonesia bervariasi dengan/tanpa +62), `email` pakai rule `email` bawaan Laravel. **Item `officers` dihapus dari checklist di sesi yang sama, atas permintaan eksplisit** setelah sempat ditambah sebentar sebagai item ke-2 ("Tambah Pengurus") — pengurus sudah bisa dikelola langsung dari dalam builder lewat link "Kelola Pengurus →" di section `struktur-pengurus` (`route('organizations.officers.index', ...)`, lihat `organizations/builder/edit.blade.php`), jadi dianggap tidak perlu jadi langkah setup checklist terpisah yang menuntun keluar builder — beda dengan brand/kontak yang memang punya halaman pengaturan sendiri di luar builder. Checklist final: `brand`, `contact`, `published` (3 item). Checklist UI di `organizations/show.blade.php` disesuaikan (nomor "Publish Situs" jadi 3, bukan 4). **Belum dikerjakan** (di luar scope sesi ini): field kontak belum ditampilkan di section manapun (`formulir-kontak`/footer templat belum baca `organizations.phone/email/whatsapp` — section itu masih cuma placeholder `title`/`subtitle` tanpa data kontak sungguhan), item checklist "profil" dan "halaman utama" dari resep PRD §6 tetap belum ada (halaman utama sengaja ditolak sebelumnya di entri Brand Settings, alasan sama masih berlaku). Test: `tests/Feature/OrganizationBrandTest.php` diperluas (update brand settings sekarang assert 3 field kontak tersimpan, test baru untuk email invalid ditolak, test checklist disesuaikan ke 3 key final).
- **2026-08-17 (validasi warna brand terlalu terang)** — Dipicu laporan user: `secondary_color` putih akan menabrak teks yang memakai `text-secondary` (dipakai langsung di atas background putih/terang di `daftar-berita`, `donasi-zakat-infak`, `tentang-organisasi`, `sambutan-ketua` — 5 partial section). `App\Rules\NotTooLightColor` (custom `ValidationRule`) menghitung **relative luminance WCAG** dari hex color, menolak kalau di atas threshold `0.85` — dipilih supaya bukan cuma `#FFFFFF` literal yang ketolak, tapi juga off-white/abu-abu terang (`#F5F5F5`, `#FEFEFE`) yang sama bermasalahnya, sambil tetap meloloskan warna terang-tapi-terbaca seperti amber (`#FFC107`, luminance 0.594). **Diterapkan ke primary_color juga**, bukan cuma secondary — `text-primary` dipakai di 14 partial section, kelas masalah yang identik, meski laporan awal user cuma soal secondary. Dipasang di 2 tempat: `OrganizationBrandController::update()` (form Brand Settings) dan `StoreOrganizationRequest` (jaga-jaga kalau field ini pernah diisi langsung saat create organisasi) — sebelum menambah rule ke `StoreOrganizationRequest`, diverifikasi dulu semua warna template di `TemplateSeeder` (11 kombinasi primary/secondary) lolos threshold (luminance tertinggi 0.519, template Tapak Suci) supaya auto-copy warna template (lihat entri Brand Settings) tidak tiba-tiba gagal untuk template yang sudah ada. **Live warning di form**: `resources/views/organizations/brand/edit.blade.php` dapat fungsi `isTooLight()` di Alpine (JS) yang menghitung luminance dengan formula identik ke `NotTooLightColor` PHP — border input jadi merah + pesan peringatan + tombol Simpan disabled saat user pilih warna terlalu terang, sebelum submit sama sekali, bukan cuma muncul setelah round-trip validasi server. **Bug PHPUnit tidak terkait yang ditemukan saat menulis test**: `@dataProvider` docblock annotation ternyata sudah tidak didukung PHPUnit 12 (project ini pakai `^12.5.12`) — harus `#[DataProvider('methodName')]` attribute. Test baru (`tests/Feature/OrganizationBrandTest.php`, ditambahkan ke file yang sudah ada dari sesi Brand Settings): warna terlalu terang ditolak baik dari update Brand Settings maupun dari create organisasi (dengan data provider 4 variasi warna terang), warna terang-tapi-terbaca (amber) tetap diterima. Verifikasi manual: cek warning HTML/JS ada di halaman form, submit `#FFFFFF` lewat curl langsung ke endpoint update, konfirmasi `secondary_color` tetap `NULL` di DB (tidak tersimpan).
- **2026-08-17 (canvas builder jadi iframe terisolasi — brand colors kini benar-benar terlihat)** — Sesi Brand Settings sebelumnya sengaja menunda penerapan visual warna ke canvas (lihat entri di bawah) karena canvas berbagi satu `tailwind.config` dengan seluruh UI builder. Sesi ini mengerjakan refactor yang ditunda itu, atas permintaan eksplisit setelah user bertanya "apa efek brand setting?" dan jawabannya ternyata "belum ada efek visual sama sekali". **Perubahan struktural:** route baru `GET organizations/{organization}/builder/{page}/canvas` (`OrganizationBuilderController::canvas()`) merender halaman HTML standalone baru `organizations/builder/canvas.blade.php` — isinya `@include('organizations.pages._render', ...)` dibungkus `<!DOCTYPE html>` lengkap dengan `tailwind.config` sendiri yang pakai `Organization::primaryColor()`/`secondaryColor()`, plus scroll-reveal `IntersectionObserver` script yang **sebelumnya tidak pernah ada** di canvas builder (jadi class `.reveal` di section partial selama ini tidak pernah animasi saat preview di builder — cuma di `templates/preview.blade.php`). Scaffold HTML disalin dari `templates/preview.blade.php` (termasuk fungsi `hexToRgb()` untuk `boxShadow.float` yang butuh rgba, pola yang sudah ada duluan di sana untuk brand warna template). `organizations/builder/edit.blade.php`: `<main>` yang sebelumnya `@include` langsung `_render.blade.php` inline diganti `<iframe id="canvas-frame" src="{{ route('organizations.builder.canvas', ...) }}">`. **Keputusan teknis kunci:** iframe di-load dari origin yang sama (bukan subdomain terpisah), jadi same-origin policy browser mengizinkan parent mengakses `iframe.contentDocument`/`contentWindow` langsung — **tidak perlu `postMessage`**, jauh lebih sederhana dari perkiraan awal di entri Brand Settings di bawah. `swapCanvas()` (dipakai oleh AJAX save section dan reorder) diubah dari `document.getElementById('canvas-body').innerHTML = ...` (DOM utama) jadi `document.getElementById('canvas-frame').contentDocument.getElementById('canvas-body').innerHTML = ...`, dengan scroll position dibaca/ditulis lewat `iframe.contentWindow.scrollY`/`scrollTo()` alih-alih `main.scrollTop` (scroll sekarang terjadi di dalam iframe, bukan di elemen `<main>` parent). `selectSection()`'s scroll-to-section logic diekstrak jadi fungsi `scrollCanvasToSection(id)` yang menunggu iframe `load` event dulu kalau dokumennya belum `readyState === 'complete'` — perlu karena `selectSection()` juga dipanggil dari `init()` saat balik dari CMS (`?section=`), yang bisa terjadi sebelum iframe sempat selesai memuat dokumennya sendiri. Layout `<main>` diubah dari `overflow-y-auto` (scroll di elemen React eh Alpine biasa) jadi `flex flex-col overflow-hidden` dengan iframe `h-full` — supaya iframe yang scroll secara internal, bukan `<main>`-nya, karena sekarang kontennya (dokumen HTML lain) hidup di dalam iframe. **Test yang perlu diperbaiki:** 5 test (`OrganizationCmsTest`, `OrganizationOrgDataTest`) sebelumnya `assertSee()` konten section langsung di response `organizations.builder.page` (`assertSee('Berita Terbit')` dkk) — setelah refactor, konten section pindah ke response route `organizations.builder.canvas` yang terpisah (builder page cuma berisi `<iframe src="...">`, bukan HTML section-nya lagi), jadi assertion dipindah ke route baru itu. Verifikasi manual: seed organisasi dengan `primary_color`/`secondary_color` kontras (pink/amber) berbeda dari platform (biru/hijau), cek response `organizations.builder.canvas` benar-benar berisi warna itu di `tailwind.config`-nya, cek response `organizations.builder.edit` (halaman builder itu sendiri, bukan iframe-nya) TETAP pakai warna platform biru/hijau — konfirmasi isolasi berhasil, chrome builder tidak ikut berubah warna. Cek juga AJAX save/reorder/hide-section masih mengembalikan shape JSON yang sama dan `swapCanvas()` masih berfungsi lewat curl langsung ke endpoint (tidak bisa test JS iframe access lewat curl, tapi response shape dan target DOM path sudah diverifikasi manual dari kode). **Pending:** belum ada test browser sungguhan (Dusk/Playwright) yang memverifikasi scroll-sync dan same-origin iframe access benar-benar jalan di browser nyata — verifikasi sejauh ini terbatas pada response HTTP level (curl) dan pembacaan kode, bukan interaksi JS di browser. Logo organisasi masih belum dipakai section manapun (lihat baris Brand settings di §24.1).
- **2026-08-17 (Brand settings + onboarding checklist)** — Mengerjakan 2 item Phase 1 lain (§20/§24.1): "Brand settings" dan "Authentication dan onboarding organisasi". **Brand settings:** migration menambah `organizations.primary_color`/`secondary_color`/`logo` (nullable — kosong berarti "belum di-override", bukan "tidak ada warna", lihat fallback chain di bawah). `OrganizationBrandController` (`edit`/`update`, policy `OrganizationPolicy::update` yang sudah ada) + view `organizations/brand/edit.blade.php` (color picker native `<input type=color>` disandingkan text input hex supaya bisa ketik manual/paste, ditambah picker logo reuse media library dengan pola `Alpine.data()` di `<script>` yang sama seperti form Officer/Post — **sengaja tidak** taruh `@json`/`@js` di dalam atribut HTML langsung, itu penyebab 2 bug quoting di sesi-sesi sebelumnya). `StoreOrganizationRequest::prepareForValidation()` diperluas: begitu `template_id` ke-resolve (baik eksplisit maupun auto-select dari `organization_type_id`, lihat entri single-page+auto-template di bawah), `template.structure.brand.primary/secondary` disalin ke `primary_color`/`secondary_color` kalau user belum isi eksplisit — realisasi PRD §6 ("warna primer/sekunder diisi otomatis dari default template"). `Organization::primaryColor()`/`secondaryColor()` adalah effective-value accessor (bukan accessor Eloquent biasa, method biasa supaya jelas beda dari raw attribute) dengan fallback 3 tingkat: kolom sendiri → `template.structure.brand` → default platform — pola ini **disalin identik** dari `templates/preview.blade.php` yang sudah lebih dulu punya logic sama untuk preview template tanpa organisasi. **Keputusan penting yang ditunda dengan sengaja:** penerapan visual warna ke kanvas builder TIDAK dikerjakan sesi ini. Kanvas builder (`organizations/builder/edit.blade.php`) berbagi satu `tailwind.config` dengan seluruh UI builder (sidebar, header, tombol Simpan) — bukan cuma kanvas. Mengganti config itu ke warna organisasi akan mewarnai ulang seluruh UI builder, bukan cuma preview situsnya. Solusi yang benar butuh kanvas jadi `<iframe>` terisolasi dengan `tailwind.config` sendiri, yang berarti membangun ulang scroll-sync (`selectSection()` → `scrollIntoView` lintas iframe perlu `postMessage`) dan logic `swapCanvas()` (AJAX save/reorder yang sudah ada, saat ini langsung replace `innerHTML` dalam DOM yang sama) — dinilai terlalu berisiko untuk sesi ini, ditunda ke sesi bersamaan subdomain publishing saat kanvas/halaman publik memang perlu dipisah dari UI builder untuk alasan lain juga. Font pairing, gaya tombol, border radius dari PRD §6 juga belum dikerjakan — scope sesi ini sengaja dibatasi warna+logo. **Onboarding checklist:** `Organization::onboardingChecklist()` — 3 item (`brand`, `officers`, `published`), dihitung langsung dari data yang sudah ada (bukan tabel progress baru): `brand` selesai kalau `logo` terisi (dipilih spesifik karena `primary_color`/`secondary_color` sekarang auto-terisi dari template sejak create, jadi tidak bisa dipakai untuk mendeteksi "user sudah sengaja buka Brand Settings" — `logo` tidak pernah di-auto-fill dari mana pun jadi aman dipakai sebagai sinyal), `officers` selesai kalau ada minimal 1 `Officer`, `published` **selalu false** untuk sekarang karena belum ada mekanisme publish sungguhan di UI (baris Subdomain publishing masih "Belum mulai") — item ini sengaja tetap ditampilkan di checklist (non-interaktif, label "Segera hadir") supaya user tahu itu ada di roadmap, bukan dihilangkan. **Keputusan yang ditolak eksplisit:** "Susun Halaman" TIDAK dijadikan item checklist — karena `Organization::ensureHomePageExists()` selalu otomatis membuat halaman+section (dari template atau default kosong) sejak organisasi dibuat, kehadiran section bukan sinyal bermakna bahwa user sudah menyusun apa pun; wizard multi-step terpisah juga ditolak — checklist ditaruh di dashboard `organizations/show.blade.php` yang sudah ada, tidak mengubah alur `organizations/create.blade.php`. Checklist card disembunyikan otomatis (`@unless`) begitu `brand` dan `officers` sama-sama selesai, supaya tidak nyangkut selamanya gara-gara item `published` yang memang belum bisa diselesaikan. Test baru: `tests/Feature/OrganizationBrandTest.php` (6 test: auto-copy warna dari template saat create, fallback ke default platform kalau tanpa template, update brand settings, forbidden non-member, validasi format hex color ditolak, checklist mendeteksi logo+officer dengan benar). Verifikasi manual: seed organisasi dengan template, cek `/organizations/{id}/brand` menampilkan warna efektif yang benar (termasuk kasus fallback ke platform default), submit form, cek checklist di dashboard berubah dari "0/3" jadi "1/3" dengan item Brand tercoret.
- **2026-08-17 (CMS: data organisasi — pengurus, program, layanan, jaringan AUM/Ortom)** — Melengkapi item Phase 1 "CMS dasar" (§20/§24.1) dengan 3 model baru untuk PRD §12 "Data organisasi: pengurus, AUM, Ortom, program, layanan": `Officer` (migration `officers`: name/role/photo/order — untuk section `struktur-pengurus`), `Program` (migration `programs`: type/title/description/icon/order — untuk **dua** section `program-unggulan` **dan** `layanan` sekaligus, dibedakan lewat kolom `type` karena `layanan.blade.php` cuma `@include` partial `program-unggulan` yang sama persis), `OrganizationNetwork` (migration `organization_networks`: name/type/order — untuk section `jaringan-aum-ortom`). **Keputusan penamaan sengaja:** model pengurus dinamai `Officer`, bukan `OrganizationMember`, karena `OrganizationMemberController` sudah ada untuk konsep berbeda (anggota akun platform: Owner/Admin/Editor/Author di pivot `organization_user`) — pakai nama yang sama akan sangat membingungkan. `OrganizationNetwork` tetap dipakai apa adanya meski berpotensi tumpang tindih makna dengan "Organization Network" Phase 3 (§18/§403, relasi parent-child organisasi berjenjang) — model ini cuma daftar tampilan nama+jenis AUM/Ortom terkait, bukan relasi organisasi sungguhan; kalau Phase 3 nanti dibangun, perlu dibedakan lebih eksplisit saat itu (nama field/dokumentasi), bukan rename sekarang. Policy (`OfficerPolicy`/`ProgramPolicy`/`OrganizationNetworkPolicy`) dan pola controller (CRUD + `belongsTo Organization` langsung + `scopeBindings()` otomatis aman) identik dengan sesi CMS berita/agenda/pengumuman sebelumnya — lihat entri di bawah untuk detail pola. **Bug lama diperbaiki sekalian:** `config('page-builder.sections.layanan.fields')` sebelumnya `[]` (kosong) sejak section itu pertama dibuat — artinya section Layanan **tidak pernah bisa dikelola** dari builder sama sekali sejak awal; sekarang dapat `fields => ['title', 'items']` sama seperti `program-unggulan`. **Auto-binding:** `struktur-pengurus.blade.php` → `Organization::officers()` (urut `order`), `program-unggulan.blade.php` → `Organization::programs()->ofType('program')`, `layanan.blade.php` → `program-unggulan.blade.php` yang sama dengan `$programType = 'layanan'` diteruskan manual (perbaikan bug lain: `layanan.blade.php` sebelumnya cuma `@include(..., ['section' => $section])`, TIDAK meneruskan `$organization` ke partial yang di-include, jadi auto-bind akan selalu gagal diam-diam untuk section ini kalau tidak diperbaiki — lihat commit ini), `jaringan-aum-ortom.blade.php` → `Organization::networks()`. Builder properties panel: keempat section field `items` diganti jadi link "Kelola Pengurus/Program/Layanan/Jaringan AUM/Ortom →", pola sama dengan 3 section CMS sebelumnya. **Fitur tambahan atas permintaan eksplisit:** halaman index Pengurus (`organizations/officers/index.blade.php`) dapat drag-and-drop reorder (SortableJS, handle `⠿`, fetch ke `organizations.officers.reorder` — pola diadaptasi dari section-list builder, tapi TANPA canvas-swap karena halaman ini bukan builder). **Bug ditemukan & diperbaiki sebelum commit:** `OrganizationOfficerController::reorder()` awalnya `$this->authorize('update', [Officer::class, $organization])` — resolve ke `OfficerPolicy::update($user, Organization)` padahal method itu type-hint `Officer $officer`, jadi selalu 500 TypeError; diperbaiki jadi `$this->authorize('update', $organization)` (cek lewat `OrganizationPolicy::update`, konsisten dengan pola `OrganizationSectionController::reorder()`). Test baru: `tests/Feature/OrganizationOrgDataTest.php` (8 test: CRUD + reorder officer, program vs layanan sebagai pool terpisah, CRUD network, forbidden non-member, 404 lintas-organisasi, auto-binding tervalidasi via `assertSee` di 3 test terpisah termasuk yang mengonfirmasi program-unggulan dan layanan menampilkan item dari pool masing-masing tanpa tercampur). Verifikasi manual: seed 4 section + data asli via tinker, cek builder canvas menampilkan semua 4 dengan benar, cek 6 halaman CMS standalone return 200, cek reorder endpoint benar-benar mengubah urutan di DB. **Pending:** UI drag-and-drop reorder belum ada untuk Program/Layanan/Network (cuma Officer), tidak ada pagination di halaman index (sama seperti CMS lain), Program tidak divalidasi field `icon` sebagai emoji/single-char (bebas teks apapun sampai 10 karakter).
- **2026-08-17 (CMS dasar: berita, agenda, pengumuman)** — Membangun irisan pertama dari item Phase 1 "CMS dasar" (§20/§24.1), fokus 3 entitas dari PRD §12 yang punya section builder siap pakai: `Post` (migration `posts`: title/slug unik per organisasi/category/image/excerpt/body/status/published_at), `Agenda` (migration `agendas`: title/starts_at/location/contact_person/description/registration_url/status), `Announcement` (migration `announcements`: title/body/priority/valid_until/status). Enum baru `App\Enums\PublishStatus` (Draft/Published) dipakai ketiganya — sengaja dibuat generik, bukan reuse `OrganizationStatus` yang namanya terikat ke Organization. Tiap model `belongsTo Organization` langsung (pola sama dengan `Media`, bukan grandchild seperti `OrganizationSection`), jadi route `organizations.{posts,agendas,announcements}.*` aman masuk grup `scopeBindings()` tanpa validasi manual. Policy (`PostPolicy`/`AgendaPolicy`/`AnnouncementPolicy`) memakai membership check yang sama dengan `MediaPolicy`/`OrganizationPolicy::update` — **keputusan sengaja**: semua role (Owner/Admin/Editor/Author) boleh create/edit/delete apapun, granularitas "Author hanya boleh edit miliknya sendiri" ditunda. Controller CRUD + view standalone (`organizations/posts|agendas|announcements/{index,form}.blade.php`, pakai `layouts.app`, bukan layout builder CDN) ditautkan dari 3 kartu "Konten" baru di `organizations/show.blade.php`. **Keputusan arsitektur kunci:** section `daftar-berita`/`agenda`/`pengumuman` (`resources/views/templates/sections/*.blade.php`) di-auto-bind ke koleksi CMS terkait via query scope (`Post::published()`, `Agenda::published()->upcoming()`, `Announcement::active()`) saat `$organization` ada di scope render — ini realisasi langsung PRD §12 ("section Berita Terbaru otomatis menampilkan post terbaru yang sudah diterbitkan"), dan sekaligus penyelesaian desain `content.items` sebagai referensi CMS yang sudah diantisipasi di §24.2 sejak sesi builder. **Penting:** partial section ini dipakai di 2 konteks berbeda — render halaman tenant (`organizations/pages/_render.blade.php`, selalu passing `$organization`) DAN preview template mentah (`templates/preview.blade.php`, TIDAK passing `$organization`) — jadi tiap partial pakai `isset($organization)` untuk fallback ke `content['items']` sample data di context preview, supaya template preview (belum py organisasi nyata) tidak crash. Builder properties panel field `items` untuk 3 section ini diganti dari placeholder generik jadi link langsung ke halaman CMS terkait ("Kelola Berita/Agenda/Pengumuman →"), karena field itu sudah tidak lagi dibaca saat render. Test baru: `tests/Feature/OrganizationCmsTest.php` (CRUD post lengkap dengan slug generation, forbidden untuk non-member, 404 lintas-organisasi, auto-binding tervalidasi lewat assertion `assertSee`/`assertDontSee` pada builder canvas). Verifikasi manual: seed org+page+section+konten CMS via tinker, login, cek builder canvas menampilkan judul post/agenda/pengumuman yang benar, cek 4 halaman CMS standalone (index/create tiap entitas) me-return 200. **Pending:** Galeri sebagai entitas CMS penuh (album/caption, bukan cuma daftar gambar), Data organisasi (pengurus/AUM/Ortom/program) sebagai koleksi CMS — field `items`/`stats` section lain masih placeholder, tidak ada pagination di halaman index CMS (akan jadi masalah kalau organisasi punya ratusan post), tidak ada draft-preview (post berstatus draft tidak muncul di section manapun, termasuk saat organisasi sendiri melihat builder — mungkin perlu preview mode nanti).
- **2026-08-17 (media library)** — Membangun media upload & library dasar (item Phase 1 di §20/§24.1): migration `media` (`organization_id`, `uploaded_by`, `disk`, `path`, `original_name`, `mime_type`, `size`, `width`, `height`), model `Media` dengan relasi `Organization::media()` (`hasMany`, `latest()`), `MediaPolicy` (viewAny/create/delete, sama pola membership check dengan `OrganizationPolicy`), dan `MediaController` (`index` JSON untuk picker, `store` upload ke disk `public` via `Storage::store()`, `destroy` hapus file + row). Route `organizations/{organization}/media*` masuk grup `scopeBindings()` yang sudah ada karena `Media belongsTo Organization` langsung (bukan grandchild seperti `OrganizationSection`), jadi binding otomatis aman tanpa validasi manual — beda dengan pola section di §24.2. Builder (`organizations/builder/edit.blade.php`) dapat `mediaPicker` Alpine state global (fetch lazy saat modal pertama dibuka, cache di memori) yang dipakai dua tempat: (1) field registry bertipe `image`/`photo` (hero, tentang-organisasi, sambutan-ketua, donasi-zakat-infak) — hidden input + tombol "Pilih gambar"/"Ganti gambar"/"Hapus"; (2) `galeri.items` khusus — repeatable list gambar (tambah dari picker, hapus per-item), submit sebagai `content[items][]` yang sudah otomatis ditangani `OrganizationSectionController::update()` lewat `Request::input()` dot-notation (tidak perlu ubah controller). **Keputusan sengaja dibatasi:** field `items`/`stats`/`times` di section lain (struktur-pengurus, program-unggulan, jaringan-aum-ortom, dll) TIDAK dapat picker sesi ini — itu perlu repeater form penuh (nama+jabatan+foto per item, dst), yang tumpang tindih dengan CMS (§24.1) dan sengaja dipisah sebagai task berikutnya, bukan diperluas di sini. Verifikasi manual: `php artisan serve` + curl end-to-end (login → upload gambar asli via multipart → cek file ada di `storage/app/public/organizations/{id}/media/` → `index` mengembalikan URL yang benar → file terserve lewat `/storage/...` → `destroy` menghapus file dari disk). Test baru: `tests/Feature/OrganizationMediaTest.php` (upload+list oleh member, forbidden untuk non-member, delete lintas-organisasi ditolak 404 lewat scoped binding). **Pending:** tidak ada validasi dimensi/rasio gambar per field (mis. galeri idealnya rasio 1:1), tidak ada pagination di picker (list media dimuat semua sekaligus — akan jadi masalah kalau organisasi upload ratusan gambar), tidak ada UI hapus media dari halaman media library standalone (baru bisa dihapus lewat picker's implied flow — sebenarnya belum ada tombol hapus permanen di picker modal itu sendiri, hanya di controller/route; perlu ditambah di sesi berikutnya kalau dibutuhkan), disk masih hardcode `local`/`public` filesystem (belum siap S3 untuk produksi multi-server).
- **2026-08-17 (single-page + auto-template)** — Dua keputusan produk untuk tahap awal, disatukan karena saling terkait: **(a) satu organisasi hanya punya satu halaman.** `Organization::seedPagesFromTemplate()` diganti `ensureHomePageExists()` — kalau organisasi punya template, hanya halaman *pertama* dari `template.structure.pages` yang di-clone (halaman lain diabaikan untuk saat ini); kalau tidak punya template, langsung dibuatkan halaman "Beranda" kosong. Form "Buat Halaman" manual dan page-switcher tabs dihapus dari `organizations/builder/edit.blade.php`. Route/controller `organizations.pages.store`/`destroy` (`OrganizationPageController`) **sengaja dipertahankan** meski tidak lagi dipanggil dari UI manapun — siap diaktifkan lagi saat multi-halaman jadi kebutuhan nyata, bukan dihapus. **(b) template otomatis dipilih dari jenis organisasi saat submit.** `StoreOrganizationRequest::prepareForValidation()` mengisi `template_id` dari `organization_type_id` (template aktif pertama untuk tipe itu) kalau `template_id` belum diisi — form `organizations/create.blade.php` tidak lagi minta user pilih template secara eksplisit. Flow lama (`TemplateUseController` → `?template=slug` → `template_id` hidden field) tetap bekerja karena auto-fill hanya jalan kalau `template_id` kosong. Ditambah 3 template baru di `TemplateSeeder` (Aisyiyah, AUM Sosial, Masjid & Mushola) supaya **seluruh 12 jenis organisasi kini punya tepat 1 template aktif** — sebelumnya 3 jenis ini tidak punya template sama sekali, yang berarti auto-select akan diam-diam gagal (template_id tetap null) untuk mereka. Test baru: `tests/Feature/OrganizationStoreTest.php` (auto-select, tidak pilih template nonaktif, tidak menimpa pilihan eksplisit). **Catatan arsitektur:** relasi `Template::organizationType()` tetap `belongsTo` (satu jenis organisasi bisa punya banyak template) — "1 jenis = 1 template" hari ini adalah keputusan konten seeder + `first()` di auto-select, bukan constraint skema. Kalau nanti ditambah template kedua untuk satu jenis organisasi, auto-select akan selalu ambil yang pertama dibuat, bukan yang "terbaik" — perlu keputusan eksplisit (mis. kolom `is_default`) saat itu terjadi.
- **2026-08-17 (mobile-first)** — Builder (`organizations/builder/edit.blade.php`) diubah dari layout tiga-panel fixed-width (desktop-only) jadi mobile-first: di bawah breakpoint `lg`, hanya satu panel (Sections/Preview/Edit) tampil sekaligus lewat Alpine `activePanel` state + bottom tab bar; dari `lg` ke atas kembali ke tiga panel bersebelahan. Perbaikan serupa (stack vertikal, `flex-wrap`, `overflow-x-auto`) diterapkan ke `layouts/app.blade.php` (nav header sebelumnya bisa overflow horizontal di ~375px), `organizations/show.blade.php` (baris anggota), dan admin (`layouts/admin.blade.php`, `admin/templates/index.blade.php`). Dipicu oleh instruksi mobile-first di `CLAUDE.md` yang sebelumnya tidak diikuti builder. 21 partial section (`templates/sections/*.blade.php`) tidak disentuh — sudah mayoritas responsive dari prototipe HTML awal.
- **2026-08-17 (builder)** — Membangun page builder tenant (irisan pertama dari Roadmap Phase 1, §20): migration `organization_pages`/`organization_sections`, model `OrganizationPage`/`OrganizationSection`, `Organization::seedPagesFromTemplate()`, controller `OrganizationBuilderController`/`OrganizationPageController`/`OrganizationSectionController`, view `organizations/builder/edit.blade.php` (Alpine.js + SortableJS via CDN) dan `organizations/pages/_render.blade.php`. Memperbaiki `templates/sections/header.blade.php` yang sebelumnya membaca `$template` langsung (tidak kompatibel dengan halaman milik organisasi yang tidak punya `$template` di scope). Menambah `tests/Feature/OrganizationBuilderTest.php` — test pertama untuk Organization di repo ini, yang menemukan dan memperbaiki 2 bug nyata sebelum commit: (1) `TemplateFactory` default punya bentuk `structure.pages[].sections` yang salah (array string, bukan array `{key, content}`) sehingga `seedPagesFromTemplate()` crash; (2) route `organizations/{organization}/sections/{section}` awalnya memakai `scopeBindings()` yang gagal karena `Organization` tidak punya relasi `sections()` langsung (section adalah grandchild lewat `OrganizationPage`) — diperbaiki dengan validasi manual, lihat §24.2. CMS binding, brand settings UI, media library, subdomain publishing, dan domain kustom **sengaja belum dikerjakan** di sesi ini (lihat §24.1). Pending: builder belum mobile-responsive (layout tiga-panel desktop-only); sinkronkan deskripsi repo di `CLAUDE.md` (masih bilang "skeleton tanpa model/route custom", padahal sudah ada Organization/Template/OrganizationType/OrganizationPage/OrganizationSection + admin panel template + builder).
- **2026-08-17** — Menambahkan section §24 ini ke prd.md sebagai mekanisme berbagi knowledge lintas sesi. Tidak ada perubahan kode.

