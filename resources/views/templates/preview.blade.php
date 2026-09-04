@php
    // Each template can carry its own brand tokens (structure.brand.primary/secondary/font/
    // radius) so Ortom templates (NA, Pemuda, Tapak Suci, HW, IMM, IPM) render in their own
    // identity instead of the default Muhammadiyah blue/green. Kept in sync deliberately with
    // organizations/pages/_document.blade.php (same brand token injection, but sourced from
    // $organization instead of $template->structure['brand']) - update both together.
    $brand = $template->structure['brand'] ?? [];
    $primaryColor = $brand['primary'] ?? '#2C368B';
    $secondaryColor = $brand['secondary'] ?? '#079C4E';
    $fontKey = $brand['font'] ?? 'Plus Jakarta Sans';
    $font = config("branding.fonts.$fontKey") ?? config('branding.fonts.'.array_key_first(config('branding.fonts')));
    $radiusToken = $brand['radius'] ?? 'soft';
    $radiusValue = config("branding.radii.$radiusToken") ?? config('branding.radii.'.array_key_first(config('branding.radii')));

    $hexToRgb = function (string $hex): string {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }

        return implode(', ', array_map('hexdec', str_split($hex, 2)));
    };
    $primaryRgb = $hexToRgb($primaryColor);
@endphp
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preview - {{ $template->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3/dist/cdn.min.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '{{ $primaryColor }}',
                        secondary: '{{ $secondaryColor }}',
                        accent: '#F59E0B',
                        softBg: '#F8FAFC',
                    },
                    fontFamily: {
                        sans: [{!! collect(explode(',', $font['stack']))->map(fn ($f) => "'".trim($f, " '\"")."'")->implode(', ') !!}],
                    },
                    borderRadius: {
                        brand: '{{ $radiusValue }}',
                    },
                    boxShadow: {
                        soft: '0 10px 40px -10px rgba(0,0,0,0.06)',
                        float: '0 20px 40px -10px rgba({{ $primaryRgb }}, 0.18)',
                    },
                },
            },
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family={{ $font['google'] }}&display=swap" rel="stylesheet">
    <style>
        body { font-family: {!! $font['stack'] !!}; }

        /* Scroll-reveal: elements start hidden/offset, JS below flips them visible on entering the viewport. */
        .reveal {
            opacity: 0;
            transform: translateY(28px);
            transition: opacity 0.7s cubic-bezier(.21,.6,.35,1), transform 0.7s cubic-bezier(.21,.6,.35,1);
        }
        .reveal.reveal-visible {
            opacity: 1;
            transform: translateY(0);
        }

        @keyframes blob {
            0%, 100% { transform: translate(0, 0) scale(1); }
            33% { transform: translate(24px, -32px) scale(1.08); }
            66% { transform: translate(-18px, 18px) scale(0.95); }
        }
        .animate-blob { animation: blob 12s ease-in-out infinite; }

        @keyframes float-y {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        .animate-float { animation: float-y 4s ease-in-out infinite; }
    </style>
</head>
<body class="bg-white text-gray-800">

    {{-- Preview toolbar: not part of the template itself, only visible while previewing. Not sticky,
         so it doesn't fight the template's own header for the top of the viewport. --}}
    <div class="relative z-[60] bg-gray-900 text-white text-sm">
        <div class="max-w-6xl mx-auto px-6 py-2 flex flex-wrap items-center justify-between gap-2">
            <div class="flex items-center gap-2">
                <span class="font-semibold">{{ $template->name }}</span>
                @if ($template->is_exclusive)
                    <span class="inline-flex items-center gap-1 bg-amber-400 text-amber-950 text-xs font-bold px-2 py-0.5 rounded-full">
                        <svg class="w-3 h-3" viewBox="0 0 20 20" fill="currentColor"><path d="M10 1.5l2.39 5.51 6 .59-4.5 4.02 1.32 5.88L10 14.6l-5.21 2.9 1.32-5.88-4.5-4.02 6-.59L10 1.5z"/></svg>
                        Eksklusif
                    </span>
                @endif
                <span class="text-gray-400">— pratinjau template</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="flex items-center gap-1">
                    @foreach ($pages as $item)
                        <a href="{{ route('templates.preview', ['template' => $template->slug, 'page' => $item['slug']]) }}"
                           class="px-3 py-1 rounded-full transition-colors {{ $item['slug'] === $currentPage['slug'] ? 'bg-primary text-white' : 'bg-gray-800 text-gray-300 hover:bg-gray-700' }}">
                            {{ $item['name'] }}
                        </a>
                    @endforeach
                </div>
                @if ($template->is_exclusive)
                    <span class="px-3 py-1 rounded-full bg-gray-800 text-gray-300 text-xs" title="Template eksklusif hanya tersedia untuk organisasi dengan paket Professional">
                        Butuh paket Professional
                    </span>
                @else
                    <a href="{{ route('templates.use', $template->slug) }}"
                       class="px-3 py-1 rounded-full bg-secondary text-white font-semibold hover:opacity-90 transition-opacity">
                        Gunakan
                    </a>
                @endif
            </div>
        </div>
    </div>

    <main>
        @foreach ($currentPage['sections'] as $section)
            @includeFirst([
                \App\Services\SectionVariantResolver::resolve($section['key'], $section['variant'] ?? null),
                'templates.sections._missing',
            ], ['section' => $section, 'template' => $template])
        @endforeach
    </main>

    <script>
        // Reveal-on-scroll for any element marked .reveal (see section partials).
        const revealObserver = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('reveal-visible');
                    revealObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.15, rootMargin: '0px 0px -40px 0px' });

        document.querySelectorAll('.reveal').forEach((el) => revealObserver.observe(el));
    </script>
</body>
</html>
