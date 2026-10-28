{{-- Port of comprof-SD-Madya's CtaSection.vue: not a full-bleed band like cta/standar, but a
     dark rounded card inset inside the page, with two soft blurred blobs bleeding off its
     corners and the buttons centred beneath the copy. The inset is the point - it reads as a
     closing card rather than another page section. --}}
@php
    $content = $section['content'] ?? [];
    $organization = $organization ?? null;
    $orgName = $template->structure['sample_org_name'] ?? null
        ?? $organization->name ?? null
        ?? '[Nama Organisasi]';

    $resolveCtaHref = function (string $prefix) use ($content, $organization, $orgName) {
        return match ($content[$prefix.'_type'] ?? null) {
            'whatsapp' => \App\Services\WhatsAppNumber::href(
                $content[$prefix.'_wa_number'] ?? ($organization->whatsapp ?? null),
                str_replace('{org_name}', $orgName, $content[$prefix.'_wa_message'] ?? '')
            ),
            'scroll' => \App\Services\SectionAnchor::href($content[$prefix.'_section'] ?? null, $organization === null),
            'url' => \App\Services\SafeUrl::href($content[$prefix.'_url'] ?? null),
            default => null,
        };
    };

    $ctaHref = $resolveCtaHref('cta');
    $ctaSecondaryHref = $resolveCtaHref('cta_secondary');
@endphp

<section class="section-y bg-surface">
    <div class="shell">
        <div class="reveal bg-primary rounded-2xl p-6 sm:p-9 md:p-12 text-center relative overflow-hidden">
            <div aria-hidden="true" class="absolute top-0 right-0 w-56 h-56 bg-secondary opacity-20 rounded-full blur-3xl -mr-32 -mt-32"></div>
            <div aria-hidden="true" class="absolute bottom-0 left-0 w-56 h-56 bg-white opacity-10 rounded-full blur-3xl -ml-32 -mb-32"></div>

            <div class="relative z-10 max-w-xl mx-auto space-y-4 sm:space-y-5">
                <h2 class="font-headline font-bold t-h2 text-white">
                    {{ $content['title'] ?? 'Judul Ajakan' }}
                </h2>

                @if (! empty($content['subtitle']))
                    <p class="font-body t-body text-white/80">{{ $content['subtitle'] }}</p>
                @endif

                <div class="flex flex-col sm:flex-row gap-3 justify-center pt-1">
                    @if (! empty($content['cta_label']))
                        <a @if ($ctaHref) href="{{ $ctaHref }}" @endif
                            {{ $ctaHref && ($content['cta_type'] ?? null) !== 'scroll' ? 'target=_blank rel=noopener' : '' }}
                            class="inline-flex items-center justify-center w-full sm:w-auto px-6 py-3 rounded-xl bg-secondary text-white font-headline font-bold shadow-ambient-lg transition-transform duration-200 hover:scale-105">
                            {{ $content['cta_label'] }}
                        </a>
                    @endif
                    @if (! empty($content['cta_secondary_label']))
                        <a @if ($ctaSecondaryHref) href="{{ $ctaSecondaryHref }}" @endif
                            {{ $ctaSecondaryHref && ($content['cta_secondary_type'] ?? null) !== 'scroll' ? 'target=_blank rel=noopener' : '' }}
                            class="inline-flex items-center justify-center w-full sm:w-auto px-6 py-3 rounded-xl border border-white/40 text-white font-headline font-bold transition-colors duration-200 hover:bg-white/10">
                            {{ $content['cta_secondary_label'] }}
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>
