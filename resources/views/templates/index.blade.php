<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Katalog Template - website-mu.id</title>
    <meta name="description" content="Jelajahi semua template website organisasi Muhammadiyah: Muhammadiyah, Aisyiyah, Klinik/Rumah Sakit, dan Media/Portal Berita.">
    <link rel="canonical" href="{{ route('templates.index') }}">
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-white text-gray-800 font-sans antialiased selection:bg-secondary selection:text-white">

    <div class="fixed top-5 left-0 right-0 z-50 flex justify-center px-4">
        <nav class="bg-white/90 backdrop-blur-md shadow-soft rounded-full px-6 py-3 flex justify-between items-center w-full max-w-6xl border border-gray-100">
            <a href="{{ url('/') }}" class="flex items-center gap-2">
                <img src="{{ asset('logo.png') }}" alt="website-mu.id" class="h-11 w-auto">
                <span class="text-xl font-extrabold text-primary tracking-tight">website-mu<span class="text-secondary">.id</span></span>
            </a>
            <a href="{{ url('/') }}" class="text-sm font-semibold text-gray-600 hover:text-primary transition">&larr; Kembali ke Beranda</a>
        </nav>
    </div>

    <section class="pt-36 pb-20 max-w-7xl mx-auto px-4">
        <div class="text-center max-w-2xl mx-auto mb-12">
            <span class="text-primary font-bold tracking-wider uppercase text-sm bg-blue-100 px-4 py-1.5 rounded-full">Katalog Template</span>
            <h1 class="text-3xl md:text-4xl font-extrabold text-gray-900 mt-4">Semua Template Website Organisasi</h1>
            {{-- Names the organization types that actually have a seeded template today (see
                 OrganizationTypeSeeder) - the earlier copy listed AUM Pendidikan and Masjid,
                 which a visitor would then look for in vain. --}}
            <p class="text-gray-500 mt-3 text-sm">{{ $templates->count() }} template tersedia untuk Muhammadiyah, Aisyiyah, Klinik/Rumah Sakit, dan Media/Portal Berita.</p>
        </div>

        <div class="flex flex-wrap justify-center gap-2 mb-12">
            <a href="{{ route('templates.index') }}"
               class="px-4 py-2 rounded-full text-sm font-semibold transition {{ ! request('organization_type_id') ? 'bg-primary text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                Semua
            </a>
            @foreach ($organizationTypes as $type)
                <a href="{{ route('templates.index', ['organization_type_id' => $type->id]) }}"
                   class="px-4 py-2 rounded-full text-sm font-semibold transition {{ request('organization_type_id') == $type->id ? 'bg-primary text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                    {{ $type->name }}
                </a>
            @endforeach
        </div>

        @php
            // Stock-photo fallback for a template with no thumbnail uploaded yet
            // (Admin\TemplateController) - real templates take priority via
            // $template->thumbnailUrl() below.
            $defaultImage = 'https://images.unsplash.com/photo-1497215728101-856f4ea42174?auto=format&fit=crop&w=800&q=80';
        @endphp

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @forelse ($templates as $template)
                <div class="relative bg-white rounded-[2.5rem] overflow-hidden shadow-soft border {{ $template->is_exclusive ? 'border-amber-300 ring-2 ring-amber-300/50' : 'border-gray-100' }} group hover:shadow-float transition-all duration-300 flex flex-col">
                    @if ($template->is_exclusive)
                        <span class="absolute top-4 right-4 z-10 flex items-center gap-1.5 bg-gradient-to-r from-amber-400 to-amber-500 text-white text-xs font-extrabold px-3 py-1.5 rounded-full shadow-md">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 20 20" fill="currentColor"><path d="M10 1.5l2.39 5.51 6 .59-4.5 4.02 1.32 5.88L10 14.6l-5.21 2.9 1.32-5.88-4.5-4.02 6-.59L10 1.5z"/></svg>
                            Eksklusif
                        </span>
                    @endif
                    <a href="{{ route('templates.preview', $template->slug) }}" class="relative overflow-hidden h-56 bg-gray-100 block">
                        <img src="{{ $template->thumbnailUrl() ?? $defaultImage }}" alt="Template {{ $template->name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        <span class="absolute top-4 left-4 bg-primary text-white text-xs font-bold px-3 py-1 rounded-full">{{ $template->organizationType->name ?? $template->name }}</span>
                    </a>
                    <div class="p-6 flex-1 flex flex-col justify-between">
                        <div>
                            <h3 class="text-xl font-bold text-gray-800 mb-2">
                                <a href="{{ route('templates.preview', $template->slug) }}" class="hover:text-primary transition">{{ $template->name }}</a>
                            </h3>
                            <a href="{{ route('templates.preview', $template->slug) }}" class="block text-gray-500 text-sm mb-6 hover:text-gray-700 transition">{{ $template->description }}</a>
                        </div>
                        <div class="flex items-center justify-between border-t border-gray-100 pt-4 gap-2">
                            @if ($template->is_exclusive)
                                <a href="{{ route('templates.preview', $template->slug) }}" target="_blank" class="text-primary hover:text-secondary px-3 py-2 rounded-xl text-xs font-bold transition">Lihat Preview</a>
                            @else
                                <a href="{{ route('templates.preview', $template->slug) }}" target="_blank" class="text-primary hover:text-secondary px-3 py-2 rounded-xl text-xs font-bold transition">Lihat Preview</a>
                                <a href="{{ route('templates.use', $template->slug) }}" class="bg-primary hover:bg-secondary text-white px-4 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap">Gunakan Template</a>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-gray-500 col-span-full text-center">Tidak ada template untuk kategori ini.</p>
            @endforelse
        </div>
    </section>

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
</body>
</html>
