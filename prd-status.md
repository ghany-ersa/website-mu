# PRD - Status Pengembangan Website-mu (Snapshot)

**Tanggal snapshot:** 2026-08-27 (update ke-2, commit `13c9f7e`)
**Status:** Dokumen ini merangkum apa yang **sudah dibangun secara nyata** di codebase saat ini, sebagai pelengkap `prd.md` (visi produk jangka panjang). Gunakan `prd.md` untuk arah dan cakupan produk; gunakan dokumen ini untuk tahu di titik mana pembangunannya sekarang.

---

## 1. Ringkasan Singkat

Website-mu sudah keluar dari tahap prototipe HTML statis dan menjadi aplikasi Laravel 13 yang berfungsi: pengguna bisa daftar, membuat organisasi, memilih template, menyusun halaman lewat page builder berbasis section, mengisi konten CMS (berita, agenda, pengumuman, galeri, pengurus, program, jaringan), mengatur brand, **mengganti template kapan saja**, memilih paket langganan dengan **entitlement konkret** (hapus watermark, template eksklusif), dan menerbitkan situs ke subdomain publik. Ada juga panel admin dengan **pencarian dan pagination** untuk mengelola organisasi, template, paket, dan approval pembayaran.

Total 29 commit sejak inisialisasi, dikerjakan berurutan dari fondasi organisasi → builder → CMS publik → brand/tema → subscription plan → **entitlement paket & tooling admin**.

---

## 2. Yang Sudah Berfungsi (Fitur Utama)

### 2.1 Autentikasi & Organisasi
- Register/login/logout standar Laravel (`routes/auth.php`).
- CRUD organisasi (`OrganizationController`): buat, lihat, hapus, publish/unpublish.
- Multi-user per organisasi via tabel pivot `organization_user` dengan role **Owner** dan **Editor** (`app/Enums/OrganizationRole.php`) - Owner bisa mengelola member, Editor tidak.
- Onboarding checklist otomatis di dashboard: brand (logo diisi), kontak (salah satu dari telp/email/WA), konten (minimal 1 section tersimpan), published - lihat `Organization::onboardingChecklist()`.

### 2.2 Jenis Organisasi & Template
- 12 jenis organisasi ter-seed (`OrganizationTypeSeeder`), dikelompokkan 3 kategori (`OrganizationCategory` enum: Persyarikatan, Ortom, AUM):
  - Persyarikatan: Muhammadiyah
  - Ortom: Aisyiyah, Pemuda Muhammadiyah, Nasyiatul Aisyiyah, Hizbul Wathan, IPM, IMM, Tapak Suci
  - AUM: Kesehatan, Pendidikan, Sosial, Masjid/Mushola
- Template disimpan sebagai JSON `structure` (halaman + section + brand warna default) di model `Template`, di-seed lewat `TemplateSeeder` - satu template awal per jenis organisasi, dengan warna brand berbeda per Ortom sesuai `prd.md` §7.
- Preview template publik tanpa login (`/templates/{slug}/preview`) dan alur "pakai template ini" (`TemplateUseController`) yang meng-clone `structure` template ke organisasi baru.
- Admin CRUD template (`Admin\TemplateController`).

### 2.3 Page Builder (Drag & Drop - Sebagian)
- Builder saat ini **hanya mendukung satu halaman per organisasi** (halaman "Beranda") - bukan multi-page seperti visi `prd.md` §9. `OrganizationPageController` ada untuk CRUD halaman tapi UI builder difokuskan ke satu halaman.
- Registry section terpusat di `config/page-builder.php` - 18 jenis section terdaftar: `hero`, `header`, `footer`, `tentang-organisasi`, `sambutan-ketua`, `struktur-pengurus`, `program-unggulan`, `layanan`, `jaringan-aum-ortom`, `daftar-berita`, `agenda`, `pengumuman`, `galeri`, `jadwal-salat`, `jadwal-kajian`, `jadwal-praktik`, `donasi-zakat-infak`, `ppdb`, `formulir-kontak`, `lokasi-peta`, `cta`.
- Setiap section punya `fields` (menentukan form panel properti) dan `defaults` (konten awal saat section ditambahkan) - jadi builder-nya *config-driven*, menambah section baru tidak perlu ubah banyak kode.
- **Header dan footer dikunci (`locked: true`)**: selalu ada persis satu di awal dan satu di akhir halaman, tidak bisa dihapus/diduplikasi/di-drag, dan tidak muncul di picker "Tambah Section" - dipaksakan di level model (`OrganizationPage::sectionsInDisplayOrder()`) maupun controller.
- Section bisa ditambah, dihapus, duplikasi, reorder (`OrganizationSectionController@reorder`), dan preview per-section (`sectionPreview`) serta preview seluruh halaman (`canvas`, `OrganizationSiteController@preview`) sebelum publish.
- Data section tersimpan sebagai JSON `content` di tabel `organization_sections`.

