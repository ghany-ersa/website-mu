# QA Test Case — Website-mu

**Versi dokumen:** 1.0
**Tanggal:** 2026-09-16
**Commit diuji:** `1c2bed9`
**Tujuan:** memverifikasi seluruh rangkaian proses end-to-end aman dan layak rilis production.

Dokumen ini adalah *rencana + register* test case. Hasil eksekusi dan angka coverage ada di
[`COVERAGE-REPORT.md`](COVERAGE-REPORT.md).

> **Status eksekusi (2026-09-16):** 384 test dijalankan, 382 lulus, 0 gagal, 0 error, 2 skipped.
> Seluruh test case bertanda `NEW` sudah diimplementasikan dan lulus. Ketiga defect pre-existing
> di §5 sudah diperbaiki dan dikunci tes regresi. Putusan rilis: **GO**.
>
> Pengujian dilakukan dalam dua gelombang. Gelombang 2 menambahkan cakupan di luar register
> awal dokumen ini: halaman `index`/`create`/`edit` seluruh resource CMS (sehingga form Blade
> benar-benar ter-render), `update`/`delete` beserta guard kepemilikan lintas-organisasi, CRUD
> panel admin, alur ganti template, dan `GoogleMapsEmbedResolver`. Rinciannya di
> [`COVERAGE-REPORT.md`](COVERAGE-REPORT.md) §4.

---

## 1. Ruang lingkup

Aplikasi yang diuji adalah platform multi-tenant no-code website builder untuk organisasi
Muhammadiyah. Rantai nilai utamanya:

```
Registrasi → Login → Pilih template → Buat organisasi → Susun halaman (builder)
   → Isi konten (CMS) → Atur brand → Beli/aktifkan paket (Midtrans) → Publish
   → Situs publik tayang di subdomain → Cache & SEO
```

### Masuk lingkup

| Area | Komponen |
|---|---|
| Autentikasi | register, login, logout, guard `auth`, guard `admin` |
| Katalog publik | homepage, `/templates`, preview template, `/berita` |
| Organisasi | create, store, show, edit (nama/slug/deskripsi), delete |
| Page builder | pages CRUD, sections CRUD/reorder/duplicate, variant, locked/hidden/exclusive |
| CMS | posts, agendas, announcements, officers, programs, networks, gallery, facilities, financial reports, donations |
| Brand | warna, font, radius, logo, kontak, sosial |
| Media | upload, list, delete, scoping antar-tenant |
| Plan & billing | plan change request, voucher diskon, Midtrans Snap, webhook, manual transfer, approval admin, limits snapshot |
| Publishing | gate `planViolations()`, publish/unpublish |
| Tenant site | render subdomain, detail berita/agenda/pengumuman/donasi, load-more, cache |
| Admin panel | templates, sandbox designer, articles, plans, discount codes, organizations, section variants, plan change requests |
| SEO/infra | `sitemap.xml`, meta/OG, security header |

### Di luar lingkup

- Uji beban / performance benchmark (tidak ada tooling di repo).
- Uji browser end-to-end (Dusk/Playwright tidak terpasang).
- Integrasi Midtrans terhadap sandbox Midtrans yang sesungguhnya (network call di-*fake*).
- Cloudflare R2 nyata (test memakai disk `public`, dipin di `phpunit.xml`).

---

## 2. Kondisi lingkungan uji

| Item | Nilai |
|---|---|
| PHP | 8.4.1 |
| Database | SQLite in-memory (`phpunit.xml`) |
| Cache | `array` |
| Queue | `sync` |
| Disk media | `public` |
| `TENANT_DOMAIN` | `website-mu.test` |
| Seeder wajib | `PlanSeeder`, `SectionVariantSeeder` (via `Tests\TestCase::setUp()`) |
| Perintah | `php artisan test` |

---

## 3. Klasifikasi severity

| Level | Arti | Efek pada rilis |
|---|---|---|
| **Critical** | Kehilangan data, kebocoran antar-tenant, uang salah dihitung, situs publik mati | Blocker |
| **Major** | Fitur utama gagal / gate keamanan tembus pada jalur tertentu | Blocker |
| **Minor** | Fungsi sekunder salah, tampilan/copy salah | Non-blocker |
| **Trivial** | Kosmetik | Non-blocker |

---

## 4. Register test case

Kolom **Status**: `AUTO` = tercover tes otomatis, `NEW` = tes otomatis ditambahkan pada siklus QA ini,
`MANUAL` = perlu verifikasi manual, `GAP` = belum tercover.

