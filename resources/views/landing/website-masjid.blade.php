@php
    // One source for the page's SEO strings so <title>, OG, and Twitter can't drift apart.
    // Title kept under ~60 chars so Google doesn't truncate it; the primary keyword
    // ("Website Masjid") leads, since the start of the title carries the most weight.
    $metaTitle = 'Website Masjid Profesional Tanpa Coding - website-mu.id';
    $metaDescription = 'Buat website masjid yang membanggakan jamaah: laporan keuangan transparan, program donasi & wakaf, jadwal kajian, kalkulator zakat, dan profil takmir. Tanpa coding, siap terbit kurang dari 30 menit.';
    $canonicalUrl = route('landing.masjid');

    // Guests go to register, signed-in users to the template picker - same rule as welcome.blade.php.
    $ctaUrl = auth()->check() ? route('organizations.template-picker') : route('register');
    $ctaLabel = auth()->check() ? 'Buat Website Masjid' : 'Coba Gratis';

    $catalogUrl = $organizationType
        ? route('templates.index', ['organization_type_id' => $organizationType->id])
        : route('templates.index');

    $heroTemplate = $templates->firstWhere('is_exclusive', true) ?? $templates->first();
    $heroImage = $heroTemplate?->thumbnailUrl() ?? asset('home.jpeg');

    // `exclusive` mirrors config/page-builder.php's flag for the same section, so the badge never
    // promises a premium section as included in every plan.
    $features = [
        ['title' => 'Laporan Keuangan Transparan', 'exclusive' => true, 'icon' => 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
            'body' => 'Pemasukan dan pengeluaran kas masjid tampil rapi per bulan. Jamaah dan donatur bisa melihat sendiri ke mana infaknya digunakan.'],
        ['title' => 'Program Donasi & Wakaf', 'exclusive' => true, 'icon' => 'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z',
            'body' => 'Wakaf pembangunan, wakaf Al-Qur\'an, bakti sosial - lengkap dengan target dan progress bar yang terus bergerak setiap donasi dicatat.'],
        ['title' => 'Kalkulator Zakat', 'exclusive' => true, 'icon' => 'M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z',
            'body' => 'Jamaah menghitung zakat maal sendiri berdasarkan nisab emas, lalu langsung terhubung ke WhatsApp amil masjid untuk konsultasi.'],
        ['title' => 'Fasilitas Masjid', 'exclusive' => true, 'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
            'body' => 'Pamerkan ruang utama, tempat wudhu, perpustakaan, TPA, hingga area parkir - kebanggaan yang selama ini hanya diketahui jamaah tetap.'],
        ['title' => 'Akad Nikah di Masjid', 'exclusive' => true, 'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
            'body' => 'Kenalkan aula masjid untuk akad nikah dan acara jamaah, dengan ketentuan infak yang bisa Anda tulis sendiri - tanpa kesan menyewakan rumah Allah.'],
        ['title' => 'Jadwal Kajian & Agenda', 'exclusive' => false, 'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253',
            'body' => 'Kajian rutin, pengajian ahad pagi, dan kegiatan Ramadan tersusun dalam agenda yang mudah dibagikan ke grup WhatsApp jamaah.'],
        ['title' => 'Pengumuman & Berita', 'exclusive' => false, 'icon' => 'M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z',
            'body' => 'Info petugas Jumat, jadwal Idul Adha, hingga liputan kegiatan - tersimpan rapi, tidak tenggelam di riwayat chat grup.'],
        ['title' => 'Galeri, Takmir & Lokasi', 'exclusive' => false, 'icon' => 'M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0zM15 11a3 3 0 11-6 0 3 3 0 016 0z',
            'body' => 'Dokumentasi kegiatan, susunan pengurus takmir, serta peta lokasi agar musafir dan jamaah baru mudah menemukan masjid Anda.'],
    ];

    // Rendered visibly below AND emitted as FAQPage JSON-LD from this same array - structured
    // data that doesn't match visible content is a Google guideline violation.
    $faqs = [
        ['q' => 'Apakah saya perlu bisa coding untuk membuat website masjid?',
            'a' => 'Tidak sama sekali. Anda cukup memilih template masjid, mengisi profil seperti nama takmir, jadwal kajian, dan foto kegiatan lewat formulir, lalu menerbitkannya. Semuanya dikelola dari dashboard tanpa menyentuh kode.'],
        ['q' => 'Berapa lama waktu yang dibutuhkan untuk membuat website masjid?',
            'a' => 'Target kami kurang dari 30 menit sampai website masjid Anda tayang. Template sudah berisi struktur halaman lengkap, sehingga Anda tinggal mengganti isinya dengan data masjid sendiri.'],
        ['q' => 'Apakah website masjid bisa dicoba gratis?',
            'a' => 'Bisa. Anda dapat mendaftar dan merancang website masjid secara gratis terlebih dahulu. Pilihan paket langganan dapat dilihat di dashboard setelah mendaftar.'],
        ['q' => 'Bagaimana cara menampilkan laporan keuangan masjid di website?',
            'a' => 'Bendahara atau takmir mencatat pemasukan dan pengeluaran kas per bulan dari dashboard. Laporan otomatis tersusun rapi di website, sehingga jamaah dan donatur dapat memantau penggunaan dana masjid secara transparan.'],
        ['q' => 'Apakah jamaah bisa melihat perkembangan donasi dan wakaf?',
            'a' => 'Bisa. Setiap program donasi atau wakaf memiliki target dana dan progress bar. Setiap kali takmir mencatat donasi yang masuk, angka terkumpul dan persentasenya langsung diperbarui di website.'],
        ['q' => 'Apa alamat website masjid yang akan saya dapatkan?',
            'a' => 'Website masjid Anda tayang di subdomain website-mu.id, misalnya nama-masjid.website-mu.id, sehingga mudah diingat dan dibagikan ke jamaah.'],
        ['q' => 'Apakah pengelolaan website bisa dibagi dengan pengurus takmir lain?',
            'a' => 'Bisa. Anda dapat menambahkan pengurus lain sebagai anggota, sehingga sekretaris, bendahara, atau seksi dakwah bisa ikut memperbarui konten sesuai tugasnya.'],
        ['q' => 'Apakah website-mu.id khusus untuk masjid Muhammadiyah?',
            'a' => 'website-mu.id dirancang untuk ekosistem Muhammadiyah, termasuk masjid dan mushola ranting, cabang, maupun masjid di lingkungan amal usaha Muhammadiyah, dengan fitur yang disesuaikan untuk kebutuhan takmir.'],
    ];

    // Built with json_encode() rather than inline JSON so schema.org's "at"-prefixed keys aren't
    // parsed as Blade directives (same workaround as welcome.blade.php).
    $atContext = '@' . 'context';
    $atType = '@' . 'type';
    $structuredData = [
        [
            $atContext => 'https://schema.org',
            $atType => 'FAQPage',
            'mainEntity' => array_map(fn ($faq) => [
                $atType => 'Question',
                'name' => $faq['q'],
                'acceptedAnswer' => [$atType => 'Answer', 'text' => $faq['a']],
            ], $faqs),
        ],
        [
            $atContext => 'https://schema.org',
            $atType => 'BreadcrumbList',
            'itemListElement' => [
                [$atType => 'ListItem', 'position' => 1, 'name' => 'website-mu.id', 'item' => url('/')],
                [$atType => 'ListItem', 'position' => 2, 'name' => 'Website Masjid', 'item' => $canonicalUrl],
            ],
        ],
    ];
