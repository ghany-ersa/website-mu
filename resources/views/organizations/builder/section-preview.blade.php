{{--
    Standalone single-section document loaded into the "Tambah Section" dropdown's thumbnail
    <iframe> (organizations/builder/edit.blade.php) — shows what a section type looks like,
    rendered with its registry default content, before the user adds it to the page. Lazily
    loaded per-option only once the dropdown is opened, not on initial page load, since a page
    can offer a dozen-plus section types.

    Deliberately a lighter scaffold than organizations/pages/_document.blade.php: no meta tags,
    no reveal-on-scroll JS (the thumbnail is never scrolled), but the same brand token injection
    so the preview matches what the section will actually look like on this organization's site.

    $brand (an Organization) is used here only for brand colors/font, and deliberately kept out
    of the `organization` variable name — @include/@includeFirst inherit this whole view's data
    array, so a variable named `organization` here would leak straight into the section partial
    below. Several section partials (struktur-pengurus, program-unggulan, daftar-berita, agenda,
    pengumuman, galeri, jaringan-aum-ortom, ...) branch on isset($organization) to pull live data
    from the database instead of showing sample content — see e.g. daftar-berita.blade.php. A
    brand-new organization has none of that data yet, so leaking it in would render these
    thumbnails blank, which is exactly the "which component is this?" confusion this dropdown
    exists to prevent. Keeping it named `brand` makes every partial fall back to its built-in
    placeholder/dummy content instead, matching how templates/preview.blade.php (the template
    gallery's own preview, which never has an $organization at all) already renders.
--}}
@php
    $primaryColor = $brand->primaryColor();
    $secondaryColor = $brand->secondaryColor();
    $fontKey = $brand->fontFamily();
    $font = config("branding.fonts.$fontKey") ?? config('branding.fonts.'.array_key_first(config('branding.fonts')));
    $radiusToken = $brand->borderRadius();
    $radiusValue = config("branding.radii.$radiusToken") ?? config('branding.radii.'.array_key_first(config('branding.radii')));
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
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
                        float: '0 20px 40px -10px rgba(0,0,0,0.12)',
                    },
                },
            },
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family={{ $font['google'] }}&display=swap" rel="stylesheet">
    <style>
        body { font-family: {!! $font['stack'] !!}; }
        /* No scroll-reveal here — the thumbnail is captured statically, so .reveal elements
           must render at their final, visible state rather than starting hidden/offset. */
        .reveal { opacity: 1 !important; transform: none !important; }
        .animate-blob, .animate-float { animation: none !important; }
    </style>
</head>
<body class="bg-white text-gray-800">
    @includeFirst([
        \App\Services\SectionVariantResolver::resolve($key),
        'templates.sections._missing',
    ], ['section' => $section + ['key' => $key]])
</body>
</html>