### 4.1 Autentikasi & otorisasi

| ID | Test case | Pra-kondisi | Langkah | Ekspektasi | Severity | Status |
|---|---|---|---|---|---|---|
| AUTH-01 | Registrasi user baru | Guest | POST `/register` dengan data valid | User dibuat, ter-login, redirect ke dashboard | Major | NEW |
| AUTH-02 | Registrasi dengan email duplikat | User `a@b.c` ada | POST `/register` email sama | 422/redirect dengan error `email` | Major | NEW |
| AUTH-03 | Registrasi dengan password lemah/tidak cocok | Guest | POST `/register` `password_confirmation` beda | Ditolak, user tidak dibuat | Major | NEW |
| AUTH-04 | Login sukses | User ada | POST `/login` kredensial benar | Redirect ke `organizations.index` | Major | AUTO |
| AUTH-05 | Login gagal | User ada | POST `/login` password salah | Tetap guest, error | Major | AUTO |
| AUTH-06 | Guest ditolak halaman organisasi | Guest | GET `organizations.index` | Redirect `login` | Major | AUTO |
| AUTH-07 | Logout | Login | POST `/logout` | Sesi berakhir | Minor | NEW |
| AUTH-08 | Non-admin ditolak area admin | User biasa | GET `admin/*` | 403 | **Critical** | NEW |
| AUTH-09 | Guest ditolak area admin | Guest | GET `admin/*` | Redirect login | **Critical** | NEW |

### 4.2 Katalog publik & SEO

| ID | Test case | Langkah | Ekspektasi | Severity | Status |
|---|---|---|---|---|---|
| PUB-01 | Homepage tayang | GET `/` | 200, hanya template `is_featured` + `is_active` | Minor | NEW |
| PUB-02 | Daftar template | GET `/templates` | 200, template aktif tampil | Minor | NEW |
| PUB-03 | Preview template | GET `templates/{slug}/preview` | 200, section ter-render | Major | AUTO |
| PUB-04 | Anchor preview valid | Setiap template seeded | Semua anchor CTA resolvable | Minor | AUTO |
| PUB-05 | Footer preview tanpa organisasi | GET preview | Footer render tanpa error | Minor | AUTO |
| PUB-06 | Limit item section di preview | Section `limit` diisi | Hanya N item tampil | Minor | AUTO |
| PUB-07 | Detail berita/donasi/agenda preview | GET preview detail | 200 | Minor | AUTO |
| PUB-08 | Artikel platform index | GET `/berita` | 200, hanya `published` | Minor | NEW |
| PUB-09 | Artikel draft tidak bocor | Artikel draft | GET `/berita/{slug}` | 404 | Major | NEW |
| PUB-10 | `sitemap.xml` valid | Ada org published + draft + sandbox | GET `/sitemap.xml` | XML valid, hanya org published non-sandbox | Major | NEW |
| PUB-11 | Header keamanan frame | GET halaman | `X-Frame-Options` diset | Minor | NEW |

### 4.3 Organisasi

| ID | Test case | Ekspektasi | Severity | Status |
|---|---|---|---|---|
| ORG-01 | Buat organisasi dengan template eksplisit | `template_id` dipakai apa adanya | Major | AUTO |
| ORG-02 | Template auto-pilih dari tipe organisasi | Template default tipe terpilih | Major | AUTO |
| ORG-03 | Template non-aktif tidak auto-terpilih | Tidak dipilih | Minor | AUTO |
| ORG-04 | Warna terlalu terang ditolak saat create | Validasi gagal | Minor | AUTO |
| ORG-05 | Ubah nama | Tersimpan; nama kosong ditolak | Minor | AUTO |
| ORG-06 | Ubah slug | Unik; slug reserved ditolak; slug sendiri boleh | Major | AUTO |
| ORG-07 | Ubah deskripsi | Tersimpan & bisa dikosongkan | Minor | AUTO |
| ORG-08 | Non-member ditolak edit | 403/404 | **Critical** | AUTO |
| ORG-09 | Owner hapus organisasi | Terhapus | Major | AUTO |
| ORG-10 | Editor tidak bisa hapus | Tombol & route ditolak | Major | AUTO |
| ORG-11 | Admin platform hapus tanpa jadi member | Berhasil | Major | AUTO |
| ORG-12 | Nama organisasi di-escape pada konfirmasi hapus | Aman dari XSS | Major | AUTO |
| ORG-13 | Tambah member | Member masuk dengan role | Major | NEW |
| ORG-14 | Ubah role member | Tersimpan | Major | NEW |
| ORG-15 | Owner terakhir tidak bisa dihapus/diturunkan | Ditolak | **Critical** | NEW |
| ORG-16 | Editor tidak bisa kelola member | 403 | **Critical** | NEW |

