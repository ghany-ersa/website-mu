<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin') — Website-mu</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
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
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3/dist/cdn.min.js"></script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-softBg text-gray-800 min-h-screen lg:flex">

    @php
        $adminMenu = [
            ['route' => 'admin.templates.index', 'pattern' => 'admin.templates.*', 'label' => 'Template', 'icon' => 'M9 3v18M4 3h16a1 1 0 0 1 1 1v16a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1Z'],
            ['route' => 'admin.organizations.index', 'pattern' => 'admin.organizations.*', 'label' => 'Organisasi', 'icon' => 'M3 21h18M5 21V7l7-4 7 4v14M9 9h1m4 0h1m-6 4h1m4 0h1m-6 4h1m4 0h1'],
            ['route' => 'admin.plans.index', 'pattern' => 'admin.plans.*', 'label' => 'Paket Langganan', 'icon' => 'M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 0 1-.825-.242m9.345-8.334a2.126 2.126 0 0 0-.476-.095 48.64 48.64 0 0 0-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0 0 11.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155',
            ],
            ['route' => 'admin.plan-change-requests.index', 'pattern' => 'admin.plan-change-requests.*', 'label' => 'Permintaan Paket', 'icon' => 'M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z'],
            ['route' => 'admin.discount-codes.index', 'pattern' => 'admin.discount-codes.*', 'label' => 'Kode Diskon', 'icon' => 'M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.169.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z M6 6h.008v.008H6V6Z'],
        ];
    @endphp

    <aside class="bg-white border-b border-gray-200 lg:w-80 lg:shrink-0 lg:border-b-0 lg:border-r lg:min-h-screen lg:flex lg:flex-col">
        <div class="px-4 sm:px-6 py-4 lg:py-6">
            <a href="{{ route('admin.templates.index') }}" class="flex items-center gap-2">
                <img src="{{ asset('logo.png') }}" alt="Website-mu" class="h-8 w-auto">
                <span class="text-xl font-extrabold text-primary tracking-tight">website-mu<span class="text-secondary">.id</span></span>
                <span class="text-gray-400 font-medium text-sm sm:text-base">/ Admin</span>
            </a>
        </div>

        <nav class="flex lg:flex-col gap-1 px-4 sm:px-6 pb-4 lg:pb-6 overflow-x-auto lg:overflow-visible text-sm font-medium lg:flex-1">
            @foreach ($adminMenu as $item)
                <a href="{{ route($item['route']) }}"
                   class="flex items-center gap-2 px-3 py-2 rounded-lg whitespace-nowrap {{ request()->routeIs($item['pattern']) ? 'bg-primary/10 text-primary' : 'text-gray-500 hover:bg-gray-50 hover:text-primary' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}" />
                    </svg>
                    {{ $item['label'] }}
                </a>
            @endforeach

            <a href="{{ route('organizations.index') }}"
               class="flex items-center gap-2 px-3 py-2 rounded-lg whitespace-nowrap text-gray-500 hover:bg-gray-50 hover:text-primary">
                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v-6.75a.75.75 0 0 1 .75-.75h4.5a.75.75 0 0 1 .75.75v6.75M3 10.5 12 3l9 7.5M4.5 9.75V19.5a.75.75 0 0 0 .75.75h13.5a.75.75 0 0 0 .75-.75V9.75" />
                </svg>
                Organisasi Saya
            </a>

            <form action="{{ route('logout') }}" method="POST" class="lg:contents">
                @csrf
                <button type="submit" class="flex items-center gap-2 w-full text-left px-3 py-2 rounded-lg whitespace-nowrap text-gray-500 hover:bg-red-50 hover:text-red-500">
                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 9V5.25A2.25 2.25 0 0 1 10.5 3h6a2.25 2.25 0 0 1 2.25 2.25v13.5A2.25 2.25 0 0 1 16.5 21h-6a2.25 2.25 0 0 1-2.25-2.25V15M12 9l-3 3m0 0 3 3m-3-3h12.75" />
                    </svg>
                    Keluar
                </button>
            </form>
        </nav>
    </aside>

    <main class="flex-1 min-w-0 px-4 sm:px-6 py-10">
        @if (session('status'))
            <div class="mb-6 rounded-lg bg-secondary/10 border border-secondary/30 text-secondary px-4 py-3 text-sm">
                {{ session('status') }}
            </div>
        @endif

        @yield('content')
    </main>

    @include('partials.confirm-modal')

</body>
</html>
