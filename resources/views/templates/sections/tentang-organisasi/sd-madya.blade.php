{{-- Port of comprof-SD-Madya index.vue's "Profil Sekolah" block: a rotated tinted panel behind
     a square photo on the left, and on the right an eyebrow label, heading, body, and two
     side-by-side cards for the vision statement and the core values.

     Adds to the standar contract: `label` (the small uppercase eyebrow), `vision_title`/
     `vision_body`, and `values_title`/`values` (a list of short strings). `stats` still works
     and renders below the cards, so a template can use either or both. --}}
@php
    $content = $section['content'] ?? [];
    $stats = $content['stats'] ?? [];
    // Normalised rather than used as-is: `values` is a list, but before the builder's properties
    // panel had a proper editor for it, saving the section wrote whatever the generic text input
    // held - a bare string - and this section then died on foreach() with a 500 (same failure
    // mode as akad-venue/nurul-huda.blade.php's `facilities`). Tolerate that older shape too: a
    // string (one-per-line, the most forgiving reading of what someone typed) and a list with
    // blank rows.
    $values = $content['values'] ?? [];
    $values = collect(is_string($values) ? preg_split('/\r\n|\r|\n/', $values) : (array) $values)
        ->map(fn ($value) => is_string($value) ? trim($value) : (is_array($value) ? ($value['label'] ?? null) : null))
        ->filter()
        ->values();
@endphp

<section class="section-y bg-surface">
    <div class="shell">
        <div class="grid md:grid-cols-12 gap-8 md:gap-12 items-center">
            {{-- Leads on a phone: a photo is a faster read than a heading, and the source's
                 desktop order (image left) is preserved from `md` up. --}}
            <div class="md:col-span-5 order-first md:order-1">
                {{-- The tilted backdrop is scaled past the photo's own box, so it needs a
                     clipping wrapper: at phone widths that overhang would otherwise stick out
                     past the gutter and give the page a hairline horizontal scroll. --}}
                <div class="reveal relative overflow-hidden rounded-3xl p-1.5">
                    <div class="absolute inset-2 bg-primary rounded-3xl -rotate-3 opacity-10"></div>
                    @if (! empty($content['image']))
                        <img src="{{ $content['image'] }}" alt="{{ $content['title'] ?? '' }}" loading="lazy"
                             class="relative z-10 rounded-3xl shadow-ambient-lg aspect-square object-cover w-full">
                    @else
                        <div class="relative z-10 rounded-3xl shadow-ambient-lg aspect-square w-full bg-surface-container-high flex items-center justify-center text-on-surface-variant text-sm">
                            Foto
                        </div>
                    @endif
                </div>
            </div>

            <div class="md:col-span-7 md:order-2 space-y-4 sm:space-y-5">
                @if (! empty($content['label']))
                    <span class="reveal block font-headline text-primary font-bold tracking-widest uppercase text-xs">
                        {{ $content['label'] }}
                    </span>
                @endif

                <h2 class="reveal font-headline font-bold t-h2 text-primary" style="transition-delay: 60ms">
                    {{ $content['title'] ?? 'Tentang Organisasi' }}
                </h2>

                <p class="reveal font-body t-body text-on-surface-variant measure" style="transition-delay: 120ms">
                    {{ $content['body'] ?? 'Deskripsi singkat sejarah, visi, dan misi organisasi.' }}
                </p>

                @if (! empty($content['vision_body']) || $values->isNotEmpty())
                    <div class="reveal grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6 pt-1 sm:pt-2" style="transition-delay: 180ms">
                        @if (! empty($content['vision_body']))
                            <div class="bg-surface-container-low p-4 sm:p-5 rounded-2xl">
                                <h3 class="font-headline font-bold text-primary text-[15px] mb-2">{{ $content['vision_title'] ?? 'Visi Kami' }}</h3>
                                <p class="font-body text-on-surface-variant italic text-sm leading-relaxed">"{{ $content['vision_body'] }}"</p>
                            </div>
                        @endif

                        @if ($values->isNotEmpty())
                            <div class="bg-surface-container-low p-4 sm:p-5 rounded-2xl">
                                <h3 class="font-headline font-bold text-primary text-[15px] mb-2">{{ $content['values_title'] ?? 'Nilai Utama' }}</h3>
                                <ul class="font-body text-on-surface-variant text-sm space-y-1.5">
                                    @foreach ($values as $value)
                                        <li class="flex items-start gap-2">
                                            <span class="w-1.5 h-1.5 bg-secondary rounded-full shrink-0 mt-[0.45rem]"></span>
                                            {{ $value }}
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                @endif

                @if ($stats)
                    <div class="reveal grid grid-cols-3 gap-3 sm:gap-4 pt-1 sm:pt-2" style="transition-delay: 240ms">
                        @foreach ($stats as $stat)
                            <div>
                                <p class="font-headline text-xl md:text-2xl font-extrabold text-primary">{{ $stat['value'] ?? '' }}</p>
                                <p class="font-body text-xs md:text-sm text-on-surface-variant">{{ $stat['label'] ?? '' }}</p>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>
