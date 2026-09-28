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
@endphp

<section class="relative overflow-hidden bg-softBg">
    <div class="absolute -top-20 -right-20 w-64 h-64 md:w-80 md:h-80 rounded-full bg-secondary/[0.07] blur-3xl"></div>

    <div class="relative max-w-6xl mx-auto px-6 pt-14 pb-16 md:py-28 grid md:grid-cols-[0.95fr_1.05fr] gap-10 md:gap-16 items-center">
        <div>
            @if (! empty($content['badge']))
                <div class="reveal flex items-center gap-3 mb-5 md:mb-6">
                    <span class="w-8 md:w-10 h-px bg-secondary"></span>
                    <span class="text-secondary text-[11px] md:text-xs font-semibold tracking-[0.2em] uppercase">{{ $content['badge'] }}</span>
                </div>
            @endif

            <h1 class="reveal text-[2rem] leading-[1.12] md:text-5xl lg:text-6xl md:leading-[1.08] text-primary tracking-tight mb-4 md:mb-6" style="transition-delay: 80ms">
                {{ $content['headline'] ?? 'Headline Utama' }}
            </h1>

            <p class="reveal max-w-md text-base md:text-lg text-gray-600 leading-relaxed mb-8 md:mb-10" style="transition-delay: 160ms">
                {{ $content['subheadline'] ?? 'Subheadline yang menjelaskan organisasi secara singkat.' }}
            </p>

            {{-- Tombol melebar penuh di mobile (target sentuh sekaligus kesan rapi), kembali
                 menyesuaikan lebar teks mulai sm. --}}
            <div class="reveal flex flex-col sm:flex-row sm:flex-wrap items-stretch sm:items-center gap-3 sm:gap-6" style="transition-delay: 240ms">
                @if (! empty($content['cta_label']))
                    @if ($ctaHref)
                        <a href="{{ $ctaHref }}" {{ ($content['cta_type'] ?? null) !== 'scroll' ? 'target=_blank rel=noopener' : '' }}
                            class="px-7 py-4 sm:py-3.5 rounded-brand bg-primary text-white text-sm font-semibold tracking-wide text-center shadow-soft transition-all duration-200 hover:bg-secondary hover:-translate-y-0.5">
                            {{ $content['cta_label'] }}
                        </a>
                    @else
                        <button type="button" class="px-7 py-4 sm:py-3.5 rounded-brand bg-primary text-white text-sm font-semibold tracking-wide text-center shadow-soft transition-all duration-200 hover:bg-secondary hover:-translate-y-0.5">
                            {{ $content['cta_label'] }}
                        </button>
                    @endif
                @endif
                @if (! empty($content['cta_secondary_label']))
                    @if ($ctaSecondaryHref)
                        <a href="{{ $ctaSecondaryHref }}" {{ ($content['cta_secondary_type'] ?? null) !== 'scroll' ? 'target=_blank rel=noopener' : '' }}
                            class="group inline-flex items-center justify-center sm:justify-start gap-2 py-1 text-sm font-semibold text-primary tracking-wide transition-colors hover:text-secondary">
                            {{ $content['cta_secondary_label'] }}
                            <span class="transition-transform duration-200 group-hover:translate-x-1">&rarr;</span>
                        </a>
                    @else
                        <button type="button" class="group inline-flex items-center justify-center sm:justify-start gap-2 py-1 text-sm font-semibold text-primary tracking-wide transition-colors hover:text-secondary">
                            {{ $content['cta_secondary_label'] }}
                            <span class="transition-transform duration-200 group-hover:translate-x-1">&rarr;</span>
                        </button>
                    @endif
                @endif
            </div>
        </div>

        <div class="reveal relative" style="transition-delay: 120ms">
            {{-- Bingkai digeser ke dalam di mobile (inset, bukan offset negatif) supaya aksennya
                 tetap ada tanpa menembus lebar viewport; mulai md baru bergeser keluar. --}}
            <div class="absolute -top-4 -left-4 right-5 bottom-5 md:-top-4 md:-right-4 md:bottom-8 md:left-8 border border-secondary/30 rounded-brand"></div>
            {{-- Rasio lebih tinggi di mobile: 16/10 yang pipih membuat foto kehilangan bobot saat
                 kolom runtuh jadi selebar layar. --}}
            <div class="relative aspect-[4/3] md:aspect-[16/10] rounded-brand overflow-hidden shadow-soft ring-1 ring-black/5">
                @if (! empty($content['image']))
                    <img src="{{ $content['image'] }}" alt="{{ $content['headline'] ?? '' }}" loading="eager"
                         class="w-full h-full object-cover">
                @else
                    <div class="w-full h-full bg-gray-100 flex items-center justify-center text-gray-400 text-sm">Gambar</div>
                @endif
            </div>
        </div>
    </div>
</section>
