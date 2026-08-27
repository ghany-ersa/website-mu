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
     * and audience.
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
                                ['key' => 'header', 'variant' => 'standar'],
                                ['key' => 'hero', 'variant' => 'standar', 'content' => [
                                    'badge' => 'Bagian dari Persyarikatan Muhammadiyah',
                                    'headline' => 'Mencerahkan Semesta, Membangun Generasi',
                                    'subheadline' => 'PCM Ambulu menggerakkan dakwah, pendidikan, dan pemberdayaan masyarakat di Kecamatan Ambulu.',
                                    'cta_label' => 'Tentang Kami',
                                    'cta_secondary_label' => 'Lihat Program',
                                    'image' => 'https://images.unsplash.com/photo-1542816417-0983c9c9ad53?auto=format&fit=crop&w=1400&q=80',
                                ]],
                                ['key' => 'sambutan-ketua', 'variant' => 'standar', 'content' => [
                                    'nama' => 'Drs. H. Ahmad Sujarwo, M.Pd.I',
                                    'jabatan' => 'Ketua PCM Ambulu periode 2022–2027',
                                    'sambutan' => 'Assalamu\'alaikum warahmatullahi wabarakatuh. Selamat datang di rumah digital PCM Ambulu. Melalui platform ini kami ingin menyapa warga Muhammadiyah dan masyarakat luas dengan informasi kegiatan dakwah, pendidikan, dan pelayanan sosial yang kami jalankan bersama.',
                                    'photo' => 'https://randomuser.me/api/portraits/men/32.jpg',
                                ]],
                                ['key' => 'program-unggulan', 'variant' => 'standar', 'content' => [
                                    'title' => 'Program Unggulan',
                                    'items' => [
                                        ['title' => 'Dakwah dan Pengajian', 'description' => 'Kajian rutin mingguan dan pengajian akbar di tingkat cabang dan ranting.', 'icon' => '🕌'],
                                        ['title' => 'Pendidikan dan Amal Usaha', 'description' => 'Pembinaan sekolah, madrasah, dan lembaga pendidikan Muhammadiyah di wilayah Ambulu.', 'icon' => '🎓'],
                                        ['title' => 'Pemberdayaan Masyarakat', 'description' => 'Program sosial, ekonomi, dan kesehatan untuk warga sekitar.', 'icon' => '🤝'],
                                    ],
                                ]],
                                ['key' => 'daftar-berita', 'variant' => 'standar', 'content' => [
                                    'title' => 'Berita Terbaru',
                                    'items' => [
                                        ['title' => 'Musycab PCM Ambulu Tetapkan Kepengurusan Baru', 'category' => 'Organisasi', 'date' => '12 Agt 2026', 'image' => 'https://images.unsplash.com/photo-1519817650390-64a93db51149?auto=format&fit=crop&w=700&q=80'],
                                        ['title' => 'Baksos Kesehatan Gratis untuk Warga Ambulu', 'category' => 'Sosial', 'date' => '05 Agt 2026', 'image' => 'https://images.unsplash.com/photo-1585036156171-384164a8c675?auto=format&fit=crop&w=700&q=80'],
                                        ['title' => 'Pelatihan Dai Muda Angkatan Ketiga Dibuka', 'category' => 'Dakwah', 'date' => '28 Jul 2026', 'image' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=700&q=80'],
                                    ],
                                ]],
                                ['key' => 'agenda', 'variant' => 'standar', 'content' => [
                                    'title' => 'Agenda Kegiatan',
                                    'items' => [
                                        ['title' => 'Pengajian Ahad Pagi', 'date_day' => '17', 'date_month' => 'Agt', 'time' => '06.30 WIB', 'location' => 'Masjid Al-Ikhlas Ambulu'],
                                        ['title' => 'Rapat Koordinasi Pimpinan Ranting', 'date_day' => '22', 'date_month' => 'Agt', 'time' => '19.30 WIB', 'location' => 'Kantor PCM Ambulu'],
                                        ['title' => 'Bakti Sosial Idul Adha Lanjutan', 'date_day' => '29', 'date_month' => 'Agt', 'time' => '08.00 WIB', 'location' => 'Balai Desa Ambulu'],
                                    ],
                                ]],
                                ['key' => 'cta', 'variant' => 'standar', 'content' => [
                                    'title' => 'Mari Bersilaturahmi',
                                    'subtitle' => 'Terhubung dengan kegiatan dan program PCM Ambulu.',
                                    'cta_label' => 'Hubungi Kami',
                                ]],
                                ['key' => 'footer', 'variant' => 'standar'],
                            ],
                        ],
                        [
                            'slug' => 'tentang',
                            'name' => 'Tentang',
                            'sections' => [
                                ['key' => 'header', 'variant' => 'standar'],
                                ['key' => 'tentang-organisasi', 'variant' => 'standar', 'content' => [
                                    'title' => 'Tentang PCM Ambulu',
                                    'body' => 'PCM Ambulu berdiri sejak 1978 dan menaungi ranting-ranting Muhammadiyah di Kecamatan Ambulu. Kami bergerak di bidang dakwah, pendidikan, kesehatan, dan pemberdayaan masyarakat bersama seluruh Amal Usaha Muhammadiyah di wilayah ini.',
                                    'image' => 'https://images.unsplash.com/photo-1470004914212-05527e49370b?auto=format&fit=crop&w=1000&q=80',
                                    'stats' => [
                                        ['value' => '12', 'label' => 'Ranting Aktif'],
                                        ['value' => '46', 'label' => 'Tahun Mengabdi'],
                                        ['value' => '8', 'label' => 'Amal Usaha'],
                                    ],
                                ]],
                                ['key' => 'struktur-pengurus', 'variant' => 'standar', 'content' => [
                                    'title' => 'Struktur Pengurus 2022–2027',
                                    'items' => [
                                        ['name' => 'Drs. H. Ahmad Sujarwo, M.Pd.I', 'role' => 'Ketua', 'photo' => 'https://randomuser.me/api/portraits/men/32.jpg'],
                                        ['name' => 'H. Bambang Riyadi, S.Ag.', 'role' => 'Wakil Ketua', 'photo' => 'https://randomuser.me/api/portraits/men/45.jpg'],
                                        ['name' => 'Siti Maryam, S.Pd.', 'role' => 'Sekretaris', 'photo' => 'https://randomuser.me/api/portraits/women/65.jpg'],
                                        ['name' => 'H. Slamet Widodo', 'role' => 'Bendahara', 'photo' => 'https://randomuser.me/api/portraits/men/78.jpg'],
                                    ],
                                ]],
                                ['key' => 'jaringan-aum-ortom', 'variant' => 'standar', 'content' => [
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
                                ['key' => 'footer', 'variant' => 'standar'],
                            ],
                        ],
                        [
                            'slug' => 'kontak',
                            'name' => 'Kontak',
                            'sections' => [
                                ['key' => 'header', 'variant' => 'standar'],
                                ['key' => 'formulir-kontak', 'variant' => 'standar', 'content' => [
                                    'title' => 'Hubungi Kami',
                                    'subtitle' => 'Punya pertanyaan atau ingin bersilaturahmi? Kirimkan pesan kepada kami.',
                                ]],
                                ['key' => 'lokasi-peta', 'variant' => 'standar', 'content' => [
                                    'title' => 'Kantor PCM Ambulu',
                                    'address' => 'Jl. Kh. Ahmad Dahlan No. 12, Ambulu, Kabupaten Jember, Jawa Timur',
                                ]],
                                ['key' => 'footer', 'variant' => 'standar'],
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
                                ['key' => 'header', 'variant' => 'standar'],
                                ['key' => 'hero', 'variant' => 'standar', 'content' => [
                                    'badge' => 'Terakreditasi A',
                                    'headline' => 'Mendidik dengan Berkemajuan',
                                    'subheadline' => 'SD Muhammadiyah 1 Ambulu membentuk generasi unggul yang berlandaskan iman dan ilmu.',
                                    'cta_label' => 'Daftar Sekarang',
                                    'cta_secondary_label' => 'Lihat Program',
                                    'image' => 'https://images.unsplash.com/photo-1585036156171-384164a8c675?auto=format&fit=crop&w=1400&q=80',
                                ]],
                                ['key' => 'tentang-organisasi', 'variant' => 'standar', 'content' => [
                                    'title' => 'Profil SD Muhammadiyah 1 Ambulu',
                                    'body' => 'Berdiri sejak 1990, SD Muhammadiyah 1 Ambulu mengintegrasikan kurikulum nasional dengan pendidikan Al-Islam dan Kemuhammadiyahan untuk membentuk siswa yang cerdas dan berakhlak mulia.',
                                    'image' => 'https://images.unsplash.com/photo-1509062522246-3755977927d7?auto=format&fit=crop&w=1000&q=80',
                                    'stats' => [
                                        ['value' => 'A', 'label' => 'Akreditasi'],
                                        ['value' => '540', 'label' => 'Siswa Aktif'],
                                        ['value' => '32', 'label' => 'Tenaga Pendidik'],
                                    ],
                                ]],
                                ['key' => 'program-unggulan', 'variant' => 'standar', 'content' => [
                                    'title' => 'Program Unggulan',
                                    'items' => [
                                        ['title' => 'Tahfidz Al-Quran', 'description' => 'Target hafalan 2 juz selama jenjang sekolah dasar.', 'icon' => '📖'],
                                        ['title' => 'Sains dan Teknologi', 'description' => 'Kelas coding dan robotika untuk kelas 4-6.', 'icon' => '🔬'],
                                        ['title' => 'Kepemimpinan Siswa', 'description' => 'Pembinaan organisasi siswa dan kegiatan kepanduan Hizbul Wathan.', 'icon' => '🧭'],
                                    ],
                                ]],
                                ['key' => 'ppdb', 'variant' => 'standar', 'content' => [
                                    'title' => 'Penerimaan Peserta Didik Baru',
                                    'body' => 'Pendaftaran tahun ajaran 2026/2027 dibuka untuk jalur reguler dan jalur prestasi.',
                                    'cta_label' => 'Info PPDB',
                                    'deadline' => 'Ditutup 30 Juni 2026',
                                ]],
                                ['key' => 'daftar-berita', 'variant' => 'standar', 'content' => [
                                    'title' => 'Berita Sekolah',
                                    'items' => [
                                        ['title' => 'Siswa Raih Juara 1 Olimpiade Sains Kabupaten', 'category' => 'Prestasi', 'date' => '10 Agt 2026', 'image' => 'https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?auto=format&fit=crop&w=700&q=80'],
                                        ['title' => 'Wisuda Tahfidz Angkatan ke-8', 'category' => 'Kegiatan', 'date' => '02 Agt 2026', 'image' => 'https://images.unsplash.com/photo-1524178232363-1fb2b075b655?auto=format&fit=crop&w=700&q=80'],
                                        ['title' => 'Kunjungan Edukasi ke Museum Jember', 'category' => 'Kegiatan', 'date' => '25 Jul 2026', 'image' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=700&q=80'],
                                    ],
                                ]],
                                ['key' => 'formulir-kontak', 'variant' => 'standar', 'content' => [
                                    'title' => 'Hubungi Kami',
                                    'subtitle' => 'Informasi pendaftaran dan kegiatan sekolah.',
                                ]],
                                ['key' => 'footer', 'variant' => 'standar'],
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
                                ['key' => 'header', 'variant' => 'standar'],
                                ['key' => 'hero', 'variant' => 'standar', 'content' => [
                                    'badge' => 'Amal Usaha Muhammadiyah',
                                    'headline' => 'Melayani dengan Ikhlas',
                                    'subheadline' => 'Klinik Muhammadiyah Sehati menyediakan layanan kesehatan dan sosial bagi masyarakat Ambulu.',
                                    'cta_label' => 'Buat Janji Temu',
                                    'image' => 'https://images.unsplash.com/photo-1531206715517-5c0ba140b2b8?auto=format&fit=crop&w=1400&q=80',
                                ]],
                                ['key' => 'layanan', 'variant' => 'standar', 'content' => [
                                    'title' => 'Layanan Kami',
                                    'items' => [
                                        ['title' => 'Layanan Umum', 'description' => 'Pemeriksaan dan konsultasi dokter umum setiap hari.', 'icon' => '🩺'],
                                        ['title' => 'Layanan Rujukan', 'description' => 'Rujukan cepat ke rumah sakit mitra untuk kasus lanjutan.', 'icon' => '🏥'],
                                        ['title' => 'Layanan Sosial', 'description' => 'Pemeriksaan gratis rutin bagi warga kurang mampu.', 'icon' => '❤️'],
                                    ],
                                ]],
                                ['key' => 'jadwal-praktik', 'variant' => 'standar', 'content' => [
                                    'title' => 'Jadwal Praktik Dokter',
                                    'doctors' => [
                                        ['name' => 'dr. Siti Rahma', 'specialty' => 'Dokter Umum', 'schedule' => 'Senin - Jumat, 08.00 - 14.00 WIB'],
                                        ['name' => 'dr. Ahmad Fauzi', 'specialty' => 'Dokter Gigi', 'schedule' => 'Selasa & Kamis, 09.00 - 12.00 WIB'],
                                        ['name' => 'dr. Nur Hidayah, Sp.A', 'specialty' => 'Dokter Anak', 'schedule' => 'Sabtu, 08.00 - 11.00 WIB'],
                                    ],
                                ]],
                                ['key' => 'agenda', 'variant' => 'standar', 'content' => [
                                    'title' => 'Agenda dan Kegiatan',
                                    'items' => [
                                        ['title' => 'Baksos Kesehatan Gratis', 'date_day' => '18', 'date_month' => 'Agt', 'time' => '08.00 WIB', 'location' => 'Balai Desa Ambulu'],
                                        ['title' => 'Donor Darah Bulanan', 'date_day' => '24', 'date_month' => 'Agt', 'time' => '09.00 WIB', 'location' => 'Klinik Muhammadiyah Sehati'],
                                        ['title' => 'Penyuluhan Gizi Anak', 'date_day' => '30', 'date_month' => 'Agt', 'time' => '10.00 WIB', 'location' => 'Posyandu Ambulu'],
                                    ],
                                ]],
                                ['key' => 'donasi-zakat-infak', 'variant' => 'standar', 'content' => [
                                    'title' => 'Donasi Layanan Kesehatan',
                                    'body' => 'Dukung layanan kesehatan gratis bagi warga kurang mampu melalui donasi.',
                                    'image' => 'https://images.unsplash.com/photo-1593113598332-cd288d649433?auto=format&fit=crop&w=600&q=80',
                                ]],
                                ['key' => 'lokasi-peta', 'variant' => 'standar', 'content' => [
                                    'title' => 'Lokasi Klinik',
                                    'address' => 'Jl. Kesehatan No. 8, Ambulu, Kabupaten Jember, Jawa Timur',
                                ]],
                                ['key' => 'footer', 'variant' => 'standar'],
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
                    'brand' => ['primary' => '#F4CE2A', 'secondary' => '#000000'],
                    'pages' => [
                        [
                            'slug' => 'home',
                            'name' => 'Beranda',
                            'sections' => [
                                ['key' => 'header', 'variant' => 'standar'],
                                ['key' => 'hero', 'variant' => 'standar', 'content' => [
                                    'badge' => 'Organisasi Otonom Muhammadiyah — Perempuan Muda',
                                    'headline' => 'Perempuan Muda, Berdaya dan Berkemajuan',
                                    'subheadline' => 'Nasyiatul Aisyiyah Ambulu menghimpun perempuan muda dalam dakwah, pendidikan, dan pemberdayaan sosial.',
                                    'cta_label' => 'Gabung Sekarang',
                                    'cta_secondary_label' => 'Lihat Program',
                                    'image' => 'https://images.unsplash.com/photo-1519817650390-64a93db51149?auto=format&fit=crop&w=1400&q=80',
                                ]],
                                ['key' => 'tentang-organisasi', 'variant' => 'standar', 'content' => [
                                    'title' => 'Tentang Nasyiatul Aisyiyah Ambulu',
                                    'body' => 'Nasyiatul Aisyiyah Ambulu menjadi wadah bagi perempuan muda untuk berdakwah, belajar, dan berkarya melalui program keputrian, pendidikan, dan pemberdayaan ekonomi.',
                                    'image' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=1000&q=80',
                                ]],
                                ['key' => 'program-unggulan', 'variant' => 'standar', 'content' => [
                                    'title' => 'Program Unggulan',
                                    'items' => [
                                        ['title' => 'Kajian Keputrian', 'description' => 'Kajian rutin seputar fikih perempuan dan pengembangan diri.', 'icon' => '📗'],
                                        ['title' => 'Pelatihan Kepemimpinan Perempuan', 'description' => 'Pembinaan kader perempuan muda menjadi pemimpin organisasi.', 'icon' => '🌸'],
                                        ['title' => 'Pemberdayaan Ekonomi Perempuan', 'description' => 'Pelatihan usaha mikro dan kewirausahaan bagi anggota.', 'icon' => '🧵'],
                                    ],
                                ]],
                                ['key' => 'daftar-berita', 'variant' => 'standar', 'content' => [
                                    'title' => 'Publikasi Terbaru',
                                    'items' => [
                                        ['title' => 'Pelantikan Pimpinan Cabang Periode 2026-2029', 'category' => 'Organisasi', 'date' => '09 Agt 2026', 'image' => 'https://images.unsplash.com/photo-1585036156171-384164a8c675?auto=format&fit=crop&w=700&q=80'],
                                        ['title' => 'Pelatihan Usaha Mikro untuk Anggota', 'category' => 'Pemberdayaan', 'date' => '02 Agt 2026', 'image' => 'https://images.unsplash.com/photo-1470004914212-05527e49370b?auto=format&fit=crop&w=700&q=80'],
                                        ['title' => 'Kajian Keputrian Bulanan Digelar', 'category' => 'Dakwah', 'date' => '25 Jul 2026', 'image' => 'https://images.unsplash.com/photo-1542816417-0983c9c9ad53?auto=format&fit=crop&w=700&q=80'],
                                    ],
                                ]],
                                ['key' => 'cta', 'variant' => 'standar', 'content' => [
                                    'title' => 'Bergabunglah dengan Nasyiatul Aisyiyah',
                                    'subtitle' => 'Jadi bagian dari gerakan perempuan muda berkemajuan.',
                                    'cta_label' => 'Daftar Jadi Anggota',
                                ]],
                                ['key' => 'formulir-kontak', 'variant' => 'standar', 'content' => [
                                    'title' => 'Hubungi Kami',
                                    'subtitle' => 'Ingin tahu lebih lanjut tentang kegiatan kami?',
                                ]],
                                ['key' => 'footer', 'variant' => 'standar'],
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
                    'brand' => ['primary' => '#E8242A', 'secondary' => '#079C4E'],
                    'pages' => [
                        [
                            'slug' => 'home',
                            'name' => 'Beranda',
                            'sections' => [
                                ['key' => 'header', 'variant' => 'standar'],
                                ['key' => 'hero', 'variant' => 'standar', 'content' => [
                                    'badge' => 'Organisasi Otonom Muhammadiyah',
                                    'headline' => 'Bergerak Bersama Pemuda Muhammadiyah',
                                    'subheadline' => 'Wadah kaderisasi dan kegiatan bagi pemuda di Kecamatan Ambulu.',
                                    'cta_label' => 'Gabung Sekarang',
                                    'cta_secondary_label' => 'Lihat Program',
                                    'image' => 'https://images.unsplash.com/photo-1519817650390-64a93db51149?auto=format&fit=crop&w=1400&q=80',
                                ]],
                                ['key' => 'tentang-organisasi', 'variant' => 'standar', 'content' => [
                                    'title' => 'Tentang Pemuda Muhammadiyah Ambulu',
                                    'body' => 'Pemuda Muhammadiyah Ambulu menjadi wadah kaderisasi, aksi sosial, dan pengembangan kepemimpinan bagi generasi muda Muhammadiyah di wilayah Ambulu.',
                                    'image' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=1000&q=80',
                                ]],
                                ['key' => 'program-unggulan', 'variant' => 'standar', 'content' => [
                                    'title' => 'Kegiatan dan Kampanye',
                                    'items' => [
                                        ['title' => 'Kaderisasi', 'description' => 'Pelatihan dasar kepemimpinan bagi anggota baru.', 'icon' => '🧑‍🤝‍🧑'],
                                        ['title' => 'Aksi Sosial', 'description' => 'Bakti sosial dan gerakan kepedulian lingkungan.', 'icon' => '🌱'],
                                        ['title' => 'Pelatihan Kewirausahaan', 'description' => 'Workshop bisnis dan pengembangan diri pemuda.', 'icon' => '🚀'],
                                    ],
                                ]],
                                ['key' => 'daftar-berita', 'variant' => 'standar', 'content' => [
                                    'title' => 'Publikasi Terbaru',
                                    'items' => [
                                        ['title' => 'Pelantikan Pengurus Baru Periode 2026-2029', 'category' => 'Organisasi', 'date' => '08 Agt 2026', 'image' => 'https://images.unsplash.com/photo-1585036156171-384164a8c675?auto=format&fit=crop&w=700&q=80'],
                                        ['title' => 'Aksi Bersih Sungai Bersama Warga', 'category' => 'Sosial', 'date' => '30 Jul 2026', 'image' => 'https://images.unsplash.com/photo-1470004914212-05527e49370b?auto=format&fit=crop&w=700&q=80'],
                                        ['title' => 'Pelatihan Kewirausahaan Pemuda', 'category' => 'Pelatihan', 'date' => '20 Jul 2026', 'image' => 'https://images.unsplash.com/photo-1542816417-0983c9c9ad53?auto=format&fit=crop&w=700&q=80'],
                                    ],
                                ]],
                                ['key' => 'cta', 'variant' => 'standar', 'content' => [
                                    'title' => 'Rekrutmen Anggota Baru',
                                    'subtitle' => 'Bergabunglah dan jadi bagian dari gerakan pemuda berkemajuan.',
                                    'cta_label' => 'Daftar Jadi Anggota',
                                ]],
                                ['key' => 'formulir-kontak', 'variant' => 'standar', 'content' => [
                                    'title' => 'Hubungi Kami',
                                    'subtitle' => 'Ingin tahu lebih lanjut tentang kegiatan kami?',
                                ]],
                                ['key' => 'footer', 'variant' => 'standar'],
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
                    'brand' => ['primary' => '#E8242A', 'secondary' => '#F4CE2A'],
                    'pages' => [
                        [
                            'slug' => 'home',
                            'name' => 'Beranda',
                            'sections' => [
                                ['key' => 'header', 'variant' => 'standar'],
                                ['key' => 'hero', 'variant' => 'standar', 'content' => [
                                    'badge' => 'Perguruan Seni Bela Diri Otentik Indonesia',
                                    'headline' => 'Kuat Fisik, Kokoh Akidah',
                                    'subheadline' => 'Tapak Suci Ambulu membina generasi pendekar yang tangguh, berprestasi, dan berakhlak mulia.',
                                    'cta_label' => 'Daftar Latihan',
                                    'cta_secondary_label' => 'Lihat Prestasi',
                                    'image' => 'https://images.unsplash.com/photo-1577896851231-70ef18881754?auto=format&fit=crop&w=1400&q=80',
                                ]],
                                ['key' => 'tentang-organisasi', 'variant' => 'standar', 'content' => [
                                    'title' => 'Tentang Tapak Suci Ambulu',
                                    'body' => 'Tapak Suci Putera Muhammadiyah Ambulu membina anggota dalam latihan pencak silat, pembentukan karakter, dan pencapaian prestasi di tingkat daerah maupun nasional.',
                                    'image' => 'https://images.unsplash.com/photo-1524178232363-1fb2b075b655?auto=format&fit=crop&w=1000&q=80',
                                ]],
                                ['key' => 'program-unggulan', 'variant' => 'standar', 'content' => [
                                    'title' => 'Program Unggulan',
                                    'items' => [
                                        ['title' => 'Latihan Rutin Pencak Silat', 'description' => 'Latihan fisik dan jurus dasar hingga lanjutan setiap pekan.', 'icon' => '🥋'],
                                        ['title' => 'Pembinaan Atlet Prestasi', 'description' => 'Persiapan anggota untuk kejuaraan tingkat daerah dan nasional.', 'icon' => '🏆'],
                                        ['title' => 'Ujian Kenaikan Tingkat', 'description' => 'Evaluasi berkala untuk kenaikan sabuk dan jenjang keanggotaan.', 'icon' => '🎖️'],
                                    ],
                                ]],
                                ['key' => 'daftar-berita', 'variant' => 'standar', 'content' => [
                                    'title' => 'Publikasi Terbaru',
                                    'items' => [
                                        ['title' => 'Atlet Tapak Suci Ambulu Raih Juara 1 Kejuaraan Daerah', 'category' => 'Prestasi', 'date' => '11 Agt 2026', 'image' => 'https://images.unsplash.com/photo-1560250097-0b93528c311a?auto=format&fit=crop&w=700&q=80'],
                                        ['title' => 'Ujian Kenaikan Sabuk Angkatan ke-14', 'category' => 'Kegiatan', 'date' => '03 Agt 2026', 'image' => 'https://images.unsplash.com/photo-1531206715517-5c0ba140b2b8?auto=format&fit=crop&w=700&q=80'],
                                        ['title' => 'Pembukaan Pendaftaran Anggota Baru', 'category' => 'Pengumuman', 'date' => '27 Jul 2026', 'image' => 'https://images.unsplash.com/photo-1593113598332-cd288d649433?auto=format&fit=crop&w=700&q=80'],
                                    ],
                                ]],
                                ['key' => 'cta', 'variant' => 'standar', 'content' => [
                                    'title' => 'Jadi Bagian dari Pendekar Tapak Suci',
                                    'subtitle' => 'Latihan terbuka untuk pelajar, mahasiswa, dan umum.',
                                    'cta_label' => 'Daftar Sekarang',
                                ]],
                                ['key' => 'formulir-kontak', 'variant' => 'standar', 'content' => [
                                    'title' => 'Hubungi Kami',
                                    'subtitle' => 'Tanya jadwal dan lokasi latihan.',
                                ]],
                                ['key' => 'footer', 'variant' => 'standar'],
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
                    'brand' => ['primary' => '#079C4E', 'secondary' => '#2C368B'],
                    'pages' => [
                        [
                            'slug' => 'home',
                            'name' => 'Beranda',
                            'sections' => [
                                ['key' => 'header', 'variant' => 'standar'],
                                ['key' => 'hero', 'variant' => 'standar', 'content' => [
                                    'badge' => 'Gerakan Kepanduan Muhammadiyah',
                                    'headline' => 'Berpetualang, Berkarakter, Berkemajuan',
                                    'subheadline' => 'Hizbul Wathan Ambulu membina generasi muda lewat kegiatan kepanduan yang mendidik dan menyenangkan.',
                                    'cta_label' => 'Ikut Perkemahan',
                                    'cta_secondary_label' => 'Lihat Kegiatan',
                                    'image' => 'https://images.unsplash.com/photo-1509062522246-3755977927d7?auto=format&fit=crop&w=1400&q=80',
                                ]],
                                ['key' => 'tentang-organisasi', 'variant' => 'standar', 'content' => [
                                    'title' => 'Tentang Hizbul Wathan Ambulu',
                                    'body' => 'Hizbul Wathan Qabilah Ambulu membina anak dan remaja melalui kegiatan kepanduan yang menumbuhkan kemandirian, kepemimpinan, dan kecintaan pada alam.',
                                    'image' => 'https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?auto=format&fit=crop&w=1000&q=80',
                                ]],
                                ['key' => 'program-unggulan', 'variant' => 'standar', 'content' => [
                                    'title' => 'Program Unggulan',
                                    'items' => [
                                        ['title' => 'Perkemahan dan Penjelajahan', 'description' => 'Kegiatan alam terbuka untuk melatih kemandirian dan kerja sama.', 'icon' => '⛺'],
                                        ['title' => 'Pelatihan Kepemimpinan Pandu', 'description' => 'Pembinaan jenjang Athfal, Pengenal, hingga Penghela.', 'icon' => '🧭'],
                                        ['title' => 'Pendidikan Karakter', 'description' => 'Penanaman nilai kedisiplinan dan kemandirian sejak dini.', 'icon' => '🌟'],
                                    ],
                                ]],
                                ['key' => 'daftar-berita', 'variant' => 'standar', 'content' => [
                                    'title' => 'Publikasi Terbaru',
                                    'items' => [
                                        ['title' => 'Perkemahan Akbar Qabilah Ambulu', 'category' => 'Kegiatan', 'date' => '07 Agt 2026', 'image' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=700&q=80'],
                                        ['title' => 'Pelantikan Pandu Athfal Angkatan Baru', 'category' => 'Organisasi', 'date' => '31 Jul 2026', 'image' => 'https://images.unsplash.com/photo-1589156229687-496a31ad1d1f?auto=format&fit=crop&w=700&q=80'],
                                        ['title' => 'Pelatihan Pertolongan Pertama Lapangan', 'category' => 'Pelatihan', 'date' => '22 Jul 2026', 'image' => 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&w=700&q=80'],
                                    ],
                                ]],
                                ['key' => 'cta', 'variant' => 'standar', 'content' => [
                                    'title' => 'Bergabung dengan Hizbul Wathan',
                                    'subtitle' => 'Terbuka untuk anak dan remaja usia 7-21 tahun.',
                                    'cta_label' => 'Daftar Jadi Anggota',
                                ]],
                                ['key' => 'formulir-kontak', 'variant' => 'standar', 'content' => [
                                    'title' => 'Hubungi Kami',
                                    'subtitle' => 'Tanya jadwal latihan dan pendaftaran.',
                                ]],
                                ['key' => 'footer', 'variant' => 'standar'],
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
                    'brand' => ['primary' => '#E8242A', 'secondary' => '#000000'],
                    'pages' => [
                        [
                            'slug' => 'home',
                            'name' => 'Beranda',
                            'sections' => [
                                ['key' => 'header', 'variant' => 'standar'],
                                ['key' => 'hero', 'variant' => 'standar', 'content' => [
                                    'badge' => 'Ikatan Mahasiswa Muhammadiyah',
                                    'headline' => 'Anggun dalam Moral, Unggul dalam Intelektual',
                                    'subheadline' => 'IMM Komisariat Ambulu mewadahi mahasiswa dalam gerakan intelektual, dakwah kampus, dan advokasi sosial.',
                                    'cta_label' => 'Gabung IMM',
                                    'cta_secondary_label' => 'Lihat Kegiatan',
                                    'image' => 'https://images.unsplash.com/photo-1470004914212-05527e49370b?auto=format&fit=crop&w=1400&q=80',
                                ]],
                                ['key' => 'tentang-organisasi', 'variant' => 'standar', 'content' => [
                                    'title' => 'Tentang IMM Komisariat Ambulu',
                                    'body' => 'IMM Komisariat Ambulu menjadi ruang bagi mahasiswa Muhammadiyah untuk berdiskusi, berorganisasi, dan mengembangkan gerakan intelektual serta advokasi sosial di lingkungan kampus.',
                                    'image' => 'https://images.unsplash.com/photo-1542816417-0983c9c9ad53?auto=format&fit=crop&w=1000&q=80',
                                ]],
                                ['key' => 'program-unggulan', 'variant' => 'standar', 'content' => [
                                    'title' => 'Program Unggulan',
                                    'items' => [
                                        ['title' => 'Diskusi dan Kajian Ilmiah', 'description' => 'Forum rutin membahas isu sosial, politik, dan keagamaan.', 'icon' => '📚'],
                                        ['title' => 'Pelatihan Instruktur Kader', 'description' => 'Jenjang Darul Arqam untuk pembinaan kepemimpinan kader.', 'icon' => '🎯'],
                                        ['title' => 'Advokasi Mahasiswa', 'description' => 'Pendampingan isu kebijakan kampus dan masyarakat sekitar.', 'icon' => '📢'],
                                    ],
                                ]],
                                ['key' => 'daftar-berita', 'variant' => 'standar', 'content' => [
                                    'title' => 'Publikasi Terbaru',
                                    'items' => [
                                        ['title' => 'Darul Arqam Dasar Angkatan XII Digelar', 'category' => 'Kaderisasi', 'date' => '06 Agt 2026', 'image' => 'https://images.unsplash.com/photo-1585036156171-384164a8c675?auto=format&fit=crop&w=700&q=80'],
                                        ['title' => 'Diskusi Publik Isu Kebijakan Kampus', 'category' => 'Advokasi', 'date' => '29 Jul 2026', 'image' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=700&q=80'],
                                        ['title' => 'Bakti Sosial Mahasiswa untuk Desa Binaan', 'category' => 'Sosial', 'date' => '18 Jul 2026', 'image' => 'https://images.unsplash.com/photo-1519817650390-64a93db51149?auto=format&fit=crop&w=700&q=80'],
                                    ],
                                ]],
                                ['key' => 'cta', 'variant' => 'standar', 'content' => [
                                    'title' => 'Bergabung Bersama IMM',
                                    'subtitle' => 'Untuk mahasiswa yang ingin berdaya secara intelektual dan sosial.',
                                    'cta_label' => 'Daftar Jadi Kader',
                                ]],
                                ['key' => 'formulir-kontak', 'variant' => 'standar', 'content' => [
                                    'title' => 'Hubungi Kami',
                                    'subtitle' => 'Ingin tahu lebih lanjut tentang kaderisasi IMM?',
                                ]],
                                ['key' => 'footer', 'variant' => 'standar'],
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
                    'brand' => ['primary' => '#F4CE2A', 'secondary' => '#079C4E'],
                    'pages' => [
                        [
                            'slug' => 'home',
                            'name' => 'Beranda',
                            'sections' => [
                                ['key' => 'header', 'variant' => 'standar'],
                                ['key' => 'hero', 'variant' => 'standar', 'content' => [
                                    'badge' => 'Organisasi Otonom Muhammadiyah — Perempuan Berkemajuan',
                                    'headline' => 'Perempuan Berkemajuan untuk Bangsa',
                                    'subheadline' => 'Pimpinan Cabang Aisyiyah Ambulu bergerak dalam dakwah, pendidikan, kesehatan, dan pemberdayaan perempuan di Kecamatan Ambulu.',
                                    'cta_label' => 'Tentang Kami',
                                    'cta_secondary_label' => 'Lihat Program',
                                    'image' => 'https://images.unsplash.com/photo-1573497019940-1c28c88b4f3e?auto=format&fit=crop&w=1400&q=80',
                                ]],
                                ['key' => 'sambutan-ketua', 'variant' => 'standar', 'content' => [
                                    'nama' => 'Hj. Siti Nur Halimah, S.Ag.',
                                    'jabatan' => 'Ketua PCA Ambulu periode 2022–2027',
                                    'sambutan' => 'Assalamu\'alaikum warahmatullahi wabarakatuh. Selamat datang di rumah digital Aisyiyah Ambulu. Kami mengajak seluruh perempuan Muhammadiyah untuk bersama membangun keluarga sakinah dan masyarakat yang berkemajuan.',
                                    'photo' => 'https://randomuser.me/api/portraits/women/44.jpg',
                                ]],
                                ['key' => 'program-unggulan', 'variant' => 'standar', 'content' => [
                                    'title' => 'Program Unggulan',
                                    'items' => [
                                        ['title' => 'Pengajian dan Dakwah', 'description' => 'Kajian rutin keputrian dan keluarga sakinah setiap pekan.', 'icon' => '🕌'],
                                        ['title' => 'Pendidikan Anak Usia Dini', 'description' => 'Pembinaan TK/PAUD Aisyiyah di wilayah Ambulu.', 'icon' => '🎓'],
                                        ['title' => 'Pemberdayaan Ekonomi Perempuan', 'description' => 'Pelatihan usaha mikro dan koperasi bagi anggota.', 'icon' => '🧵'],
                                    ],
                                ]],
                                ['key' => 'daftar-berita', 'variant' => 'standar', 'content' => [
                                    'title' => 'Berita Terbaru',
                                    'items' => [
                                        ['title' => 'Pelantikan Pimpinan Cabang Aisyiyah Ambulu', 'category' => 'Organisasi', 'date' => '11 Agt 2026', 'image' => 'https://images.unsplash.com/photo-1573497019940-1c28c88b4f3e?auto=format&fit=crop&w=700&q=80'],
                                        ['title' => 'Pelatihan Usaha Mikro bagi Anggota', 'category' => 'Pemberdayaan', 'date' => '04 Agt 2026', 'image' => 'https://images.unsplash.com/photo-1470004914212-05527e49370b?auto=format&fit=crop&w=700&q=80'],
                                        ['title' => 'Wisuda TK Aisyiyah Bustanul Athfal', 'category' => 'Pendidikan', 'date' => '27 Jul 2026', 'image' => 'https://images.unsplash.com/photo-1509062522246-3755977927d7?auto=format&fit=crop&w=700&q=80'],
                                    ],
                                ]],
                                ['key' => 'cta', 'variant' => 'standar', 'content' => [
                                    'title' => 'Bergabunglah dengan Aisyiyah',
                                    'subtitle' => 'Jadi bagian dari gerakan perempuan berkemajuan.',
                                    'cta_label' => 'Daftar Jadi Anggota',
                                ]],
                                ['key' => 'formulir-kontak', 'variant' => 'standar', 'content' => [
                                    'title' => 'Hubungi Kami',
                                    'subtitle' => 'Ingin tahu lebih lanjut tentang kegiatan kami?',
                                ]],
                                ['key' => 'footer', 'variant' => 'standar'],
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
                                ['key' => 'header', 'variant' => 'standar'],
                                ['key' => 'hero', 'variant' => 'standar', 'content' => [
                                    'badge' => 'Amal Usaha Muhammadiyah',
                                    'headline' => 'Merawat dengan Kasih, Mendidik dengan Iman',
                                    'subheadline' => 'Panti Asuhan Muhammadiyah Ambulu memberi rumah, pendidikan, dan kasih sayang bagi anak yatim dan dhuafa.',
                                    'cta_label' => 'Salurkan Donasi',
                                    'image' => 'https://images.unsplash.com/photo-1488521787991-ed7bbaae773c?auto=format&fit=crop&w=1400&q=80',
                                ]],
                                ['key' => 'layanan', 'variant' => 'standar', 'content' => [
                                    'title' => 'Layanan Kami',
                                    'items' => [
                                        ['title' => 'Pengasuhan Anak Yatim', 'description' => 'Tempat tinggal, gizi, dan pendampingan bagi anak yatim dan dhuafa.', 'icon' => '🏠'],
                                        ['title' => 'Pendidikan dan Bimbingan Belajar', 'description' => 'Dukungan pendidikan formal dan bimbingan belajar rutin.', 'icon' => '📖'],
                                        ['title' => 'Layanan Sosial Masyarakat', 'description' => 'Bantuan sembako dan santunan bagi warga kurang mampu.', 'icon' => '❤️'],
                                    ],
                                ]],
                                ['key' => 'agenda', 'variant' => 'standar', 'content' => [
                                    'title' => 'Agenda Kegiatan',
                                    'items' => [
                                        ['title' => 'Santunan Anak Yatim Bulanan', 'date_day' => '15', 'date_month' => 'Agt', 'time' => '09.00 WIB', 'location' => 'Panti Asuhan Muhammadiyah Ambulu'],
                                        ['title' => 'Bimbingan Belajar Sore', 'date_day' => '19', 'date_month' => 'Agt', 'time' => '15.30 WIB', 'location' => 'Aula Panti Asuhan'],
                                        ['title' => 'Bakti Sosial Sembako', 'date_day' => '26', 'date_month' => 'Agt', 'time' => '08.00 WIB', 'location' => 'Desa Ambulu'],
                                    ],
                                ]],
                                ['key' => 'donasi-zakat-infak', 'variant' => 'standar', 'content' => [
                                    'title' => 'Donasi untuk Anak Yatim dan Dhuafa',
                                    'body' => 'Dukung pengasuhan dan pendidikan anak-anak yatim melalui donasi, zakat, atau infak Anda.',
                                    'image' => 'https://images.unsplash.com/photo-1593113598332-cd288d649433?auto=format&fit=crop&w=600&q=80',
                                ]],
                                ['key' => 'lokasi-peta', 'variant' => 'standar', 'content' => [
                                    'title' => 'Lokasi Panti Asuhan',
                                    'address' => 'Jl. Sosial No. 5, Ambulu, Kabupaten Jember, Jawa Timur',
                                ]],
                                ['key' => 'footer', 'variant' => 'standar'],
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
                                ['key' => 'header', 'variant' => 'standar'],
                                ['key' => 'hero', 'variant' => 'standar', 'content' => [
                                    'badge' => 'Rumah Ibadah dan Dakwah Umat',
                                    'headline' => 'Memakmurkan Masjid, Menguatkan Ukhuwah',
                                    'subheadline' => 'Masjid Al-Ikhlas Ambulu menjadi pusat ibadah, kajian, dan pelayanan umat di Kecamatan Ambulu.',
                                    'cta_label' => 'Lihat Jadwal Kajian',
                                    'image' => 'https://images.unsplash.com/photo-1591741535018-d042766c62eb?auto=format&fit=crop&w=1400&q=80',
                                ]],
                                ['key' => 'jadwal-salat', 'variant' => 'standar', 'content' => [
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
                                ['key' => 'pengumuman', 'variant' => 'standar', 'content' => [
                                    'title' => 'Pengumuman Penting',
                                    'items' => [
                                        ['title' => 'Renovasi Tempat Wudu Dimulai Pekan Depan', 'date' => '14 Agt 2026'],
                                        ['title' => 'Jadwal Imam dan Khatib Jumat Bulan Ini', 'date' => '10 Agt 2026'],
                                    ],
                                ]],
                                ['key' => 'jadwal-kajian', 'variant' => 'standar', 'content' => [
                                    'title' => 'Jadwal Kajian Rutin',
                                    'items' => [
                                        ['title' => 'Kajian Subuh', 'ustadz' => 'Ustadz H. Fauzi Rahman', 'day' => 'Setiap Ahad', 'time' => 'Ba\'da Subuh'],
                                        ['title' => 'Kajian Tafsir', 'ustadz' => 'Ustadz Drs. Abdul Karim', 'day' => 'Setiap Selasa', 'time' => 'Ba\'da Maghrib'],
                                        ['title' => 'Kajian Fikih Keluarga', 'ustadz' => 'Ustadzah Hj. Nur Aini', 'day' => 'Setiap Jumat', 'time' => 'Ba\'da Ashar'],
                                    ],
                                ]],
                                ['key' => 'donasi-zakat-infak', 'variant' => 'standar', 'content' => [
                                    'title' => 'Donasi, Zakat, dan Infak',
                                    'body' => 'Salurkan zakat, infak, dan donasi pembangunan masjid melalui kotak amal atau transfer resmi.',
                                    'image' => 'https://images.unsplash.com/photo-1591604129939-f1efa4d9f7fa?auto=format&fit=crop&w=600&q=80',
                                ]],
                                ['key' => 'lokasi-peta', 'variant' => 'standar', 'content' => [
                                    'title' => 'Lokasi Masjid',
                                    'address' => 'Jl. Kh. Ahmad Dahlan No. 3, Ambulu, Kabupaten Jember, Jawa Timur',
                                ]],
                                ['key' => 'footer', 'variant' => 'standar'],
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
                    'brand' => ['primary' => '#F4CE2A', 'secondary' => '#EE942E'],
                    'pages' => [
                        [
                            'slug' => 'home',
                            'name' => 'Beranda',
                            'sections' => [
                                ['key' => 'header', 'variant' => 'standar'],
                                ['key' => 'hero', 'variant' => 'standar', 'content' => [
                                    'badge' => 'Ikatan Pelajar Muhammadiyah',
                                    'headline' => 'Pelajar Berkemajuan, Kreatif, dan Berkarakter',
                                    'subheadline' => 'IPM Ambulu mewadahi kreativitas dan kepemimpinan pelajar Muhammadiyah di sekolah-sekolah Ambulu.',
                                    'cta_label' => 'Gabung IPM',
                                    'cta_secondary_label' => 'Lihat Kegiatan',
                                    'image' => 'https://images.unsplash.com/photo-1585036156171-384164a8c675?auto=format&fit=crop&w=1400&q=80',
                                ]],
                                ['key' => 'tentang-organisasi', 'variant' => 'standar', 'content' => [
                                    'title' => 'Tentang IPM Ambulu',
                                    'body' => 'IPM Ambulu menjadi wadah kreativitas, literasi, dan kepemimpinan bagi pelajar Muhammadiyah di tingkat SMP dan SMA se-Kecamatan Ambulu.',
                                    'image' => 'https://images.unsplash.com/photo-1509062522246-3755977927d7?auto=format&fit=crop&w=1000&q=80',
                                ]],
                                ['key' => 'program-unggulan', 'variant' => 'standar', 'content' => [
                                    'title' => 'Program Unggulan',
                                    'items' => [
                                        ['title' => 'Pelatihan Kepemimpinan Pelajar', 'description' => 'Pembinaan ranting IPM di sekolah-sekolah Muhammadiyah.', 'icon' => '🎓'],
                                        ['title' => 'Literasi dan Karya Tulis', 'description' => 'Komunitas menulis dan lomba karya tulis pelajar.', 'icon' => '✍️'],
                                        ['title' => 'Kegiatan Ekstrakurikuler dan Seni', 'description' => 'Wadah bakat seni, musik, dan kreativitas pelajar.', 'icon' => '🎨'],
                                    ],
                                ]],
                                ['key' => 'daftar-berita', 'variant' => 'standar', 'content' => [
                                    'title' => 'Publikasi Terbaru',
                                    'items' => [
                                        ['title' => 'Pelantikan Pimpinan Ranting IPM SMP Muhammadiyah 2', 'category' => 'Organisasi', 'date' => '10 Agt 2026', 'image' => 'https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?auto=format&fit=crop&w=700&q=80'],
                                        ['title' => 'Lomba Karya Tulis Pelajar Se-Ambulu', 'category' => 'Literasi', 'date' => '01 Agt 2026', 'image' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=700&q=80'],
                                        ['title' => 'Pentas Seni dan Kreativitas Pelajar', 'category' => 'Kegiatan', 'date' => '24 Jul 2026', 'image' => 'https://images.unsplash.com/photo-1524178232363-1fb2b075b655?auto=format&fit=crop&w=700&q=80'],
                                    ],
                                ]],
                                ['key' => 'cta', 'variant' => 'standar', 'content' => [
                                    'title' => 'Bergabung Bersama IPM',
                                    'subtitle' => 'Untuk pelajar SMP dan SMA yang ingin berkarya dan berorganisasi.',
                                    'cta_label' => 'Daftar Jadi Anggota',
                                ]],
                                ['key' => 'formulir-kontak', 'variant' => 'standar', 'content' => [
                                    'title' => 'Hubungi Kami',
                                    'subtitle' => 'Ingin tahu lebih lanjut tentang kegiatan IPM?',
                                ]],
                                ['key' => 'footer', 'variant' => 'standar'],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'organization_type_slug' => 'muhammadiyah',
                'name' => 'Portal Berita',
                'slug' => 'portal-berita',
                'description' => 'Template untuk organisasi yang fokus menerbitkan berita dan artikel: headline utama, kategori berita, dan ajakan mengikuti kabar terbaru.',
                'structure' => [
                    'sample_org_name' => 'Warta Muhammadiyah Jember',
                    'brand' => ['primary' => '#2C368B', 'secondary' => '#079C4E'],
                    'pages' => [
                        [
                            'slug' => 'home',
                            'name' => 'Beranda',
                            'sections' => [
                                ['key' => 'header', 'variant' => 'standar'],
                                ['key' => 'hero', 'variant' => 'headline-berita', 'content' => [
                                    'badge' => 'Headline',
                                    'headline' => 'Musyda ke-XII Tetapkan Arah Gerak Muhammadiyah Jember 2022-2027',
                                    'subheadline' => 'Musyawarah Daerah menghasilkan pokok pikiran strategis dan susunan Pimpinan Daerah untuk lima tahun mendatang, dengan penekanan pada penguatan dakwah digital dan jaringan Amal Usaha.',
                                    'cta_label' => 'Baca Selengkapnya',
                                    'cta_type' => 'scroll',
                                    'cta_section' => 'daftar-berita',
                                    'image' => 'https://images.unsplash.com/photo-1591123120675-6f7f1aae0e5b?auto=format&fit=crop&w=1400&q=80',
                                ]],
                                // category_filter keeps this disjoint from the 'opini' section below once
                                // real posts exist — an organization's post authors need to type "Berita"
                                // (not "Opini") into a post's Kategori field for it to show up here.
                                ['key' => 'daftar-berita', 'variant' => 'ringkas', 'content' => [
                                    'title' => 'Kabar Persyarikatan',
                                    'category_filter' => 'Berita',
                                    'items' => [
                                        ['title' => 'Rakor Lazismu dan MDMC Perkuat Kesiapsiagaan Bencana', 'category' => 'Kemanusiaan', 'date' => '14 Agt 2026', 'image' => 'https://images.unsplash.com/photo-1593113598332-cd288d649433?auto=format&fit=crop&w=700&q=80'],
                                        ['title' => 'RS Muhammadiyah Resmikan Gedung Layanan Terpadu', 'category' => 'Amal Usaha', 'date' => '10 Agt 2026', 'image' => 'https://images.unsplash.com/photo-1519494026892-80bbd2d6fd0d?auto=format&fit=crop&w=700&q=80'],
                                        ['title' => 'Pelatihan Dai Muda Angkatan Ketiga Dibuka', 'category' => 'Dakwah', 'date' => '05 Agt 2026', 'image' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=700&q=80'],
                                        ['title' => 'Pelantikan Pimpinan Cabang Digelar Serentak', 'category' => 'Organisasi', 'date' => '28 Jul 2026', 'image' => 'https://images.unsplash.com/photo-1560472354-b33ff0c44a43?auto=format&fit=crop&w=700&q=80'],
                                        ['title' => 'Baksos Kesehatan Gratis untuk Warga Ambulu', 'category' => 'Sosial', 'date' => '12 Agt 2026', 'image' => 'https://images.unsplash.com/photo-1585036156171-384164a8c675?auto=format&fit=crop&w=700&q=80'],
                                        ['title' => 'Silaturahmi Akbar Pimpinan Ranting se-Kabupaten', 'category' => 'Organisasi', 'date' => '29 Jul 2026', 'image' => 'https://images.unsplash.com/photo-1519452575417-564c1401ecc0?auto=format&fit=crop&w=700&q=80'],
                                    ],
                                ]],
                                // category_filter keeps this disjoint from the 'ringkas' section above —
                                // an organization's post authors need to type "Opini" into a post's
                                // Kategori field for it to show up here instead of Kabar Persyarikatan.
                                ['key' => 'daftar-berita', 'variant' => 'mozaik', 'content' => [
                                    'title' => 'Opini',
                                    'category_filter' => 'Opini',
                                    'items' => [
                                        ['title' => 'Dakwah Digital: Tantangan dan Peluang bagi Generasi Muda Muhammadiyah', 'category' => 'Opini', 'date' => '13 Agt 2026', 'image' => 'https://images.unsplash.com/photo-1531482615713-2afd69097998?auto=format&fit=crop&w=700&q=80'],
                                        ['title' => 'Menjaga Semangat Berkemajuan di Tengah Arus Perubahan Zaman', 'category' => 'Opini', 'date' => '08 Agt 2026', 'image' => 'https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?auto=format&fit=crop&w=700&q=80'],
                                        ['title' => 'Ekonomi Umat Berbasis Ranting: Belajar dari Praktik Baik Persyarikatan', 'category' => 'Opini', 'date' => '02 Agt 2026', 'image' => 'https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?auto=format&fit=crop&w=700&q=80'],
                                    ],
                                ]],
                                ['key' => 'formulir-kontak', 'variant' => 'standar', 'content' => [
                                    'title' => 'Kirim Informasi atau Tips Berita',
                                    'subtitle' => 'Punya informasi kegiatan yang ingin diliput? Kirimkan kepada redaksi kami.',
                                ]],
                                ['key' => 'footer', 'variant' => 'standar'],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $templates = [...$templates, ...$this->exclusiveTemplates()];

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
                    'is_exclusive' => $template['is_exclusive'] ?? false,
                ],
            );
        }
    }

    /**
     * Exclusive templates, gated behind `Plan::has_exclusive_templates` (see
     * Organization::canUseExclusiveTemplates() and prd-status.md §2.5/§2.8). These reuse the
     * exact same section partials/fields as the standard templates above — no new component
     * capability — but combine a richer section lineup, a distinct brand pairing (serif `Lora`
     * + `sharp` radius, instead of the platform default `Plus Jakarta Sans` + `soft`), and more
     * elaborate sample copy so they read as a premium tier rather than a reskin.
     *
     * Deliberately single-page (`pages` has exactly one entry): Organization::seedPagesFromTemplate()
     * only ever clones `structure['pages'][0]` into a new tenant — a template-preview-only second
     * or third page (as some standard templates above have) would render in the preview tool but
     * never reach a real organization, which would be a misleading structure for the flagship
     * exclusive tier to model. "Tentang" content (struktur-pengurus, jaringan-aum-ortom) is a
     * section further down this same page rather than a separate page for that reason.
     *
     * @return array<int, array<string, mixed>>
     */
    private function exclusiveTemplates(): array
    {
        return [
            [
                'organization_type_slug' => 'muhammadiyah',
                'name' => 'Muhammadiyah Eksekutif',
                'slug' => 'muhammadiyah-eksekutif',
                'description' => 'Template eksklusif untuk PDM/PCM/PRM: identitas serif yang berwibawa, narasi kepemimpinan yang lebih dalam, dan struktur section lengkap untuk organisasi dengan jaringan AUM/Ortom yang besar. Khusus paket dengan entitlement template eksklusif.',
                'is_exclusive' => true,
                'structure' => [
                    'sample_org_name' => 'PDM Kabupaten Jember',
                    'brand' => [
                        'primary' => '#1E2761',
                        'secondary' => '#0B7A3E',
                        'font' => 'Lora',
                        'radius' => 'sharp',
                    ],
                    'pages' => [
                        [
                            'slug' => 'home',
                            'name' => 'Beranda',
                            'sections' => [
                                ['key' => 'header', 'variant' => 'standar'],
                                ['key' => 'hero', 'variant' => 'modern', 'content' => [
                                    'badge' => 'Pimpinan Daerah Muhammadiyah Kabupaten Jember',
                                    'headline' => 'Berkemajuan dalam Amal, Teguh dalam Ikhtiar',
                                    'subheadline' => 'PDM Kabupaten Jember memimpin dan menaungi jaringan cabang, ranting, Ortom, serta Amal Usaha Muhammadiyah di seluruh wilayah Jember dalam dakwah, pendidikan, kesehatan, dan pemberdayaan umat.',
                                    'cta_label' => 'Profil Kepengurusan',
                                    'cta_type' => 'scroll',
                                    'cta_section' => 'struktur-pengurus',
                                    'cta_secondary_label' => 'Jaringan Amal Usaha',
                                    'cta_secondary_type' => 'scroll',
                                    'cta_secondary_section' => 'jaringan-aum-ortom',
                                    'image' => 'https://images.unsplash.com/photo-1519452575417-564c1401ecc0?auto=format&fit=crop&w=1400&q=80',
                                ]],
                                ['key' => 'sambutan-ketua', 'variant' => 'modern', 'content' => [
                                    'nama' => 'Prof. Dr. H. Muhammad Sholihin, M.Ag.',
                                    'jabatan' => 'Ketua PDM Kabupaten Jember periode 2022–2027',
                                    'sambutan' => 'Assalamu\'alaikum warahmatullahi wabarakatuh. Muhammadiyah Kabupaten Jember hadir sebagai gerakan dakwah amar makruf nahi mungkar yang berikhtiar hadir di setiap lini kehidupan masyarakat — dari mimbar masjid hingga ruang kelas, dari klinik hingga panti asuhan. Melalui kanal digital ini, kami ingin setiap warga Muhammadiyah dan masyarakat Jember dapat mengikuti, mendukung, dan turut serta dalam setiap langkah dakwah dan amal usaha yang kami jalankan bersama seluruh Pimpinan Cabang, Ranting, Ortom, dan Amal Usaha di wilayah ini.',
                                    'photo' => 'https://randomuser.me/api/portraits/men/52.jpg',
                                ]],
                                ['key' => 'tentang-organisasi', 'variant' => 'modern', 'content' => [
                                    'title' => 'Muhammadiyah di Bumi Pandalungan',
                                    'body' => 'Sejak berdiri pada 1930, Muhammadiyah Kabupaten Jember telah tumbuh menjadi salah satu pimpinan daerah terbesar di Jawa Timur, menaungi puluhan cabang dan ratusan ranting yang tersebar dari dataran tinggi hingga pesisir selatan. Gerakan ini menyatukan dakwah, pendidikan, kesehatan, dan pemberdayaan sosial dalam satu ikhtiar berkemajuan bagi masyarakat Jember.',
                                    'image' => 'https://images.unsplash.com/photo-1564769662533-4f00a87b4056?auto=format&fit=crop&w=1000&q=80',
                                    'stats' => [
                                        ['value' => '31', 'label' => 'Pimpinan Cabang'],
                                        ['value' => '94', 'label' => 'Tahun Berkiprah'],
                                        ['value' => '120+', 'label' => 'Amal Usaha'],
                                    ],
                                ]],
                                ['key' => 'program-unggulan', 'variant' => 'modern', 'content' => [
                                    'title' => 'Program Strategis Daerah',
                                    'items' => [
                                        ['title' => 'Konsolidasi Cabang dan Ranting', 'description' => 'Penguatan kapasitas kepemimpinan dan organisasi di seluruh tingkat cabang dan ranting se-Kabupaten Jember.', 'icon' => '🏛️'],
                                        ['title' => 'Tata Kelola Amal Usaha', 'description' => 'Pembinaan mutu dan akuntabilitas AUM Pendidikan, Kesehatan, dan Sosial di bawah koordinasi PDM.', 'icon' => '📊'],
                                        ['title' => 'Dakwah Digital dan Kaderisasi', 'description' => 'Pengembangan dai muda dan konten dakwah yang menjangkau generasi digital.', 'icon' => '🕌'],
                                        ['title' => 'Ketahanan Pangan dan Ekonomi Umat', 'description' => 'Program lumbung pangan dan pemberdayaan ekonomi berbasis masjid dan ranting.', 'icon' => '🌾'],
                                        ['title' => 'Mitigasi dan Respon Kebencanaan', 'description' => 'Koordinasi Lazismu dan MDMC dalam kesiapsiagaan bencana wilayah Tapal Kuda.', 'icon' => '🚨'],
                                        ['title' => 'Penguatan Ortom dan Kaderisasi Muda', 'description' => 'Sinergi program dengan Aisyiyah, Pemuda Muhammadiyah, NA, IMM, IPM, Tapak Suci, dan HW.', 'icon' => '🤝'],
                                    ],
                                ]],
                                ['key' => 'struktur-pengurus', 'variant' => 'modern', 'content' => [
                                    'title' => 'Pimpinan Harian 2022–2027',
                                    'items' => [
                                        ['name' => 'Prof. Dr. H. Muhammad Sholihin, M.Ag.', 'role' => 'Ketua', 'photo' => 'https://randomuser.me/api/portraits/men/52.jpg'],
                                        ['name' => 'Dr. H. Bagus Setiawan, M.Pd.', 'role' => 'Wakil Ketua I', 'photo' => 'https://randomuser.me/api/portraits/men/61.jpg'],
                                        ['name' => 'Hj. Ratna Puspitasari, S.Ag., M.Si.', 'role' => 'Wakil Ketua II', 'photo' => 'https://randomuser.me/api/portraits/women/68.jpg'],
                                        ['name' => 'H. Anwar Sanusi, S.E.', 'role' => 'Sekretaris', 'photo' => 'https://randomuser.me/api/portraits/men/74.jpg'],
                                        ['name' => 'Nur Kholis, S.T.', 'role' => 'Wakil Sekretaris', 'photo' => 'https://randomuser.me/api/portraits/men/29.jpg'],
                                        ['name' => 'Hj. Siti Aminah, S.E., Ak.', 'role' => 'Bendahara', 'photo' => 'https://randomuser.me/api/portraits/women/51.jpg'],
                                        ['name' => 'Drs. H. Rohmat Wijaya', 'role' => 'Ketua Majelis Dikdasmen', 'photo' => 'https://randomuser.me/api/portraits/men/85.jpg'],
                                        ['name' => 'dr. Hj. Latifah Zahra, Sp.PD.', 'role' => 'Ketua Majelis Kesehatan', 'photo' => 'https://randomuser.me/api/portraits/women/39.jpg'],
                                    ],
                                ]],
                                ['key' => 'jaringan-aum-ortom', 'variant' => 'standar', 'content' => [
                                    'title' => 'Jaringan Amal Usaha & Organisasi Otonom',
                                    'items' => [
                                        ['name' => 'Universitas Muhammadiyah Jember', 'type' => 'AUM Pendidikan'],
                                        ['name' => 'RS Muhammadiyah Jember', 'type' => 'AUM Kesehatan'],
                                        ['name' => 'SMA Muhammadiyah 2 Jember', 'type' => 'AUM Pendidikan'],
                                        ['name' => 'Panti Asuhan Muhammadiyah Jember', 'type' => 'AUM Sosial'],
                                        ['name' => 'Aisyiyah Daerah Jember', 'type' => 'Ortom'],
                                        ['name' => 'Pemuda Muhammadiyah Daerah Jember', 'type' => 'Ortom'],
                                        ['name' => 'IMM Cabang Jember', 'type' => 'Ortom'],
                                        ['name' => 'Lazismu Jember', 'type' => 'AUM Sosial'],
                                    ],
                                ]],
                                ['key' => 'daftar-berita', 'variant' => 'modern', 'content' => [
                                    'title' => 'Warta Daerah',
                                    'items' => [
                                        ['title' => 'Musyda ke-XII Tetapkan Arah Gerak Muhammadiyah Jember 2022-2027', 'category' => 'Organisasi', 'date' => '14 Agt 2026', 'image' => 'https://images.unsplash.com/photo-1591123120675-6f7f1aae0e5b?auto=format&fit=crop&w=700&q=80'],
                                        ['title' => 'RS Muhammadiyah Jember Resmikan Gedung Layanan Terpadu', 'category' => 'Amal Usaha', 'date' => '07 Agt 2026', 'image' => 'https://images.unsplash.com/photo-1519494026892-80bbd2d6fd0d?auto=format&fit=crop&w=700&q=80'],
                                        ['title' => 'Rakor Lazismu dan MDMC Jember Perkuat Kesiapsiagaan Bencana', 'category' => 'Kemanusiaan', 'date' => '30 Jul 2026', 'image' => 'https://images.unsplash.com/photo-1593113598332-cd288d649433?auto=format&fit=crop&w=700&q=80'],
                                        ['title' => 'Pelantikan Pimpinan Cabang se-Kabupaten Jember Digelar Serentak', 'category' => 'Organisasi', 'date' => '21 Jul 2026', 'image' => 'https://images.unsplash.com/photo-1560472354-b33ff0c44a43?auto=format&fit=crop&w=700&q=80'],
                                    ],
                                ]],
                                ['key' => 'agenda', 'variant' => 'standar', 'content' => [
                                    'title' => 'Agenda Daerah',
                                    'items' => [
                                        ['title' => 'Rapat Pleno Pimpinan Daerah', 'date_day' => '19', 'date_month' => 'Agt', 'time' => '13.00 WIB', 'location' => 'Kantor PDM Kabupaten Jember'],
                                        ['title' => 'Silaturahmi Akbar Pimpinan Cabang se-Jember', 'date_day' => '23', 'date_month' => 'Agt', 'time' => '08.00 WIB', 'location' => 'Auditorium Universitas Muhammadiyah Jember'],
                                        ['title' => 'Rapat Koordinasi Majelis dan Lembaga', 'date_day' => '28', 'date_month' => 'Agt', 'time' => '19.30 WIB', 'location' => 'Kantor PDM Kabupaten Jember'],
                                    ],
                                ]],
                                ['key' => 'galeri', 'variant' => 'standar', 'content' => [
                                    'title' => 'Dokumentasi Kegiatan',
                                    'items' => [
                                        ['image' => 'https://images.unsplash.com/photo-1591123120675-6f7f1aae0e5b?auto=format&fit=crop&w=600&q=80', 'caption' => 'Musyda ke-XII PDM Jember'],
                                        ['image' => 'https://images.unsplash.com/photo-1519494026892-80bbd2d6fd0d?auto=format&fit=crop&w=600&q=80', 'caption' => 'Peresmian gedung RS Muhammadiyah'],
                                        ['image' => 'https://images.unsplash.com/photo-1593113598332-cd288d649433?auto=format&fit=crop&w=600&q=80', 'caption' => 'Rakor Lazismu dan MDMC'],
                                        ['image' => 'https://images.unsplash.com/photo-1560472354-b33ff0c44a43?auto=format&fit=crop&w=600&q=80', 'caption' => 'Pelantikan Pimpinan Cabang'],
                                        ['image' => 'https://images.unsplash.com/photo-1542816417-0983c9c9ad53?auto=format&fit=crop&w=600&q=80', 'caption' => 'Silaturahmi akbar warga Muhammadiyah'],
                                        ['image' => 'https://images.unsplash.com/photo-1509062522246-3755977927d7?auto=format&fit=crop&w=600&q=80', 'caption' => 'Kegiatan pendidikan kader'],
                                        ['image' => 'https://images.unsplash.com/photo-1470004914212-05527e49370b?auto=format&fit=crop&w=600&q=80', 'caption' => 'Bakti sosial lintas Ortom'],
                                        ['image' => 'https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?auto=format&fit=crop&w=600&q=80', 'caption' => 'Kajian rutin pimpinan daerah'],
                                    ],
                                ]],
                                ['key' => 'donasi-zakat-infak', 'variant' => 'standar', 'content' => [
                                    'title' => 'Lazismu Kabupaten Jember',
                                    'body' => 'Salurkan zakat, infak, wakaf, dan donasi kemanusiaan melalui Lazismu Kabupaten Jember untuk mendukung program dakwah, pendidikan, dan tanggap bencana di seluruh wilayah.',
                                    'image' => 'https://images.unsplash.com/photo-1532629345422-7515f3d16bb6?auto=format&fit=crop&w=600&q=80',
                                ]],
                                ['key' => 'cta', 'variant' => 'modern', 'content' => [
                                    'title' => 'Bersinergi Membangun Jember Berkemajuan',
                                    'subtitle' => 'Untuk Pimpinan Cabang, Ranting, Ortom, dan Amal Usaha yang ingin berkoordinasi dengan PDM Kabupaten Jember.',
                                    'cta_label' => 'Hubungi Sekretariat',
                                    'cta_type' => 'scroll',
                                    'cta_section' => 'formulir-kontak',
                                ]],
                                ['key' => 'formulir-kontak', 'variant' => 'standar', 'content' => [
                                    'title' => 'Hubungi Sekretariat PDM',
                                    'subtitle' => 'Untuk koordinasi Pimpinan Cabang/Ranting, kemitraan Amal Usaha, atau pertanyaan umum, silakan hubungi kami.',
                                ]],
                                ['key' => 'lokasi-peta', 'variant' => 'standar', 'content' => [
                                    'title' => 'Kantor PDM Kabupaten Jember',
                                    'address' => 'Jl. Kh. Ahmad Dahlan No. 1, Kaliwates, Kabupaten Jember, Jawa Timur',
                                ]],
                                ['key' => 'footer', 'variant' => 'standar'],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
