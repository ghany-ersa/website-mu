{{--
    Exclusive hero variant (Template "Masjid Nurul Huda"): a full-bleed photo with the brand
    gradient laid over it and the copy sitting on top, mirroring the nurul-huda project's own
    home.blade.php hero (min-h-[90svh], absolute inset-0 object-cover, gradient via-primary/50
    to-secondary/55). hero/standar.blade.php is a two-column layout instead - text beside the
    image - which reads as a different site entirely, hence this variant rather than a tweak.

    CTA resolution is copied from hero/standar.blade.php so both variants honour the same
    cta_type/cta_section/cta_url/cta_wa_* content fields the builder's properties panel writes.
--}}
@php
    $content = $section['content'] ?? [];
    $organization = $organization ?? null;
    $orgName = $template->structure['sample_org_name'] ?? null
        ?? $organization->name ?? null
        ?? '[Nama Organisasi]';

    $resolveCtaHref = function (string $prefix) use ($content, $organization, $orgName) {
        $type = $content[$prefix.'_type'] ?? null;

        return match ($type) {
            'whatsapp' => \App\Services\WhatsAppNumber::href(
                $content[$prefix.'_wa_number'] ?? ($organization->whatsapp ?? null),
                str_replace('{org_name}', $orgName, $content[$prefix.'_wa_message'] ?? config("page-builder.sections.hero.defaults.{$prefix}_wa_message", ''))
            ),
            'scroll' => filled($content[$prefix.'_section'] ?? null) ? '#canvas-section-'.$content[$prefix.'_section'] : null,
            'url' => filled($content[$prefix.'_url'] ?? null) ? $content[$prefix.'_url'] : null,
            default => null,
        };
    };

    $ctaHref = $resolveCtaHref('cta');
    $ctaSecondaryHref = $resolveCtaHref('cta_secondary');
    $image = $content['image'] ?? null;
@endphp

{{-- Explicit height rather than min-h: with a portrait source photo and object-cover, a bare
     min-h let the section grow to the image's own aspect ratio - well past a screenful. --}}
<section id="top" class="relative h-[90svh] md:h-[80svh] md:max-h-[720px] flex items-end overflow-hidden bg-primary">
    @if ($image)
        <img src="{{ $image }}" alt="{{ $content['headline'] ?? $orgName }}" loading="eager"
             class="absolute inset-0 w-full h-full object-cover">
    @endif
    {{-- Transparent at the top so the photo reads, deepening toward the copy at the bottom. --}}
    <div class="absolute inset-0 bg-gradient-to-b from-transparent via-primary/45 to-primary/85"></div>

    <div class="relative w-full px-5 pt-24 pb-16 max-w-3xl mx-auto text-white">
        @if (! empty($content['badge']))
            <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/15 backdrop-blur border border-white/25 text-xs font-semibold mb-5">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                {{ $content['badge'] }}
            </span>
        @endif

        <h1 class="text-3xl sm:text-5xl font-bold leading-tight tracking-tight">
            {{ $content['headline'] ?? $orgName }}
        </h1>

        <p class="mt-3 text-base sm:text-lg text-white/90 max-w-xl">
            {{ $content['subheadline'] ?? 'Pusat ibadah dan kegiatan umat.' }}
        </p>

        <div class="mt-6 flex flex-wrap gap-3">
            @if (! empty($content['cta_label']))
                <a @if ($ctaHref) href="{{ $ctaHref }}" @endif
                   {{ $ctaHref && ($content['cta_type'] ?? null) !== 'scroll' ? 'target=_blank rel=noopener' : '' }}
                   class="inline-flex items-center gap-2 bg-white text-primary font-semibold py-3 px-6 rounded-brand shadow-lg hover:bg-slate-50 active:scale-[.98] transition">
                    {{ $content['cta_label'] }}
                </a>
            @endif
            @if (! empty($content['cta_secondary_label']))
                <a @if ($ctaSecondaryHref) href="{{ $ctaSecondaryHref }}" @endif
                   {{ $ctaSecondaryHref && ($content['cta_secondary_type'] ?? null) !== 'scroll' ? 'target=_blank rel=noopener' : '' }}
                   class="inline-flex items-center gap-2 bg-white/15 backdrop-blur border border-white/30 text-white font-semibold py-3 px-6 rounded-brand hover:bg-white/25 active:scale-[.98] transition">
                    {{ $content['cta_secondary_label'] }}
                </a>
            @endif
        </div>
    </div>
</section>
