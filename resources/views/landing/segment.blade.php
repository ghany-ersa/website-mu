{{--
    Shared layout for every per-segment SEO landing page. All copy comes from
    config/landing-pages.php (see its header for the copy rules); this view only decides
    structure, so the segments stay visually consistent and can't drift apart.
--}}
@php
    $canonicalUrl = route("landing.$segment");

    // Guests go to register, signed-in users to the template picker - same rule as welcome.blade.php.
    $ctaUrl = auth()->check() ? route('organizations.template-picker') : route('register');
    $navCtaLabel = auth()->check() ? 'Buat '.$page['name'] : 'Coba Gratis';

    $catalogUrl = $organizationType
        ? route('templates.index', ['organization_type_id' => $organizationType->id])
        : route('templates.index');

    $heroTemplate = $templates->firstWhere('is_exclusive', true) ?? $templates->first();
    $heroImage = $heroTemplate?->thumbnailUrl() ?? asset('home.jpeg');

    $features = $page['features']['items'];
    $hasExclusiveFeature = collect($features)->contains('exclusive', true);
    $faqs = $page['faq']['items'];

    // Heroicons (outline, 24px) path data, keyed by the `icon` names config/landing-pages.php uses.
    $icons = [
        'report' => 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
        'heart' => 'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z',
        'calculator' => 'M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z',
        'building' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
        'calendar' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
        'book' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253',
        'megaphone' => 'M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z',
        'map' => 'M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0zM15 11a3 3 0 11-6 0 3 3 0 016 0z',
        'star' => 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z',
        'user' => 'M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z',
        'chat' => 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z',
        'users' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z',
        'photo' => 'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z',
        'clock' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
        'clipboard' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01',
        'sparkles' => 'M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z',
        'academic' => 'M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z',
        'newspaper' => 'M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z',
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
                [$atType => 'ListItem', 'position' => 2, 'name' => $page['name'], 'item' => $canonicalUrl],
            ],
        ],
    ];
