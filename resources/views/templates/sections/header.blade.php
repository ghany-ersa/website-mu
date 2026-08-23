@php
    $orgName = $section['content']['org_name']
        ?? $template->structure['sample_org_name'] ?? null
        ?? $organization->name ?? null
        ?? '[Nama Organisasi]';
    $orgLogo = $organization->logo ?? null;
@endphp

<header class="sticky top-0 z-40 bg-white/80 backdrop-blur border-b border-gray-100">
    <div class="max-w-6xl mx-auto px-6 py-4 flex items-center justify-between">
        <div class="flex items-center gap-2">
            @if ($orgLogo)
                <img src="{{ $orgLogo }}" alt="{{ $orgName }}" class="w-9 h-9 rounded-brand object-contain bg-white">
            @else
                <div class="w-9 h-9 rounded-brand bg-primary flex items-center justify-center text-white font-bold text-sm">
                    {{ mb_substr($orgName, 0, 1) }}
                </div>
            @endif
            <span class="font-extrabold text-primary tracking-tight">{{ $orgName }}</span>
        </div>
        <nav class="hidden md:flex items-center gap-6 text-sm font-medium text-gray-600">
            @foreach (['Beranda', 'Tentang', 'Berita', 'Kontak'] as $item)
                <span class="relative cursor-default group">
                    {{ $item }}
                    <span class="absolute left-0 -bottom-1 h-0.5 w-0 bg-secondary transition-all duration-300 group-hover:w-full"></span>
                </span>
            @endforeach
        </nav>
        <button class="px-4 py-2 rounded-brand bg-primary text-white text-sm font-semibold transition-transform duration-200 hover:scale-105 hover:shadow-float">
            Hubungi Kami
        </button>
    </div>
</header>
