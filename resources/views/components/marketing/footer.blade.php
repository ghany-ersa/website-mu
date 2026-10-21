{{-- Shared footer for website-mu.id marketing pages (landing, articles index/show) --}}
<footer class="bg-gray-900 text-white pt-12 pb-8 mx-2">
    <div class="container mx-auto px-4 max-w-6xl text-center">
        <h3 class="text-2xl font-extrabold mb-2">website-mu<span class="text-secondary">.id</span></h3>
        <p class="text-gray-400 text-xs mb-6">Platform Pembuatan Website &amp; Digitalisasi Persyarikatan Muhammadiyah</p>

        {{-- Crawlable internal links to the per-segment landing pages: a page linked only from the
             sitemap ranks poorly, so every marketing page points here through this shared footer. --}}
        <nav aria-label="Solusi" class="flex flex-wrap justify-center gap-x-6 gap-y-2 text-sm font-semibold text-gray-300 mb-8">
            @foreach (config('landing-pages.segments') as $key => $segment)
                <a href="{{ route("landing.$key") }}" class="hover:text-secondary transition">{{ $segment['name'] }}</a>
            @endforeach
            <a href="{{ route('templates.index') }}" class="hover:text-secondary transition">Katalog Template</a>
            <a href="{{ route('articles.index') }}" class="hover:text-secondary transition">Berita &amp; Artikel</a>
        </nav>

        <div class="border-t border-gray-800 pt-6 text-xs text-gray-500 flex flex-col md:flex-row justify-between items-center gap-4">
            <p>&copy; {{ date('Y') }} website-mu.id. All rights reserved.</p>
            <p>Created by Suara Muhammadiyah Ambulu</p>
        </div>
    </div>
</footer>
