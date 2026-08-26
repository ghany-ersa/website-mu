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
<body class="bg-softBg text-gray-800 min-h-screen">

    <header class="bg-white/80 backdrop-blur-md border-b border-gray-200 sticky top-0 z-40" x-data="{ open: false }">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 py-3.5 flex items-center justify-between gap-3">
            <a href="{{ route('organizations.index') }}" class="flex items-center">
                <img src="{{ asset('logo.png') }}" alt="Website-mu" class="h-9 w-auto">
                <span class="text-xl font-extrabold text-primary tracking-tight">website-mu<span class="text-secondary">.id</span></span>
            </a>

            <nav class="hidden md:flex items-center gap-5 text-sm font-medium">
                @auth
                    <a href="{{ route('organizations.index') }}" class="text-gray-600 hover:text-primary transition-colors">Organisasi Saya</a>
                    <a href="{{ route('organizations.create') }}" class="text-gray-600 hover:text-primary transition-colors">+ Buat Organisasi</a>
                    @if (auth()->user()->is_admin)
                        <a href="{{ route('admin.templates.index') }}" class="text-gray-600 hover:text-primary transition-colors">Admin</a>
                    @endif
                    <span class="h-4 w-px bg-gray-200"></span>
                    <span class="text-gray-500">{{ auth()->user()->name }}</span>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="text-gray-600 hover:text-red-500 transition-colors">Keluar</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="text-gray-600 hover:text-primary transition-colors">Masuk</a>
                    <a href="{{ route('register') }}" class="px-4 py-2 rounded-full bg-primary text-white hover:opacity-90 transition-opacity">Daftar</a>
                @endauth
            </nav>

            <button
                @click="open = !open"
                type="button"
                class="md:hidden relative w-9 h-9 flex items-center justify-center rounded-full text-gray-600 hover:bg-gray-100 transition-colors"
                aria-label="Buka menu"
                :aria-expanded="open"
            >
                <svg class="w-5 h-5 transition-transform duration-200" :class="{ 'rotate-90': open }" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path x-show="!open" stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                    <path x-show="open" x-cloak stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

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
            class="md:hidden border-t border-gray-100 bg-white px-4 pb-4 pt-2 flex flex-col gap-1 text-sm font-medium"
        >
            @auth
                <span class="px-3 py-2 text-xs uppercase tracking-wide text-gray-400">{{ auth()->user()->name }}</span>
                <a href="{{ route('organizations.index') }}" class="px-3 py-2.5 rounded-lg text-gray-600 hover:bg-gray-50 hover:text-primary transition-colors">Organisasi Saya</a>
                <a href="{{ route('organizations.create') }}" class="px-3 py-2.5 rounded-lg text-gray-600 hover:bg-gray-50 hover:text-primary transition-colors">+ Buat Organisasi</a>
                @if (auth()->user()->is_admin)
                    <a href="{{ route('admin.templates.index') }}" class="px-3 py-2.5 rounded-lg text-gray-600 hover:bg-gray-50 hover:text-primary transition-colors">Admin</a>
                @endif
                <form action="{{ route('logout') }}" method="POST" class="mt-1">
                    @csrf
                    <button type="submit" class="w-full text-left px-3 py-2.5 rounded-lg text-red-500 hover:bg-red-50 transition-colors">Keluar</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="px-3 py-2.5 rounded-lg text-gray-600 hover:bg-gray-50 hover:text-primary transition-colors">Masuk</a>
                <a href="{{ route('register') }}" class="mt-1 px-3 py-2.5 rounded-full bg-primary text-white text-center hover:opacity-90 transition-opacity">Daftar</a>
            @endauth
        </nav>
    </header>

    <main class="max-w-5xl mx-auto px-4 sm:px-6 py-10">
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
