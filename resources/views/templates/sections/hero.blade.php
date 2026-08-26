@php
    $content = $section['content'] ?? [];
    $organization = $organization ?? null;
    $orgName = $template->structure['sample_org_name'] ?? null
        ?? $organization->name ?? null
        ?? '[Nama Organisasi]';
    // Exclusive templates (Template::is_exclusive / structure.exclusive) opt into a distinct,
    // more editorial hero treatment — see tentang-organisasi.blade.php, sambutan-ketua.blade.php,
    // program-unggulan.blade.php, struktur-pengurus.blade.php, and cta.blade.php for the same
    // flag driving their own exclusive variants. Resolved from $template in preview context or
    // $organization->template in live tenant context, since only one of the two is ever set.
    $isExclusive = isset($template)
        ? ($template->structure['exclusive'] ?? false)
        : ($organization->template?->structure['exclusive'] ?? false);

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

@if ($isExclusive)
    <section class="relative overflow-hidden bg-primary">
        <div class="absolute inset-0 bg-[linear-gradient(160deg,transparent_45%,rgba(255,255,255,0.05)_45%,rgba(255,255,255,0.05)_46%,transparent_46%)]"></div>

        <div class="relative max-w-6xl mx-auto px-6 py-24 md:py-32 grid md:grid-cols-[1.1fr_0.9fr] gap-16 items-center">
            <div>
                @if (! empty($content['badge']))
                    <div class="reveal flex items-center gap-3 mb-6">
                        <span class="w-10 h-px bg-secondary"></span>
                        <span class="text-secondary text-xs font-semibold tracking-[0.2em] uppercase">{{ $content['badge'] }}</span>
                    </div>
                @endif

                <h1 class="reveal text-4xl md:text-6xl text-white leading-[1.1] mb-6 tracking-tight" style="transition-delay: 80ms">
                    {{ $content['headline'] ?? 'Headline Utama' }}
                </h1>

                <p class="reveal max-w-xl text-lg text-white/70 leading-relaxed mb-10" style="transition-delay: 160ms">
                    {{ $content['subheadline'] ?? 'Subheadline yang menjelaskan organisasi secara singkat.' }}
                </p>

                <div class="reveal flex flex-wrap items-center gap-5" style="transition-delay: 240ms">
                    @if (! empty($content['cta_label']))
                        @if ($ctaHref)
                            <a href="{{ $ctaHref }}" {{ ($content['cta_type'] ?? null) !== 'scroll' ? 'target=_blank rel=noopener' : '' }}
                                class="px-7 py-3.5 rounded-brand bg-white text-primary text-sm font-semibold tracking-wide transition-all duration-200 hover:bg-secondary hover:text-white">
                                {{ $content['cta_label'] }}
                            </a>
                        @else
                            <button type="button" class="px-7 py-3.5 rounded-brand bg-white text-primary text-sm font-semibold tracking-wide transition-all duration-200 hover:bg-secondary hover:text-white">
                                {{ $content['cta_label'] }}
                            </button>
                        @endif
                    @endif
                    @if (! empty($content['cta_secondary_label']))
                        @if ($ctaSecondaryHref)
                            <a href="{{ $ctaSecondaryHref }}" {{ ($content['cta_secondary_type'] ?? null) !== 'scroll' ? 'target=_blank rel=noopener' : '' }}
                                class="group inline-flex items-center gap-2 text-sm font-semibold text-white/80 tracking-wide transition-colors hover:text-white">
                                {{ $content['cta_secondary_label'] }}
                                <span class="transition-transform duration-200 group-hover:translate-x-1">&rarr;</span>
                            </a>
                        @else
                            <button type="button" class="group inline-flex items-center gap-2 text-sm font-semibold text-white/80 tracking-wide transition-colors hover:text-white">
                                {{ $content['cta_secondary_label'] }}
                                <span class="transition-transform duration-200 group-hover:translate-x-1">&rarr;</span>
                            </button>
                        @endif
                    @endif
                </div>
            </div>

            <div class="reveal relative" style="transition-delay: 120ms">
                <div class="absolute -inset-3 border border-secondary/40 rounded-brand"></div>
                <div class="relative rounded-brand overflow-hidden shadow-float">
                    @if (! empty($content['image']))
                        <img src="{{ $content['image'] }}" alt="{{ $content['headline'] ?? '' }}" class="w-full aspect-[4/5] object-cover">
                    @else
                        <div class="w-full aspect-[4/5] bg-white/10 flex items-center justify-center text-white/40 text-sm">Gambar</div>
                    @endif
                </div>
            </div>
        </div>
    </section>
@else
    <section class="relative overflow-hidden bg-primary">
        <div class="absolute -top-24 -left-24 w-72 h-72 rounded-full bg-white/10 blur-3xl animate-blob"></div>
        <div class="absolute -bottom-24 -right-24 w-72 h-72 rounded-full bg-white/10 blur-3xl animate-blob" style="animation-delay: 2s"></div>

        <div class="relative max-w-6xl mx-auto px-6 py-20 grid md:grid-cols-2 gap-12 items-center">
            <div>
                @if (! empty($content['badge']))
                    <span class="reveal inline-block px-3 py-1 rounded-brand bg-white/15 text-white text-xs font-semibold mb-4">
                        {{ $content['badge'] }}
                    </span>
                @endif

                <h1 class="reveal text-4xl md:text-5xl font-extrabold text-white leading-tight mb-4" style="transition-delay: 80ms">
                    {{ $content['headline'] ?? 'Headline Utama' }}
                </h1>

                <p class="reveal max-w-xl text-lg text-white/80 mb-8" style="transition-delay: 160ms">
                    {{ $content['subheadline'] ?? 'Subheadline yang menjelaskan organisasi secara singkat.' }}
                </p>

                <div class="reveal flex flex-wrap gap-3" style="transition-delay: 240ms">
                    @if (! empty($content['cta_label']))
                        @if ($ctaHref)
                            <a href="{{ $ctaHref }}" {{ ($content['cta_type'] ?? null) !== 'scroll' ? 'target=_blank rel=noopener' : '' }}
                                class="px-6 py-3 rounded-brand bg-white text-primary font-semibold shadow-float transition-transform duration-200 hover:scale-105">
                                {{ $content['cta_label'] }}
                            </a>
                        @else
                            <button type="button" class="px-6 py-3 rounded-brand bg-white text-primary font-semibold shadow-float transition-transform duration-200 hover:scale-105">
                                {{ $content['cta_label'] }}
                            </button>
                        @endif
                    @endif
                    @if (! empty($content['cta_secondary_label']))
                        @if ($ctaSecondaryHref)
                            <a href="{{ $ctaSecondaryHref }}" {{ ($content['cta_secondary_type'] ?? null) !== 'scroll' ? 'target=_blank rel=noopener' : '' }}
                                class="px-6 py-3 rounded-brand bg-secondary text-white font-semibold shadow-float transition-transform duration-200 hover:scale-105">
                                {{ $content['cta_secondary_label'] }}
                            </a>
                        @else
                            <button type="button" class="px-6 py-3 rounded-brand bg-secondary text-white font-semibold shadow-float transition-transform duration-200 hover:scale-105">
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
@endif
