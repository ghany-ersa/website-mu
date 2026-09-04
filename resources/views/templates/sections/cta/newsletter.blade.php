@php
    $content = $section['content'] ?? [];
    $organization = $organization ?? null;
    $orgName = $template->structure['sample_org_name'] ?? null
        ?? $organization->name ?? null
        ?? '[Nama Organisasi]';

    // Same resolver as hero.blade.php's $resolveCtaHref('cta') - kept local rather than shared
    // since cta.blade.php only ever has one button (no cta_secondary_* fields).
    $ctaType = $content['cta_type'] ?? null;
    $ctaHref = match ($ctaType) {
        'whatsapp' => \App\Services\WhatsAppNumber::href(
            $content['cta_wa_number'] ?? ($organization->whatsapp ?? null),
            str_replace('{org_name}', $orgName, $content['cta_wa_message'] ?? config('page-builder.sections.cta.defaults.cta_wa_message', ''))
        ),
        'scroll' => filled($content['cta_section'] ?? null) ? '#canvas-section-'.$content['cta_section'] : null,
        'url' => filled($content['cta_url'] ?? null) ? $content['cta_url'] : null,
        default => null,
    };
@endphp

<section class="py-12 bg-gray-900">
    <div class="max-w-3xl mx-auto px-6 flex flex-col md:flex-row items-center justify-between gap-6 text-center md:text-left">
        <div>
            <h2 class="reveal text-2xl font-extrabold text-white mb-1">
                {{ $content['title'] ?? 'Jangan Lewatkan Berita Terbaru' }}
            </h2>
            <p class="reveal text-gray-400 text-sm" style="transition-delay: 80ms">
                {{ $content['subtitle'] ?? 'Ikuti kabar terbaru dari kami setiap harinya.' }}
            </p>
        </div>
        @if ($ctaHref)
            <a href="{{ $ctaHref }}" {{ $ctaType !== 'scroll' ? 'target=_blank rel=noopener' : '' }}
                class="reveal shrink-0 inline-block px-6 py-3 rounded-brand bg-secondary text-white font-semibold transition-transform duration-200 hover:scale-105"
                style="transition-delay: 160ms">
                {{ $content['cta_label'] ?? 'Ikuti Sekarang' }}
            </a>
        @else
            <button type="button" class="reveal shrink-0 px-6 py-3 rounded-brand bg-secondary text-white font-semibold transition-transform duration-200 hover:scale-105"
                    style="transition-delay: 160ms">
                {{ $content['cta_label'] ?? 'Ikuti Sekarang' }}
            </button>
        @endif
    </div>
</section>