### 4.4 Page builder

| ID | Test case | Ekspektasi | Severity | Status |
|---|---|---|---|---|
| BLD-01 | Buka builder; halaman di-clone dari template | Halaman & section tercipta | Major | AUTO |
| BLD-02 | Non-member ditolak | 403 | **Critical** | AUTO |
| BLD-03 | Halaman milik org lain 404 | Tidak bocor | **Critical** | AUTO |
| BLD-04 | Tambah section → langsung ke form edit | Redirect ke edit | Minor | AUTO |
| BLD-05 | Tambah/update/duplicate/reorder/hapus section | Semua berhasil | Major | AUTO |
| BLD-06 | Update section tidak menghapus field yang tak ada di form | Field lain utuh | Major | AUTO |
| BLD-07 | Update via AJAX mengembalikan canvas | HTML canvas | Minor | AUTO |
| BLD-08 | Header/footer terkunci | Tidak bisa ditambah/duplikat/hapus | Major | AUTO |
| BLD-09 | Footer selalu render terakhir | Urutan dipaksa | Minor | AUTO |
| BLD-10 | Halaman home tidak bisa dihapus | Ditolak | Major | AUTO |
| BLD-11 | Halaman non-home bisa dihapus | Berhasil | Minor | AUTO |
| BLD-12 | Paket non-Professional tak bisa buat halaman ke-2 | Ditolak + CTA upgrade | Major | AUTO |
| BLD-13 | Paket Professional bisa halaman tambahan | Berhasil | Major | AUTO |
| BLD-14 | Section `exclusive` diblokir di paket rendah | Ditolak | **Critical** | AUTO |
| BLD-15 | Variant terkunci tidak bisa dipakai lewat request manual | Ditolak | **Critical** | AUTO |
| BLD-16 | Semua variant satu section membaca field registry yang sama | Konsisten | Minor | AUTO |
| BLD-17 | Setiap section CMS menaut ke CMS-nya tepat sekali | Konsisten | Minor | AUTO |
| BLD-18 | Section `hidden` tidak muncul di picker & render publik | Tersembunyi | Major | NEW |
| BLD-19 | Section milik org lain tidak bisa diubah | 403/404 | **Critical** | NEW |
| BLD-20 | Melebihi `sections_total` ditolak | Ditolak | Major | NEW |

### 4.5 CMS

| ID | Test case | Ekspektasi | Severity | Status |
|---|---|---|---|---|
| CMS-01 | Posts: create/update/delete | Berhasil | Major | AUTO |
| CMS-02 | Agenda & pengumuman: create | Berhasil | Major | AUTO |
| CMS-03 | Non-member ditolak kelola konten | 403 | **Critical** | AUTO |
| CMS-04 | Konten org lain 404 | Tidak bocor | **Critical** | AUTO |
| CMS-05 | Officers CRUD + reorder | Berhasil | Major | AUTO |
| CMS-06 | Program & layanan sebagai dua pool terpisah | Terpisah | Minor | AUTO |
| CMS-07 | Networks CRUD | Berhasil | Minor | AUTO |
| CMS-08 | Gallery CRUD + reorder | Berhasil | Major | AUTO |
| CMS-09 | Facilities CRUD + reorder | Berhasil | Minor | NEW |
| CMS-10 | Financial reports CRUD | Berhasil | Minor | NEW |
| CMS-11 | Donation program CRUD | Berhasil | Major | NEW |
| CMS-12 | Donation transaction create/delete memperbarui progres | Akurat | Major | NEW |
| CMS-13 | Batas paket (`posts`) menolak record ke-N+1 | Ditolak | Major | NEW |
| CMS-14 | Auto-bind section ke CMS pada halaman tenant | Terikat | Major | AUTO |

### 4.6 Brand & media

