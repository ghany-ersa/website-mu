<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Website-mu')</title>
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
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-softBg text-gray-800 min-h-screen lg:flex">

    @php
        $accountMenu = [
            ['route' => 'organizations.index', 'pattern' => 'organizations.index', 'label' => 'Organisasi Saya', 'icon' => 'M9 17.25v-6.75a.75.75 0 0 1 .75-.75h4.5a.75.75 0 0 1 .75.75v6.75M3 10.5 12 3l9 7.5M4.5 9.75V19.5a.75.75 0 0 0 .75.75h13.5a.75.75 0 0 0 .75-.75V9.75'],
        ];
    @endphp

    <aside class="bg-white border-b border-gray-200 lg:w-80 lg:shrink-0 lg:border-b-0 lg:border-r lg:min-h-screen lg:flex lg:flex-col">
        <div class="px-4 sm:px-6 py-4 lg:py-6">
            <a href="{{ route('organizations.index') }}" class="flex items-center">
                <img src="{{ asset('logo.png') }}" alt="Website-mu" class="h-8 w-auto">
                <span class="text-xl font-extrabold text-primary tracking-tight">website-mu<span class="text-secondary">.id</span></span>
            </a>
        </div>

        <nav class="flex lg:flex-col gap-1 px-4 sm:px-6 pb-4 lg:pb-6 overflow-x-auto lg:overflow-visible text-sm font-medium lg:flex-1">
            @foreach ($accountMenu as $item)
                <a href="{{ route($item['route']) }}"
                   class="flex items-center gap-2 px-3 py-2 rounded-lg whitespace-nowrap {{ request()->routeIs($item['pattern']) ? 'bg-primary/10 text-primary' : 'text-gray-500 hover:bg-gray-50 hover:text-primary' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}" />
                    </svg>
                    {{ $item['label'] }}
                </a>
            @endforeach

            @if (auth()->user()->is_admin)
                <a href="{{ route('admin.templates.index') }}"
                   class="flex items-center gap-2 px-3 py-2 rounded-lg whitespace-nowrap text-gray-500 hover:bg-gray-50 hover:text-primary">
                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.098 19.902a3.75 3.75 0 0 0 5.304 0l6.401-6.402M4.098 19.902a3.75 3.75 0 0 1 0-5.304l6.401-6.402m-6.401 6.402L14.802 4.5a3.75 3.75 0 1 1 5.304 5.304l-9.594 9.594" />
                    </svg>
                    Admin
                </a>
            @endif

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
