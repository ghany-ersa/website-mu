{{--
    Shared <head> fragment for every organization-rendered document: the Google Fonts link and
    the design tokens that sections build on. Included by organizations/pages/_document.blade.php
    (public tenant site + builder canvas) and templates/preview.blade.php (template preview), so
    the two cannot drift - the same reason _document itself is shared.

    Callers pass:
      $font       - the resolved config('branding.fonts.*') entry (has 'stack' and 'google')
      $fontPair   - a config('branding.font_pairs') key, or null for none
      $primaryRgb - the primary color as "r, g, b", for alpha-composited surfaces

    Two things happen here that did not before:

    1. Fonts are actually LOADED. config/branding.php has always carried a 'google' spec per
       font, but nothing ever emitted a stylesheet link for it, so a font only applied when the
       visitor happened to have it installed locally and silently fell back otherwise. The link
       below fixes that for every template, not just the new ones.

    2. Surface tokens. Sections so far have only had --color-primary/--color-secondary plus raw
       Tailwind grays. The comprof-SD-Madya design leans on a Material 3 surface ramp for its
       layered off-white cards; this exposes the slice of that ramp the ported sections actually
       use, derived from the organization's own primary color so it stays on-brand rather than
       hardcoding SD-Madya's navy. Existing sections don't reference these, so nothing changes
       for them.
--}}
@php
    // Both roles collapse to the document's body font unless the template names a pairing, so
    // a section can always use var(--font-headline) without caring which template it's in.
    $pair = $fontPair ? config("branding.font_pairs.$fontPair") : null;
    $headlineStack = $pair['headline'] ?? $font['stack'];
    $bodyStack = $pair['body'] ?? $font['stack'];

    // A pairing's own faces replace the single body font's - listing both would download a
    // family nothing renders.
    $googleFamilies = $pair
        ? (array) ($pair['google'] ?? [])
        : array_filter([$font['google'] ?? null]);

    // Material Symbols is ~200KB, so only templates whose sections actually draw icons (the
    // ones that opt into a font pairing) pay for it; everything else keeps its inline SVGs.
    if ($pair) {
        $googleFamilies[] = 'Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200';
    }
@endphp
@if ($googleFamilies)
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    {{-- Unescaped on purpose: Blade's {{ }} would turn the separating & into &amp;, which
         Google Fonts answers with a 200 carrying only the FIRST family - so a pairing would
         silently lose its body font instead of failing loudly. The families are our own
         config values, not user input. --}}
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?{!! collect($googleFamilies)->map(fn ($family) => 'family='.$family)->implode('&') !!}&display=swap">
@endif
<style>
    :root {
        --font-headline: {!! $headlineStack !!};
        --font-body: {!! $bodyStack !!};

        /*
         * Material-3-style surface ramp. Each step is the page's off-white tinted by a little
         * more of the brand primary, which is what gives stacked cards their depth without
         * borders. Percentages are small on purpose: past ~8% the tint stops reading as
         * "paper" and starts looking like a colored panel.
         */
        --color-surface: color-mix(in srgb, rgb({{ $primaryRgb }}) 2%, #ffffff);
        --color-surface-container-lowest: #ffffff;
        --color-surface-container-low: color-mix(in srgb, rgb({{ $primaryRgb }}) 3%, #ffffff);
        --color-surface-container: color-mix(in srgb, rgb({{ $primaryRgb }}) 5%, #ffffff);
        --color-surface-container-high: color-mix(in srgb, rgb({{ $primaryRgb }}) 7%, #ffffff);
        --color-surface-container-highest: color-mix(in srgb, rgb({{ $primaryRgb }}) 9%, #ffffff);

        /* Text and hairlines. on-surface-variant is the muted body color used for every
           supporting paragraph; outline-variant is the near-invisible card border. */
        --color-on-surface: #191c1e;
        --color-on-surface-variant: #43474e;
        --color-outline-variant: color-mix(in srgb, rgb({{ $primaryRgb }}) 22%, #d5d8dd);

        /* A lighter and a darker step of the brand primary, for the hero gradient and for
           tinted panels that still need white text on them. */
        --color-primary-container: color-mix(in srgb, rgb({{ $primaryRgb }}) 82%, #ffffff);

        --shadow-ambient: 0 12px 32px rgba({{ $primaryRgb }}, 0.06);
        --shadow-ambient-lg: 0 12px 32px rgba({{ $primaryRgb }}, 0.12);
    }

    /*
     * The hero's diagonal wash, from the brand primary into its lighter container step -
     * SD-Madya's `hero-gradient`, but brand-derived so every organization gets its own.
     */
    .hero-gradient {
        background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-container) 100%);
    }

    /* Horizontal filter rails scroll but must not show a scrollbar over the design. */
    .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    .hide-scrollbar::-webkit-scrollbar { display: none; }

    {{-- `font-headline` / `font-body` are real Tailwind utilities, generated from the
         --font-headline / --font-body theme entries in resources/css/app.css. --}}

    /*
     * Material Symbols, the icon set the SD-Madya sections use. Loaded as a variable font so a
     * single request covers both the outlined and filled weights; `.material-symbols-filled`
     * is the same face with FILL dialled to 1 (see the x-icon component).
     */
    .material-symbols-outlined,
    .material-symbols-filled {
        font-family: 'Material Symbols Outlined';
        font-weight: normal;
        font-style: normal;
        line-height: 1;
        letter-spacing: normal;
        text-transform: none;
        display: inline-block;
        white-space: nowrap;
        word-wrap: normal;
        direction: ltr;
        -webkit-font-feature-settings: 'liga';
        -webkit-font-smoothing: antialiased;
    }
    .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
    .material-symbols-filled { font-variation-settings: 'FILL' 1, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
</style>
