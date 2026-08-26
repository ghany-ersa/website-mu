@php
    $content = $section['content'] ?? [];
    $organization = $organization ?? null;
    $orgName = $template->structure['sample_org_name'] ?? null
        ?? $organization->name ?? null
        ?? '[Nama Organisasi]';

    // Same resolver as hero.blade.php's $resolveCtaHref('cta') — kept local rather than shared
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

    // See hero.blade.php's header comment for the shared $isExclusive resolution rationale.
    $isExclusive = isset($template)
        ? ($template->structure['exclusive'] ?? false)
        : ($organization->template?->structure['exclusive'] ?? false);
@endphp

@if ($isExclusive)
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
@else
    <section class="relative overflow-hidden py-16 bg-primary">
        <div class="absolute -top-16 -right-16 w-64 h-64 rounded-full bg-white/5 animate-blob"></div>
        <div class="absolute -bottom-20 -left-10 w-56 h-56 rounded-full bg-white/5 animate-blob" style="animation-delay: 3s"></div>

        <div class="relative max-w-6xl mx-auto px-6 text-center">
            <h2 class="reveal text-3xl font-extrabold text-white mb-3">
                {{ $content['title'] ?? 'Mari Bergabung' }}
            </h2>
            <p class="reveal text-white/80 max-w-xl mx-auto mb-6" style="transition-delay: 80ms">
                {{ $content['subtitle'] ?? 'Ajakan singkat untuk bergabung atau berpartisipasi bersama kami.' }}
            </p>
            @if ($ctaHref)
                <a href="{{ $ctaHref }}" {{ $ctaType !== 'scroll' ? 'target=_blank rel=noopener' : '' }}
                    class="reveal inline-block px-6 py-3 rounded-brand bg-white text-primary font-semibold transition-transform duration-200 hover:scale-105"
                    style="transition-delay: 160ms">
                    {{ $content['cta_label'] ?? 'Selengkapnya' }}
                </a>
            @else
                <button type="button" class="reveal px-6 py-3 rounded-brand bg-white text-primary font-semibold transition-transform duration-200 hover:scale-105"
                        style="transition-delay: 160ms">
                    {{ $content['cta_label'] ?? 'Selengkapnya' }}
                </button>
            @endif
        </div>
    </section>
@endif
