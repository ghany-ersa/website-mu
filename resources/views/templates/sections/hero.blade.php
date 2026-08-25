@php
    $content = $section['content'] ?? [];
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
@endphp

<section class="relative overflow-hidden bg-softBg">
    <div class="absolute -top-24 -left-24 w-72 h-72 rounded-full bg-primary/10 blur-3xl animate-blob"></div>
    <div class="absolute -bottom-24 -right-24 w-72 h-72 rounded-full bg-secondary/10 blur-3xl animate-blob" style="animation-delay: 2s"></div>

    <div class="relative max-w-6xl mx-auto px-6 py-20 grid md:grid-cols-2 gap-12 items-center">
        <div>
            @if (! empty($content['badge']))
                <span class="reveal inline-block px-3 py-1 rounded-brand bg-primary/10 text-primary text-xs font-semibold mb-4">
                    {{ $content['badge'] }}
                </span>
            @endif

            <h1 class="reveal text-4xl md:text-5xl font-extrabold text-primary leading-tight mb-4" style="transition-delay: 80ms">
                {{ $content['headline'] ?? 'Headline Utama' }}
            </h1>

            <p class="reveal max-w-xl text-lg text-gray-600 mb-8" style="transition-delay: 160ms">
                {{ $content['subheadline'] ?? 'Subheadline yang menjelaskan organisasi secara singkat.' }}
            </p>

            <div class="reveal flex flex-wrap gap-3" style="transition-delay: 240ms">
                @if (! empty($content['cta_label']))
                    @if ($ctaHref)
                        <a href="{{ $ctaHref }}" {{ ($content['cta_type'] ?? null) !== 'scroll' ? 'target=_blank rel=noopener' : '' }}
                            class="px-6 py-3 rounded-brand bg-secondary text-white font-semibold shadow-float transition-transform duration-200 hover:scale-105">
                            {{ $content['cta_label'] }}
                        </a>
                    @else
                        <button type="button" class="px-6 py-3 rounded-brand bg-secondary text-white font-semibold shadow-float transition-transform duration-200 hover:scale-105">
                            {{ $content['cta_label'] }}
                        </button>
                    @endif
                @endif
                @if (! empty($content['cta_secondary_label']))
                    @if ($ctaSecondaryHref)
                        <a href="{{ $ctaSecondaryHref }}" {{ ($content['cta_secondary_type'] ?? null) !== 'scroll' ? 'target=_blank rel=noopener' : '' }}
                            class="px-6 py-3 rounded-brand border border-gray-300 text-gray-700 font-semibold transition-colors duration-200 hover:border-primary hover:text-primary">
                            {{ $content['cta_secondary_label'] }}
                        </a>
                    @else
                        <button type="button" class="px-6 py-3 rounded-brand border border-gray-300 text-gray-700 font-semibold transition-colors duration-200 hover:border-primary hover:text-primary">
                            {{ $content['cta_secondary_label'] }}
                        </button>
                    @endif
                @endif
            </div>
        </div>

        <div class="reveal relative" style="transition-delay: 120ms">
            <div class="animate-float rounded-brand overflow-hidden shadow-float">
                @if (! empty($content['image']))
                    <img src="{{ $content['image'] }}" alt="{{ $content['headline'] ?? '' }}" class="w-full aspect-[4/3] object-cover">
                @else
                    <div class="w-full aspect-[4/3] bg-gray-100 flex items-center justify-center text-gray-400 text-sm">Gambar</div>
                @endif
            </div>
        </div>
    </div>
</section>
