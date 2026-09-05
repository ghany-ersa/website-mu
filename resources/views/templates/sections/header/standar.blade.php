@php
    $orgName = $section['content']['org_name']
        ?? $template->structure['sample_org_name'] ?? null
        ?? $organization->name ?? null
        ?? '[Nama Organisasi]';
    $orgLogo = $organization->logo ?? null;

    // Two nav modes, picked by how many pages the organization actually has:
    //
    //  - Multi-page: one link per OrganizationPage, so every page the visitor can reach is
    //    reachable. Page links win over anchors because a section anchor on another page
    //    would scroll nowhere.
    //  - Single page (still the common case): anchors within that page, each mapping to the
    //    first visible section of that key, and only shown when such a section exists (a
    //    template without a Formulir Kontak section just doesn't get a "Kontak" item).
    $pageSections = isset($page) ? $page->sectionsInDisplayOrder() : collect();
    $anchorFor = function (array $keys) use ($pageSections) {
        $match = $pageSections->first(fn ($s) => in_array($s->key, $keys, true) && $s->is_visible);

        return $match ? '#canvas-section-'.$match->id : null;
    };

    $pages = $organization->pages ?? collect();
    $homeHref = $pageSections->isNotEmpty() ? '#top' : null;

    if ($pages->count() > 1) {
        $navItems = $pages
            ->map(fn ($navPage) => [
                'label' => $navPage->name,
                'href' => \App\Services\TenantPageUrl::for($organization, $navPage),
                'active' => isset($page) && $navPage->slug === $page->slug,
            ])
            ->filter(fn ($item) => filled($item['href']))
            ->values()
            ->all();
    } else {
        $navItems = array_filter([
            ['label' => 'Beranda', 'href' => $homeHref],
            ['label' => 'Tentang', 'href' => $anchorFor(['tentang-organisasi', 'sambutan-ketua'])],
            ['label' => 'Berita', 'href' => $anchorFor(['daftar-berita', 'pengumuman'])],
            ['label' => 'Kontak', 'href' => $anchorFor(['formulir-kontak', 'lokasi-peta'])],
        ], fn ($item) => filled($item['href']));
    }

    $contactHref = $anchorFor(['formulir-kontak'])
        ?? \App\Services\WhatsAppNumber::href($organization->whatsapp ?? null);

    // On a multi-page site the wordmark goes to the home page, not to this page's own top.
    $brandHref = $pages->count() > 1
        ? (\App\Services\TenantPageUrl::for($organization, $pages->firstWhere('is_home', true)) ?? $homeHref ?? '#top')
        : ($homeHref ?? '#top');
@endphp

<header id="top" class="sticky top-0 z-40 bg-primary backdrop-blur" x-data="{ open: false }">
    <div class="max-w-6xl mx-auto px-6 py-4 flex items-center justify-between gap-3">
        <a href="{{ $brandHref }}" class="flex items-center gap-2">
            @if ($orgLogo)
                <img src="{{ $orgLogo }}" alt="{{ $orgName }}" class="w-9 h-9 rounded-brand object-contain">
            @else
                <div class="w-9 h-9 rounded-brand bg-white flex items-center justify-center text-primary font-bold text-sm">
                    {{ mb_substr($orgName, 0, 1) }}
                </div>
            @endif
            <span class="font-extrabold text-white tracking-tight">{{ $orgName }}</span>
        </a>
        @if (! empty($navItems))
            <nav class="hidden md:flex items-center gap-6 text-sm font-medium text-white/80">
                @foreach ($navItems as $item)
                    <a href="{{ $item['href'] }}" @class([
                        'relative group transition-colors',
                        'text-white' => $item['active'] ?? false,
                        'hover:text-white' => ! ($item['active'] ?? false),
                    ])>
                        {{ $item['label'] }}
                        <span @class([
                            'absolute left-0 -bottom-1 h-0.5 bg-secondary transition-all duration-300',
                            'w-full' => $item['active'] ?? false,
                            'w-0 group-hover:w-full' => ! ($item['active'] ?? false),
                        ])></span>
                    </a>
                @endforeach
            </nav>
        @endif
        <div class="flex items-center gap-2">
            @if ($contactHref)
                <a href="{{ $contactHref }}" {{ str_starts_with($contactHref, '#') ? '' : 'target=_blank rel=noopener' }}
                    class="hidden sm:inline-block px-4 py-2 rounded-brand bg-white text-primary text-sm font-semibold transition-transform duration-200 hover:scale-105 hover:shadow-float">
                    Hubungi Kami
                </a>
            @endif
            @if (! empty($navItems))
                <button
                    @click="open = !open"
                    type="button"
                    class="md:hidden relative w-9 h-9 flex items-center justify-center rounded-brand text-white hover:bg-white/10 transition-colors"
                    aria-label="Buka menu"
                    :aria-expanded="open"
                >
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path x-show="!open" stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                        <path x-show="open" x-cloak stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            @endif
        </div>
    </div>

    @if (! empty($navItems))
        <nav
            x-show="open"
            x-cloak
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 -translate-y-1"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-100"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click.outside="open = false"
            @click="open = false"
            class="md:hidden border-t border-white/10 px-6 pb-4 pt-2 flex flex-col gap-1 text-sm font-medium"
        >
            @foreach ($navItems as $item)
                <a href="{{ $item['href'] }}" @class([
                    'px-3 py-2.5 rounded-brand transition-colors',
                    'bg-white/10 text-white' => $item['active'] ?? false,
                    'text-white/80 hover:bg-white/10 hover:text-white' => ! ($item['active'] ?? false),
                ])>
                    {{ $item['label'] }}
                </a>
            @endforeach
            @if ($contactHref)
                <a href="{{ $contactHref }}" {{ str_starts_with($contactHref, '#') ? '' : 'target=_blank rel=noopener' }}
                    class="sm:hidden mt-1 px-3 py-2.5 rounded-brand bg-white text-primary text-center font-semibold">
                    Hubungi Kami
                </a>
            @endif
        </nav>
    @endif
</header>
