{{--
    Standalone single-section-variant document - admin-only preview of one specific variant
    (including non-default ones), opened from admin/section-variants/index.blade.php's "Preview"
    link. Mirrors organizations/builder/section-preview.blade.php's scaffold; see that file's own
    comment for the full rationale behind the `$brand`-not-`$organization` naming discipline
    (section partials branch on isset($organization) to pull live CMS data, which would blank out
    a sample preview). Here $brand is a fresh, never-persisted Organization (see
    SectionVariantPreviewController), not a real one, since there's no organization context here.
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
    </style>
</head>
<body class="bg-white text-gray-800">
    @includeFirst([
        \App\Services\SectionVariantResolver::resolve($sectionVariant->section_key, $sectionVariant->variant_key),
        'templates.sections._missing',
    ], ['section' => $section + ['key' => $sectionVariant->section_key]])
</body>
</html>
