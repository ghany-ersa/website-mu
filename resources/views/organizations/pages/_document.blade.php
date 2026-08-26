{{--
    Shared <html> scaffold for a fully-rendered organization page — brand colors/font/radius
    (Organization::primaryColor()/secondaryColor()/fontFamily()/borderRadius(), tokens defined
    in config('branding')), favicon, OG tags, Tailwind CDN config, reveal-scroll script.
    Used by both organizations/builder/canvas.blade.php (builder iframe) and organizations/public/
    show.blade.php (public tenant site) so the two documents can't silently drift apart. Callers
    pass $organization, $page, and a rendered $body string (the section markup to place inside
    #canvas-body) — this partial owns everything else.

    Kept in sync deliberately with templates/preview.blade.php (same brand token injection, but
    sourced from $template->structure['brand'] instead of $organization) — update both together.
--}}
@php
    $primaryColor = $organization->primaryColor();
    $secondaryColor = $organization->secondaryColor();
    $fontKey = $organization->fontFamily();
    $font = config("branding.fonts.$fontKey") ?? config('branding.fonts.'.array_key_first(config('branding.fonts')));
    $radiusToken = $organization->borderRadius();
    $radiusValue = config("branding.radii.$radiusToken") ?? config('branding.radii.'.array_key_first(config('branding.radii')));
    $heroImage = $page->sections->firstWhere('key', 'hero')?->content['image'] ?? null;
    $metaTitle = $metaTitle ?? $organization->name;
    $metaDescription = $metaDescription ?? $organization->description;
    $metaImage = $metaImage ?? $heroImage;
    $canonicalUrl = $canonicalUrl ?? url()->current();

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
    <title>{{ $metaTitle }}</title>
    @if ($metaDescription)
        <meta name="description" content="{{ $metaDescription }}">
    @endif
    <link rel="canonical" href="{{ $canonicalUrl }}">
    @if ($organization->logo)
        <link rel="icon" href="{{ $organization->logo }}">
    @endif
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $metaTitle }}">
    @if ($metaDescription)
        <meta property="og:description" content="{{ $metaDescription }}">
    @endif
    <meta property="og:url" content="{{ $canonicalUrl }}">
    @if ($metaImage)
        <meta property="og:image" content="{{ $metaImage }}">
    @endif
    <meta name="twitter:card" content="{{ $metaImage ? 'summary_large_image' : 'summary' }}">
    <meta name="twitter:title" content="{{ $metaTitle }}">
    @if ($metaDescription)
        <meta name="twitter:description" content="{{ $metaDescription }}">
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

        @if ($organization->planIsExpired() || $organization->violatesPlanRules())
            /* Header sections render `sticky top-0` (see templates/sections/header.blade.php)
               — the plan-violation banner sits fixed above it, so this pushes both the header
               and the rest of the page down by the banner's own height instead of overlapping. */
            body { padding-top: var(--plan-banner-height, 0px); }
        @endif

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
    @if ($organization->planIsExpired() || $organization->violatesPlanRules())
        {{-- Fixed (not sticky) so it stays pinned above the header section's own `sticky
             top-0` (see templates/sections/header.blade.php) rather than competing with it
             for the same scroll-anchored slot — the body's padding-top above makes room. --}}
        <div id="plan-banner" class="fixed top-0 inset-x-0 z-50 bg-gradient-to-r from-amber-500 to-orange-500 text-white shadow-md">
            <div class="max-w-6xl mx-auto flex items-center gap-2.5 px-4 py-2 text-xs sm:text-sm font-medium text-center sm:text-left">
                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                </svg>
                <span class="min-w-0">
                    @if ($organization->planIsExpired())
                        Masa aktif paket situs ini telah berakhir. Sebagian fitur mungkin perlu diperbarui oleh pengelola.
                    @elseif (! $organization->hasPaidForCurrentPlan())
                        Pembayaran paket situs ini belum dikonfirmasi. Sebagian fitur mungkin perlu diperbarui oleh pengelola.
                    @else
                        Situs ini melebihi batas paket yang berlaku. Sebagian konten mungkin perlu disesuaikan oleh pengelola.
                    @endif
                </span>
            </div>
        </div>
        <script>
            // Measures the banner's actual rendered height (varies by viewport width as the
            // text wraps) and feeds it to --plan-banner-height so the body's padding-top
            // (see the <style> block above) always clears it exactly, with no magic number.
            (function () {
                const banner = document.getElementById('plan-banner');
                const setHeight = () => document.documentElement.style.setProperty('--plan-banner-height', banner.offsetHeight + 'px');
                setHeight();
                window.addEventListener('resize', setHeight);
            })();
        </script>
    @endif

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
