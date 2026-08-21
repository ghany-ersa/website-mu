{{--
    Shared <html> scaffold for a fully-rendered organization page — brand colors (Organization::
    primaryColor()/secondaryColor()), favicon, OG tags, Tailwind CDN config, reveal-scroll script.
    Used by both organizations/builder/canvas.blade.php (builder iframe) and organizations/public/
    show.blade.php (public tenant site) so the two documents can't silently drift apart. Callers
    pass $organization, $page, and a rendered $body string (the section markup to place inside
    #canvas-body) — this partial owns everything else.
--}}
@php
    $primaryColor = $organization->primaryColor();
    $secondaryColor = $organization->secondaryColor();
    $heroImage = $page->sections->firstWhere('key', 'hero')?->content['image'] ?? null;

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
    <title>{{ $organization->name }}</title>
    @if ($organization->logo)
        <link rel="icon" href="{{ $organization->logo }}">
    @endif
    <meta property="og:title" content="{{ $organization->name }}">
    @if ($heroImage)
        <meta property="og:image" content="{{ $heroImage }}">
    @endif
    <script src="https://cdn.tailwindcss.com"></script>
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
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    },
                    boxShadow: {
                        soft: '0 10px 40px -10px rgba(0,0,0,0.06)',
                        float: '0 20px 40px -10px rgba({{ $primaryRgb }}, 0.18)',
                    },
                },
            },
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }

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
    {{-- The builder swaps this div's innerHTML after an AJAX section save/reorder (see
         swapCanvas() in organizations/builder/edit.blade.php) — the reveal-observer script
         below deliberately sits outside it, so re-rendering the sections doesn't wipe out
         the running IntersectionObserver along with them. Public rendering doesn't use this
         swap mechanism but keeps the same id so this scaffold stays identical either way. --}}
    <div id="canvas-body">
        {!! $body !!}
    </div>

    <script>
        const revealObserver = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('reveal-visible');
                    revealObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.15, rootMargin: '0px 0px -40px 0px' });

        // Exposed globally so the parent builder page can re-run it after swapping
        // #canvas-body's content in place (new elements need to be (re-)observed —
        // ones already marked .reveal-visible are harmlessly re-queried and skipped).
        window.observeRevealElements = () => {
            document.querySelectorAll('.reveal:not(.reveal-visible)').forEach((el) => revealObserver.observe(el));
        };
        window.observeRevealElements();
    </script>
</body>
</html>
