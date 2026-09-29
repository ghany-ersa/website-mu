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

<section class="relative flex items-center overflow-hidden hero-gradient min-h-[clamp(30rem,62vh,34rem)] md:min-h-[clamp(34rem,72vh,42rem)]">
    @if (! empty($content['image']))
        {{-- Barely-there backdrop: it sets the place without competing with the headline. --}}
        <div class="absolute inset-0 opacity-20">
            <img src="{{ $content['image'] }}" alt="" aria-hidden="true" class="w-full h-full object-cover">
        </div>
    @endif

    {{-- 7fr/5fr rather than an even split: the portrait is capped at max-w-sm (384px) below, so
         an equal right column just pads empty space either side of it while starving the copy.
         The shell is 1120px (--shell, not 1280 - see _brand-head.blade.php), which leaves
         1024px of grid; an even split gives the copy only 512px. --}}
    <div class="relative z-10 shell section-y grid md:grid-cols-[7fr_5fr] gap-8 md:gap-12 items-center w-full">
        {{-- @container so the headline above can size itself against this column's width
             (cqi) instead of the viewport's. --}}
        <div class="@container space-y-4 sm:space-y-5 md:space-y-6">
            {{-- AppBadge's `subtle` variant in the source: a translucent gold chip with a
                 hairline border, not a plain white pill. --}}
            @if (! empty($content['badge']))
                <span class="reveal inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-secondary/20 border border-secondary/30 text-secondary font-headline font-bold uppercase text-[11px] tracking-widest">
                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M12 2l1.6 5.2L19 9l-5.4 1.8L12 16l-1.6-5.2L5 9l5.4-1.8L12 2Zm7 12 .8 2.5L22 17l-2.2.8L19 20l-.8-2.2L16 17l2.2-.7L19 14ZM6 15l.6 1.9L8.5 17l-1.9.6L6 19.5l-.6-1.9L3.5 17l1.9-.6L6 15Z" />
                    </svg>
                    {{ $content['badge'] }}
                </span>
            @endif

            {{-- Caps out at 6xl rather than the source's 7xl: an organization name is editable
                 here and most are longer than "SD Muhammadiyah 01 Ambulu", so the larger step
                 pushed common names to three lines and unbalanced the two columns. --}}
            {{-- Sized against THIS COLUMN (cqi) rather than the viewport, unlike the shared
                 `t-hero`. "SD Muhammadiyah 1 Ambulu" is 14.6em wide in Inter ExtraBold, so at
                 t-hero's 52px ceiling it needs ~737px - more than any grid ratio can give it out
                 of a 1120px shell, which is why widening the column alone never fixed this. The
                 column is a container (`@container` on the wrapper), so 6.6cqi keeps the name on
                 one line at every width and only shrinks when the column does.

                 text-balance still matters: the headline is editable, and a name longer than
                 this one must split into even lines rather than strand a trailing word. --}}
            <h1 class="reveal font-headline font-extrabold text-white text-balance text-[clamp(1.875rem,6.6cqi,3.25rem)] leading-[1.08] tracking-[-0.02em]" style="transition-delay: 80ms">
                {{ $content['headline'] ?? 'Headline Utama' }}
            </h1>

            <p class="reveal font-body t-lead text-white/90 italic measure" style="transition-delay: 160ms">
                {{ $content['subheadline'] ?? 'Subheadline yang menjelaskan organisasi secara singkat.' }}
            </p>

            {{-- Buttons go full-width side-by-side on a phone (a 44px-tall target spanning half
                 the screen is far easier to hit than an inline pill), and shrink to content
                 from `sm` up. --}}
            <div class="reveal flex flex-col sm:flex-row flex-wrap gap-3 sm:gap-4 pt-1 sm:pt-2" style="transition-delay: 240ms">
                @if (! empty($content['cta_label']))
                    <a @if ($ctaHref) href="{{ $ctaHref }}" @endif
                        {{ $ctaHref && ($content['cta_type'] ?? null) !== 'scroll' ? 'target=_blank rel=noopener' : '' }}
                        class="inline-flex items-center justify-center gap-2 w-full sm:w-auto px-5 py-3 rounded-xl bg-secondary text-white font-headline font-bold shadow-ambient-lg transition-transform duration-200 hover:scale-105">
                        {{ $content['cta_label'] }}
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-7 7 7-7 7" />
                    </svg>
                    </a>
                @endif
                @if (! empty($content['cta_secondary_label']))
                    <a @if ($ctaSecondaryHref) href="{{ $ctaSecondaryHref }}" @endif
                        {{ $ctaSecondaryHref && ($content['cta_secondary_type'] ?? null) !== 'scroll' ? 'target=_blank rel=noopener' : '' }}
                        class="inline-flex items-center justify-center w-full sm:w-auto px-5 py-3 rounded-xl border border-white/40 text-white font-headline font-bold transition-colors duration-200 hover:bg-white/10">
                        {{ $content['cta_secondary_label'] }}
                    </a>
                @endif
            </div>
        </div>

        {{-- Shown on every screen, but reshaped rather than hidden: the source drops this
             entirely below `md`, which leaves a phone with nothing but a flat colour field.
             Here it becomes a shorter landscape crop above the copy (order-first), and only
             takes the tall 4:5 portrait once there are two columns to balance. --}}
        {{-- max-h caps the portrait instead of letting the 4/5 ratio alone set its height: the
             image is `w-full`, so on a wide viewport the COLUMN width drives the height (at a
             1280px shell the column is ~592px, which at 4/5 is 740px against a section that
             maxes out at 42rem/672px). It overflowed the section, crowded the headline, and
             pushed the accreditation card past the bottom edge. Capping the height and letting
             the image center itself keeps the same crop at every width, just never taller than
             the section it sits in. mx-auto so the narrower box stays centred in its column. --}}
        <div class="relative order-first md:order-none md:mb-0 mb-2 w-full md:max-w-sm md:mx-auto">
            <div class="relative z-10 rounded-2xl overflow-hidden shadow-2xl border-4 border-white/10">
                @if ($portrait)
                    <img src="{{ $portrait }}" alt="{{ $content['headline'] ?? '' }}"
                         class="w-full aspect-[16/10] md:aspect-[4/5] md:max-h-[26rem] lg:max-h-[30rem] object-cover">
                @else
                    <div class="w-full aspect-[16/10] md:aspect-[4/5] md:max-h-[26rem] lg:max-h-[30rem] bg-white/10 flex items-center justify-center text-white/50 text-sm">Gambar</div>
                @endif
            </div>

            @if (! empty($content['highlight_title']))
                {{-- Overlapping the image's corner needs room to overlap into; on a phone there
                     is none, so it sits inline under the image instead of hanging off it. --}}
                <div class="relative md:absolute md:-bottom-8 md:-left-8 mt-3 md:mt-0 bg-white p-4 md:p-5 rounded-2xl shadow-2xl md:max-w-[17rem] z-20">
                    <div class="flex gap-3 md:gap-4 items-center">
                        <div class="w-10 h-10 rounded-full bg-secondary/15 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 text-primary" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M12 2 9.5 6.5 4.5 7l3.5 3.5-1 5 5-2.6 5 2.6-1-5L19.5 7l-5-.5L12 2Zm-4 14.5V22l4-2 4 2v-5.5l-4 2-4-2Z" />
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <p class="font-headline font-bold text-primary text-[15px] leading-tight">{{ $content['highlight_title'] }}</p>
                            @if (! empty($content['highlight_subtitle']))
                                <p class="font-body text-on-surface-variant text-xs">{{ $content['highlight_subtitle'] }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</section>
