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
@endphp

<section class="relative overflow-hidden bg-gray-900 h-[420px] md:h-[540px] flex items-end">
    @if (! empty($content['image']))
        <img src="{{ $content['image'] }}" alt="{{ $content['headline'] ?? '' }}" class="absolute inset-0 w-full h-full object-cover">
    @endif
    <div class="absolute inset-0 bg-gradient-to-t from-gray-900 via-gray-900/70 to-gray-900/10"></div>

    <div class="relative max-w-6xl mx-auto px-6 pb-10 md:pb-14 w-full">
        <div class="max-w-2xl">
            @if (! empty($content['badge']))
                <span class="reveal inline-flex items-center gap-2 px-3 py-1 rounded-full bg-secondary text-white text-xs font-bold uppercase tracking-wide mb-4">
                    {{ $content['badge'] }}
                </span>
            @endif

            <h1 class="reveal text-4xl md:text-6xl font-extrabold text-white leading-[1.1] mb-4" style="transition-delay: 80ms">
                {{ $content['headline'] ?? 'Judul Berita Utama' }}
            </h1>

            <p class="reveal text-gray-200 leading-relaxed mb-6" style="transition-delay: 160ms">
                {{ $content['subheadline'] ?? 'Ringkasan singkat berita utama yang sedang menjadi sorotan.' }}
            </p>

            @if (! empty($content['cta_label']))
                @if ($ctaHref)
                    <a href="{{ $ctaHref }}" {{ ($content['cta_type'] ?? null) !== 'scroll' ? 'target=_blank rel=noopener' : '' }}
                        class="reveal inline-flex items-center gap-2 text-sm font-semibold text-white transition-colors hover:text-secondary"
                        style="transition-delay: 240ms">
                        {{ $content['cta_label'] }}
                        <span>&rarr;</span>
                    </a>
                @else
                    <button type="button" class="reveal inline-flex items-center gap-2 text-sm font-semibold text-white transition-colors hover:text-secondary"
                            style="transition-delay: 240ms">
                        {{ $content['cta_label'] }}
                        <span>&rarr;</span>
                    </button>
                @endif
            @endif
        </div>
    </div>
</section>