| ID | Test case | Ekspektasi | Severity | Status |
|---|---|---|---|---|
| BRD-01 | Warna brand disalin dari template saat create | Tersalin | Minor | AUTO |
| BRD-02 | Tanpa template → fallback warna platform | Fallback | Minor | AUTO |
| BRD-03 | Member update brand | Tersimpan | Major | AUTO |
| BRD-04 | Email/URL sosial invalid ditolak | Validasi | Minor | AUTO |
| BRD-05 | Warna tidak kontras ditolak | Validasi | Minor | AUTO |
| BRD-06 | Font & radius whitelist | Non-whitelist ditolak | Minor | AUTO |
| BRD-07 | Non-member ditolak | 403 | **Critical** | AUTO |
| BRD-08 | Checklist onboarding akurat | Status benar | Minor | AUTO ✅ *DEF-03 diperbaiki* |
| MED-01 | Upload & list media | Berhasil | Major | AUTO |
| MED-02 | Non-member ditolak upload/list | 403 | **Critical** | AUTO |
| MED-03 | Tidak bisa hapus media org lain | Ditolak | **Critical** | AUTO |
| MED-04 | Semua kategori image picker diterima | Diterima | Minor | AUTO |
| MED-05 | File non-image ditolak | Validasi | Major | NEW |

### 4.7 Paket & pembayaran

| ID | Test case | Ekspektasi | Severity | Status |
|---|---|---|---|---|
| PAY-01 | Buat plan change request | Status `Pending`, `plan_id` org **tidak** berubah | **Critical** | NEW |
| PAY-02 | Voucher diskon persen/nominal terhitung benar | `discount_amount` akurat, total ≥ 0 | **Critical** | NEW |
| PAY-03 | Voucher kedaluwarsa/limit habis ditolak | Ditolak | Major | NEW |
| PAY-04 | Webhook: signature salah | 403, tidak ada perubahan | **Critical** | NEW |
| PAY-05 | Webhook: order tidak dikenal | 404 | Major | NEW |
| PAY-06 | Webhook: `gross_amount` tidak cocok | Tidak diproses, status tak berubah | **Critical** | NEW |
| PAY-07 | Webhook: `settlement` → plan aktif | `plan_id` & `plan_expires_at` diset, snapshot limit dibekukan | **Critical** | NEW |
| PAY-08 | Webhook: notifikasi ganda idempoten | `plan_expires_at` tidak dobel | **Critical** | NEW |
| PAY-09 | Webhook: `expire`/`cancel` → `Expired` | Status benar | Major | NEW |
| PAY-10 | Webhook: `deny` → `Rejected` | Status benar | Major | NEW |
| PAY-11 | Webhook: `pending` → status tersimpan, belum aktif | Tidak aktif | Major | NEW |
| PAY-12 | Webhook: `approve()` gagal → `PaymentReceivedNeedsReview` | Status + `approve_error` terisi | **Critical** | NEW |
| PAY-13 | Perpanjangan paket sama sebelum kedaluwarsa menambah dari sisa masa | Waktu tidak hilang | Major | NEW |
| PAY-14 | Ganti paket berbeda dihitung dari sekarang | Tidak menumpuk | **Critical** | NEW |
| PAY-15 | `limits_snapshot` melindungi dari perubahan limit plan setelah bayar | Limit lama dipakai | Major | NEW |
| PAY-16 | Override limit per-tenant menang atas snapshot & plan | Prioritas benar | Major | NEW |
| PAY-17 | Manual transfer: konfirmasi tenant tidak mengaktifkan paket | Tetap butuh admin | **Critical** | AUTO |
| PAY-18 | Non-owner tak bisa konfirmasi transfer | Ditolak | **Critical** | AUTO |
| PAY-19 | Konfirmasi dua kali ditolak | Ditolak | Major | AUTO |
| PAY-20 | Admin approve transfer → paket aktif | Aktif | **Critical** | AUTO |
| PAY-21 | Mode manual-only melewati Midtrans | Snap tidak dibuat | Major | AUTO |
| PAY-22 | Admin tak bisa approve yang belum dikonfirmasi tenant | Ditolak | Major | AUTO |
| PAY-23 | Admin reject request | Status `Rejected` | Major | NEW |
| PAY-24 | Admin retry approve dibatasi `max_approve_attempts` | Dibatasi | Minor | NEW |
| PAY-25 | Admin override plan langsung | Plan aktif, tercatat di log | Major | NEW |

### 4.8 Publishing & situs tenant

