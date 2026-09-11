# PRD - Status Pengembangan Website-mu (Snapshot)

**Tanggal snapshot:** 2026-09-12
**Status:** Dokumen ini merangkum apa yang **benar-benar ada di codebase**, sebagai pelengkap `prd.md` (visi produk jangka panjang). Gunakan `prd.md` untuk arah produk; gunakan dokumen ini untuk tahu posisi pembangunan sekarang.

> **Kondisi saat ini: fitur dianggap selesai dan stabil.** Pekerjaan yang tersisa adalah **mengisi aplikasi dengan data/konten yang sesuai**, bukan membangun subsistem baru. Jangan memulai sesi dengan menelusuri `git log`/`git diff` - semua yang perlu diketahui ada di dokumen ini dan di `CLAUDE.md`.

---

## 1. Ringkasan

Website-mu adalah aplikasi Laravel 13 yang berfungsi penuh: pengguna mendaftar, membuat organisasi, memilih template, menyusun **beberapa halaman** lewat page builder berbasis section, mengisi konten CMS, mengatur brand, berlangganan lewat **pembayaran otomatis Midtrans**, dan menerbitkan situs ke subdomain publik yang ter-cache. Admin punya panel lengkap termasuk **editor template visual (sandbox)**.

Skala kode: ~26 model, ~44 controller, ~65 migration, ~26 jenis section, 10 template ter-seed, 167 test.

---

## 2. Yang Sudah Berfungsi

### 2.1 Autentikasi, Organisasi, Member
- Register/login/logout (`routes/auth.php`).
- CRUD organisasi + publish/unpublish.
- Multi-user per organisasi (pivot `organization_user`), role **Owner** dan **Editor** (`OrganizationRole`). Hanya Owner yang mengelola member.
- Onboarding checklist 4 item di dashboard (`Organization::onboardingChecklist()`): brand, kontak, konten, published.
- **Onboarding tour interaktif** (driver.js) untuk `dashboard` dan `builder`, status tersimpan per user (`onboarding_tours_seen`).

### 2.2 Jenis Organisasi & Template
- **5 jenis organisasi** dalam **2 kategori** (`OrganizationCategory`):
  - **Organisasi:** Muhammadiyah, Aisyiyah
  - **Amal Usaha Muhammadiyah:** Klinik/Rumah Sakit, Media/Portal Berita, Masjid/Mushola
  - Penamaan sengaja menyebut **gerakan atau institusi**, bukan tingkatan/kategori ("Muhammadiyah", bukan "Pimpinan Cabang Muhammadiyah"; "Klinik/Rumah Sakit", bukan "AUM Kesehatan").
  - Kategori `Ortom` masih ada sebagai **alias deprecated** semata-mata agar baris lama tetap bisa di-cast.
- **10 template ter-seed** - pasangan standar + eksklusif untuk masing-masing: PCM Ambulu, PCA Ambulu, Klinik Aisyiyah Ambulu, Suara Muhammadiyah Ambulu, Masjid Nurul Huda. Template eksklusif memakai section/variant premium dan multi-halaman.
- `TemplateSeeder` dan `OrganizationSeeder` **sengaja kosong/dinonaktifkan** - template kini di-seed per organisasi lewat seeder masing-masing.
- Preview template publik tanpa login, alur "pakai template ini", flag `is_featured` (tampil di homepage) dan `is_exclusive` (butuh entitlement paket).

### 2.3 Page Builder (multi-halaman)
- **Multi-halaman sudah nyata**, digerbangi limit paket `pages_total` (Starter/Organization = 1, Professional = 10). UI tambah/ubah/hapus halaman ada di builder.
- Registry section terpusat di `config/page-builder.php`, **~26 jenis section**, config-driven (`fields`, `defaults`, `cms`).
- Flag per section: `locked` (header/footer), `hidden` (belum siap - saat ini `jadwal-salat`), `exclusive` (butuh paket Professional).
- **Section variant**: tiap section dirender dari `templates/sections/{key}/{variant}.blade.php` lewat tabel `section_variants` (`SectionVariantResolver`). Variant punya `is_exclusive` sendiri, terpisah dari `exclusive` di registry - keduanya diperlukan untuk 5 section masjid premium.
- Tambah, hapus, duplikasi, reorder (SortableJS), preview per-section dan preview seluruh halaman.