@endphp
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $metaTitle }}</title>
    <meta name="description" content="{{ $metaDescription }}">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ $canonicalUrl }}">
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">

    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ $canonicalUrl }}">
    <meta property="og:site_name" content="website-mu.id">
    <meta property="og:locale" content="id_ID">
    <meta property="og:title" content="{{ $metaTitle }}">
    <meta property="og:description" content="{{ $metaDescription }}">
    <meta property="og:image" content="{{ asset('og-image.jpg') }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $metaTitle }}">
    <meta name="twitter:description" content="{{ $metaDescription }}">
    <meta name="twitter:image" content="{{ asset('og-image.jpg') }}">

    @foreach ($structuredData as $schema)
        <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
    @endforeach

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body { background-color: #ffffff; font-family: 'Plus Jakarta Sans', sans-serif; }
        .text-gradient {
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-image: linear-gradient(135deg, #2C368B 0%, #079C4E 100%);
        }
    </style>
    @include('partials.google-analytics')
</head>
<body class="text-gray-800 font-sans antialiased selection:bg-secondary selection:text-white">

    <!-- Floating Navbar -->
    <div class="fixed top-5 left-0 right-0 z-50 flex justify-center px-4" x-data="{ mobileOpen: false }">
        <nav class="bg-white/90 backdrop-blur-md shadow-soft rounded-full px-6 py-3 flex justify-between items-center w-full max-w-6xl border border-gray-100 relative">
            <a href="{{ route('home') }}" class="flex items-center gap-2">
                <img src="{{ asset('logo.png') }}" alt="website-mu.id" class="h-11 w-auto">
                <span class="text-xl font-extrabold text-primary tracking-tight">website-mu<span class="text-secondary">.id</span></span>
            </a>

            <div class="hidden md:flex items-center space-x-8 text-sm font-semibold text-gray-600">
                <a href="#fitur" class="hover:text-primary transition">Fitur</a>
                <a href="#template-masjid" class="hover:text-primary transition">Template</a>
                <a href="#faq" class="hover:text-primary transition">FAQ</a>
            </div>

            <div class="hidden md:flex items-center gap-3">
                @guest
                    <a href="{{ route('login') }}" class="text-sm font-semibold text-gray-600 hover:text-primary transition px-2">Masuk</a>
                @endguest
                <a href="{{ $ctaUrl }}" class="bg-primary hover:bg-secondary text-white px-5 py-2.5 rounded-full text-sm font-bold shadow-soft transition-all hover:shadow-float">
                    {{ $ctaLabel }}
                </a>
            </div>

            <button type="button" x-on:click="mobileOpen = !mobileOpen" class="md:hidden text-gray-700 p-2" aria-label="Buka menu">
                <svg x-show="!mobileOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                <svg x-show="mobileOpen" x-cloak class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>

            <div
                x-show="mobileOpen"
                x-cloak
                x-on:click.outside="mobileOpen = false"
                x-transition
                class="md:hidden absolute top-full left-0 right-0 mt-3 bg-white rounded-3xl shadow-float border border-gray-100 p-6 flex flex-col gap-4 text-sm font-semibold text-gray-600"
            >
                <a href="#fitur" x-on:click="mobileOpen = false" class="hover:text-primary transition">Fitur</a>
                <a href="#template-masjid" x-on:click="mobileOpen = false" class="hover:text-primary transition">Template</a>
                <a href="#faq" x-on:click="mobileOpen = false" class="hover:text-primary transition">FAQ</a>
                @guest
                    <a href="{{ route('login') }}" class="hover:text-primary transition">Masuk</a>
                @endguest
                <a href="{{ $ctaUrl }}" class="bg-primary hover:bg-secondary text-white px-5 py-3 rounded-full text-center font-bold shadow-soft transition-all">
                    {{ $ctaLabel }}
                </a>
            </div>
        </nav>
    </div>

    <!-- Hero -->
    <section class="pt-32 pb-16 px-4 max-w-7xl mx-auto">
        <nav aria-label="Breadcrumb" class="text-xs text-gray-400 font-semibold mb-6 md:px-16">
            <a href="{{ route('home') }}" class="hover:text-primary transition">website-mu.id</a>
            <span class="mx-1.5">/</span>
            <span class="text-gray-600">Website Masjid</span>
        </nav>

        <div class="md:px-16 grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
            <div class="lg:col-span-7 text-center lg:text-left">
                <span class="inline-flex items-center gap-2 bg-green-100 text-secondary px-4 py-1.5 rounded-full text-xs md:text-sm font-bold mb-6">
                    <span class="w-2 h-2 rounded-full bg-secondary animate-pulse"></span>
                    Khusus Masjid &amp; Mushola
                </span>

                <h1 class="text-4xl md:text-6xl font-extrabold text-gray-900 leading-[1.15] mb-6">
                    Website Masjid yang <span class="text-gradient">Membanggakan Jamaah.</span>
                </h1>

                <p class="text-gray-600 text-lg md:text-xl leading-relaxed mb-8 max-w-2xl mx-auto lg:mx-0">
                    Setiap masjid punya keunggulan: kajian yang hidup, program wakaf yang terus tumbuh, kas yang dikelola amanah. Tampilkan semuanya dalam satu website resmi masjid - tanpa coding, siap terbit kurang dari 30 menit.
                </p>

                <div class="flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-4">
                    <a href="{{ $ctaUrl }}" class="w-full sm:w-auto bg-primary hover:bg-secondary text-white px-8 py-4 rounded-full font-bold text-center shadow-float transition-all hover:scale-105">
                        Buat Website Masjid Sekarang
                    </a>
                    <a href="#template-masjid" class="w-full sm:w-auto bg-white hover:bg-gray-50 text-gray-700 border border-gray-200 px-8 py-4 rounded-full font-bold text-center transition-all shadow-sm">
                        Lihat Contoh Website Masjid
                    </a>
                </div>

                <div class="mt-12 pt-8 border-t border-gray-200/60 grid grid-cols-3 gap-4 text-center lg:text-left">
                    <div>
                        <p class="text-2xl md:text-3xl font-extrabold text-primary">{{ count($features) }}+</p>
                        <p class="text-xs md:text-sm text-gray-500 font-medium">Fitur Khas Masjid</p>
                    </div>
                    <div>
                        <p class="text-2xl md:text-3xl font-extrabold text-secondary">&lt; 30 Menit</p>
                        <p class="text-xs md:text-sm text-gray-500 font-medium">Target Waktu Terbit</p>
                    </div>
                    <div>
                        <p class="text-2xl md:text-3xl font-extrabold text-gray-800">Gratis</p>
                        <p class="text-xs md:text-sm text-gray-500 font-medium">Coba &amp; Rancang Dulu</p>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-5 relative">
                <div class="bg-white p-4 md:p-6 rounded-3xl shadow-float border border-gray-100 relative transform rotate-1 hover:rotate-0 transition duration-500">
                    <div class="flex items-center gap-2 mb-4 px-2">
                        <span class="w-3 h-3 rounded-full bg-red-400 shrink-0"></span>
                        <span class="w-3 h-3 rounded-full bg-yellow-400 shrink-0"></span>
                        <span class="w-3 h-3 rounded-full bg-green-400 shrink-0"></span>
                        <span class="ml-2 text-xs font-mono text-gray-400 bg-gray-50 px-3 py-1 rounded-full w-full text-center truncate">masjid-anda.website-mu.id</span>
                    </div>
                    <img src="{{ $heroImage }}" alt="Contoh website masjid buatan website-mu.id" class="rounded-2xl w-full object-cover object-top shadow-sm h-64 md:h-80">

                    <div class="absolute -bottom-6 -left-2 sm:-left-6 bg-white p-4 rounded-2xl shadow-soft border border-gray-100 flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-secondary/10 text-secondary flex items-center justify-center font-bold">✓</div>
                        <div>
                            <p class="text-xs text-gray-400 font-bold uppercase">Kas Bulan Ini</p>
                            <p class="text-sm font-bold text-gray-800">Laporan Terbuka untuk Jamaah</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Why: pride + trust -->
    <section class="py-16 md:py-20 container mx-auto px-4 max-w-6xl">
        <div class="text-center mb-12 md:mb-16">
            <span class="text-secondary font-bold tracking-wider uppercase text-sm bg-green-50 px-4 py-1.5 rounded-full">Mengapa Masjid Perlu Website?</span>
            <h2 class="text-3xl md:text-5xl font-extrabold text-primary mt-4">Keunggulan Masjid Anda Layak Dikenal Lebih Luas</h2>
            <p class="text-gray-500 mt-4 max-w-2xl mx-auto text-base md:text-lg">Jamaah hari ini mencari masjid lewat ponsel. Website masjid menjadi wajah resmi yang menumbuhkan kepercayaan - jauh sebelum mereka melangkah masuk.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 md:gap-8">
            <div class="bg-softBg p-8 rounded-3xl border border-gray-100">
                <p class="text-4xl font-extrabold text-primary mb-4">01</p>
                <h3 class="text-xl font-bold text-gray-800 mb-3">Kepercayaan Donatur Tumbuh</h3>
                <p class="text-gray-500 leading-relaxed text-sm">Laporan keuangan yang terbuka adalah bukti amanah. Donatur yang melihat dananya tercatat rapi cenderung kembali berinfak.</p>
            </div>
            <div class="bg-softBg p-8 rounded-3xl border border-gray-100">
                <p class="text-4xl font-extrabold text-secondary mb-4">02</p>
                <h3 class="text-xl font-bold text-gray-800 mb-3">Dakwah Menjangkau Generasi Muda</h3>
                <p class="text-gray-500 leading-relaxed text-sm">Jadwal kajian dan kegiatan yang mudah ditemukan di Google membawa jamaah baru, termasuk anak muda dan pendatang di sekitar masjid.</p>
            </div>
            <div class="bg-softBg p-8 rounded-3xl border border-gray-100">
                <p class="text-4xl font-extrabold text-primary mb-4">03</p>
                <h3 class="text-xl font-bold text-gray-800 mb-3">Informasi Tidak Tenggelam</h3>
                <p class="text-gray-500 leading-relaxed text-sm">Pengumuman di grup WhatsApp cepat tertimbun. Di website, info penting tetap tersimpan dan bisa dibagikan ulang kapan saja.</p>
            </div>
        </div>
    </section>

    <!-- Features -->
    <section id="fitur" class="scroll-mt-28 py-16 md:py-20 bg-softBg mx-2 px-4">
        <div class="max-w-6xl mx-auto">
            <div class="text-center mb-12 md:mb-16">
                <span class="text-primary font-bold tracking-wider uppercase text-sm bg-blue-100 px-4 py-1.5 rounded-full">Fitur Website Masjid</span>
                <h2 class="text-3xl md:text-4xl font-extrabold text-gray-900 mt-4">Semua yang Dibutuhkan Takmir, dalam Satu Website</h2>
                <p class="text-gray-500 mt-4 max-w-2xl mx-auto text-sm md:text-base">Dirancang bersama pengurus masjid, bukan template website umum yang dipaksakan.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 md:gap-6">
                @foreach ($features as $feature)
                    <div class="bg-white p-6 rounded-3xl border border-gray-100 shadow-soft flex flex-col">
                        <div class="flex items-start justify-between gap-3 mb-5">
                            <div class="w-12 h-12 {{ $loop->odd ? 'bg-primary' : 'bg-secondary' }} text-white rounded-2xl flex items-center justify-center shadow-md shrink-0">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $feature['icon'] }}"></path></svg>
                            </div>
                            @if ($feature['exclusive'])
                                <span class="bg-amber-50 text-amber-600 border border-amber-200 text-[10px] font-extrabold uppercase tracking-wider px-2.5 py-1 rounded-full">Eksklusif</span>
                            @endif
                        </div>
                        <h3 class="text-lg font-bold text-gray-800 mb-2">{{ $feature['title'] }}</h3>
                        <p class="text-gray-500 leading-relaxed text-sm">{{ $feature['body'] }}</p>
                    </div>
                @endforeach
            </div>

            <p class="text-center text-xs text-gray-400 mt-8">Fitur bertanda <span class="font-bold text-amber-600">Eksklusif</span> tersedia pada paket Infak Eksklusif.</p>
        </div>
    </section>

    <!-- Templates -->
    @if ($templates->isNotEmpty())
        <section id="template-masjid" class="scroll-mt-28 py-16 md:py-20 container mx-auto px-4 max-w-6xl">
            <div class="flex flex-col md:flex-row md:items-end justify-between mb-10 md:mb-12 gap-4">
                <div>
                    <span class="text-secondary font-bold tracking-wider uppercase text-sm bg-green-50 px-4 py-1.5 rounded-full">Contoh Website Masjid</span>
                    <h2 class="text-3xl md:text-4xl font-extrabold text-gray-900 mt-4">Pilih Template Website Masjid</h2>
                </div>
                <p class="text-gray-500 max-w-md text-sm">Buka preview untuk melihat contoh website masjid yang sudah terisi lengkap, lalu gunakan sebagai titik awal masjid Anda.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 md:gap-8">
                @foreach ($templates as $template)
                    <div class="relative bg-white rounded-3xl overflow-hidden shadow-soft border {{ $template->is_exclusive ? 'border-amber-300 ring-2 ring-amber-300/50' : 'border-gray-100' }} group hover:shadow-float transition-all duration-300 flex flex-col">
                        @if ($template->is_exclusive)
                            <span class="absolute top-4 right-4 z-10 flex items-center gap-1.5 bg-gradient-to-r from-amber-400 to-amber-500 text-white text-xs font-extrabold px-3 py-1.5 rounded-full shadow-md">
                                <svg class="w-3.5 h-3.5" viewBox="0 0 20 20" fill="currentColor"><path d="M10 1.5l2.39 5.51 6 .59-4.5 4.02 1.32 5.88L10 14.6l-5.21 2.9 1.32-5.88-4.5-4.02 6-.59L10 1.5z"/></svg>
                                Eksklusif
                            </span>
                        @endif
                        <a href="{{ route('templates.preview', $template->slug) }}" class="relative overflow-hidden h-56 md:h-64 bg-gray-100 block">
                            <img src="{{ $template->thumbnailUrl() ?? asset('home.jpeg') }}" alt="Template website masjid {{ $template->name }}" loading="lazy" class="w-full h-full object-cover object-top group-hover:scale-105 transition-transform duration-500">
                        </a>
                        <div class="p-6 flex-1 flex flex-col justify-between">
                            <div>
                                <h3 class="text-xl font-bold text-gray-800 mb-2">
                                    <a href="{{ route('templates.preview', $template->slug) }}" class="hover:text-primary transition">{{ $template->name }}</a>
                                </h3>
                                <p class="text-gray-500 text-sm mb-6">{{ $template->description }}</p>
                            </div>
                            <div class="flex items-center justify-between border-t border-gray-100 pt-4 gap-2">
                                <a href="{{ route('templates.preview', $template->slug) }}" target="_blank" class="text-primary hover:text-secondary px-3 py-2 rounded-xl text-xs font-bold transition">Lihat Preview</a>
                                <a href="{{ route('templates.use', $template->slug) }}" class="bg-primary hover:bg-secondary text-white px-4 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap">Gunakan Template</a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="text-center mt-10">
                <a href="{{ $catalogUrl }}" class="inline-flex items-center gap-2 text-primary hover:text-secondary font-bold text-sm transition">
                    Lihat Semua Template Masjid
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" /></svg>
                </a>
            </div>
        </section>
    @endif

    <!-- Steps -->
    <section class="py-16 md:py-20 container mx-auto px-4 max-w-6xl">
        <div class="text-center mb-12 md:mb-16">
            <span class="text-primary font-bold tracking-wider uppercase text-sm bg-blue-100 px-4 py-1.5 rounded-full">Cara Membuat Website Masjid</span>
            <h2 class="text-3xl md:text-4xl font-extrabold text-primary mt-4">Tiga Langkah, Masjid Anda Online</h2>
        </div>

        <ol class="grid grid-cols-1 md:grid-cols-3 gap-6 md:gap-8">
            <li class="bg-white p-8 rounded-3xl shadow-soft border border-gray-100 text-center">
                <div class="w-12 h-12 bg-primary text-white rounded-full font-extrabold text-xl flex items-center justify-center mx-auto mb-6 shadow-md">1</div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Pilih Template Masjid</h3>
                <p class="text-gray-500 text-sm">Pilih desain yang paling mencerminkan karakter masjid Anda, lalu sesuaikan warna dan logonya.</p>
            </li>
            <li class="bg-white p-8 rounded-3xl shadow-soft border border-gray-100 text-center">
                <div class="w-12 h-12 bg-secondary text-white rounded-full font-extrabold text-xl flex items-center justify-center mx-auto mb-6 shadow-md">2</div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Isi Profil Masjid</h3>
                <p class="text-gray-500 text-sm">Masukkan susunan takmir, jadwal kajian, fasilitas, program donasi, dan foto kegiatan lewat formulir sederhana.</p>
            </li>
            <li class="bg-white p-8 rounded-3xl shadow-soft border border-gray-100 text-center">
                <div class="w-12 h-12 bg-primary text-white rounded-full font-extrabold text-xl flex items-center justify-center mx-auto mb-6 shadow-md">3</div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Terbitkan &amp; Bagikan</h3>
                <p class="text-gray-500 text-sm">Website masjid tayang di alamat sendiri dan siap dibagikan ke grup WhatsApp jamaah.</p>
            </li>
        </ol>
    </section>

    <!-- FAQ -->
    {{-- Native <details>, not Alpine: answers stay in the HTML for crawlers and work without JS. --}}
    <section id="faq" class="scroll-mt-28 py-16 md:py-20 container mx-auto px-4 max-w-3xl">
        <div class="text-center mb-10 md:mb-12">
            <span class="text-secondary font-bold tracking-wider uppercase text-sm bg-green-50 px-4 py-1.5 rounded-full">Pertanyaan Umum</span>
            <h2 class="text-3xl md:text-4xl font-extrabold text-gray-900 mt-4">Seputar Website Masjid</h2>
        </div>

        <div class="flex flex-col gap-3">
            @foreach ($faqs as $faq)
                <details class="group bg-softBg rounded-2xl border border-gray-100 px-5 py-4 md:px-6" @if ($loop->first) open @endif>
                    <summary class="flex items-center justify-between gap-4 cursor-pointer list-none font-bold text-gray-800 text-sm md:text-base">
                        <h3>{{ $faq['q'] }}</h3>
                        <svg class="w-5 h-5 text-primary shrink-0 transition-transform group-open:rotate-45" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                    </summary>
                    <p class="text-gray-500 text-sm leading-relaxed mt-3">{{ $faq['a'] }}</p>
                </details>
            @endforeach
        </div>
    </section>

    <!-- Closing CTA -->
    <section class="py-16 md:py-20 container mx-auto px-4 max-w-5xl">
        <div class="bg-primary text-white rounded-[3rem] px-6 py-12 md:p-16 shadow-float text-center">
            <span class="bg-white/10 text-green-300 font-bold px-4 py-1.5 rounded-full text-xs uppercase tracking-wider">Digitalisasi Masjid</span>
            <h2 class="text-3xl md:text-4xl font-extrabold mt-4 mb-4">Saatnya Masjid Anda Tampil Membanggakan</h2>
            <p class="text-blue-100 text-sm md:text-base leading-relaxed mb-8 max-w-xl mx-auto">Coba gratis dan rancang website masjid Anda sendiri sekarang. Jamaah akan bangga membagikannya.</p>
            <a href="{{ $ctaUrl }}" class="w-full sm:w-auto inline-block bg-white hover:bg-green-50 text-primary px-10 py-4 rounded-full font-bold text-center shadow-float transition-all hover:scale-105">
                Buat Website Masjid Sekarang
            </a>
        </div>
    </section>

    <x-marketing.footer />

    @include('components.support-bubble')

</body>
</html>
