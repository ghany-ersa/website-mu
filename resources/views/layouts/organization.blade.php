<!DOCTYPE html>
<html lang="id" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Website-mu')</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    @vite(['resources/css/app.css', 'resources/css/richtext.css', 'resources/js/organization.js'])
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
    </style>
</head>

<body class="bg-softBg text-gray-800 min-h-screen lg:flex">

    @php
        // Menu items with a 'section' key only show up when the organization's builder pages
        // actually contain that section - an org whose template never included e.g. galeri or
        // jaringan-aum-ortom shouldn't see a CMS menu for content it has no section to display.
        // Dashboard (and any item without a 'section' key) is always shown.
        $activeSectionKeys = $organization->pages->flatMap->sections->pluck('key')->unique();

        $orgMenu = [
            ['route' => 'organizations.show', 'pattern' => 'organizations.show', 'label' => 'Dashboard', 'icon' => 'M3.75 12l8.25-8.25L20.25 12M5.25 9.75V19.5a.75.75 0 0 0 .75.75h4.5v-6h3v6h4.5a.75.75 0 0 0 .75-.75V9.75'],
            ['route' => 'organizations.builder.edit', 'pattern' => 'organizations.builder.*', 'label' => 'Page Builder', 'icon' => 'M9.75 3.104v5.714a2.25 2.25 0 0 1-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 0 1 4.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0 1 12 15a9.065 9.065 0 0 0-6.23-.693L5 14.5m14.8.8 1.402 1.402c1.232 1.232.65 3.318-1.067 3.611A48.309 48.309 0 0 1 12 21c-2.773 0-5.491-.235-8.135-.687-1.718-.293-2.3-2.379-1.067-3.61L5 14.5'],
            ['route' => 'organizations.brand.edit', 'pattern' => 'organizations.brand.*', 'label' => 'Brand Settings', 'icon' => 'M4.098 19.902a3.75 3.75 0 0 0 5.304 0l6.401-6.402M4.098 19.902a3.75 3.75 0 0 1 0-5.304l6.401-6.402m-6.401 6.402L14.802 4.5a3.75 3.75 0 1 1 5.304 5.304l-9.594 9.594'],
            // ['route' => 'organizations.edit.edit', 'pattern' => 'organizations.edit.*', 'label' => 'Edit Organisasi', 'icon' => 'M16.862 4.487 18.549 2.8a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125'],
            [
                'route' => config('page-builder.sections.daftar-berita.cms.route'),
                'pattern' => 'organizations.posts.*',
                'label' => config('page-builder.sections.daftar-berita.cms.label'),
                'icon' => 'M19 20H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h9l5 5v9a2 2 0 0 1-2 2ZM9 12h6M9 16h6M9 8h2',
                'section' => 'daftar-berita',
            ],
            [
                'route' => config('page-builder.sections.agenda.cms.route'),
                'pattern' => 'organizations.agendas.*',
                'label' => config('page-builder.sections.agenda.cms.label'),
                'icon' => 'M8 7V3m8 4V3M4 11h16M5 5h14a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1Z',
                'section' => ['agenda', 'jadwal-kajian'],
            ],
            [
                'route' => config('page-builder.sections.pengumuman.cms.route'),
                'pattern' => 'organizations.announcements.*',
                'label' => config('page-builder.sections.pengumuman.cms.label'),
                'icon' => 'M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V11a6 6 0 1 0-12 0v3.2c0 .5-.2 1-.6 1.4L4 17h5m6 0v1a3 3 0 1 1-6 0v-1m6 0H9',
                'section' => 'pengumuman',
            ],
            [
                'route' => config('page-builder.sections.galeri.cms.route'),
                'pattern' => 'organizations.gallery.*',
                'label' => config('page-builder.sections.galeri.cms.label'),
                'icon' => 'M4 16.5V6a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v10.5M4 16.5 8.5 12a1.5 1.5 0 0 1 2.1 0l1.4 1.4a1.5 1.5 0 0 0 2.1 0L17.5 10 20 12.5M4 16.5V18a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-1.5',
                'section' => 'galeri',
            ],
            [
                'route' => config('page-builder.sections.struktur-pengurus.cms.route'),
                'pattern' => 'organizations.officers.*',
                'label' => config('page-builder.sections.struktur-pengurus.cms.label'),
                'icon' => 'M17 20h5v-2a4 4 0 0 0-3-3.87M9 20H4v-2a4 4 0 0 1 3-3.87m5-1.63a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm7-2.13a4 4 0 0 0-3-7.75M5 8.13a4 4 0 0 1 3-7.75',
                'section' => 'struktur-pengurus',
            ],
            [
                'route' => config('page-builder.sections.jaringan-aum-ortom.cms.route'),
                'pattern' => 'organizations.networks.*',
                'label' => config('page-builder.sections.jaringan-aum-ortom.cms.label'),
                'icon' => 'M12 2 3 6.5v11L12 22l9-4.5v-11L12 2Zm0 0v20M3 6.5l9 4.5 9-4.5',
                'section' => 'jaringan-aum-ortom',
            ],
            [
                'url' => route(config('page-builder.sections.program-unggulan.cms.route'), $organization).'?type=program',
                'pattern' => request()->routeIs('organizations.programs.*') && request('type') !== 'layanan',
                'label' => 'Program Unggulan',
                'icon' => 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',
                'section' => 'program-unggulan',
            ],
            [
                'url' => route(config('page-builder.sections.layanan.cms.route'), $organization).'?type=layanan',
                'pattern' => request()->routeIs('organizations.programs.*') && request('type') === 'layanan',
                'label' => config('page-builder.sections.layanan.cms.label'),
                'icon' => 'M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 1 1-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 0 0 4.486-6.336l-3.276 3.277a3.004 3.004 0 0 1-2.25-2.25l3.276-3.276a4.5 4.5 0 0 0-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437 5.87 8.293c.288.288.657.469 1.052.535m-8.03-8.03-8.03 8.03',
                'section' => 'layanan',
            ],
            [
                'route' => config('page-builder.sections.fasilitas-masjid.cms.route'),
                'pattern' => 'organizations.facilities.*',
                'label' => config('page-builder.sections.fasilitas-masjid.cms.label'),
                'icon' => 'M3 21h18M5 21V7l8-4v18M13 21V11l6 3v7M9 9h.01M9 12h.01M9 15h.01',
                'section' => 'fasilitas-masjid',
            ],
            [
                'route' => config('page-builder.sections.donasi-progress.cms.route'),
                'pattern' => 'organizations.donations.*',
                'label' => config('page-builder.sections.donasi-progress.cms.label'),
                'icon' => 'M12 8c-1.657 0-3 .672-3 1.5S10.343 11 12 11s3 .672 3 1.5-1.343 1.5-3 1.5m0-6V6m0 8v1.5m0-9.5C7.582 3 4 5.686 4 9s3.582 6 8 6 8-2.686 8-6-3.582-6-8-6Z',
                'section' => 'donasi-progress',
            ],
            [
                'route' => config('page-builder.sections.laporan-keuangan.cms.route'),
                'pattern' => 'organizations.financial-reports.*',
                'label' => config('page-builder.sections.laporan-keuangan.cms.label'),
                'icon' => 'M9 17V9m4 8V5m4 12v-3M5 21h14a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2Z',
                'section' => 'laporan-keuangan',
            ],
            [
                'route' => 'organizations.plan.edit',
                'pattern' => 'organizations.plan.*',
                'label' => 'Langganan',
                'icon' => 'M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3M4.5 19.5h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z',
            ],
        ];

        $orgMenu = array_filter(
            $orgMenu,
            // 'section' may list more than one key: two sections can be backed by the same CMS
            // (jadwal-kajian and agenda both read the `agendas` table), and the menu should show
            // if the organization has either of them.
            fn ($item) => ! isset($item['section'])
                || collect((array) $item['section'])->intersect($activeSectionKeys)->isNotEmpty()
        );
    @endphp

    <aside
        class="bg-white border-b border-gray-200 lg:w-80 lg:shrink-0 lg:border-b-0 lg:border-r lg:min-h-screen lg:flex lg:flex-col">
        <div class="px-4 sm:px-6 py-4 lg:py-6">
            <a href="{{ route('organizations.index') }}" class="flex items-center">
                <img src="{{ asset('logo.png') }}" alt="Website-mu" class="h-8 w-auto">
                <span class="text-xl font-extrabold text-primary tracking-tight">website-mu<span class="text-secondary">.id</span></span>
            </a>
            <p class="text-xs text-gray-400 mt-0.5 truncate">{{ $organization->name }}</p>
        </div>

        <nav
            class="flex lg:flex-col gap-1 px-4 sm:px-6 pb-4 lg:pb-6 overflow-x-auto lg:overflow-visible text-sm font-medium lg:flex-1">

            @unless (request('from') === 'builder')
                <a href="{{ route('organizations.index') }}"
                    class="flex items-center gap-2 px-3 py-2 rounded-lg whitespace-nowrap text-gray-500 hover:bg-gray-50 hover:text-primary">
                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                    </svg>
                    Kembali ke Organisasi
                </a>
            @endunless

            @foreach ($orgMenu as $item)
                @php
                    $href = $item['url'] ?? route($item['route'], $organization);
                    $isActive = is_bool($item['pattern']) ? $item['pattern'] : request()->routeIs($item['pattern']);
                @endphp
                <a href="{{ $href }}"
                    class="flex items-center gap-2 px-3 py-2 rounded-lg whitespace-nowrap {{ $isActive ? 'bg-primary/10 text-primary' : 'text-gray-500 hover:bg-gray-50 hover:text-primary' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}" />
                    </svg>
                    {{ $item['label'] }}
                </a>
            @endforeach

        </nav>
    </aside>

    <main class="flex-1 min-w-0 px-4 sm:px-6 py-10">
        @if (session('status'))
            <div class="mb-6 rounded-lg bg-secondary/10 border border-secondary/30 text-secondary px-4 py-3 text-sm">
                {{ session('status') }}
            </div>
        @endif

        @if (session('warning'))
            <div x-data="{ open: true }" x-show="open" x-cloak
                class="fixed inset-0 z-[100] bg-gray-900/50 backdrop-blur-sm flex items-center justify-center p-4"
                @keydown.escape.window="open = false">
                <div @click.outside="open = false"
                    class="bg-white rounded-2xl w-full max-w-sm shadow-2xl p-6 text-center">
                    <div class="w-12 h-12 rounded-full bg-amber-100 text-amber-500 flex items-center justify-center mx-auto mb-4">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                        </svg>
                    </div>
                    <h3 class="font-bold text-gray-800 mb-1">Batas Paket Tercapai</h3>
                    <p class="text-sm text-gray-500 mb-6">{{ session('warning') }}</p>
                    <div class="flex items-center justify-center gap-3">
                        <button type="button" @click="open = false"
                            class="px-4 py-2.5 rounded-full text-gray-600 text-sm font-semibold hover:bg-gray-100 transition-colors">
                            Nanti Saja
                        </button>
                        <a href="{{ route('organizations.plan.edit', $organization) }}"
                            class="px-4 py-2.5 rounded-full bg-primary text-white text-sm font-semibold hover:bg-secondary transition-colors">
                            Upgrade Paket
                        </a>
                    </div>
                </div>
            </div>
        @endif

        @if (session('error'))
            <div class="mb-6 rounded-lg bg-red-50 border border-red-200 text-red-600 px-4 py-3 text-sm">
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-6 rounded-lg bg-red-50 border border-red-200 text-red-600 px-4 py-3 text-sm">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>

    @include('partials.confirm-modal')

    {{-- Components that register Alpine.data() push their script here (see
         components/form/image-picker.blade.php), so it's defined once per page no matter how
         many instances the form renders. --}}
    @stack('scripts')

</body>

</html>