### 2.4 CMS Konten
Model + migration + controller resource lengkap, semua ter-scope ke organisasi: **Post/Berita, Agenda, Pengumuman, Officer/Pengurus, Program, OrganizationNetwork, GalleryPhoto, MasjidFacility, FinancialReport, DonationProgram, DonationTransaction, Media**.
- Rich text lewat TipTap, disanitasi (`SanitizesRichText`, `config/purifier.php`).
- Menu CMS hanya muncul untuk section yang benar-benar dipakai organisasi (`Organization::hasSection()`).
- Sample data otomatis saat organisasi dibuat dari template (`CmsSampleDataSeeder` + `App\Services\Samples\*`).

### 2.5 Brand & Ganti Template
- Warna primer/sekunder, logo, font, border radius. Fallback 3 tingkat: **override organisasi → default template → default platform**.
- Font dan radius di-whitelist di `config/branding.php`.
- Validasi warna terlalu terang (`NotTooLightColor`, luminance WCAG).
- Ganti template kapan saja (destruktif: halaman lama dihapus dan di-clone ulang), hanya Owner.
- Kontak & sosial media organisasi: telepon, email, WhatsApp, alamat, Instagram, Facebook, TikTok, YouTube - semua dengan fallback ke `template.structure.contact`.

### 2.6 Situs Publik Tenant
- Subdomain routing native (`Route::domain()`), dikontrol `TENANT_DOMAIN`. Jika kosong, grup route tenant tidak didaftarkan sama sekali.
- Middleware group `tenant` khusus (bukan `web`): tanpa session/CSRF, plus `UseReadOnlyConnection` (koneksi MySQL SELECT-only di produksi).
- **`TenantPageCache`** - cache HTML penuh per halaman, invalidasi lewat version counter (bukan cache tags, karena store file/database tidak mendukungnya). Dibersihkan otomatis lewat trait `InvalidatesTenantPageCache` di setiap model CMS.
- Halaman publik: beranda, halaman builder lain, detail berita/pengumuman/agenda/program donasi, load-more berita & galeri, JSON-LD, SEO meta, `sitemap.xml`.
- Halaman error kustom (401/402/403/404/419/429/500/503).

### 2.7 Paket Langganan & Pembayaran
- **3 paket**: Starter Rp 10.000, Organization Rp 18.000, Professional Rp 25.000 per bulan.
- Entitlement: `hide_branding`, `has_exclusive_templates`.
- Limit per resource: posts, agendas, announcements, officers, programs, gallery_photos, facilities, donation_programs, `sections_total`, `pages_total`.
- `PlanLimitService` - resolusi 3 tingkat: **override per-tenant → snapshot limit yang sudah dibayar → limit live paket**.
- **Pembayaran otomatis lewat Midtrans Snap** (`config/billing.php`, `MidtransService`, `MidtransWebhookController` dengan verifikasi signature + re-fetch status). **Tidak ada** alur transfer manual.
- State machine `PlanChangeRequestStatus`: Pending → PaymentConfirmed → Approved/Rejected, plus `PaymentReceivedNeedsReview` (pembayaran masuk tapi auto-approve gagal, admin bisa retry maksimal `max_approve_attempts`) dan `Expired`.
- Kode diskon (`DiscountCode`), diskon durasi, auto-approve bila diskon menutup seluruh biaya.
- `Organization::planViolations()` memblokir publish dan menampilkan badge bila melanggar.
- Plan override oleh admin (`PlanOverrideLog`) untuk melewati pembayaran.

### 2.8 Admin Panel
- Middleware `admin` (`is_admin`) menggerbangi `/admin/*`.
- Kelola template (termasuk upload thumbnail, `is_featured`, `is_exclusive`), paket + limit, kode diskon, artikel/blog platform, daftar organisasi, approve/reject/retry plan change request, kelola section variant + preview-nya.
- **Editor template visual (sandbox)** - fitur paling khas: admin tidak menulis JSON `Template::structure` manual. `TemplateSandboxService` membuat organisasi `is_sandbox` sekali pakai, admin mendesainnya lewat builder biasa, lalu "Simpan ke Template" meng-export balik ke `structure`. Sandbox disembunyikan dari listing tenant asli (`scopeExcludingSandbox`).

### 2.9 Konten Platform
- Blog/artikel platform (`/berita`), dikelola dari admin, 4 artikel terbaru tampil di homepage.
- Homepage menampilkan template ber-`is_featured` (kurasi, bukan seluruh katalog) + daftar paket aktif.

---

## 3. Stack Teknis Aktual

