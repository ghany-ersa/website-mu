<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Berita &amp; Artikel - website-mu.id</title>
    <meta name="description" content="Kabar produk, tips digitalisasi, dan cerita seputar ekosistem Muhammadiyah dari tim website-mu.id.">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ url('/berita') }}">
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body { background-color: #ffffff; font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="text-gray-800 font-sans antialiased selection:bg-secondary selection:text-white">

    @php
        $ctaUrl = auth()->check() ? route('organizations.create') : route('register');
        $articleDefaultImage = 'https://images.unsplash.com/photo-1495020689067-958852a7765e?auto=format&fit=crop&w=1200&q=80';
    @endphp

    <!-- Floating Navbar -->
    <div class="fixed top-5 left-0 right-0 z-50 flex justify-center px-4">
        <nav class="bg-white/90 backdrop-blur-md shadow-soft rounded-full px-6 py-3 flex justify-between items-center w-full max-w-6xl border border-gray-100">
            <a href="{{ route('home') }}" class="flex items-center gap-2">
                <img src="{{ asset('logo.png') }}" alt="website-mu.id" class="h-11 w-auto">
                <span class="text-xl font-extrabold text-primary tracking-tight">website-mu<span class="text-secondary">.id</span></span>
            </a>
            <div class="hidden md:flex items-center gap-3">
                <a href="{{ route('home') }}" class="text-sm font-semibold text-gray-600 hover:text-primary transition">Beranda</a>
                <a href="{{ $ctaUrl }}" class="bg-primary hover:bg-secondary text-white px-5 py-2.5 rounded-full text-sm font-bold shadow-soft transition-all hover:shadow-float">
                    Buat Web Sekarang
                </a>
            </div>
        </nav>
    </div>

    <!-- Header -->
    <section class="pt-32 pb-12 px-4 max-w-6xl mx-auto">
        <span class="text-primary font-bold tracking-wider uppercase text-sm bg-blue-100 px-4 py-1.5 rounded-full">Kabar Terbaru</span>
        <h1 class="text-3xl md:text-5xl font-extrabold text-gray-900 mt-4">Berita &amp; Artikel website-mu.id</h1>
        <p class="text-gray-500 mt-4 max-w-2xl text-lg">Kabar produk, tips digitalisasi, dan cerita seputar ekosistem Muhammadiyah.</p>

        <form method="GET" class="flex flex-wrap items-center gap-3 mt-8">
            <div class="relative flex-1 min-w-[200px] max-w-sm">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4 text-gray-400 absolute left-3.5 top-1/2 -translate-y-1/2">
                    <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 1 0 0 11 5.5 5.5 0 0 0 0-11ZM2 9a7 7 0 1 1 12.452 4.391l3.328 3.329a.75.75 0 1 1-1.06 1.06l-3.329-3.328A7 7 0 0 1 2 9Z" clip-rule="evenodd" />
                </svg>
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari artikel..."
                       class="w-full pl-10 pr-4 py-2.5 rounded-full border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
            </div>
            @if ($categories->isNotEmpty())
                <select name="category" onchange="this.form.submit()"
                        class="px-4 py-2.5 rounded-full border border-gray-200 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                    <option value="">Semua Kategori</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category }}" @selected(request('category') === $category)>{{ $category }}</option>
                    @endforeach
                </select>
            @endif
            @if (request('q') || request('category'))
                <a href="{{ route('articles.index') }}" class="text-sm font-medium text-gray-400 hover:text-gray-600">Reset</a>
            @endif
        </form>
    </section>

    <!-- Article Grid -->
    <section class="pb-24 px-4 max-w-6xl mx-auto">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @forelse ($articles as $article)
                <a href="{{ route('articles.show', $article) }}" class="group bg-white rounded-[2rem] overflow-hidden shadow-soft border border-gray-100 hover:shadow-float transition-all duration-300 flex flex-col">
                    <div class="relative overflow-hidden h-48 bg-gray-100">
                        <img src="{{ $article->cover_image ?? $articleDefaultImage }}" alt="{{ $article->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                    </div>
                    <div class="p-6 flex-1 flex flex-col">
                        @if ($article->category)
                            <span class="text-secondary text-xs font-bold uppercase tracking-wider mb-2">{{ $article->category }}</span>
                        @endif
                        <h2 class="text-lg font-bold text-gray-800 leading-snug mb-2 line-clamp-2 group-hover:text-primary transition">{{ $article->title }}</h2>
                        @if ($article->excerpt)
                            <p class="text-gray-500 text-sm leading-relaxed mb-4 line-clamp-2">{{ $article->excerpt }}</p>
                        @endif
                        <p class="text-gray-400 text-xs font-semibold mt-auto">{{ $article->published_at->translatedFormat('d F Y') }}</p>
                    </div>
                </a>
            @empty
                <p class="text-gray-500 col-span-full text-center py-16">
                    @if (request('q') || request('category'))
                        Tidak ada artikel yang cocok dengan pencarian.
                    @else
                        Belum ada artikel yang diterbitkan.
                    @endif
                </p>
            @endforelse
        </div>

        <div class="mt-12">
            {{ $articles->onEachSide(1)->links() }}
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white pt-12 pb-8 rounded-t-[3rem] mx-2 md:mx-4">
        <div class="container mx-auto px-4 max-w-6xl text-center">
            <h3 class="text-2xl font-extrabold mb-2">website-mu<span class="text-secondary">.id</span></h3>
            <p class="text-gray-400 text-xs mb-8">Platform Pembuatan Website &amp; Digitalisasi Persyarikatan Muhammadiyah</p>
            <div class="border-t border-gray-800 pt-6 text-xs text-gray-500 flex flex-col md:flex-row justify-between items-center gap-4">
                <p>&copy; 2026 website-mu.id. All rights reserved.</p>
                <p>Mendorong Gerakan Dakwah Digital Berkemajuan.</p>
            </div>
        </div>
    </footer>

</body>
</html>
