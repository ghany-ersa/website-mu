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

<section class="py-24 bg-primary">
    <div class="max-w-3xl mx-auto px-6 text-center">
        <span class="reveal block w-10 h-px bg-secondary mx-auto mb-6"></span>
        <h2 class="reveal text-3xl md:text-4xl text-white mb-4 leading-tight">
            {{ $content['title'] ?? 'Mari Bergabung' }}
        </h2>
        <p class="reveal text-white/70 max-w-xl mx-auto mb-10" style="transition-delay: 80ms">
            {{ $content['subtitle'] ?? 'Ajakan singkat untuk bergabung atau berpartisipasi bersama kami.' }}
        </p>
        @if ($ctaHref)
            <a href="{{ $ctaHref }}" {{ $ctaType !== 'scroll' ? 'target=_blank rel=noopener' : '' }}
                class="reveal inline-block px-8 py-3.5 rounded-brand border border-white/30 text-white text-sm font-semibold tracking-wide transition-all duration-200 hover:bg-white hover:text-primary"
                style="transition-delay: 160ms">
                {{ $content['cta_label'] ?? 'Selengkapnya' }}
            </a>
        @else
            <button type="button" class="reveal px-8 py-3.5 rounded-brand border border-white/30 text-white text-sm font-semibold tracking-wide transition-all duration-200 hover:bg-white hover:text-primary"
                    style="transition-delay: 160ms">
                {{ $content['cta_label'] ?? 'Selengkapnya' }}
            </button>
        @endif
    </div>
</section>