- **Backend:** Laravel 13, PHP 8.3, SQLite (dev), MySQL (produksi, dengan user read-only terpisah untuk path tenant).
- **Frontend:** **Vite + Tailwind v4** (`resources/css/app.css`). Pola Tailwind CDN sudah **dihapus sepenuhnya** - jangan diperkenalkan kembali.
- **JS:** Alpine, TipTap, SortableJS, Litepicker, Swiper, driver.js.
- **Storage:** Cloudflare R2 (`MEDIA_DISK=r2`); test memakai disk `public`.
- **Pembayaran:** Midtrans Snap.
- **Multi-tenancy:** native `Route::domain()`, bukan paket pihak ketiga.
- **Config khusus produk:** `page-builder`, `tenancy`, `branding`, `billing`, `media`, `purifier`.
- **Konvensi dokumentasi:** komentar panjang yang menjelaskan *keputusan* dan alternatif yang ditolak, bukan sekadar apa yang dilakukan kode. Pertahankan gaya ini.

---

## 4. Status Test

`php artisan test` → **2 gagal, 2 error, 2 skip**, sisanya lulus (~168 test; totalnya bergeser mengikuti pekerjaan yang sedang berjalan).

Keempat kegagalan ini **sudah ada sebelumnya** dan tetap muncul pada `HEAD` yang bersih (working tree di-stash) - bukan akibat pekerjaan yang sedang berjalan:

| Test | Gejala | Sebab |
|---|---|---|
| `TenantDetailPagesTest::test_announcement_detail_page_renders` | 404 | Route cocok, tapi implicit model binding tidak tersubstitusi - parameter tetap string `"1"`, bukan model. Route berita sebelahnya lulus karena memakai slug string dan query manual. |
| `TenantDetailPagesTest::test_agenda_detail_page_renders` | 404 | Sama seperti di atas (`tenant.agendas.show`). |
| `OrganizationBrandTest::test_onboarding_checklist_reflects_logo_and_contact` | Error | `Organization` punya kolom `phone` **dan** method `phone()`. `onboardingChecklist()` membaca `filled($this->phone)`; bila atribut belum ter-load (instance hasil factory), Eloquent jatuh ke resolusi relasi lalu melempar error. Model hasil `fresh()`/route binding aman. Shadowing yang sama berlaku untuk `email`, `whatsapp`, `address`, dan 4 method URL sosial media. |
| `OrganizationBrandTest::test_onboarding_checklist_content_is_done_once_a_page_has_a_section` | Error | Sama seperti di atas. |

**Gunakan angka ini sebagai baseline.** Setelah mengubah kode, bandingkan dengan baseline ini, jangan berasumsi suite-nya hijau.

---

## 5. Yang Memang Belum Dibangun

Diverifikasi tidak ada di codebase (bukan sekadar "belum dicek"):

| Area | Status |
|---|---|
| Domain kustom (custom domain) | **Tidak ada.** Hanya subdomain platform. Tidak ada kolom, config, maupun kode terkait. |
| AI Co-Pilot | **Tidak ada.** Tidak ada integrasi LLM apa pun. |
| Analytics untuk tenant | **Tidak ada.** |
| Verifikasi DNS/SSL | **Tidak ada** (mengikuti custom domain). |
| Template marketplace | **Tidak ada.** |
| Organization Network (parent-child, syndication) | Model `OrganizationNetwork` ada, tapi hanya sebagai **daftar tautan jaringan AUM/Ortom untuk ditampilkan**, bukan relasi organisasi bertingkat maupun distribusi konten. |
| Role selain Owner/Editor | Hanya 2 role; PRD §15 menyebut lebih banyak. |

---

## 6. Catatan Penting untuk Sesi Berikutnya

- **Prototipe HTML root (`*.html`, `prompt`) sudah dihapus** di commit `fa9545c`. Salinan basi masih ada di `.claude/worktrees/prd-masjid-kegiatan/` dan **ter-track git secara tidak sengaja** - abaikan direktori itu; pertimbangkan menghapusnya dari index.
- **Hati-hati mengganti nama jenis organisasi.** Slug adalah kunci pencarian di semua template seeder, dan pencariannya null-safe (`$organizationType?->id`) sehingga slug basi **gagal diam-diam** - template ter-seed tanpa jenis organisasi. Ubah kedua sisi bersamaan.
- Menambah section baru: cukup `config/page-builder.php` + view + baris di `section_variants`. Tidak perlu menyentuh controller.
