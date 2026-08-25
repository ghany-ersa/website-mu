<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin') — Website-mu</title>
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
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-softBg text-gray-800 min-h-screen lg:flex">

    @php
        $adminMenu = [
            ['route' => 'admin.templates.index', 'pattern' => 'admin.templates.*', 'label' => 'Template'],
            ['route' => 'admin.organizations.index', 'pattern' => 'admin.organizations.*', 'label' => 'Organisasi'],
        ];
    @endphp

    <aside class="bg-white border-b border-gray-200 lg:w-64 lg:shrink-0 lg:border-b-0 lg:border-r lg:min-h-screen lg:flex lg:flex-col">
        <div class="px-4 sm:px-6 py-4 lg:py-6">
            <a href="{{ route('admin.templates.index') }}" class="font-extrabold text-primary tracking-tight text-sm sm:text-base">
                Website-mu <span class="text-gray-400 font-medium">/ Admin</span>
            </a>
        </div>

        <nav class="flex lg:flex-col gap-1 px-4 sm:px-6 pb-4 lg:pb-6 overflow-x-auto lg:overflow-visible text-sm font-medium lg:flex-1">
            @foreach ($adminMenu as $item)
                <a href="{{ route($item['route']) }}"
                   class="px-3 py-2 rounded-lg whitespace-nowrap {{ request()->routeIs($item['pattern']) ? 'bg-primary/10 text-primary' : 'text-gray-500 hover:bg-gray-50 hover:text-primary' }}">
                    {{ $item['label'] }}
                </a>
            @endforeach

            <a href="{{ route('organizations.index') }}"
               class="px-3 py-2 rounded-lg whitespace-nowrap text-gray-500 hover:bg-gray-50 hover:text-primary">
                Organisasi Saya
            </a>

            <form action="{{ route('logout') }}" method="POST" class="lg:contents">
                @csrf
                <button type="submit" class="w-full text-left px-3 py-2 rounded-lg whitespace-nowrap text-gray-500 hover:bg-red-50 hover:text-red-500">
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

</body>
</html>