### 2.4 CMS Konten
Entitas CMS yang sudah diimplementasikan penuh (model + migration + controller resource + relasi ke Organization):
- **Post/Berita** - judul, isi, gambar, status publish, tanggal.
- **Agenda** - tanggal mulai, lokasi, deskripsi.
- **Pengumuman** - judul, isi, status publish.
- **Officer/Pengurus** - nama, jabatan, foto, urutan (bisa di-reorder).
- **Program** - judul, deskripsi, ikon, tipe, urutan.
- **OrganizationNetwork** - jaringan AUM/Ortom terkait, nama, tipe, urutan.
- **GalleryPhoto** - url, caption, urutan (bisa di-reorder).
- **Media library** - upload/hapus media, disimpan di Cloudflare R2 (lihat §2.6).

Semua resource ini di-scope ke `{organization}` lewat route model binding dan hanya bisa diakses jika section terkait ada di halaman organisasi (`Organization::hasSection()`), jadi tenant tidak melihat menu CMS untuk section yang templatenya tidak punya.

Sample data otomatis di-generate saat organisasi baru dibuat dari template (`CmsSampleDataSeeder`), supaya builder & halaman publik langsung berisi konten contoh yang bisa diedit, bukan kosong.

### 2.5 Brand Settings & Ganti Template
- `OrganizationBrandController`: warna primer/sekunder, logo, font family, border radius.
- Fallback chain 3 tingkat untuk setiap token brand (warna, font, radius): **override organisasi → default template → default platform** (`Organization::primaryColor()`, dst.) - konsisten dengan prinsip Guided Design System di `prd.md` §10.
- UI elemen (tombol, avatar, badge) mengikuti `border_radius` organisasi.
- **Baru:** `OrganizationTemplateController` - organisasi bisa mengganti template kapan saja (`organizations/{organization}/template`). Karena builder cuma dukung 1 halaman, ganti template berarti **halaman & section lama dihapus total** dan di-clone ulang dari template baru (bukan merge) - didesain sebagai aksi destruktif yang disengaja, hanya bisa dilakukan Owner.
- Template bertanda `is_exclusive` hanya bisa dipilih organisasi dengan entitlement `has_exclusive_templates` dari paketnya (lihat §2.8) - template terkunci tetap ditampilkan (read-only) di halaman ganti template supaya tenant tahu ada pilihan lain jika upgrade.

### 2.6 Media & Storage
- `MediaController` - upload, list, hapus media per organisasi.
- Disimpan di **Cloudflare R2** (S3-compatible, via `league/flysystem-aws-s3-v3`), bukan disk lokal.
- Resize/optimasi gambar via `intervention/image`.

### 2.7 Situs Publik Tenant (Multi-Tenancy via Subdomain)
- Routing berbasis subdomain sungguhan sudah jalan: `Route::domain('{organization_slug}.'.$tenantDomain)` di `routes/web.php`, dikontrol oleh `config('tenancy.domain')` (env `TENANT_DOMAIN`).
- Jika `TENANT_DOMAIN` kosong, grup route tenant **tidak didaftarkan sama sekali** - supaya `php artisan serve` lokal tetap jalan normal tanpa wildcard subdomain.
- Halaman publik yang sudah ada: beranda tenant, detail berita, detail pengumuman, detail agenda - semua dengan SEO meta tags.
- `SitemapController` - sitemap.xml otomatis.
- Publish/unpublish digerbangi oleh aturan paket (lihat §2.8) - organisasi yang melanggar limit paket tidak bisa publish, dan situs yang sudah publish tapi jadi melanggar (misal downgrade) menampilkan badge peringatan alih-alih diturunkan paksa.

### 2.8 Sistem Paket Langganan (Plan/Subscription)
Ini bagian paling baru dan paling matang secara arsitektur (3 commit terakhir).

**3 paket ter-seed** (`PlanSeeder`, harga masih draft/dummy, **naik dari snapshot sebelumnya**):
| Paket | Harga/bulan | Entitlement | Catatan |
|---|---|---|---|
| Starter | Rp 10.000 | - | Limit kecil di semua resource (posts 5, sections_total 5) |
| Organization | Rp 18.000 | - | CMS lebih lengkap, belum ada entitlement khusus |
| Professional | Rp 25.000 | `hide_branding`, `has_exclusive_templates` | Limit paling longgar (posts 20, sections_total 25) + watermark hilang + akses template eksklusif |

