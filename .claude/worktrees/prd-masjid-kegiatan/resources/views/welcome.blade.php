<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>website-mu.id - Platform Pembuatan Web & Landing Page Muhammadiyah</title>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#2C368B',    /* Biru Muhammadiyah */
                        secondary: '#079C4E',  /* Hijau Muhammadiyah */
                        accent: '#F59E0B',     /* Kuning Aksen */
                        softBg: '#F8FAFC',
                    },
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    },
                    boxShadow: {
                        'soft': '0 10px 40px -10px rgba(0,0,0,0.06)',
                        'float': '0 20px 40px -10px rgba(44, 54, 139, 0.18)',
                    }
                }
            }
        }
    </script>

    <!-- Swiper CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css" />

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        body { background-color: #ffffff; }
        .text-gradient {
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-image: linear-gradient(135deg, #2C368B 0%, #079C4E 100%);
        }
        .swiper-pagination-bullet-active {
            width: 28px !important;
            border-radius: 9999px !important;
            background-color: #079C4E !important;
        }
    </style>
</head>
<body class="text-gray-800 font-sans antialiased selection:bg-secondary selection:text-white">

    <!-- Floating Navbar -->
    <div class="fixed top-5 left-0 right-0 z-50 flex justify-center px-4">
        <nav class="bg-white/90 backdrop-blur-md shadow-soft rounded-full px-6 py-3 flex justify-between items-center w-full max-w-6xl border border-gray-100">
            <a href="#" class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-full bg-primary flex items-center justify-center text-white font-extrabold text-sm">WM</div>
                <span class="text-xl font-extrabold text-primary tracking-tight">website-mu<span class="text-secondary">.id</span></span>
            </a>

            <div class="hidden md:flex items-center space-x-8 text-sm font-semibold text-gray-600">
                <a href="#keunggulan" class="hover:text-primary transition">Keunggulan</a>
                <a href="#template" class="hover:text-primary transition">Pilihan Template</a>
                <a href="#cara-kerja" class="hover:text-primary transition">Cara Kerja</a>
                <a href="#harga" class="hover:text-primary transition">Harga Paket</a>
            </div>

            <div class="hidden md:flex items-center gap-3">
                <a href="#template" class="bg-primary hover:bg-secondary text-white px-5 py-2.5 rounded-full text-sm font-bold shadow-soft transition-all hover:shadow-float">
                    Buat Web Sekarang
                </a>
            </div>

            <!-- Mobile menu trigger -->
            <button class="md:hidden text-gray-700 p-2">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
            </button>
        </nav>
    </div>

    <!-- Hero Section -->
    <section class="pt-32 pb-20 px-4 max-w-7xl mx-auto">
        <div class="bg-gradient-to-br from-softBg via-white to-green-50/30 rounded-[3rem] p-8 md:p-16 border border-gray-100 relative overflow-hidden">
            <!-- Decorative SVG Circles -->
            <div class="absolute -top-24 -right-24 w-96 h-96 bg-primary/5 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-24 -left-24 w-96 h-96 bg-secondary/10 rounded-full blur-3xl pointer-events-none"></div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center relative z-10">
                <div class="lg:col-span-7 text-center lg:text-left">
                    <span class="inline-flex items-center gap-2 bg-green-100 text-secondary px-4 py-1.5 rounded-full text-xs md:text-sm font-bold mb-6">
                        <span class="w-2 h-2 rounded-full bg-secondary animate-pulse"></span>
                        Platform Digitalisasi Profil Muhammadiyah
                    </span>

                    <h1 class="text-4xl md:text-6xl font-extrabold text-gray-900 leading-[1.15] mb-6">
                        Modernkan Profil <span class="text-gradient">Muhammadiyah-mu</span> Hanya dalam Hitungan Menit
                    </h1>

                    <p class="text-gray-600 text-lg md:text-xl leading-relaxed mb-8 max-w-2xl mx-auto lg:mx-0">
                        Platform khusus pembuatan website & landing page siap pakai untuk PRM, PCM, PDM, Sekolah, Masjid, RSU, dan Amal Usaha Muhammadiyah (AUM) di seluruh Indonesia.
                    </p>

                    <div class="flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-4">
                        <a href="#template" class="w-full sm:w-auto bg-primary hover:bg-secondary text-white px-8 py-4 rounded-full font-bold text-center shadow-float transition-all hover:scale-105">
                            Pilih Template Web
                        </a>
                        <a href="#demo" class="w-full sm:w-auto bg-white hover:bg-gray-50 text-gray-700 border border-gray-200 px-8 py-4 rounded-full font-bold text-center transition-all shadow-sm">
                            Lihat Contoh Hasil
                        </a>
                    </div>

                    <!-- Trust Metrics -->
                    <div class="mt-12 pt-8 border-t border-gray-200/60 grid grid-cols-3 gap-4 text-center lg:text-left">
                        <div>
                            <p class="text-2xl md:text-3xl font-extrabold text-primary">150+</p>
                            <p class="text-xs md:text-sm text-gray-500 font-medium">Cabang & Ranting</p>
                        </div>
                        <div>
                            <p class="text-2xl md:text-3xl font-extrabold text-secondary">80+</p>
                            <p class="text-xs md:text-sm text-gray-500 font-medium">Sekolah & AUM</p>
                        </div>
                        <div>
                            <p class="text-2xl md:text-3xl font-extrabold text-gray-800">100%</p>
                            <p class="text-xs md:text-sm text-gray-500 font-medium">Desain Responsive</p>
                        </div>
                    </div>
                </div>

                <!-- Hero Graphic Mockup -->
                <div class="lg:col-span-5 relative">
                    <div class="bg-white p-4 md:p-6 rounded-[2.5rem] shadow-float border border-gray-100 relative transform rotate-1 hover:rotate-0 transition duration-500">
                        <div class="flex items-center gap-2 mb-4 px-2">
                            <span class="w-3 h-3 rounded-full bg-red-400"></span>
                            <span class="w-3 h-3 rounded-full bg-yellow-400"></span>
                            <span class="w-3 h-3 rounded-full bg-green-400"></span>
                            <span class="ml-2 text-xs font-mono text-gray-400 bg-gray-50 px-3 py-1 rounded-full w-full text-center">pcm-ambulu.website-mu.id</span>
                        </div>
                        <img src="https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&w=800&q=80" alt="Website Template Preview" class="rounded-2xl w-full object-cover shadow-sm h-64 md:h-80">

                        <!-- Floating Badge inside Mockup -->
                        <div class="absolute -bottom-6 -left-6 bg-white p-4 rounded-2xl shadow-soft border border-gray-100 flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-secondary/10 text-secondary flex items-center justify-center font-bold">
                                ✓
                            </div>
                            <div>
                                <p class="text-xs text-gray-400 font-bold uppercase">Status Live</p>
                                <p class="text-sm font-bold text-gray-800">Siap Diakses Publik</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Mengapa Penting (Why Digitize?) -->
    <section id="keunggulan" class="py-20 container mx-auto px-4 max-w-6xl">
        <div class="text-center mb-16">
            <span class="text-secondary font-bold tracking-wider uppercase text-sm bg-green-50 px-4 py-1.5 rounded-full">Mengapa Harus website-mu.id?</span>
            <h2 class="text-3xl md:text-5xl font-extrabold text-primary mt-4">Tingkatkan Kepercayaan Umat Melalui Transparansi Digital</h2>
            <p class="text-gray-500 mt-4 max-w-2xl mx-auto text-lg">Era dakwah digital menuntut setiap struktur dan AUM memiliki wajah online yang profesional, tepercaya, dan mudah diakses.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Point 1 -->
            <div class="bg-softBg p-8 rounded-[2.5rem] border border-gray-100 hover:-translate-y-2 transition-all duration-300">
                <div class="w-14 h-14 bg-primary text-white rounded-2xl flex items-center justify-center mb-6 shadow-md">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-3">Tanpa Koding (No-Code)</h3>
                <p class="text-gray-500 leading-relaxed text-sm">Tidak perlu keahlian IT rumit. Cukup pilih template, isi formulir profil, dan website Anda langsung siap digunakan.</p>
            </div>

            <!-- Point 2 -->
            <div class="bg-softBg p-8 rounded-[2.5rem] border border-gray-100 hover:-translate-y-2 transition-all duration-300">
                <div class="w-14 h-14 bg-secondary text-white rounded-2xl flex items-center justify-center mb-6 shadow-md">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-3">Sangat Responsif & Cepat</h3>
                <p class="text-gray-500 leading-relaxed text-sm">Tampilan otomatis menyesuaikan layar smartphone, tablet, maupun laptop dengan kecepatan *loading* optimal.</p>
            </div>

            <!-- Point 3 -->
            <div class="bg-softBg p-8 rounded-[2.5rem] border border-gray-100 hover:-translate-y-2 transition-all duration-300">
                <div class="w-14 h-14 bg-primary text-white rounded-2xl flex items-center justify-center mb-6 shadow-md">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-3">Struktur Standar Muhammadiyah</h3>
                <p class="text-gray-500 leading-relaxed text-sm">Template dirancang khusus mencakup profil Pimpinan, Struktur 9/11 Anggota, Daftar Masjid, Lazismu, hingga AUM.</p>
            </div>
        </div>
    </section>

    <!-- Template Showcase (Interactive Gallery) -->
    <section id="template" class="py-20 bg-softBg rounded-[3rem] mx-2 md:mx-6 px-4 my-10">
        <div class="max-w-7xl mx-auto">
            <div class="flex flex-col md:flex-row md:items-end justify-between mb-12">
                <div>
                    <span class="text-primary font-bold tracking-wider uppercase text-sm bg-blue-100 px-4 py-1.5 rounded-full">Katalog Template</span>
                    <h2 class="text-3xl md:text-4xl font-extrabold text-gray-900 mt-4">Pilih Template Sesuai Kebutuhan Anda</h2>
                </div>
                <p class="text-gray-500 mt-4 md:mt-0 max-w-md text-sm">Setiap template dilengkapi dengan fitur penyesuaian warna khas Muhammadiyah, carousel hero, dan integrasi WhatsApp.</p>
            </div>

            <!-- Grid Templates -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">

                <!-- Card Template 1 -->
                <div class="bg-white rounded-[2.5rem] overflow-hidden shadow-soft border border-gray-100 group hover:shadow-float transition-all duration-300 flex flex-col">
                    <div class="relative overflow-hidden h-56 bg-gray-100">
                        <img src="https://images.unsplash.com/photo-1542816417-0983c9c9ad53?auto=format&fit=crop&w=800&q=80" alt="Template PCM / PRM" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        <span class="absolute top-4 left-4 bg-primary text-white text-xs font-bold px-3 py-1 rounded-full">Terpopuler</span>
                    </div>
                    <div class="p-6 flex-1 flex flex-col justify-between">
                        <div>
                            <h3 class="text-xl font-bold text-gray-800 mb-2">Template Cabang & Ranting (PCM / PRM)</h3>
                            <p class="text-gray-500 text-sm mb-6">Lengkap dengan Jumbotron, Sambutan Ketua, Carousel Struktur 9 Pimpinan, & Daftar Masjid Binaan.</p>
                        </div>
                        <div class="flex items-center justify-between border-t border-gray-100 pt-4">
                            <span class="text-xs font-bold text-secondary bg-green-50 px-3 py-1 rounded-full">Pro Responsive</span>
                            <a href="#kontak" class="bg-primary hover:bg-secondary text-white px-4 py-2 rounded-xl text-xs font-bold transition">Gunakan Template</a>
                        </div>
                    </div>
                </div>

                <!-- Card Template 2 -->
                <div class="bg-white rounded-[2.5rem] overflow-hidden shadow-soft border border-gray-100 group hover:shadow-float transition-all duration-300 flex flex-col">
                    <div class="relative overflow-hidden h-56 bg-gray-100">
                        <img src="https://images.unsplash.com/photo-1523050854058-8df90110c9f1?auto=format&fit=crop&w=800&q=80" alt="Template Sekolah" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        <span class="absolute top-4 left-4 bg-secondary text-white text-xs font-bold px-3 py-1 rounded-full">Pendidikan</span>
                    </div>
                    <div class="p-6 flex-1 flex flex-col justify-between">
                        <div>
                            <h3 class="text-xl font-bold text-gray-800 mb-2">Template Sekolah & Madrasah (AUM)</h3>
                            <p class="text-gray-500 text-sm mb-6">Khusus SD/MI, SMP/MTs, SMA/SMK Muhammadiyah. Dilengkapi PPDB Online, Jurusan, & Prestasi.</p>
                        </div>
                        <div class="flex items-center justify-between border-t border-gray-100 pt-4">
                            <span class="text-xs font-bold text-secondary bg-green-50 px-3 py-1 rounded-full">Pro Responsive</span>
                            <a href="#kontak" class="bg-primary hover:bg-secondary text-white px-4 py-2 rounded-xl text-xs font-bold transition">Gunakan Template</a>
                        </div>
                    </div>
                </div>

                <!-- Card Template 3 -->
                <div class="bg-white rounded-[2.5rem] overflow-hidden shadow-soft border border-gray-100 group hover:shadow-float transition-all duration-300 flex flex-col">
                    <div class="relative overflow-hidden h-56 bg-gray-100">
                        <img src="https://images.unsplash.com/photo-1519817650390-64a93db51149?auto=format&fit=crop&w=800&q=80" alt="Template Masjid" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        <span class="absolute top-4 left-4 bg-accent text-white text-xs font-bold px-3 py-1 rounded-full">Masjid & Lazismu</span>
                    </div>
                    <div class="p-6 flex-1 flex flex-col justify-between">
                        <div>
                            <h3 class="text-xl font-bold text-gray-800 mb-2">Template Masjid & Kantor Lazismu</h3>
                            <p class="text-gray-500 text-sm mb-6">Fitur Jadwal Shalat, Laporan Keuangan Infaq Transparan, Program Zakat, dan Pengajian Rutin.</p>
                        </div>
                        <div class="flex items-center justify-between border-t border-gray-100 pt-4">
                            <span class="text-xs font-bold text-secondary bg-green-50 px-3 py-1 rounded-full">Pro Responsive</span>
                            <a href="#kontak" class="bg-primary hover:bg-secondary text-white px-4 py-2 rounded-xl text-xs font-bold transition">Gunakan Template</a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- Cara Kerja (3 Easy Steps) -->
    <section id="cara-kerja" class="py-20 container mx-auto px-4 max-w-6xl">
        <div class="text-center mb-16">
            <span class="text-secondary font-bold tracking-wider uppercase text-sm bg-green-50 px-4 py-1.5 rounded-full">Langkah Mudah</span>
            <h2 class="text-3xl md:text-4xl font-extrabold text-primary mt-4">Hanya 3 Langkah Cepat</h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 relative">
            <!-- Step 1 -->
            <div class="bg-white p-8 rounded-[2.5rem] shadow-soft border border-gray-100 relative text-center">
                <div class="w-12 h-12 bg-primary text-white rounded-full font-extrabold text-xl flex items-center justify-center mx-auto mb-6 shadow-md">1</div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Pilih Template</h3>
                <p class="text-gray-500 text-sm">Pilih desain tampilan yang sesuai dengan jenis pimpinan atau AUM Anda.</p>
            </div>

            <!-- Step 2 -->
            <div class="bg-white p-8 rounded-[2.5rem] shadow-soft border border-gray-100 relative text-center">
                <div class="w-12 h-12 bg-secondary text-white rounded-full font-extrabold text-xl flex items-center justify-center mx-auto mb-6 shadow-md">2</div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Kirim Data Profil</h3>
                <p class="text-gray-500 text-sm">Isi data nama pimpinan, foto kegiatan, daftar masjid, atau informasi AUM Anda.</p>
            </div>

            <!-- Step 3 -->
            <div class="bg-white p-8 rounded-[2.5rem] shadow-soft border border-gray-100 relative text-center">
                <div class="w-12 h-12 bg-primary text-white rounded-full font-extrabold text-xl flex items-center justify-center mx-auto mb-6 shadow-md">3</div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Web Siap Digunakan</h3>
                <p class="text-gray-500 text-sm">Website Anda langsung tayang dengan domain pilihan dan dapat diakses seluruh dunia.</p>
            </div>
        </div>
    </section>

    <!-- Pricing Section -->
    <section id="harga" class="py-20 bg-softBg rounded-[3rem] mx-2 md:mx-6 px-4 my-10">
        <div class="max-w-6xl mx-auto">
            <div class="text-center mb-16">
                <span class="text-primary font-bold tracking-wider uppercase text-sm bg-blue-100 px-4 py-1.5 rounded-full">Investasi Dakwah</span>
                <h2 class="text-3xl md:text-4xl font-extrabold text-gray-900 mt-4">Paket Pembuatan Web Terjangkau</h2>
                <p class="text-gray-500 mt-2">Didesain khusus agar ramah anggaran untuk Ranting, Cabang, maupun AUM.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-4xl mx-auto">
                <!-- Basic Plan -->
                <div class="bg-white p-8 md:p-10 rounded-[2.5rem] shadow-soft border border-gray-100 flex flex-col justify-between">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-gray-400 bg-gray-100 px-3 py-1 rounded-full">Paket Ranting / Landing Page</span>
                        <h3 class="text-2xl font-bold text-gray-800 mt-4">Landing Page PRM / AUM</h3>
                        <p class="text-4xl font-extrabold text-primary mt-4 mb-6">Rp 499.000 <span class="text-xs text-gray-400 font-normal">/ sekali bayar</span></p>
                        <ul class="space-y-4 text-sm text-gray-600 mb-8">
                            <li class="flex items-center"><span class="text-secondary font-bold mr-3">✓</span> 1 Halaman Landing Page Kompleks</li>
                            <li class="flex items-center"><span class="text-secondary font-bold mr-3">✓</span> Free Subdomain .website-mu.id</li>
                            <li class="flex items-center"><span class="text-secondary font-bold mr-3">✓</span> Carousel Foto & Profil Pimpinan</li>
                            <li class="flex items-center"><span class="text-secondary font-bold mr-3">✓</span> Integrasi Tombol Kontak WhatsApp</li>
                            <li class="flex items-center"><span class="text-secondary font-bold mr-3">✓</span> Optimasi Tampilan HP / Tablet</li>
                        </ul>
                    </div>
                    <a href="#kontak" class="w-full bg-gray-100 hover:bg-gray-200 text-gray-800 font-bold py-3.5 rounded-2xl text-center block transition">Pilih Paket Ranting</a>
                </div>

                <!-- Pro Plan (Featured) -->
                <div class="bg-white p-8 md:p-10 rounded-[2.5rem] shadow-float border-2 border-secondary relative flex flex-col justify-between">
                    <span class="absolute -top-4 right-8 bg-secondary text-white text-xs font-extrabold px-4 py-1.5 rounded-full uppercase tracking-wider">Paling Direkomendasikan</span>
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-secondary bg-green-50 px-3 py-1 rounded-full">Paket Cabang / AUM Pro</span>
                        <h3 class="text-2xl font-bold text-gray-800 mt-4">Website PCM / Sekolah</h3>
                        <p class="text-4xl font-extrabold text-secondary mt-4 mb-6">Rp 1.250.000 <span class="text-xs text-gray-400 font-normal">/ tahun</span></p>
                        <ul class="space-y-4 text-sm text-gray-600 mb-8">
                            <li class="flex items-center"><span class="text-secondary font-bold mr-3">✓</span> Fitur Paket Ranting Lengkap</li>
                            <li class="flex items-center"><span class="text-secondary font-bold mr-3">✓</span> Multi-Halaman (Berita, Agenda, Galeri)</li>
                            <li class="flex items-center"><span class="text-secondary font-bold mr-3">✓</span> Domain Custom (.or.id / .sch.id)</li>
                            <li class="flex items-center"><span class="text-secondary font-bold mr-3">✓</span> Pendampingan Kelola Konten</li>
                            <li class="flex items-center"><span class="text-secondary font-bold mr-3">✓</span> Keamanan SSL & Server Cepat</li>
                        </ul>
                    </div>
                    <a href="#kontak" class="w-full bg-secondary hover:bg-green-700 text-white font-bold py-3.5 rounded-2xl text-center block transition shadow-md">Pilih Paket Cabang</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Form Konsultasi & Pemesanan -->
    <section id="kontak" class="py-20 container mx-auto px-4 max-w-5xl">
        <div class="bg-primary text-white rounded-[3rem] p-8 md:p-14 shadow-float relative overflow-hidden">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center relative z-10">
                <div class="lg:col-span-6">
                    <span class="bg-white/10 text-green-300 font-bold px-4 py-1.5 rounded-full text-xs uppercase tracking-wider">Mulai Digitalisasi</span>
                    <h2 class="text-3xl md:text-4xl font-extrabold mt-4 mb-4">Siap Memodernkan Web Muhammadiyah Anda?</h2>
                    <p class="text-blue-100 text-sm leading-relaxed mb-6">Konsultasikan kebutuhan pembuatan website pimpinan atau AUM Anda secara gratis bersama tim kami.</p>

                    <div class="space-y-3 text-sm">
                        <p class="flex items-center gap-3"><span class="w-2 h-2 rounded-full bg-secondary"></span> WhatsApp: +62 812-3456-7890</p>
                        <p class="flex items-center gap-3"><span class="w-2 h-2 rounded-full bg-secondary"></span> Email: salam@website-mu.id</p>
                    </div>
                </div>

                <div class="lg:col-span-6">
                    <form class="bg-white text-gray-800 p-6 md:p-8 rounded-[2rem] space-y-4 shadow-lg">
                        <div>
                            <label class="block text-xs font-bold uppercase text-gray-500 mb-1">Nama / Nama Cabang / AUM</label>
                            <input type="text" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-secondary text-sm" placeholder="Contoh: PCM Ambulu / SD Muhammadiyah 1">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase text-gray-500 mb-1">Nomor WhatsApp</label>
                            <input type="tel" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-secondary text-sm" placeholder="08123456789">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase text-gray-500 mb-1">Template yang Diminati</label>
                            <select class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-secondary text-sm bg-white">
                                <option>Template Cabang & Ranting (PCM / PRM)</option>
                                <option>Template Sekolah / Pendidikan</option>
                                <option>Template Masjid & Lazismu</option>
                                <option>Lainnya / Custom</option>
                            </select>
                        </div>
                        <button type="button" class="w-full bg-secondary hover:bg-green-700 text-white font-bold py-3.5 rounded-xl transition text-sm shadow-md">
                            Konsultasi via WhatsApp Sekarang
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white pt-12 pb-8 rounded-t-[3rem] mx-2 md:mx-4">
        <div class="container mx-auto px-4 max-w-6xl text-center">
            <h3 class="text-2xl font-extrabold mb-2">website-mu<span class="text-secondary">.id</span></h3>
            <p class="text-gray-400 text-xs mb-8">Platform Pembuatan Website & Digitalisasi Persyarikatan Muhammadiyah</p>

            <div class="border-t border-gray-800 pt-6 text-xs text-gray-500 flex flex-col md:flex-row justify-between items-center gap-4">
                <p>&copy; 2026 website-mu.id. All rights reserved.</p>
                <p>Mendorong Gerakan Dakwah Digital Berkemajuan.</p>
            </div>
        </div>
    </footer>

    <!-- Swiper JS CDN -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
</body>
</html>
