{{--
    Shared shell for every HTTP error page, styled to match the marketing landing page
    (welcome.blade.php): floating pill navbar, big rounded card with blurred brand-color
    orbs, gradient headline. Child views fill in the per-status copy via @section.

    Deliberately standalone rather than extending layouts/app.blade.php: error pages are
    rendered for guests and for tenant-domain requests (the 'tenant' middleware group has
    no session), so they must not touch auth()/session state.

    Sections:
      code      - big status number (also used as the <title> prefix)
      title     - short headline
      message   - one-paragraph explanation, in plain Indonesian
      actions   - optional extra buttons; a "back home" button is always rendered
--}}
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('code') &middot; @yield('title') - website-mu.id</title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    @vite(['resources/css/app.css'])
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .text-gradient {
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-image: linear-gradient(135deg, #2C368B 0%, #079C4E 100%);
        }
    </style>
</head>
<body class="bg-white text-gray-800 font-sans antialiased selection:bg-secondary selection:text-white min-h-screen flex flex-col">

    {{-- Floating navbar, trimmed to the logo: an error page has nothing to navigate to. --}}
    <div class="pt-5 px-4 flex justify-center">
        <nav class="bg-white/90 backdrop-blur-md shadow-soft rounded-full px-6 py-3 flex justify-center items-center w-full max-w-6xl border border-gray-100">
            <a href="{{ url('/') }}" class="flex items-center gap-2">
                <img src="{{ asset('logo.png') }}" alt="website-mu.id" class="h-9 md:h-11 w-auto">
                <span class="text-lg md:text-xl font-extrabold text-primary tracking-tight">website-mu<span class="text-secondary">.id</span></span>
            </a>
        </nav>
    </div>

    <main class="flex-1 flex items-center px-4 py-12 md:py-20">
        <div class="w-full max-w-4xl mx-auto">
            <div class="bg-gradient-to-br from-softBg via-white to-green-50/30 rounded-[2.5rem] md:rounded-[3rem] p-8 sm:p-12 md:p-16 border border-gray-100 relative overflow-hidden text-center">
                {{-- Same decorative blurred orbs as the landing hero. --}}
                <div class="absolute -top-24 -right-24 w-72 h-72 md:w-96 md:h-96 bg-primary/5 rounded-full blur-3xl pointer-events-none"></div>
                <div class="absolute -bottom-24 -left-24 w-72 h-72 md:w-96 md:h-96 bg-secondary/10 rounded-full blur-3xl pointer-events-none"></div>

                <div class="relative z-10">
                    <span class="inline-block text-primary font-bold tracking-wider uppercase text-xs bg-blue-100 px-4 py-1.5 rounded-full">
                        @yield('badge', 'Ada Kendala')
                    </span>

                    <p class="text-gradient text-7xl sm:text-8xl md:text-9xl font-extrabold leading-none mt-6 tracking-tight">
                        @yield('code')
                    </p>

                    <h1 class="text-2xl sm:text-3xl md:text-4xl font-extrabold text-gray-900 mt-4">
                        @yield('title')
                    </h1>

                    <p class="text-gray-500 text-sm md:text-base leading-relaxed mt-4 max-w-xl mx-auto">
                        @yield('message')
                    </p>

                    <div class="mt-8 flex flex-col sm:flex-row gap-3 justify-center">
                        @yield('actions')
                        <a href="{{ url('/') }}" class="bg-primary hover:bg-secondary text-white px-6 py-3.5 rounded-full text-sm font-bold shadow-soft transition-all hover:shadow-float">
                            Kembali ke Beranda
                        </a>
                    </div>

                    <p class="text-xs text-gray-400 mt-8">
                        Butuh bantuan?
                        <a href="https://wa.me/6285183220977?text={{ urlencode('Assalamualaikum, saya menemukan error '.trim($__env->yieldContent('code')).' di website-mu.id.') }}"
                           target="_blank" rel="noopener"
                           class="text-secondary font-bold hover:underline">Hubungi kami via WhatsApp</a>
                    </p>
                </div>
            </div>
        </div>
    </main>

    <footer class="pb-8 px-4 text-center">
        <p class="text-xs text-gray-400">&copy; {{ date('Y') }} website-mu.id &middot; Platform Digitalisasi Persyarikatan Muhammadiyah</p>
    </footer>

</body>
</html>