- Limit per resource: `posts`, `agendas`, `announcements`, `officers`, `programs`, `gallery_photos`, dan `sections_total` (total section builder non-locked di seluruh halaman).
- **Baru - Entitlement biner per paket** (bukan cuma limit angka): kolom `hide_branding` dan `has_exclusive_templates` di tabel `plans`, plus `is_exclusive` di tabel `templates`.
  - `hide_branding`: badge "Dibuat dengan website-mu.id" di footer publik disembunyikan untuk organisasi dengan paket berentitlement ini (`templates/sections/footer.blade.php`) - bukan sesuatu yang bisa di-toggle tenant sendiri.
  - `has_exclusive_templates` (`Organization::canUseExclusiveTemplates()`): menggerbangi apakah organisasi boleh memilih template yang ditandai `is_exclusive` saat ganti template (lihat §2.5).
  - Deskripsi paket di seeder sengaja hanya menyebut entitlement yang benar-benar ada di kode - sebelumnya sempat menyebut domain kustom/AI content yang belum dibangun, sudah dikoreksi.
- **`PlanLimitService`** - resolusi limit 3 tingkat: **override per-tenant** (`OrganizationLimitOverride`, untuk negosiasi khusus) → **snapshot limit yang sudah dibayar** (`limits_snapshot` di `PlanChangeRequest`, dibekukan saat approval supaya perubahan limit paket di kemudian hari tidak merugikan tenant yang sudah bayar) → **limit live paket saat ini**.
- **Alur ganti/upgrade paket** (`OrganizationPlanController` + `PlanChangeRequestService`) dengan state machine `PlanChangeRequestStatus`: `Pending` (menunggu bayar) → `PaymentConfirmed` (tenant klaim sudah bayar) → `Approved`/`Rejected` oleh admin. Admin approval memperpanjang `plan_expires_at` (extend dari expiry lama jika masih aktif, atau mulai dari sekarang) dan membekukan snapshot limit.
- Hanya boleh ada satu `PlanChangeRequest` in-flight per organisasi.
- `Organization::planViolations()` mengecek pelanggaran: konten CMS melebihi limit, total section melebihi limit, masa aktif paket habis, atau belum pernah dikonfirmasi pembayarannya - dipakai untuk memblokir publish dan menampilkan badge di situs publik.
- Panel admin (`Admin\PlanController`, `Admin\PlanChangeRequestController`) untuk CRUD paket dan approve/reject permintaan ganti paket.

### 2.9 Admin Panel
- Middleware `admin` (kolom `is_admin` di tabel users) menggerbangi `/admin/*`.
- Kelola template (termasuk toggle `is_exclusive`), kelola paket + limitnya (termasuk toggle `hide_branding`/`has_exclusive_templates`), lihat daftar semua organisasi, approve/reject plan change request.
- **Baru - Pencarian & pagination** di semua listing admin (organisasi, template, paket, plan change request): komponen reusable `<x-crud.search-form>` (input `q` + slot filter tambahan + tombol reset), backend query pakai `->when()`/`->paginate(20)->withQueryString()`. Listing organisasi bisa dicari berdasarkan nama/slug organisasi maupun nama/email Owner-nya, dan difilter per jenis organisasi.

---

## 3. Arsitektur & Stack Teknis Aktual

- **Backend:** Laravel 13, PHP 8.3, SQLite (dev).
- **Storage:** Cloudflare R2 (S3-compatible) untuk media upload.
- **Image processing:** Intervention Image v4.
- **Frontend:** Blade + Tailwind CDN pattern (belum migrasi ke Vite-compiled Tailwind untuk halaman aplikasi - masih konsisten dengan gaya prototipe HTML awal).
- **Multi-tenancy:** subdomain-based routing native Laravel (`Route::domain()`), bukan paket multi-tenancy pihak ketiga.
- **Konfigurasi khusus produk:** `config/page-builder.php` (registry section) dan `config/tenancy.php` (domain tenant) - pola config-driven, bukan hardcode di controller.
- Kode secara konsisten didokumentasikan dengan komentar panjang yang menjelaskan *keputusan* dan *alasan* (bukan sekadar apa yang dilakukan kode) - memudahkan pembacaan ulang di masa depan.
- **Baru - Refactor komponen UI organisasi**: view-view CMS organisasi (`agendas`, `announcements`, `gallery`, `networks`, `officers`, `posts`, `programs` - index & form) dirapikan ulang untuk memakai komponen Blade reusable (`components/ui/card`, `empty-state`, `list-panel`, `status-badge`) alih-alih markup berulang di tiap halaman. Mengurangi duplikasi tapi tidak menambah fitur baru.
- File prototipe HTML lama (`index.html`, `prompt`) sudah dihapus dari root repo - dokumentasi produk kini sepenuhnya di `prd.md` + landing page hidup di `welcome.blade.php`. `tests/Feature/ExampleTest.php` bawaan Laravel juga sudah dibuang.

