@php
    $orgName = $section['content']['org_name']
        ?? $template->structure['sample_org_name'] ?? null
        ?? $organization->name ?? null
        ?? '[Nama Organisasi]';
    $orgLogo = $organization->logo ?? null;

    // The builder only supports a single page (see Organization::seedPagesFromTemplate()),
    // so these nav items are anchors within that same page rather than links to separate
    // pages/routes — each maps to the first visible section of that key present on the page,
    // and is only shown when such a section actually exists (a template without a Formulir
    // Kontak section, say, just doesn't get a "Kontak" nav item).
    $pageSections = isset($page) ? $page->sectionsWithFooterLast() : collect();
    $anchorFor = function (array $keys) use ($pageSections) {
        $match = $pageSections->first(fn ($s) => in_array($s->key, $keys, true) && $s->is_visible);

        return $match ? '#canvas-section-'.$match->id : null;
    };

    $homeHref = $pageSections->isNotEmpty() ? '#top' : null;
    $navItems = array_filter([
        ['label' => 'Beranda', 'href' => $homeHref],
        ['label' => 'Tentang', 'href' => $anchorFor(['tentang-organisasi', 'sambutan-ketua'])],
        ['label' => 'Berita', 'href' => $anchorFor(['daftar-berita', 'pengumuman'])],
        ['label' => 'Kontak', 'href' => $anchorFor(['formulir-kontak', 'lokasi-peta'])],
    ], fn ($item) => filled($item['href']));

    $contactHref = $anchorFor(['formulir-kontak'])
        ?? \App\Services\WhatsAppNumber::href($organization->whatsapp ?? null);
@endphp

<header id="top" class="sticky top-0 z-40 bg-white/80 backdrop-blur border-b border-gray-100">
    <div class="max-w-6xl mx-auto px-6 py-4 flex items-center justify-between">
        <a href="{{ $homeHref ?? '#top' }}" class="flex items-center gap-2">
            @if ($orgLogo)
                <img src="{{ $orgLogo }}" alt="{{ $orgName }}" class="w-9 h-9 rounded-brand object-contain bg-white">
            @else
                <div class="w-9 h-9 rounded-brand bg-primary flex items-center justify-center text-white font-bold text-sm">
                    {{ mb_substr($orgName, 0, 1) }}
                </div>
            @endif
            <span class="font-extrabold text-primary tracking-tight">{{ $orgName }}</span>
        </a>
        @if (! empty($navItems))
            <nav class="hidden md:flex items-center gap-6 text-sm font-medium text-gray-600">
                @foreach ($navItems as $item)
                    <a href="{{ $item['href'] }}" class="relative group">
                        {{ $item['label'] }}
                        <span class="absolute left-0 -bottom-1 h-0.5 w-0 bg-secondary transition-all duration-300 group-hover:w-full"></span>
                    </a>
                @endforeach
            </nav>
        @endif
        @if ($contactHref)
            <a href="{{ $contactHref }}" {{ str_starts_with($contactHref, '#') ? '' : 'target=_blank rel=noopener' }}
                class="px-4 py-2 rounded-brand bg-primary text-white text-sm font-semibold transition-transform duration-200 hover:scale-105 hover:shadow-float">
                Hubungi Kami
            </a>
        @endif
    </div>
</header>
