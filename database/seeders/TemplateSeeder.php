<?php

namespace Database\Seeders;

use App\Models\OrganizationType;
use App\Models\Template;
use Illuminate\Database\Seeder;

class TemplateSeeder extends Seeder
{
    /**
     * Seed one starter template per organization type, using the page/section structure
     * and component vocabulary from prd.md §7 (Template Library) and §8 (Component
     * Library). Content is representative sample copy (fictional org names, photos,
     * articles) meant to be replaced by the tenant, per prd.md §6 ("konten contoh yang
     * jelas ditandai untuk diganti").
     *
     * Each Ortom (NA, Pemuda Muhammadiyah, Tapak Suci, Hizbul Wathan, IMM, IPM) gets its
     * own template with tailored purpose/content and its own `structure.brand` color pair
     * instead of the default Muhammadiyah blue/green, since each has a distinct identity
     * and audience. The Ortom colors here are a draft based on general association with
     * each organization's visual identity, not verified official brand guidelines — swap
     * them for the real hex codes when available.
     */
    public function run(): void
    {
        $templates = [
            [
                'organization_type_slug' => 'muhammadiyah',
                'name' => 'Muhammadiyah',
                'slug' => 'muhammadiyah',
                'description' => 'Template untuk Muhammadiyah dan Aisyiyah: profil organisasi, struktur pengurus, berita, agenda, dan jaringan AUM/Ortom.',
                'structure' => [
                    'sample_org_name' => 'PCM Ambulu',
                    'brand' => ['primary' => '#2C368B', 'secondary' => '#079C4E'],
                    'pages' => [
                        [
                            'slug' => 'home',
                            'name' => 'Beranda',
                            'sections' => [
                                ['key' => 'header'],
                                ['key' => 'hero', 'content' => [
                                    'badge' => 'Bagian dari Persyarikatan Muhammadiyah',
                                    'headline' => 'Mencerahkan Semesta, Membangun Generasi',
                                    'subheadline' => 'PCM Ambulu menggerakkan dakwah, pendidikan, dan pemberdayaan masyarakat di Kecamatan Ambulu.',
                                    'cta_label' => 'Tentang Kami',
                                    'cta_secondary_label' => 'Lihat Program',
                                    'image' => 'https://images.unsplash.com/photo-1542816417-0983c9c9ad53?auto=format&fit=crop&w=1400&q=80',
                                ]],
                                ['key' => 'sambutan-ketua', 'content' => [
                                    'nama' => 'Drs. H. Ahmad Sujarwo, M.Pd.I',
                                    'jabatan' => 'Ketua PCM Ambulu periode 2022–2027',
                                    'sambutan' => 'Assalamu\'alaikum warahmatullahi wabarakatuh. Selamat datang di rumah digital PCM Ambulu. Melalui platform ini kami ingin menyapa warga Muhammadiyah dan masyarakat luas dengan informasi kegiatan dakwah, pendidikan, dan pelayanan sosial yang kami jalankan bersama.',
                                    'photo' => 'https://randomuser.me/api/portraits/men/32.jpg',
                                ]],
                                ['key' => 'program-unggulan', 'content' => [
                                    'title' => 'Program Unggulan',
                                    'items' => [
                                        ['title' => 'Dakwah dan Pengajian', 'description' => 'Kajian rutin mingguan dan pengajian akbar di tingkat cabang dan ranting.', 'icon' => '🕌'],
                                        ['title' => 'Pendidikan dan Amal Usaha', 'description' => 'Pembinaan sekolah, madrasah, dan lembaga pendidikan Muhammadiyah di wilayah Ambulu.', 'icon' => '🎓'],
                                        ['title' => 'Pemberdayaan Masyarakat', 'description' => 'Program sosial, ekonomi, dan kesehatan untuk warga sekitar.', 'icon' => '🤝'],
                                    ],
                                ]],
                                ['key' => 'daftar-berita', 'content' => [
                                    'title' => 'Berita Terbaru',
                                    'items' => [
                                        ['title' => 'Musycab PCM Ambulu Tetapkan Kepengurusan Baru', 'excerpt' => 'Musyawarah Cabang menghasilkan susunan pimpinan periode 2022-2027 dengan semangat berkemajuan.', 'category' => 'Organisasi', 'date' => '12 Agt 2026', 'image' => 'https://images.unsplash.com/photo-1519817650390-64a93db51149?auto=format&fit=crop&w=700&q=80'],
                                        ['title' => 'Baksos Kesehatan Gratis untuk Warga Ambulu', 'excerpt' => 'PCM bersama Klinik Muhammadiyah Sehati menggelar pemeriksaan kesehatan gratis.', 'category' => 'Sosial', 'date' => '05 Agt 2026', 'image' => 'https://images.unsplash.com/photo-1585036156171-384164a8c675?auto=format&fit=crop&w=700&q=80'],
                                        ['title' => 'Pelatihan Dai Muda Angkatan Ketiga Dibuka', 'excerpt' => 'Kaderisasi dai muda untuk memperkuat dakwah di tingkat ranting.', 'category' => 'Dakwah', 'date' => '28 Jul 2026', 'image' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=700&q=80'],
                                    ],
                                ]],
                                ['key' => 'agenda', 'content' => [
                                    'title' => 'Agenda Kegiatan',
                                    'items' => [
                                        ['title' => 'Pengajian Ahad Pagi', 'date_day' => '17', 'date_month' => 'Agt', 'time' => '06.30 WIB', 'location' => 'Masjid Al-Ikhlas Ambulu'],
                                        ['title' => 'Rapat Koordinasi Pimpinan Ranting', 'date_day' => '22', 'date_month' => 'Agt', 'time' => '19.30 WIB', 'location' => 'Kantor PCM Ambulu'],
                                        ['title' => 'Bakti Sosial Idul Adha Lanjutan', 'date_day' => '29', 'date_month' => 'Agt', 'time' => '08.00 WIB', 'location' => 'Balai Desa Ambulu'],
                                    ],
                                ]],
                                ['key' => 'cta', 'content' => [
                                    'title' => 'Mari Bersilaturahmi',
                                    'subtitle' => 'Terhubung dengan kegiatan dan program PCM Ambulu.',
                                    'cta_label' => 'Hubungi Kami',
                                ]],
                                ['key' => 'footer'],
                            ],
                        ],
                        [
                            'slug' => 'tentang',
                            'name' => 'Tentang',
                            'sections' => [
                                ['key' => 'header'],
                                ['key' => 'tentang-organisasi', 'content' => [
                                    'title' => 'Tentang PCM Ambulu',
                                    'body' => 'PCM Ambulu berdiri sejak 1978 dan menaungi ranting-ranting Muhammadiyah di Kecamatan Ambulu. Kami bergerak di bidang dakwah, pendidikan, kesehatan, dan pemberdayaan masyarakat bersama seluruh Amal Usaha Muhammadiyah di wilayah ini.',
                                    'image' => 'https://images.unsplash.com/photo-1470004914212-05527e49370b?auto=format&fit=crop&w=1000&q=80',
                                    'stats' => [
                                        ['value' => '12', 'label' => 'Ranting Aktif'],
                                        ['value' => '46', 'label' => 'Tahun Mengabdi'],
                                        ['value' => '8', 'label' => 'Amal Usaha'],
                                    ],
                                ]],
                                ['key' => 'struktur-pengurus', 'content' => [
                                    'title' => 'Struktur Pengurus 2022–2027',
                                    'items' => [
                                        ['name' => 'Drs. H. Ahmad Sujarwo, M.Pd.I', 'role' => 'Ketua', 'photo' => 'https://randomuser.me/api/portraits/men/32.jpg'],
                                        ['name' => 'H. Bambang Riyadi, S.Ag.', 'role' => 'Wakil Ketua', 'photo' => 'https://randomuser.me/api/portraits/men/45.jpg'],
                                        ['name' => 'Siti Maryam, S.Pd.', 'role' => 'Sekretaris', 'photo' => 'https://randomuser.me/api/portraits/women/65.jpg'],
                                        ['name' => 'H. Slamet Widodo', 'role' => 'Bendahara', 'photo' => 'https://randomuser.me/api/portraits/men/78.jpg'],
                                    ],
                                ]],
                                ['key' => 'jaringan-aum-ortom', 'content' => [
                                    'title' => 'Jaringan AUM & Ortom',
                                    'items' => [
                                        ['name' => 'SD Muhammadiyah 1 Ambulu', 'type' => 'AUM Pendidikan'],
                                        ['name' => 'SMP Muhammadiyah 2 Ambulu', 'type' => 'AUM Pendidikan'],
                                        ['name' => 'Klinik Muhammadiyah Sehati', 'type' => 'AUM Kesehatan'],
                                        ['name' => 'Pemuda Muhammadiyah Ambulu', 'type' => 'Ortom'],
                                        ['name' => 'Nasyiatul Aisyiyah Ambulu', 'type' => 'Ortom'],
                                        ['name' => 'Masjid Al-Ikhlas Ambulu', 'type' => 'Masjid'],
                                    ],
                                ]],
                                ['key' => 'footer'],
                            ],
                        ],
                        [
                            'slug' => 'kontak',
                            'name' => 'Kontak',
                            'sections' => [
                                ['key' => 'header'],
                                ['key' => 'formulir-kontak', 'content' => [
                                    'title' => 'Hubungi Kami',
                                    'subtitle' => 'Punya pertanyaan atau ingin bersilaturahmi? Kirimkan pesan kepada kami.',
                                ]],
                                ['key' => 'lokasi-peta', 'content' => [
                                    'title' => 'Kantor PCM Ambulu',
                                    'address' => 'Jl. Kh. Ahmad Dahlan No. 12, Ambulu, Kabupaten Jember, Jawa Timur',
                                ]],
                                ['key' => 'footer'],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'organization_type_slug' => 'aum-pendidikan',
                'name' => 'AUM Pendidikan',
                'slug' => 'aum-pendidikan',
                'description' => 'Template untuk sekolah, madrasah, dan perguruan tinggi Muhammadiyah: profil, PPDB/PMB, program, dan berita.',
                'structure' => [
                    'sample_org_name' => 'SD Muhammadiyah 1 Ambulu',
                    'brand' => ['primary' => '#2C368B', 'secondary' => '#079C4E'],
                    'pages' => [
                        [
                            'slug' => 'home',
                            'name' => 'Beranda',
                            'sections' => [
                                ['key' => 'header'],
                                ['key' => 'hero', 'content' => [
                                    'badge' => 'Terakreditasi A',
                                    'headline' => 'Mendidik dengan Berkemajuan',
                                    'subheadline' => 'SD Muhammadiyah 1 Ambulu membentuk generasi unggul yang berlandaskan iman dan ilmu.',
                                    'cta_label' => 'Daftar Sekarang',
                                    'cta_secondary_label' => 'Lihat Program',
                                    'image' => 'https://images.unsplash.com/photo-1585036156171-384164a8c675?auto=format&fit=crop&w=1400&q=80',
                                ]],
                                ['key' => 'tentang-organisasi', 'content' => [
                                    'title' => 'Profil SD Muhammadiyah 1 Ambulu',
                                    'body' => 'Berdiri sejak 1990, SD Muhammadiyah 1 Ambulu mengintegrasikan kurikulum nasional dengan pendidikan Al-Islam dan Kemuhammadiyahan untuk membentuk siswa yang cerdas dan berakhlak mulia.',
                                    'image' => 'https://images.unsplash.com/photo-1509062522246-3755977927d7?auto=format&fit=crop&w=1000&q=80',
                                    'stats' => [
                                        ['value' => 'A', 'label' => 'Akreditasi'],
                                        ['value' => '540', 'label' => 'Siswa Aktif'],
                                        ['value' => '32', 'label' => 'Tenaga Pendidik'],
                                    ],
                                ]],
                                ['key' => 'program-unggulan', 'content' => [
                                    'title' => 'Program Unggulan',
                                    'items' => [
                                        ['title' => 'Tahfidz Al-Quran', 'description' => 'Target hafalan 2 juz selama jenjang sekolah dasar.', 'icon' => '📖'],
                                        ['title' => 'Sains dan Teknologi', 'description' => 'Kelas coding dan robotika untuk kelas 4-6.', 'icon' => '🔬'],
                                        ['title' => 'Kepemimpinan Siswa', 'description' => 'Pembinaan organisasi siswa dan kegiatan kepanduan Hizbul Wathan.', 'icon' => '🧭'],
                                    ],
                                ]],
                                ['key' => 'ppdb', 'content' => [
                                    'title' => 'Penerimaan Peserta Didik Baru',
                                    'body' => 'Pendaftaran tahun ajaran 2026/2027 dibuka untuk jalur reguler dan jalur prestasi.',
                                    'cta_label' => 'Info PPDB',
                                    'deadline' => 'Ditutup 30 Juni 2026',
                                ]],
                                ['key' => 'daftar-berita', 'content' => [
                                    'title' => 'Berita Sekolah',
                                    'items' => [
                                        ['title' => 'Siswa Raih Juara 1 Olimpiade Sains Kabupaten', 'excerpt' => 'Tim sains sekolah berhasil membawa pulang medali emas tingkat kabupaten.', 'category' => 'Prestasi', 'date' => '10 Agt 2026', 'image' => 'https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?auto=format&fit=crop&w=700&q=80'],
                                        ['title' => 'Wisuda Tahfidz Angkatan ke-8', 'excerpt' => '48 siswa menyelesaikan target hafalan 2 juz tahun ini.', 'category' => 'Kegiatan', 'date' => '02 Agt 2026', 'image' => 'https://images.unsplash.com/photo-1524178232363-1fb2b075b655?auto=format&fit=crop&w=700&q=80'],
                                        ['title' => 'Kunjungan Edukasi ke Museum Jember', 'excerpt' => 'Kelas 5 dan 6 mengikuti pembelajaran luar kelas bertema sejarah lokal.', 'category' => 'Kegiatan', 'date' => '25 Jul 2026', 'image' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=700&q=80'],
                                    ],
                                ]],
                                ['key' => 'formulir-kontak', 'content' => [
                                    'title' => 'Hubungi Kami',
                                    'subtitle' => 'Informasi pendaftaran dan kegiatan sekolah.',
                                ]],
                                ['key' => 'footer'],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'organization_type_slug' => 'aum-kesehatan',
                'name' => 'AUM Kesehatan & Sosial',
                'slug' => 'aum-kesehatan-sosial',
                'description' => 'Template untuk klinik, rumah sakit, dan layanan sosial: layanan, agenda, dan donasi.',
                'structure' => [
                    'sample_org_name' => 'Klinik Muhammadiyah Sehati',
                    'brand' => ['primary' => '#2C368B', 'secondary' => '#079C4E'],
                    'pages' => [
                        [
                            'slug' => 'home',
                            'name' => 'Beranda',
                            'sections' => [
                                ['key' => 'header'],
                                ['key' => 'hero', 'content' => [
                                    'badge' => 'Amal Usaha Muhammadiyah',
                                    'headline' => 'Melayani dengan Ikhlas',
                                    'subheadline' => 'Klinik Muhammadiyah Sehati menyediakan layanan kesehatan dan sosial bagi masyarakat Ambulu.',
                                    'cta_label' => 'Buat Janji Temu',
                                    'image' => 'https://images.unsplash.com/photo-1531206715517-5c0ba140b2b8?auto=format&fit=crop&w=1400&q=80',
                                ]],
                                ['key' => 'layanan', 'content' => [
                                    'title' => 'Layanan Kami',
                                    'items' => [
                                        ['title' => 'Layanan Umum', 'description' => 'Pemeriksaan dan konsultasi dokter umum setiap hari.', 'icon' => '🩺'],
                                        ['title' => 'Layanan Rujukan', 'description' => 'Rujukan cepat ke rumah sakit mitra untuk kasus lanjutan.', 'icon' => '🏥'],
                                        ['title' => 'Layanan Sosial', 'description' => 'Pemeriksaan gratis rutin bagi warga kurang mampu.', 'icon' => '❤️'],
                                    ],
                                ]],
                                ['key' => 'jadwal-praktik', 'content' => [
                                    'title' => 'Jadwal Praktik Dokter',
                                    'doctors' => [
                                        ['name' => 'dr. Siti Rahma', 'specialty' => 'Dokter Umum', 'schedule' => 'Senin - Jumat, 08.00 - 14.00 WIB'],
                                        ['name' => 'dr. Ahmad Fauzi', 'specialty' => 'Dokter Gigi', 'schedule' => 'Selasa & Kamis, 09.00 - 12.00 WIB'],
                                        ['name' => 'dr. Nur Hidayah, Sp.A', 'specialty' => 'Dokter Anak', 'schedule' => 'Sabtu, 08.00 - 11.00 WIB'],
                                    ],
                                ]],
                                ['key' => 'agenda', 'content' => [
                                    'title' => 'Agenda dan Kegiatan',
                                    'items' => [
                                        ['title' => 'Baksos Kesehatan Gratis', 'date_day' => '18', 'date_month' => 'Agt', 'time' => '08.00 WIB', 'location' => 'Balai Desa Ambulu'],
                                        ['title' => 'Donor Darah Bulanan', 'date_day' => '24', 'date_month' => 'Agt', 'time' => '09.00 WIB', 'location' => 'Klinik Muhammadiyah Sehati'],
                                        ['title' => 'Penyuluhan Gizi Anak', 'date_day' => '30', 'date_month' => 'Agt', 'time' => '10.00 WIB', 'location' => 'Posyandu Ambulu'],
                                    ],
                                ]],
                                ['key' => 'donasi-zakat-infak', 'content' => [
                                    'title' => 'Donasi Layanan Kesehatan',
                                    'body' => 'Dukung layanan kesehatan gratis bagi warga kurang mampu melalui donasi.',
                                    'image' => 'https://images.unsplash.com/photo-1593113598332-cd288d649433?auto=format&fit=crop&w=600&q=80',
                                ]],
                                ['key' => 'lokasi-peta', 'content' => [
                                    'title' => 'Lokasi Klinik',
                                    'address' => 'Jl. Kesehatan No. 8, Ambulu, Kabupaten Jember, Jawa Timur',
                                ]],
                                ['key' => 'footer'],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'organization_type_slug' => 'nasyiatul-aisyiyah',
                'name' => 'Nasyiatul Aisyiyah',
                'slug' => 'nasyiatul-aisyiyah',
                'description' => 'Template untuk Nasyiatul Aisyiyah: menonjolkan citra perempuan muda berkemajuan, program keputrian, dan ajakan bergabung. Warna draf — sesuaikan dengan pedoman identitas resmi.',
                'structure' => [
                    'sample_org_name' => 'Nasyiatul Aisyiyah Ambulu',
                    'brand' => ['primary' => '#6B2C91', 'secondary' => '#EC4899'],
                    'pages' => [
                        [
                            'slug' => 'home',
                            'name' => 'Beranda',
                            'sections' => [
                                ['key' => 'header'],
                                ['key' => 'hero', 'content' => [
                                    'badge' => 'Organisasi Otonom Muhammadiyah — Perempuan Muda',
                                    'headline' => 'Perempuan Muda, Berdaya dan Berkemajuan',
                                    'subheadline' => 'Nasyiatul Aisyiyah Ambulu menghimpun perempuan muda dalam dakwah, pendidikan, dan pemberdayaan sosial.',
                                    'cta_label' => 'Gabung Sekarang',
                                    'cta_secondary_label' => 'Lihat Program',
                                    'image' => 'https://images.unsplash.com/photo-1519817650390-64a93db51149?auto=format&fit=crop&w=1400&q=80',
                                ]],
                                ['key' => 'tentang-organisasi', 'content' => [
                                    'title' => 'Tentang Nasyiatul Aisyiyah Ambulu',
                                    'body' => 'Nasyiatul Aisyiyah Ambulu menjadi wadah bagi perempuan muda untuk berdakwah, belajar, dan berkarya melalui program keputrian, pendidikan, dan pemberdayaan ekonomi.',
                                    'image' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=1000&q=80',
                                ]],
                                ['key' => 'program-unggulan', 'content' => [
                                    'title' => 'Program Unggulan',
                                    'items' => [
                                        ['title' => 'Kajian Keputrian', 'description' => 'Kajian rutin seputar fikih perempuan dan pengembangan diri.', 'icon' => '📗'],
                                        ['title' => 'Pelatihan Kepemimpinan Perempuan', 'description' => 'Pembinaan kader perempuan muda menjadi pemimpin organisasi.', 'icon' => '🌸'],
                                        ['title' => 'Pemberdayaan Ekonomi Perempuan', 'description' => 'Pelatihan usaha mikro dan kewirausahaan bagi anggota.', 'icon' => '🧵'],
                                    ],
                                ]],
                                ['key' => 'daftar-berita', 'content' => [
                                    'title' => 'Publikasi Terbaru',
                                    'items' => [
                                        ['title' => 'Pelantikan Pimpinan Cabang Periode 2026-2029', 'excerpt' => 'Pelantikan pengurus baru berlangsung khidmat di Aula PCM Ambulu.', 'category' => 'Organisasi', 'date' => '09 Agt 2026', 'image' => 'https://images.unsplash.com/photo-1585036156171-384164a8c675?auto=format&fit=crop&w=700&q=80'],
                                        ['title' => 'Pelatihan Usaha Mikro untuk Anggota', 'excerpt' => 'Puluhan anggota mengikuti pelatihan pengelolaan usaha rumahan.', 'category' => 'Pemberdayaan', 'date' => '02 Agt 2026', 'image' => 'https://images.unsplash.com/photo-1470004914212-05527e49370b?auto=format&fit=crop&w=700&q=80'],
                                        ['title' => 'Kajian Keputrian Bulanan Digelar', 'excerpt' => 'Kajian membahas peran perempuan dalam keluarga dan masyarakat.', 'category' => 'Dakwah', 'date' => '25 Jul 2026', 'image' => 'https://images.unsplash.com/photo-1542816417-0983c9c9ad53?auto=format&fit=crop&w=700&q=80'],
                                    ],
                                ]],
                                ['key' => 'cta', 'content' => [
                                    'title' => 'Bergabunglah dengan Nasyiatul Aisyiyah',
                                    'subtitle' => 'Jadi bagian dari gerakan perempuan muda berkemajuan.',
                                    'cta_label' => 'Daftar Jadi Anggota',
                                ]],
                                ['key' => 'formulir-kontak', 'content' => [
                                    'title' => 'Hubungi Kami',
                                    'subtitle' => 'Ingin tahu lebih lanjut tentang kegiatan kami?',
                                ]],
                                ['key' => 'footer'],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'organization_type_slug' => 'pemuda-muhammadiyah',
                'name' => 'Pemuda Muhammadiyah',
                'slug' => 'pemuda-muhammadiyah',
                'description' => 'Template untuk Pemuda Muhammadiyah: menonjolkan citra organisasi, kaderisasi, dan ajakan bergabung. Warna draf — sesuaikan dengan pedoman identitas resmi.',
                'structure' => [
                    'sample_org_name' => 'Pemuda Muhammadiyah Ambulu',
                    'brand' => ['primary' => '#079C4E', 'secondary' => '#E8622C'],
                    'pages' => [
                        [
                            'slug' => 'home',
                            'name' => 'Beranda',
                            'sections' => [
                                ['key' => 'header'],
                                ['key' => 'hero', 'content' => [
                                    'badge' => 'Organisasi Otonom Muhammadiyah',
                                    'headline' => 'Bergerak Bersama Pemuda Muhammadiyah',
                                    'subheadline' => 'Wadah kaderisasi dan kegiatan bagi pemuda di Kecamatan Ambulu.',
                                    'cta_label' => 'Gabung Sekarang',
                                    'cta_secondary_label' => 'Lihat Program',
                                    'image' => 'https://images.unsplash.com/photo-1519817650390-64a93db51149?auto=format&fit=crop&w=1400&q=80',
                                ]],
                                ['key' => 'tentang-organisasi', 'content' => [
                                    'title' => 'Tentang Pemuda Muhammadiyah Ambulu',
                                    'body' => 'Pemuda Muhammadiyah Ambulu menjadi wadah kaderisasi, aksi sosial, dan pengembangan kepemimpinan bagi generasi muda Muhammadiyah di wilayah Ambulu.',
                                    'image' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=1000&q=80',
                                ]],
                                ['key' => 'program-unggulan', 'content' => [
                                    'title' => 'Kegiatan dan Kampanye',
                                    'items' => [
                                        ['title' => 'Kaderisasi', 'description' => 'Pelatihan dasar kepemimpinan bagi anggota baru.', 'icon' => '🧑‍🤝‍🧑'],
                                        ['title' => 'Aksi Sosial', 'description' => 'Bakti sosial dan gerakan kepedulian lingkungan.', 'icon' => '🌱'],
                                        ['title' => 'Pelatihan Kewirausahaan', 'description' => 'Workshop bisnis dan pengembangan diri pemuda.', 'icon' => '🚀'],
                                    ],
                                ]],
                                ['key' => 'daftar-berita', 'content' => [
                                    'title' => 'Publikasi Terbaru',
                                    'items' => [
                                        ['title' => 'Pelantikan Pengurus Baru Periode 2026-2029', 'excerpt' => 'Pelantikan berlangsung khidmat dihadiri jajaran PCM Ambulu.', 'category' => 'Organisasi', 'date' => '08 Agt 2026', 'image' => 'https://images.unsplash.com/photo-1585036156171-384164a8c675?auto=format&fit=crop&w=700&q=80'],
                                        ['title' => 'Aksi Bersih Sungai Bersama Warga', 'excerpt' => 'Kegiatan gotong royong membersihkan aliran sungai di Desa Ambulu.', 'category' => 'Sosial', 'date' => '30 Jul 2026', 'image' => 'https://images.unsplash.com/photo-1470004914212-05527e49370b?auto=format&fit=crop&w=700&q=80'],
                                        ['title' => 'Pelatihan Kewirausahaan Pemuda', 'excerpt' => 'Puluhan anggota mengikuti workshop bisnis digital selama dua hari.', 'category' => 'Pelatihan', 'date' => '20 Jul 2026', 'image' => 'https://images.unsplash.com/photo-1542816417-0983c9c9ad53?auto=format&fit=crop&w=700&q=80'],
                                    ],
                                ]],
                                ['key' => 'cta', 'content' => [
                                    'title' => 'Rekrutmen Anggota Baru',
                                    'subtitle' => 'Bergabunglah dan jadi bagian dari gerakan pemuda berkemajuan.',
                                    'cta_label' => 'Daftar Jadi Anggota',
                                ]],
                                ['key' => 'formulir-kontak', 'content' => [
                                    'title' => 'Hubungi Kami',
                                    'subtitle' => 'Ingin tahu lebih lanjut tentang kegiatan kami?',
                                ]],
                                ['key' => 'footer'],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'organization_type_slug' => 'tapak-suci',
                'name' => 'Tapak Suci',
                'slug' => 'tapak-suci',
                'description' => 'Template untuk Tapak Suci: menonjolkan citra perguruan bela diri, prestasi atlet, dan ajakan bergabung latihan. Warna draf — sesuaikan dengan pedoman identitas resmi.',
                'structure' => [
                    'sample_org_name' => 'Tapak Suci Putera Muhammadiyah Ambulu',
                    'brand' => ['primary' => '#C1272D', 'secondary' => '#F4B400'],
                    'pages' => [
                        [
                            'slug' => 'home',
                            'name' => 'Beranda',
                            'sections' => [
                                ['key' => 'header'],
                                ['key' => 'hero', 'content' => [
                                    'badge' => 'Perguruan Seni Bela Diri Otentik Indonesia',
                                    'headline' => 'Kuat Fisik, Kokoh Akidah',
                                    'subheadline' => 'Tapak Suci Ambulu membina generasi pendekar yang tangguh, berprestasi, dan berakhlak mulia.',
                                    'cta_label' => 'Daftar Latihan',
                                    'cta_secondary_label' => 'Lihat Prestasi',
                                    'image' => 'https://images.unsplash.com/photo-1577896851231-70ef18881754?auto=format&fit=crop&w=1400&q=80',
                                ]],
                                ['key' => 'tentang-organisasi', 'content' => [
                                    'title' => 'Tentang Tapak Suci Ambulu',
                                    'body' => 'Tapak Suci Putera Muhammadiyah Ambulu membina anggota dalam latihan pencak silat, pembentukan karakter, dan pencapaian prestasi di tingkat daerah maupun nasional.',
                                    'image' => 'https://images.unsplash.com/photo-1524178232363-1fb2b075b655?auto=format&fit=crop&w=1000&q=80',
                                ]],
                                ['key' => 'program-unggulan', 'content' => [
                                    'title' => 'Program Unggulan',
                                    'items' => [
                                        ['title' => 'Latihan Rutin Pencak Silat', 'description' => 'Latihan fisik dan jurus dasar hingga lanjutan setiap pekan.', 'icon' => '🥋'],
                                        ['title' => 'Pembinaan Atlet Prestasi', 'description' => 'Persiapan anggota untuk kejuaraan tingkat daerah dan nasional.', 'icon' => '🏆'],
                                        ['title' => 'Ujian Kenaikan Tingkat', 'description' => 'Evaluasi berkala untuk kenaikan sabuk dan jenjang keanggotaan.', 'icon' => '🎖️'],
                                    ],
                                ]],
                                ['key' => 'daftar-berita', 'content' => [
                                    'title' => 'Publikasi Terbaru',
                                    'items' => [
                                        ['title' => 'Atlet Tapak Suci Ambulu Raih Juara 1 Kejuaraan Daerah', 'excerpt' => 'Tim pencak silat berhasil membawa pulang medali emas kategori tanding.', 'category' => 'Prestasi', 'date' => '11 Agt 2026', 'image' => 'https://images.unsplash.com/photo-1560250097-0b93528c311a?auto=format&fit=crop&w=700&q=80'],
                                        ['title' => 'Ujian Kenaikan Sabuk Angkatan ke-14', 'excerpt' => '32 anggota mengikuti ujian kenaikan tingkat sabuk kuning ke hijau.', 'category' => 'Kegiatan', 'date' => '03 Agt 2026', 'image' => 'https://images.unsplash.com/photo-1531206715517-5c0ba140b2b8?auto=format&fit=crop&w=700&q=80'],
                                        ['title' => 'Pembukaan Pendaftaran Anggota Baru', 'excerpt' => 'Latihan perdana bagi anggota baru dimulai awal bulan depan.', 'category' => 'Pengumuman', 'date' => '27 Jul 2026', 'image' => 'https://images.unsplash.com/photo-1593113598332-cd288d649433?auto=format&fit=crop&w=700&q=80'],
                                    ],
                                ]],
                                ['key' => 'cta', 'content' => [
                                    'title' => 'Jadi Bagian dari Pendekar Tapak Suci',
                                    'subtitle' => 'Latihan terbuka untuk pelajar, mahasiswa, dan umum.',
                                    'cta_label' => 'Daftar Sekarang',
                                ]],
                                ['key' => 'formulir-kontak', 'content' => [
                                    'title' => 'Hubungi Kami',
                                    'subtitle' => 'Tanya jadwal dan lokasi latihan.',
                                ]],
                                ['key' => 'footer'],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'organization_type_slug' => 'hizbul-wathan',
                'name' => 'Hizbul Wathan',
                'slug' => 'hizbul-wathan',
                'description' => 'Template untuk Hizbul Wathan: menonjolkan citra kepanduan, kegiatan alam, dan ajakan bergabung. Warna draf — sesuaikan dengan pedoman identitas resmi.',
                'structure' => [
                    'sample_org_name' => 'Hizbul Wathan Qabilah Ambulu',
                    'brand' => ['primary' => '#6F4E23', 'secondary' => '#079C4E'],
                    'pages' => [
                        [
                            'slug' => 'home',
                            'name' => 'Beranda',
                            'sections' => [
                                ['key' => 'header'],
                                ['key' => 'hero', 'content' => [
                                    'badge' => 'Gerakan Kepanduan Muhammadiyah',
                                    'headline' => 'Berpetualang, Berkarakter, Berkemajuan',
                                    'subheadline' => 'Hizbul Wathan Ambulu membina generasi muda lewat kegiatan kepanduan yang mendidik dan menyenangkan.',
                                    'cta_label' => 'Ikut Perkemahan',
                                    'cta_secondary_label' => 'Lihat Kegiatan',
                                    'image' => 'https://images.unsplash.com/photo-1509062522246-3755977927d7?auto=format&fit=crop&w=1400&q=80',
                                ]],
                                ['key' => 'tentang-organisasi', 'content' => [
                                    'title' => 'Tentang Hizbul Wathan Ambulu',
                                    'body' => 'Hizbul Wathan Qabilah Ambulu membina anak dan remaja melalui kegiatan kepanduan yang menumbuhkan kemandirian, kepemimpinan, dan kecintaan pada alam.',
                                    'image' => 'https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?auto=format&fit=crop&w=1000&q=80',
                                ]],
                                ['key' => 'program-unggulan', 'content' => [
                                    'title' => 'Program Unggulan',
                                    'items' => [
                                        ['title' => 'Perkemahan dan Penjelajahan', 'description' => 'Kegiatan alam terbuka untuk melatih kemandirian dan kerja sama.', 'icon' => '⛺'],
                                        ['title' => 'Pelatihan Kepemimpinan Pandu', 'description' => 'Pembinaan jenjang Athfal, Pengenal, hingga Penghela.', 'icon' => '🧭'],
                                        ['title' => 'Pendidikan Karakter', 'description' => 'Penanaman nilai kedisiplinan dan kemandirian sejak dini.', 'icon' => '🌟'],
                                    ],
                                ]],
                                ['key' => 'daftar-berita', 'content' => [
                                    'title' => 'Publikasi Terbaru',
                                    'items' => [
                                        ['title' => 'Perkemahan Akbar Qabilah Ambulu', 'excerpt' => '120 anggota mengikuti perkemahan tiga hari di kaki Gunung Raung.', 'category' => 'Kegiatan', 'date' => '07 Agt 2026', 'image' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=700&q=80'],
                                        ['title' => 'Pelantikan Pandu Athfal Angkatan Baru', 'excerpt' => 'Pelantikan diikuti oleh 40 anggota baru jenjang Athfal.', 'category' => 'Organisasi', 'date' => '31 Jul 2026', 'image' => 'https://images.unsplash.com/photo-1589156229687-496a31ad1d1f?auto=format&fit=crop&w=700&q=80'],
                                        ['title' => 'Pelatihan Pertolongan Pertama Lapangan', 'excerpt' => 'Pembekalan keterampilan P3K dasar bagi pandu penghela.', 'category' => 'Pelatihan', 'date' => '22 Jul 2026', 'image' => 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&w=700&q=80'],
                                    ],
                                ]],
                                ['key' => 'cta', 'content' => [
                                    'title' => 'Bergabung dengan Hizbul Wathan',
                                    'subtitle' => 'Terbuka untuk anak dan remaja usia 7-21 tahun.',
                                    'cta_label' => 'Daftar Jadi Anggota',
                                ]],
                                ['key' => 'formulir-kontak', 'content' => [
                                    'title' => 'Hubungi Kami',
                                    'subtitle' => 'Tanya jadwal latihan dan pendaftaran.',
                                ]],
                                ['key' => 'footer'],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'organization_type_slug' => 'imm',
                'name' => 'Ikatan Mahasiswa Muhammadiyah',
                'slug' => 'imm',
                'description' => 'Template untuk IMM: menonjolkan citra gerakan intelektual mahasiswa, kaderisasi, dan ajakan bergabung. Warna draf — sesuaikan dengan pedoman identitas resmi.',
                'structure' => [
                    'sample_org_name' => 'IMM Komisariat Ambulu',
                    'brand' => ['primary' => '#1A1A1A', 'secondary' => '#7A1F2B'],
                    'pages' => [
                        [
                            'slug' => 'home',
                            'name' => 'Beranda',
                            'sections' => [
                                ['key' => 'header'],
                                ['key' => 'hero', 'content' => [
                                    'badge' => 'Ikatan Mahasiswa Muhammadiyah',
                                    'headline' => 'Anggun dalam Moral, Unggul dalam Intelektual',
                                    'subheadline' => 'IMM Komisariat Ambulu mewadahi mahasiswa dalam gerakan intelektual, dakwah kampus, dan advokasi sosial.',
                                    'cta_label' => 'Gabung IMM',
                                    'cta_secondary_label' => 'Lihat Kegiatan',
                                    'image' => 'https://images.unsplash.com/photo-1470004914212-05527e49370b?auto=format&fit=crop&w=1400&q=80',
                                ]],
                                ['key' => 'tentang-organisasi', 'content' => [
                                    'title' => 'Tentang IMM Komisariat Ambulu',
                                    'body' => 'IMM Komisariat Ambulu menjadi ruang bagi mahasiswa Muhammadiyah untuk berdiskusi, berorganisasi, dan mengembangkan gerakan intelektual serta advokasi sosial di lingkungan kampus.',
                                    'image' => 'https://images.unsplash.com/photo-1542816417-0983c9c9ad53?auto=format&fit=crop&w=1000&q=80',
                                ]],
                                ['key' => 'program-unggulan', 'content' => [
                                    'title' => 'Program Unggulan',
                                    'items' => [
                                        ['title' => 'Diskusi dan Kajian Ilmiah', 'description' => 'Forum rutin membahas isu sosial, politik, dan keagamaan.', 'icon' => '📚'],
                                        ['title' => 'Pelatihan Instruktur Kader', 'description' => 'Jenjang Darul Arqam untuk pembinaan kepemimpinan kader.', 'icon' => '🎯'],
                                        ['title' => 'Advokasi Mahasiswa', 'description' => 'Pendampingan isu kebijakan kampus dan masyarakat sekitar.', 'icon' => '📢'],
                                    ],
                                ]],
                                ['key' => 'daftar-berita', 'content' => [
                                    'title' => 'Publikasi Terbaru',
                                    'items' => [
                                        ['title' => 'Darul Arqam Dasar Angkatan XII Digelar', 'excerpt' => 'Pelatihan kader dasar diikuti 60 mahasiswa baru anggota IMM.', 'category' => 'Kaderisasi', 'date' => '06 Agt 2026', 'image' => 'https://images.unsplash.com/photo-1585036156171-384164a8c675?auto=format&fit=crop&w=700&q=80'],
                                        ['title' => 'Diskusi Publik Isu Kebijakan Kampus', 'excerpt' => 'IMM mengundang akademisi untuk membahas isu pendidikan tinggi.', 'category' => 'Advokasi', 'date' => '29 Jul 2026', 'image' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=700&q=80'],
                                        ['title' => 'Bakti Sosial Mahasiswa untuk Desa Binaan', 'excerpt' => 'Kegiatan pengabdian masyarakat di desa binaan IMM Ambulu.', 'category' => 'Sosial', 'date' => '18 Jul 2026', 'image' => 'https://images.unsplash.com/photo-1519817650390-64a93db51149?auto=format&fit=crop&w=700&q=80'],
                                    ],
                                ]],
                                ['key' => 'cta', 'content' => [
                                    'title' => 'Bergabung Bersama IMM',
                                    'subtitle' => 'Untuk mahasiswa yang ingin berdaya secara intelektual dan sosial.',
                                    'cta_label' => 'Daftar Jadi Kader',
                                ]],
                                ['key' => 'formulir-kontak', 'content' => [
                                    'title' => 'Hubungi Kami',
                                    'subtitle' => 'Ingin tahu lebih lanjut tentang kaderisasi IMM?',
                                ]],
                                ['key' => 'footer'],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'organization_type_slug' => 'aisyiyah',
                'name' => 'Aisyiyah',
                'slug' => 'aisyiyah',
                'description' => 'Template untuk Aisyiyah: profil organisasi, program pemberdayaan perempuan, berita, dan ajakan bergabung.',
                'structure' => [
                    'sample_org_name' => 'Pimpinan Cabang Aisyiyah Ambulu',
                    'brand' => ['primary' => '#2C368B', 'secondary' => '#079C4E'],
                    'pages' => [
                        [
                            'slug' => 'home',
                            'name' => 'Beranda',
                            'sections' => [
                                ['key' => 'header'],
                                ['key' => 'hero', 'content' => [
                                    'badge' => 'Organisasi Otonom Muhammadiyah — Perempuan Berkemajuan',
                                    'headline' => 'Perempuan Berkemajuan untuk Bangsa',
                                    'subheadline' => 'Pimpinan Cabang Aisyiyah Ambulu bergerak dalam dakwah, pendidikan, kesehatan, dan pemberdayaan perempuan di Kecamatan Ambulu.',
                                    'cta_label' => 'Tentang Kami',
                                    'cta_secondary_label' => 'Lihat Program',
                                    'image' => 'https://images.unsplash.com/photo-1573497019940-1c28c88b4f3e?auto=format&fit=crop&w=1400&q=80',
                                ]],
                                ['key' => 'sambutan-ketua', 'content' => [
                                    'nama' => 'Hj. Siti Nur Halimah, S.Ag.',
                                    'jabatan' => 'Ketua PCA Ambulu periode 2022–2027',
                                    'sambutan' => 'Assalamu\'alaikum warahmatullahi wabarakatuh. Selamat datang di rumah digital Aisyiyah Ambulu. Kami mengajak seluruh perempuan Muhammadiyah untuk bersama membangun keluarga sakinah dan masyarakat yang berkemajuan.',
                                    'photo' => 'https://randomuser.me/api/portraits/women/44.jpg',
                                ]],
                                ['key' => 'program-unggulan', 'content' => [
                                    'title' => 'Program Unggulan',
                                    'items' => [
                                        ['title' => 'Pengajian dan Dakwah', 'description' => 'Kajian rutin keputrian dan keluarga sakinah setiap pekan.', 'icon' => '🕌'],
                                        ['title' => 'Pendidikan Anak Usia Dini', 'description' => 'Pembinaan TK/PAUD Aisyiyah di wilayah Ambulu.', 'icon' => '🎓'],
                                        ['title' => 'Pemberdayaan Ekonomi Perempuan', 'description' => 'Pelatihan usaha mikro dan koperasi bagi anggota.', 'icon' => '🧵'],
                                    ],
                                ]],
                                ['key' => 'daftar-berita', 'content' => [
                                    'title' => 'Berita Terbaru',
                                    'items' => [
                                        ['title' => 'Pelantikan Pimpinan Cabang Aisyiyah Ambulu', 'excerpt' => 'Pelantikan pengurus baru periode 2022-2027 berlangsung khidmat.', 'category' => 'Organisasi', 'date' => '11 Agt 2026', 'image' => 'https://images.unsplash.com/photo-1573497019940-1c28c88b4f3e?auto=format&fit=crop&w=700&q=80'],
                                        ['title' => 'Pelatihan Usaha Mikro bagi Anggota', 'excerpt' => 'Puluhan anggota mengikuti pelatihan pengelolaan usaha rumahan.', 'category' => 'Pemberdayaan', 'date' => '04 Agt 2026', 'image' => 'https://images.unsplash.com/photo-1470004914212-05527e49370b?auto=format&fit=crop&w=700&q=80'],
                                        ['title' => 'Wisuda TK Aisyiyah Bustanul Athfal', 'excerpt' => '60 siswa TK Aisyiyah mengikuti prosesi wisuda tahun ajaran ini.', 'category' => 'Pendidikan', 'date' => '27 Jul 2026', 'image' => 'https://images.unsplash.com/photo-1509062522246-3755977927d7?auto=format&fit=crop&w=700&q=80'],
                                    ],
                                ]],
                                ['key' => 'cta', 'content' => [
                                    'title' => 'Bergabunglah dengan Aisyiyah',
                                    'subtitle' => 'Jadi bagian dari gerakan perempuan berkemajuan.',
                                    'cta_label' => 'Daftar Jadi Anggota',
                                ]],
                                ['key' => 'formulir-kontak', 'content' => [
                                    'title' => 'Hubungi Kami',
                                    'subtitle' => 'Ingin tahu lebih lanjut tentang kegiatan kami?',
                                ]],
                                ['key' => 'footer'],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'organization_type_slug' => 'aum-sosial',
                'name' => 'AUM Sosial',
                'slug' => 'aum-sosial',
                'description' => 'Template untuk panti asuhan dan layanan sosial Muhammadiyah: layanan, agenda, dan donasi.',
                'structure' => [
                    'sample_org_name' => 'Panti Asuhan Muhammadiyah Ambulu',
                    'brand' => ['primary' => '#2C368B', 'secondary' => '#079C4E'],
                    'pages' => [
                        [
                            'slug' => 'home',
                            'name' => 'Beranda',
                            'sections' => [
                                ['key' => 'header'],
                                ['key' => 'hero', 'content' => [
                                    'badge' => 'Amal Usaha Muhammadiyah',
                                    'headline' => 'Merawat dengan Kasih, Mendidik dengan Iman',
                                    'subheadline' => 'Panti Asuhan Muhammadiyah Ambulu memberi rumah, pendidikan, dan kasih sayang bagi anak yatim dan dhuafa.',
                                    'cta_label' => 'Salurkan Donasi',
                                    'image' => 'https://images.unsplash.com/photo-1488521787991-ed7bbaae773c?auto=format&fit=crop&w=1400&q=80',
                                ]],
                                ['key' => 'layanan', 'content' => [
                                    'title' => 'Layanan Kami',
                                    'items' => [
                                        ['title' => 'Pengasuhan Anak Yatim', 'description' => 'Tempat tinggal, gizi, dan pendampingan bagi anak yatim dan dhuafa.', 'icon' => '🏠'],
                                        ['title' => 'Pendidikan dan Bimbingan Belajar', 'description' => 'Dukungan pendidikan formal dan bimbingan belajar rutin.', 'icon' => '📖'],
                                        ['title' => 'Layanan Sosial Masyarakat', 'description' => 'Bantuan sembako dan santunan bagi warga kurang mampu.', 'icon' => '❤️'],
                                    ],
                                ]],
                                ['key' => 'agenda', 'content' => [
                                    'title' => 'Agenda Kegiatan',
                                    'items' => [
                                        ['title' => 'Santunan Anak Yatim Bulanan', 'date_day' => '15', 'date_month' => 'Agt', 'time' => '09.00 WIB', 'location' => 'Panti Asuhan Muhammadiyah Ambulu'],
                                        ['title' => 'Bimbingan Belajar Sore', 'date_day' => '19', 'date_month' => 'Agt', 'time' => '15.30 WIB', 'location' => 'Aula Panti Asuhan'],
                                        ['title' => 'Bakti Sosial Sembako', 'date_day' => '26', 'date_month' => 'Agt', 'time' => '08.00 WIB', 'location' => 'Desa Ambulu'],
                                    ],
                                ]],
                                ['key' => 'donasi-zakat-infak', 'content' => [
                                    'title' => 'Donasi untuk Anak Yatim dan Dhuafa',
                                    'body' => 'Dukung pengasuhan dan pendidikan anak-anak yatim melalui donasi, zakat, atau infak Anda.',
                                    'image' => 'https://images.unsplash.com/photo-1593113598332-cd288d649433?auto=format&fit=crop&w=600&q=80',
                                ]],
                                ['key' => 'lokasi-peta', 'content' => [
                                    'title' => 'Lokasi Panti Asuhan',
                                    'address' => 'Jl. Sosial No. 5, Ambulu, Kabupaten Jember, Jawa Timur',
                                ]],
                                ['key' => 'footer'],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'organization_type_slug' => 'masjidmushola',
                'name' => 'Masjid & Mushola',
                'slug' => 'masjid-mushola',
                'description' => 'Template untuk masjid, mushala, dan Islamic Center: jadwal salat, kajian, pengumuman, dan donasi.',
                'structure' => [
                    'sample_org_name' => 'Masjid Al-Ikhlas Ambulu',
                    'brand' => ['primary' => '#2C368B', 'secondary' => '#079C4E'],
                    'pages' => [
                        [
                            'slug' => 'home',
                            'name' => 'Beranda',
                            'sections' => [
                                ['key' => 'header'],
                                ['key' => 'hero', 'content' => [
                                    'badge' => 'Rumah Ibadah dan Dakwah Umat',
                                    'headline' => 'Memakmurkan Masjid, Menguatkan Ukhuwah',
                                    'subheadline' => 'Masjid Al-Ikhlas Ambulu menjadi pusat ibadah, kajian, dan pelayanan umat di Kecamatan Ambulu.',
                                    'cta_label' => 'Lihat Jadwal Kajian',
                                    'image' => 'https://images.unsplash.com/photo-1591741535018-d042766c62eb?auto=format&fit=crop&w=1400&q=80',
                                ]],
                                ['key' => 'jadwal-salat', 'content' => [
                                    'title' => 'Jadwal Salat Hari Ini',
                                    'location' => 'Ambulu, Kabupaten Jember',
                                    'times' => [
                                        ['label' => 'Subuh', 'time' => '04.15'],
                                        ['label' => 'Dzuhur', 'time' => '11.35'],
                                        ['label' => 'Ashar', 'time' => '14.55'],
                                        ['label' => 'Maghrib', 'time' => '17.42'],
                                        ['label' => 'Isya', 'time' => '18.55'],
                                    ],
                                ]],
                                ['key' => 'pengumuman', 'content' => [
                                    'title' => 'Pengumuman Penting',
                                    'items' => [
                                        ['title' => 'Renovasi Tempat Wudu Dimulai Pekan Depan', 'excerpt' => 'Mohon maaf atas ketidaknyamanan selama masa renovasi.', 'date' => '14 Agt 2026'],
                                        ['title' => 'Jadwal Imam dan Khatib Jumat Bulan Ini', 'excerpt' => 'Silakan cek papan pengumuman masjid untuk jadwal lengkap.', 'date' => '10 Agt 2026'],
                                    ],
                                ]],
                                ['key' => 'jadwal-kajian', 'content' => [
                                    'title' => 'Jadwal Kajian Rutin',
                                    'items' => [
                                        ['title' => 'Kajian Subuh', 'ustadz' => 'Ustadz H. Fauzi Rahman', 'day' => 'Setiap Ahad', 'time' => 'Ba\'da Subuh'],
                                        ['title' => 'Kajian Tafsir', 'ustadz' => 'Ustadz Drs. Abdul Karim', 'day' => 'Setiap Selasa', 'time' => 'Ba\'da Maghrib'],
                                        ['title' => 'Kajian Fikih Keluarga', 'ustadz' => 'Ustadzah Hj. Nur Aini', 'day' => 'Setiap Jumat', 'time' => 'Ba\'da Ashar'],
                                    ],
                                ]],
                                ['key' => 'donasi-zakat-infak', 'content' => [
                                    'title' => 'Donasi, Zakat, dan Infak',
                                    'body' => 'Salurkan zakat, infak, dan donasi pembangunan masjid melalui kotak amal atau transfer resmi.',
                                    'image' => 'https://images.unsplash.com/photo-1591604129939-f1efa4d9f7fa?auto=format&fit=crop&w=600&q=80',
                                ]],
                                ['key' => 'lokasi-peta', 'content' => [
                                    'title' => 'Lokasi Masjid',
                                    'address' => 'Jl. Kh. Ahmad Dahlan No. 3, Ambulu, Kabupaten Jember, Jawa Timur',
                                ]],
                                ['key' => 'footer'],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'organization_type_slug' => 'ipm',
                'name' => 'Ikatan Pelajar Muhammadiyah',
                'slug' => 'ipm',
                'description' => 'Template untuk IPM: menonjolkan citra organisasi pelajar, kreativitas, dan ajakan bergabung. Warna draf — sesuaikan dengan pedoman identitas resmi.',
                'structure' => [
                    'sample_org_name' => 'IPM Ambulu',
                    'brand' => ['primary' => '#2E7D32', 'secondary' => '#2C368B'],
                    'pages' => [
                        [
                            'slug' => 'home',
                            'name' => 'Beranda',
                            'sections' => [
                                ['key' => 'header'],
                                ['key' => 'hero', 'content' => [
                                    'badge' => 'Ikatan Pelajar Muhammadiyah',
                                    'headline' => 'Pelajar Berkemajuan, Kreatif, dan Berkarakter',
                                    'subheadline' => 'IPM Ambulu mewadahi kreativitas dan kepemimpinan pelajar Muhammadiyah di sekolah-sekolah Ambulu.',
                                    'cta_label' => 'Gabung IPM',
                                    'cta_secondary_label' => 'Lihat Kegiatan',
                                    'image' => 'https://images.unsplash.com/photo-1585036156171-384164a8c675?auto=format&fit=crop&w=1400&q=80',
                                ]],
                                ['key' => 'tentang-organisasi', 'content' => [
                                    'title' => 'Tentang IPM Ambulu',
                                    'body' => 'IPM Ambulu menjadi wadah kreativitas, literasi, dan kepemimpinan bagi pelajar Muhammadiyah di tingkat SMP dan SMA se-Kecamatan Ambulu.',
                                    'image' => 'https://images.unsplash.com/photo-1509062522246-3755977927d7?auto=format&fit=crop&w=1000&q=80',
                                ]],
                                ['key' => 'program-unggulan', 'content' => [
                                    'title' => 'Program Unggulan',
                                    'items' => [
                                        ['title' => 'Pelatihan Kepemimpinan Pelajar', 'description' => 'Pembinaan ranting IPM di sekolah-sekolah Muhammadiyah.', 'icon' => '🎓'],
                                        ['title' => 'Literasi dan Karya Tulis', 'description' => 'Komunitas menulis dan lomba karya tulis pelajar.', 'icon' => '✍️'],
                                        ['title' => 'Kegiatan Ekstrakurikuler dan Seni', 'description' => 'Wadah bakat seni, musik, dan kreativitas pelajar.', 'icon' => '🎨'],
                                    ],
                                ]],
                                ['key' => 'daftar-berita', 'content' => [
                                    'title' => 'Publikasi Terbaru',
                                    'items' => [
                                        ['title' => 'Pelantikan Pimpinan Ranting IPM SMP Muhammadiyah 2', 'excerpt' => 'Pelantikan pengurus ranting baru periode 2026-2027.', 'category' => 'Organisasi', 'date' => '10 Agt 2026', 'image' => 'https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?auto=format&fit=crop&w=700&q=80'],
                                        ['title' => 'Lomba Karya Tulis Pelajar Se-Ambulu', 'excerpt' => 'Diikuti oleh pelajar dari lima sekolah Muhammadiyah di Ambulu.', 'category' => 'Literasi', 'date' => '01 Agt 2026', 'image' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=700&q=80'],
                                        ['title' => 'Pentas Seni dan Kreativitas Pelajar', 'excerpt' => 'Ajang unjuk bakat musik, tari, dan teater pelajar Muhammadiyah.', 'category' => 'Kegiatan', 'date' => '24 Jul 2026', 'image' => 'https://images.unsplash.com/photo-1524178232363-1fb2b075b655?auto=format&fit=crop&w=700&q=80'],
                                    ],
                                ]],
                                ['key' => 'cta', 'content' => [
                                    'title' => 'Bergabung Bersama IPM',
                                    'subtitle' => 'Untuk pelajar SMP dan SMA yang ingin berkarya dan berorganisasi.',
                                    'cta_label' => 'Daftar Jadi Anggota',
                                ]],
                                ['key' => 'formulir-kontak', 'content' => [
                                    'title' => 'Hubungi Kami',
                                    'subtitle' => 'Ingin tahu lebih lanjut tentang kegiatan IPM?',
                                ]],
                                ['key' => 'footer'],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        foreach ($templates as $template) {
            $organizationType = OrganizationType::where('slug', $template['organization_type_slug'])->first();

            Template::updateOrCreate(
                ['slug' => $template['slug']],
                [
                    'organization_type_id' => $organizationType?->id,
                    'name' => $template['name'],
                    'description' => $template['description'],
                    'structure' => $template['structure'],
                    'is_active' => true,
                ],
            );
        }
    }
}