---

## 4. Yang Belum Dibangun (Gap terhadap Visi `prd.md`)

| Area | Status |
|---|---|
| Multi-page per organisasi | Builder baru mendukung 1 halaman ("Beranda"); model `OrganizationPage` sudah siap untuk multi-page tapi belum ada UI-nya |
| Domain kustom (custom domain) | Belum ada - baru subdomain platform. Deskripsi paket sudah dikoreksi agar tidak over-promise soal ini |
| AI Co-Pilot (draf konten, saran struktur) | Belum ada sama sekali |
| Drag-and-drop reorder dengan interaksi visual (JS drag) | Section reorder ada endpoint-nya, belum dikonfirmasi UI-nya pakai drag interaktif atau tombol naik/turun |
| Analytics dasar untuk tenant | Belum ada |
| Verifikasi DNS/SSL untuk domain kustom | Belum ada (menyusul custom domain) |
| Pembayaran otomatis (payment gateway) | Alur saat ini manual: tenant klaim sudah bayar → admin verifikasi manual, belum ada integrasi payment gateway |
| Template per semua kategori (AUM Kesehatan, Sosial, dll.) | Baru 1 template per jenis organisasi (12 total), belum tentu semua varian section/konten khas per kategori sudah lengkap |
| Testing otomatis | `tests/Feature/ExampleTest.php` bawaan sudah dihapus dan belum digantikan test fitur baru; `tests/TestCase.php` sempat disentuh (kemungkinan helper untuk setup plan/organization di test) tapi belum terlihat ada suite test untuk fitur-fitur di atas - perlu verifikasi terpisah dengan `composer test` |
| Prototipe HTML root | Seluruh file `*.html` prototipe (`landingpage websitemu.html`, `index.html`, `PCA.html`, `PCM*.html`) dan `prompt` sudah dihapus dari repo - referensinya di `CLAUDE.md` sekarang usang dan perlu disesuaikan terpisah jika masih relevan |

---

## 5. Urutan Pembangunan (dari Git History)

1. Inisialisasi project + organisasi dasar, upload media ke R2.
2. Edit profil organisasi (nama, slug, deskripsi) + SEO publik.
3. Halaman publik tenant untuk detail post/pengumuman/agenda + SEO.
4. Footer terkunci → header terkunci, brand radius, layout organisasi.
5. Section preview, CTA dinamis, penguncian header/footer disempurnakan.
6. Update visual/warna template.
7. **Sistem paket langganan** - model Plan/PlanLimit, `PlanLimitService`, gating publish.
8. **Plan change request** - alur pengajuan-approval-pembayaran manual, admin panel.
9. Perbaikan UX kecil (confirm dialog custom, progress bar usage).
10. Bersih-bersih repo - hapus prototipe HTML lama dan test bawaan yang sudah tidak relevan.
11. **Aturan publish berbasis kepatuhan paket** - `Organization::planViolations()`/`hasPaidForCurrentPlan()`, memblokir publish & menampilkan badge pelanggaran.
12. **Entitlement paket konkret** - `hide_branding` (watermark footer) dan `has_exclusive_templates` (template terkunci), plus fitur ganti template organisasi (destruktif, clone ulang).
13. Refactor komponen UI CMS organisasi ke komponen Blade reusable.
14. **Pencarian & pagination** di seluruh listing panel admin.

---

## 6. Rekomendasi Langkah Berikutnya (opsional, bukan keputusan)

Berdasarkan gap di atas, kandidat prioritas berikutnya biasanya salah satu dari:
- Multi-page builder (paling besar dampaknya untuk kelengkapan produk inti).
- Integrasi payment gateway (mengurangi kerja manual admin approval - sekarang makin terasa karena alur approval, snapshot limit, dan entitlement paket sudah cukup matang di sisi manualnya).
- AI Co-Pilot dasar (headline/deskripsi generation) - nilai jual pembeda dari builder biasa.
- Test coverage untuk fitur inti (builder, plan gating, publish rules) - makin berisiko regresi seiring makin banyak logika bisnis (limit, snapshot, entitlement) yang saling bergantung.

Keputusan prioritas ada di tangan Anda - dokumen ini hanya memetakan posisi saat ini.
