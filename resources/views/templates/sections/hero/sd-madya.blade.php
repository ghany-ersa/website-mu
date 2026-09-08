{{-- Port of comprof-SD-Madya's app/pages/index.vue hero: a dark diagonal wash over a faded
     building photo, an editorial serif-italic tagline, and a portrait image on the right whose
     corner carries a floating accreditation card. The float card is desktop-only in the source
     (the whole right column is `hidden md:block`), so it stays that way here.

     Same $content contract as hero/standar - badge, headline, subheadline, the two CTAs, image -
     plus two extras this layout has room for: `portrait` (the right-hand image) and
     `highlight_title`/`highlight_subtitle` for the float card. --}}
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
            'scroll' => \App\Services\SectionAnchor::href($content[$prefix.'_section'] ?? null, $organization === null),
            'url' => filled($content[$prefix.'_url'] ?? null) ? $content[$prefix.'_url'] : null,
            default => null,
        };
    };

    $ctaHref = $resolveCtaHref('cta');
    $ctaSecondaryHref = $resolveCtaHref('cta_secondary');
    $portrait = $content['portrait'] ?? $content['image'] ?? null;
@endphp

<section class="relative flex items-center overflow-hidden hero-gradient min-h-[620px] md:min-h-[751px]">
    @if (! empty($content['image']))
        {{-- Barely-there backdrop: it sets the place without competing with the headline. --}}
        <div class="absolute inset-0 opacity-20">
            <img src="{{ $content['image'] }}" alt="" aria-hidden="true" class="w-full h-full object-cover">
        </div>
    @endif

    <div class="relative z-10 max-w-7xl mx-auto px-5 sm:px-6 py-14 sm:py-20 md:py-24 grid md:grid-cols-2 gap-10 md:gap-16 items-center w-full">
        <div class="space-y-5 sm:space-y-6 md:space-y-8">
            {{-- AppBadge's `subtle` variant in the source: a translucent gold chip with a
                 hairline border, not a plain white pill. --}}
            @if (! empty($content['badge']))
                <span class="reveal inline-flex items-center gap-2 px-4 py-2 rounded-full bg-secondary/20 border border-secondary/30 text-secondary font-headline font-bold uppercase text-xs tracking-widest">
                    <x-tenant.icon name="stars" filled class="text-sm" />
                    {{ $content['badge'] }}
                </span>
            @endif

            {{-- Caps out at 6xl rather than the source's 7xl: an organization name is editable
                 here and most are longer than "SD Muhammadiyah 01 Ambulu", so the larger step
                 pushed common names to three lines and unbalanced the two columns. --}}
            <h1 class="reveal font-headline font-extrabold text-4xl sm:text-5xl lg:text-6xl text-white leading-[1.1] tracking-tight" style="transition-delay: 80ms">
                {{ $content['headline'] ?? 'Headline Utama' }}
            </h1>

            <p class="reveal font-body text-lg md:text-2xl text-white/90 leading-relaxed italic max-w-xl" style="transition-delay: 160ms">
                {{ $content['subheadline'] ?? 'Subheadline yang menjelaskan organisasi secara singkat.' }}
            </p>

            {{-- Buttons go full-width side-by-side on a phone (a 44px-tall target spanning half
                 the screen is far easier to hit than an inline pill), and shrink to content
                 from `sm` up. --}}
            <div class="reveal flex flex-col sm:flex-row flex-wrap gap-3 sm:gap-4 pt-1 sm:pt-2" style="transition-delay: 240ms">
                @if (! empty($content['cta_label']))
                    <a @if ($ctaHref) href="{{ $ctaHref }}" @endif
                        {{ $ctaHref && ($content['cta_type'] ?? null) !== 'scroll' ? 'target=_blank rel=noopener' : '' }}
                        class="inline-flex items-center justify-center gap-2 w-full sm:w-auto px-6 py-3.5 rounded-xl bg-secondary text-white font-headline font-bold shadow-ambient-lg transition-transform duration-200 hover:scale-105">
                        {{ $content['cta_label'] }}
                        <x-tenant.icon name="arrow_forward" class="text-lg" />
                    </a>
                @endif
                @if (! empty($content['cta_secondary_label']))
                    <a @if ($ctaSecondaryHref) href="{{ $ctaSecondaryHref }}" @endif
                        {{ $ctaSecondaryHref && ($content['cta_secondary_type'] ?? null) !== 'scroll' ? 'target=_blank rel=noopener' : '' }}
                        class="inline-flex items-center justify-center w-full sm:w-auto px-6 py-3.5 rounded-xl border border-white/40 text-white font-headline font-bold transition-colors duration-200 hover:bg-white/10">
                        {{ $content['cta_secondary_label'] }}
                    </a>
                @endif
            </div>
        </div>

        {{-- Shown on every screen, but reshaped rather than hidden: the source drops this
             entirely below `md`, which leaves a phone with nothing but a flat colour field.
             Here it becomes a shorter landscape crop above the copy (order-first), and only
             takes the tall 4:5 portrait once there are two columns to balance. --}}
        <div class="relative order-first md:order-none md:mb-0 mb-2">
            <div class="relative z-10 rounded-2xl overflow-hidden shadow-2xl border-4 border-white/10">
                @if ($portrait)
                    <img src="{{ $portrait }}" alt="{{ $content['headline'] ?? '' }}"
                         class="w-full aspect-[16/10] md:aspect-[4/5] object-cover">
                @else
                    <div class="w-full aspect-[16/10] md:aspect-[4/5] bg-white/10 flex items-center justify-center text-white/50 text-sm">Gambar</div>
                @endif
            </div>

            @if (! empty($content['highlight_title']))
                {{-- Overlapping the image's corner needs room to overlap into; on a phone there
                     is none, so it sits inline under the image instead of hanging off it. --}}
                <div class="relative md:absolute md:-bottom-8 md:-left-8 mt-3 md:mt-0 bg-white p-4 md:p-6 rounded-2xl shadow-2xl md:max-w-xs z-20">
                    <div class="flex gap-3 md:gap-4 items-center">
                        <div class="w-11 h-11 md:w-12 md:h-12 rounded-full bg-secondary/15 flex items-center justify-center shrink-0">
                            <x-tenant.icon name="workspace_premium" filled class="text-primary" />
                        </div>
                        <div class="min-w-0">
                            <p class="font-headline font-bold text-primary text-base md:text-lg leading-tight">{{ $content['highlight_title'] }}</p>
                            @if (! empty($content['highlight_subtitle']))
                                <p class="font-body text-on-surface-variant text-sm">{{ $content['highlight_subtitle'] }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</section>
