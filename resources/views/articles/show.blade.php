<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $article->title }} - website-mu.id</title>
    <meta name="description" content="{{ $article->excerpt ?? Str::limit(strip_tags((string) $article->body), 160) }}">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ route('articles.show', $article) }}">
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">

    <!-- Open Graph -->
    <meta property="og:type" content="article">
    <meta property="og:url" content="{{ route('articles.show', $article) }}">
    <meta property="og:site_name" content="website-mu.id">
    <meta property="og:title" content="{{ $article->title }}">
    <meta property="og:description" content="{{ $article->excerpt ?? Str::limit(strip_tags((string) $article->body), 160) }}">
    @if ($article->cover_image)
        <meta property="og:image" content="{{ $article->cover_image }}">
    @endif

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body { background-color: #ffffff; font-family: 'Plus Jakarta Sans', sans-serif; }
        .prose :where(h2, h3) { color: #1f2937; font-weight: 800; margin-top: 1.75em; margin-bottom: 0.6em; }
        .prose p { margin-top: 1.1em; margin-bottom: 1.1em; line-height: 1.8; color: #374151; }
        .prose a { color: #2C368B; font-weight: 600; text-decoration: underline; }
        .prose blockquote { border-left: 4px solid #079C4E; padding-left: 1.25rem; font-style: italic; color: #4b5563; }
        .prose ul, .prose ol { margin-top: 1em; margin-bottom: 1em; padding-left: 1.5rem; }
        .prose ul { list-style: disc; }
        .prose ol { list-style: decimal; }
    </style>
</head>
<body class="text-gray-800 font-sans antialiased selection:bg-secondary selection:text-white">

    @php $ctaUrl = auth()->check() ? route('organizations.create') : route('register'); @endphp

    <!-- Floating Navbar -->
    <div class="fixed top-5 left-0 right-0 z-50 flex justify-center px-4">
        <nav class="bg-white/90 backdrop-blur-md shadow-soft rounded-full px-6 py-3 flex justify-between items-center w-full max-w-6xl border border-gray-100">
            <a href="{{ route('home') }}" class="flex items-center gap-2">
                <img src="{{ asset('logo.png') }}" alt="website-mu.id" class="h-11 w-auto">
                <span class="text-xl font-extrabold text-primary tracking-tight">website-mu<span class="text-secondary">.id</span></span>
            </a>
            <div class="hidden md:flex items-center gap-3">
                <a href="{{ route('articles.index') }}" class="text-sm font-semibold text-gray-600 hover:text-primary transition">Berita</a>
                <a href="{{ $ctaUrl }}" class="bg-primary hover:bg-secondary text-white px-5 py-2.5 rounded-full text-sm font-bold shadow-soft transition-all hover:shadow-float">
                    Buat Web Sekarang
                </a>
            </div>
        </nav>
    </div>

    <article class="pt-32 pb-20 px-4 max-w-3xl mx-auto">
        <a href="{{ route('articles.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-gray-500 hover:text-primary transition mb-8">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
            Kembali ke Berita
        </a>

        @if ($article->category)
            <span class="text-secondary text-xs font-bold uppercase tracking-wider bg-green-50 px-3 py-1.5 rounded-full">{{ $article->category }}</span>
        @endif
        <h1 class="text-3xl md:text-4xl font-extrabold text-gray-900 mt-4 leading-tight">{{ $article->title }}</h1>
        <p class="text-gray-400 text-sm font-semibold mt-4">{{ $article->published_at->translatedFormat('d F Y') }}@if ($article->author) &middot; {{ $article->author->name }} @endif</p>

        @if ($article->cover_image)
            <div class="rounded-[2rem] overflow-hidden mt-8 aspect-video bg-gray-100">
                <img src="{{ $article->cover_image }}" alt="{{ $article->title }}" class="w-full h-full object-cover">
            </div>
        @endif

        <div class="prose max-w-none mt-10 text-base">
            {!! $article->body !!}
        </div>
    </article>

    @if ($related->isNotEmpty())
        <section class="pb-24 px-4 max-w-6xl mx-auto">
            <h2 class="text-xl font-extrabold text-gray-900 mb-8">Artikel Terkait</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                @foreach ($related as $relatedArticle)
                    <a href="{{ route('articles.show', $relatedArticle) }}" class="group bg-white rounded-[2rem] overflow-hidden shadow-soft border border-gray-100 hover:shadow-float transition-all duration-300 flex flex-col">
                        <div class="relative overflow-hidden h-40 bg-gray-100">
                            <img src="{{ $relatedArticle->cover_image ?? 'https://images.unsplash.com/photo-1495020689067-958852a7765e?auto=format&fit=crop&w=1200&q=80' }}" alt="{{ $relatedArticle->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        </div>
                        <div class="p-5">
                            <h3 class="text-base font-bold text-gray-800 leading-snug line-clamp-2 group-hover:text-primary transition">{{ $relatedArticle->title }}</h3>
                            <p class="text-gray-400 text-xs font-semibold mt-2">{{ $relatedArticle->published_at->translatedFormat('d F Y') }}</p>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

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