| ID | Test case | Ekspektasi | Severity | Status |
|---|---|---|---|---|
| PUBL-01 | Organisasi published tayang di subdomain | 200 | **Critical** | AUTO |
| PUBL-02 | Organisasi draft 404 di subdomain | 404 | **Critical** | AUTO |
| PUBL-03 | Situs publik tanpa login | 200 | **Critical** | AUTO |
| PUBL-04 | Publish diblokir tanpa paket aktif | Ditolak | **Critical** | AUTO |
| PUBL-05 | Publish diblokir saat paket kedaluwarsa | Ditolak | **Critical** | AUTO |
| PUBL-06 | Unpublish tetap boleh saat kedaluwarsa | Diizinkan | Major | AUTO |
| PUBL-07 | Non-member tak bisa publish | 403 | **Critical** | AUTO |
| PUBL-08 | Publish diblokir saat konten melebihi limit | Ditolak dengan alasan | Major | NEW |
| PUBL-09 | `published_at` hanya distempel saat publish pertama | Tidak berubah | Minor | NEW |
| TEN-01 | Detail berita tayang | 200 | Major | AUTO |
| TEN-02 | Detail pengumuman tayang | 200 | Major | AUTO ✅ *DEF-01 diperbaiki* |
| TEN-03 | Detail agenda tayang | 200 | Major | AUTO ✅ *DEF-02 diperbaiki* |
| TEN-04 | Berita belum publish 404 | 404 | Major | AUTO |
| TEN-05 | Detail donasi tayang & ter-scope per org | 200 / 404 lintas org | **Critical** | AUTO |
| TEN-06 | Slug halaman tak dikenal 404 | 404 | Minor | AUTO |
| TEN-07 | Load-more berita & galeri | Batch berikutnya + `hasMore` | Minor | AUTO |
| TEN-08 | Halaman tenant di-cache | Render kedua dari cache | Major | NEW |
| TEN-09 | Tulis CMS mem-bump versi cache | Konten baru langsung tampil | **Critical** | NEW |
| TEN-10 | Detail pengumuman/agenda org lain tidak bocor | 404 | **Critical** | NEW |

### 4.9 Admin panel

| ID | Test case | Ekspektasi | Severity | Status |
|---|---|---|---|---|
| ADM-01 | Template CRUD + thumbnail | Berhasil, file lama dihapus | Major | AUTO |
| ADM-02 | Thumbnail non-image ditolak | Validasi | Minor | AUTO |
| ADM-03 | Sandbox round-trip struktur template | Tidak kehilangan section | Major | AUTO |
| ADM-04 | Sandbox tersembunyi dari listing tenant | Tersembunyi | Major | AUTO |
| ADM-05 | Non-admin tak bisa buka designer | 403 | **Critical** | AUTO |
| ADM-06 | Discount code CRUD | Berhasil | Minor | AUTO |
| ADM-07 | Non-admin ditolak discount code | 403 | **Critical** | AUTO |
| ADM-08 | Artikel platform CRUD | Berhasil | Minor | NEW |
| ADM-09 | Plan CRUD | Berhasil | Major | NEW |
| ADM-10 | Daftar & detail organisasi | 200 | Minor | NEW |
| ADM-11 | Section variant list & update | Berhasil | Minor | NEW |
| ADM-12 | Daftar plan change request | 200 | Minor | NEW |

---

## 5. Defect teridentifikasi

| ID | Ringkasan | Test case terkait | Severity | Status |
|---|---|---|---|---|
| **DEF-01** | Detail pengumuman tenant 404 — `withoutMiddleware('web')` ikut membuang `SubstituteBindings` dari grup `tenant`, sehingga parameter terikat tiba sebagai string | TEN-02 | Major | ✅ **Diperbaiki** |
| **DEF-02** | Detail agenda tenant 404 — akar masalah sama dengan DEF-01 | TEN-03 | Major | ✅ **Diperbaiki** |
| **DEF-03** | `Organization::phone` — kolom & method bernama sama; melempar `must return a relationship instance` saat atribut belum ter-load. Berlaku juga untuk `email`, `whatsapp`, `address` (4 method; accessor URL sosial tidak terdampak) | BRD-08 | Major | ✅ **Diperbaiki** |

Rincian akar masalah, perbaikan yang diterapkan, dan verifikasinya ada di
[`COVERAGE-REPORT.md`](COVERAGE-REPORT.md) §5. Ketiganya dikunci oleh
`tests/Feature/DefectRegressionTest.php`.

---

## 6. Kriteria kelulusan rilis

Rilis production dinyatakan **layak** bila seluruh syarat berikut terpenuhi:

1. Tidak ada defect **Critical** terbuka.
2. Tidak ada defect **Major** terbuka pada jalur uang (PAY-*), isolasi antar-tenant, atau
   ketersediaan situs publik (PUBL-*, TEN-*).
3. `php artisan test` hijau seluruhnya.
4. Route coverage alur kritikal ≥ 85%.
5. Line coverage `app/Services` dan `app/Http/Controllers` ≥ 70%.

Status pemenuhan kriteria dicatat di [`COVERAGE-REPORT.md`](COVERAGE-REPORT.md) §6.