@endphp
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $page['meta_title'] }}</title>
    <meta name="description" content="{{ $page['meta_description'] }}">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ $canonicalUrl }}">
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">

    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ $canonicalUrl }}">
    <meta property="og:site_name" content="website-mu.id">
    <meta property="og:locale" content="id_ID">
    <meta property="og:title" content="{{ $page['meta_title'] }}">
    <meta property="og:description" content="{{ $page['meta_description'] }}">
    <meta property="og:image" content="{{ asset('og-image.jpg') }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $page['meta_title'] }}">
    <meta name="twitter:description" content="{{ $page['meta_description'] }}">
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
                @if ($templates->isNotEmpty())
                    <a href="#template" class="hover:text-primary transition">Template</a>
                @endif
                <a href="#faq" class="hover:text-primary transition">FAQ</a>
            </div>

            <div class="hidden md:flex items-center gap-3">
                @guest
                    <a href="{{ route('login') }}" class="text-sm font-semibold text-gray-600 hover:text-primary transition px-2">Masuk</a>
                @endguest
                <a href="{{ $ctaUrl }}" class="bg-primary hover:bg-secondary text-white px-5 py-2.5 rounded-full text-sm font-bold shadow-soft transition-all hover:shadow-float">
                    {{ $navCtaLabel }}
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
                @if ($templates->isNotEmpty())
                    <a href="#template" x-on:click="mobileOpen = false" class="hover:text-primary transition">Template</a>
                @endif
                <a href="#faq" x-on:click="mobileOpen = false" class="hover:text-primary transition">FAQ</a>
                @guest
                    <a href="{{ route('login') }}" class="hover:text-primary transition">Masuk</a>
                @endguest
                <a href="{{ $ctaUrl }}" class="bg-primary hover:bg-secondary text-white px-5 py-3 rounded-full text-center font-bold shadow-soft transition-all">
                    {{ $navCtaLabel }}
                </a>
            </div>
        </nav>
    </div>

    <!-- Hero -->
    <section class="pt-32 pb-16 px-4 max-w-7xl mx-auto">
        <nav aria-label="Breadcrumb" class="text-xs text-gray-400 font-semibold mb-6 md:px-16">
            <a href="{{ route('home') }}" class="hover:text-primary transition">website-mu.id</a>
            <span class="mx-1.5">/</span>
            <span class="text-gray-600">{{ $page['name'] }}</span>
        </nav>

        <div class="md:px-16 grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
            <div class="lg:col-span-7 text-center lg:text-left">
                <span class="inline-flex items-center gap-2 bg-green-100 text-secondary px-4 py-1.5 rounded-full text-xs md:text-sm font-bold mb-6">
                    <span class="w-2 h-2 rounded-full bg-secondary animate-pulse"></span>
                    {{ $page['hero']['badge'] }}
                </span>

                <h1 class="text-4xl md:text-6xl font-extrabold text-gray-900 leading-[1.15] mb-6">
                    {{ $page['hero']['title'] }} <span class="text-gradient">{{ $page['hero']['highlight'] }}</span>
                </h1>

                <p class="text-gray-600 text-lg md:text-xl leading-relaxed mb-8 max-w-2xl mx-auto lg:mx-0">
                    {{ $page['hero']['body'] }}
                </p>

                <div class="flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-4">
                    <a href="{{ $ctaUrl }}" class="w-full sm:w-auto bg-primary hover:bg-secondary text-white px-8 py-4 rounded-full font-bold text-center shadow-float transition-all hover:scale-105">
                        {{ $page['hero']['primary_cta'] }}
                    </a>
                    <a href="{{ $templates->isNotEmpty() ? '#template' : $catalogUrl }}" class="w-full sm:w-auto bg-white hover:bg-gray-50 text-gray-700 border border-gray-200 px-8 py-4 rounded-full font-bold text-center transition-all shadow-sm">
                        {{ $page['hero']['secondary_cta'] }}
                    </a>
                </div>

                <div class="mt-12 pt-8 border-t border-gray-200/60 grid grid-cols-3 gap-4 text-center lg:text-left">
                    <div>
                        <p class="text-2xl md:text-3xl font-extrabold text-primary">{{ count($features) }}+</p>
                        <p class="text-xs md:text-sm text-gray-500 font-medium">{{ $page['hero']['stat_label'] }}</p>
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
                        <span class="ml-2 text-xs font-mono text-gray-400 bg-gray-50 px-3 py-1 rounded-full w-full text-center truncate">{{ $page['hero']['mockup_url'] }}</span>
                    </div>
                    <img src="{{ $heroImage }}" alt="{{ $page['hero']['image_alt'] }}" class="rounded-2xl w-full object-cover object-top shadow-sm h-64 md:h-80">

                    <div class="absolute -bottom-6 -left-2 sm:-left-6 bg-white p-4 rounded-2xl shadow-soft border border-gray-100 flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-secondary/10 text-secondary flex items-center justify-center font-bold">✓</div>
                        <div>
                            <p class="text-xs text-gray-400 font-bold uppercase">{{ $page['hero']['floating_label'] }}</p>
                            <p class="text-sm font-bold text-gray-800">{{ $page['hero']['floating_text'] }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Why -->
    <section class="py-16 md:py-20 container mx-auto px-4 max-w-6xl">
        <div class="text-center mb-12 md:mb-16">
            <span class="text-secondary font-bold tracking-wider uppercase text-sm bg-green-50 px-4 py-1.5 rounded-full">{{ $page['why']['badge'] }}</span>
            <h2 class="text-3xl md:text-5xl font-extrabold text-primary mt-4">{{ $page['why']['title'] }}</h2>
            <p class="text-gray-500 mt-4 max-w-2xl mx-auto text-base md:text-lg">{{ $page['why']['body'] }}</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 md:gap-8">
            @foreach ($page['why']['points'] as $point)
                <div class="bg-softBg p-8 rounded-3xl border border-gray-100">
                    <p class="text-4xl font-extrabold {{ $loop->even ? 'text-secondary' : 'text-primary' }} mb-4">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</p>
                    <h3 class="text-xl font-bold text-gray-800 mb-3">{{ $point['title'] }}</h3>
                    <p class="text-gray-500 leading-relaxed text-sm">{{ $point['body'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    <!-- Features -->
    <section id="fitur" class="scroll-mt-28 py-16 md:py-20 bg-softBg mx-2 px-4">
        <div class="max-w-6xl mx-auto">
            <div class="text-center mb-12 md:mb-16">
                <span class="text-primary font-bold tracking-wider uppercase text-sm bg-blue-100 px-4 py-1.5 rounded-full">{{ $page['features']['badge'] }}</span>
                <h2 class="text-3xl md:text-4xl font-extrabold text-gray-900 mt-4">{{ $page['features']['title'] }}</h2>
                <p class="text-gray-500 mt-4 max-w-2xl mx-auto text-sm md:text-base">{{ $page['features']['body'] }}</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 md:gap-6">
                @foreach ($features as $feature)
                    <div class="bg-white p-6 rounded-3xl border border-gray-100 shadow-soft flex flex-col">
                        <div class="flex items-start justify-between gap-3 mb-5">
                            <div class="w-12 h-12 {{ $loop->odd ? 'bg-primary' : 'bg-secondary' }} text-white rounded-2xl flex items-center justify-center shadow-md shrink-0">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icons[$feature['icon']] }}"></path></svg>
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

            @if ($hasExclusiveFeature)
                <p class="text-center text-xs text-gray-400 mt-8">Fitur bertanda <span class="font-bold text-amber-600">Eksklusif</span> tersedia pada paket Infak Eksklusif.</p>
            @endif
        </div>
    </section>

    <!-- Templates -->
    @if ($templates->isNotEmpty())
        <section id="template" class="scroll-mt-28 py-16 md:py-20 container mx-auto px-4 max-w-6xl">
            <div class="flex flex-col md:flex-row md:items-end justify-between mb-10 md:mb-12 gap-4">
                <div>
                    <span class="text-secondary font-bold tracking-wider uppercase text-sm bg-green-50 px-4 py-1.5 rounded-full">{{ $page['templates']['badge'] }}</span>
                    <h2 class="text-3xl md:text-4xl font-extrabold text-gray-900 mt-4">{{ $page['templates']['title'] }}</h2>
                </div>
                <p class="text-gray-500 max-w-md text-sm">{{ $page['templates']['body'] }}</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 md:gap-8">
                @foreach ($templates as $template)
                    <div class="relative bg-white rounded-3xl overflow-hidden shadow-soft border {{ $template->is_exclusive ? 'border-amber-300 ring-2 ring-amber-300/50' : 'border-gray-100' }} group hover:shadow-float transition-all duration-300 flex flex-col">
                        @if ($template->is_exclusive)
                            <span class="absolute top-4 right-4 z-10 flex items-center gap-1.5 bg-gradient-to-r from-amber-400 to-amber-500 text-white text-xs font-extrabold px-3 py-1.5 rounded-full shadow-md">
                                <svg class="w-3.5 h-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M10 1.5l2.39 5.51 6 .59-4.5 4.02 1.32 5.88L10 14.6l-5.21 2.9 1.32-5.88-4.5-4.02 6-.59L10 1.5z"/></svg>
                                Eksklusif
                            </span>
                        @endif
                        <a href="{{ route('templates.preview', $template->slug) }}" class="relative overflow-hidden h-56 md:h-64 bg-gray-100 block">
                            <img src="{{ $template->thumbnailUrl() ?? asset('home.jpeg') }}" alt="Template {{ strtolower($page['name']) }} {{ $template->name }}" loading="lazy" class="w-full h-full object-cover object-top group-hover:scale-105 transition-transform duration-500">
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
                    {{ $page['templates']['link_label'] }}
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" /></svg>
                </a>
            </div>
        </section>
    @endif

    <!-- Steps -->
    <section class="py-16 md:py-20 container mx-auto px-4 max-w-6xl">
        <div class="text-center mb-12 md:mb-16">
            <span class="text-primary font-bold tracking-wider uppercase text-sm bg-blue-100 px-4 py-1.5 rounded-full">{{ $page['steps']['badge'] }}</span>
            <h2 class="text-3xl md:text-4xl font-extrabold text-primary mt-4">{{ $page['steps']['title'] }}</h2>
        </div>

        <ol class="grid grid-cols-1 md:grid-cols-3 gap-6 md:gap-8">
            @foreach ($page['steps']['items'] as $step)
                <li class="bg-white p-8 rounded-3xl shadow-soft border border-gray-100 text-center">
                    <div class="w-12 h-12 {{ $loop->even ? 'bg-secondary' : 'bg-primary' }} text-white rounded-full font-extrabold text-xl flex items-center justify-center mx-auto mb-6 shadow-md">{{ $loop->iteration }}</div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">{{ $step['title'] }}</h3>
                    <p class="text-gray-500 text-sm">{{ $step['body'] }}</p>
                </li>
            @endforeach
        </ol>
    </section>

    <!-- FAQ -->
    {{-- Native <details>, not Alpine: answers stay in the HTML for crawlers and work without JS. --}}
    <section id="faq" class="scroll-mt-28 py-16 md:py-20 container mx-auto px-4 max-w-3xl">
        <div class="text-center mb-10 md:mb-12">
            <span class="text-secondary font-bold tracking-wider uppercase text-sm bg-green-50 px-4 py-1.5 rounded-full">Pertanyaan Umum</span>
            <h2 class="text-3xl md:text-4xl font-extrabold text-gray-900 mt-4">{{ $page['faq']['title'] }}</h2>
        </div>

        <div class="flex flex-col gap-3">
            @foreach ($faqs as $faq)
                <details class="group bg-softBg rounded-2xl border border-gray-100 px-5 py-4 md:px-6" @if ($loop->first) open @endif>
                    <summary class="flex items-center justify-between gap-4 cursor-pointer list-none font-bold text-gray-800 text-sm md:text-base">
                        <h3>{{ $faq['q'] }}</h3>
                        <svg class="w-5 h-5 text-primary shrink-0 transition-transform group-open:rotate-45" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                    </summary>
                    <p class="text-gray-500 text-sm leading-relaxed mt-3">{{ $faq['a'] }}</p>
                </details>
            @endforeach
        </div>
    </section>

    <!-- Closing CTA -->
    <section class="py-16 md:py-20 container mx-auto px-4 max-w-5xl">
        <div class="bg-primary text-white rounded-[3rem] px-6 py-12 md:p-16 shadow-float text-center">
            <span class="bg-white/10 text-green-300 font-bold px-4 py-1.5 rounded-full text-xs uppercase tracking-wider">{{ $page['cta']['badge'] }}</span>
            <h2 class="text-3xl md:text-4xl font-extrabold mt-4 mb-4">{{ $page['cta']['title'] }}</h2>
            <p class="text-blue-100 text-sm md:text-base leading-relaxed mb-8 max-w-xl mx-auto">{{ $page['cta']['body'] }}</p>
            <a href="{{ $ctaUrl }}" class="w-full sm:w-auto inline-block bg-white hover:bg-green-50 text-primary px-10 py-4 rounded-full font-bold text-center shadow-float transition-all hover:scale-105">
                {{ $page['hero']['primary_cta'] }}
            </a>
        </div>
    </section>

    <x-marketing.footer />

    @include('components.support-bubble')

</body>
</html>
