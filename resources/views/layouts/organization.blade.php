<!DOCTYPE html>
<html lang="id" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Website-mu')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#2C368B',
                        secondary: '#079C4E',
                        accent: '#F59E0B',
                        softBg: '#F8FAFC',
                    },
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    },
                    boxShadow: {
                        soft: '0 10px 40px -10px rgba(0,0,0,0.06)',
                    },
                },
            },
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3/dist/cdn.min.js"></script>
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
    </style>
</head>

<body class="bg-softBg text-gray-800 min-h-screen lg:flex">

    @php
        $orgMenu = [
            ['route' => 'organizations.show', 'pattern' => 'organizations.show', 'label' => 'Dashboard', 'icon' => 'M3.75 12l8.25-8.25L20.25 12M5.25 9.75V19.5a.75.75 0 0 0 .75.75h4.5v-6h3v6h4.5a.75.75 0 0 0 .75-.75V9.75'],
            // ['route' => 'organizations.brand.edit', 'pattern' => 'organizations.brand.*', 'label' => 'Brand Settings', 'icon' => 'M4.098 19.902a3.75 3.75 0 0 0 5.304 0l6.401-6.402M4.098 19.902a3.75 3.75 0 0 1 0-5.304l6.401-6.402m-6.401 6.402L14.802 4.5a3.75 3.75 0 1 1 5.304 5.304l-9.594 9.594'],
            // ['route' => 'organizations.edit.edit', 'pattern' => 'organizations.edit.*', 'label' => 'Edit Organisasi', 'icon' => 'M16.862 4.487 18.549 2.8a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125'],
            ['route' => 'organizations.posts.index', 'pattern' => 'organizations.posts.*', 'label' => 'Berita', 'icon' => 'M19 20H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h9l5 5v9a2 2 0 0 1-2 2ZM9 12h6M9 16h6M9 8h2'],
            ['route' => 'organizations.agendas.index', 'pattern' => 'organizations.agendas.*', 'label' => 'Agenda', 'icon' => 'M8 7V3m8 4V3M4 11h16M5 5h14a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1Z'],
            [
                'route' => 'organizations.announcements.index',
                'pattern' => 'organizations.announcements.*',
                'label' => 'Pengumuman',
                'icon' => 'M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V11a6 6 0 1 0-12 0v3.2c0 .5-.2 1-.6 1.4L4 17h5m6 0v1a3 3 0 1 1-6 0v-1m6 0H9',
            ],
            ['route' => 'organizations.gallery.index', 'pattern' => 'organizations.gallery.*', 'label' => 'Galeri', 'icon' => 'M4 16.5V6a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v10.5M4 16.5 8.5 12a1.5 1.5 0 0 1 2.1 0l1.4 1.4a1.5 1.5 0 0 0 2.1 0L17.5 10 20 12.5M4 16.5V18a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-1.5'],
            ['route' => 'organizations.officers.index', 'pattern' => 'organizations.officers.*', 'label' => 'Pengurus', 'icon' => 'M17 20h5v-2a4 4 0 0 0-3-3.87M9 20H4v-2a4 4 0 0 1 3-3.87m5-1.63a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm7-2.13a4 4 0 0 0-3-7.75M5 8.13a4 4 0 0 1 3-7.75'],
            [
                'route' => 'organizations.networks.index',
                'pattern' => 'organizations.networks.*',
                'label' => 'Jaringan AUM/Ortom',
                'icon' => 'M12 2 3 6.5v11L12 22l9-4.5v-11L12 2Zm0 0v20M3 6.5l9 4.5 9-4.5',
            ],
        ];
    @endphp

    <aside
        class="bg-white border-b border-gray-200 lg:w-64 lg:shrink-0 lg:border-b-0 lg:border-r lg:min-h-screen lg:flex lg:flex-col">
        <div class="px-4 sm:px-6 py-4 lg:py-6">
            <a href="{{ route('organizations.index') }}"
                class="font-extrabold text-primary tracking-tight text-sm sm:text-base">
                Website-mu
            </a>
            <p class="text-xs text-gray-400 mt-0.5 truncate">{{ $organization->name }}</p>
        </div>

        <nav
            class="flex lg:flex-col gap-1 px-4 sm:px-6 pb-4 lg:pb-6 overflow-x-auto lg:overflow-visible text-sm font-medium lg:flex-1">

            <a href="{{ route('organizations.index') }}"
                class="flex items-center gap-2 px-3 py-2 rounded-lg whitespace-nowrap text-gray-500 hover:bg-gray-50 hover:text-primary">
                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                </svg>
                Kembali ke Organisasi
            </a>

            @foreach ($orgMenu as $item)
                <a href="{{ route($item['route'], $organization) }}"
                    class="flex items-center gap-2 px-3 py-2 rounded-lg whitespace-nowrap {{ request()->routeIs($item['pattern']) ? 'bg-primary/10 text-primary' : 'text-gray-500 hover:bg-gray-50 hover:text-primary' }}">
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

</body>

</html>
