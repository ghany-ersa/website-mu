{{-- Port of comprof-SD-Madya index.vue's "Profil Sekolah" block: a rotated tinted panel behind
     a square photo on the left, and on the right an eyebrow label, heading, body, and two
     side-by-side cards for the vision statement and the core values.

     Adds to the standar contract: `label` (the small uppercase eyebrow), `vision_title`/
     `vision_body`, and `values_title`/`values` (a list of short strings). `stats` still works
     and renders below the cards, so a template can use either or both. --}}
@php
    $content = $section['content'] ?? [];
    $stats = $content['stats'] ?? [];
    $values = $content['values'] ?? [];
@endphp

<section class="py-14 sm:py-20 md:py-24 bg-surface">
    <div class="max-w-7xl mx-auto px-5 sm:px-6">
        <div class="grid md:grid-cols-12 gap-8 md:gap-16 items-center">
            {{-- Leads on a phone: a photo is a faster read than a heading, and the source's
                 desktop order (image left) is preserved from `md` up. --}}
            <div class="md:col-span-5 order-first md:order-1">
                <div class="reveal relative">
                    <div class="absolute inset-0 bg-primary rounded-3xl -rotate-3 scale-105 opacity-10"></div>
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

            <div class="md:col-span-7 md:order-2 space-y-5 sm:space-y-6 md:space-y-8">
                @if (! empty($content['label']))
                    <span class="reveal block font-headline text-primary font-bold tracking-widest uppercase text-sm">
                        {{ $content['label'] }}
                    </span>
                @endif

                <h2 class="reveal font-headline font-bold text-3xl md:text-4xl text-primary leading-tight" style="transition-delay: 60ms">
                    {{ $content['title'] ?? 'Tentang Organisasi' }}
                </h2>

                <p class="reveal font-body text-lg md:text-xl text-on-surface-variant leading-relaxed" style="transition-delay: 120ms">
                    {{ $content['body'] ?? 'Deskripsi singkat sejarah, visi, dan misi organisasi.' }}
                </p>

                @if (! empty($content['vision_body']) || $values)
                    <div class="reveal grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6 pt-1 sm:pt-2" style="transition-delay: 180ms">
                        @if (! empty($content['vision_body']))
                            <div class="bg-surface-container-low p-5 sm:p-6 rounded-2xl">
                                <h3 class="font-headline font-bold text-primary mb-3">{{ $content['vision_title'] ?? 'Visi Kami' }}</h3>
                                <p class="font-body text-on-surface-variant italic">"{{ $content['vision_body'] }}"</p>
                            </div>
                        @endif

                        @if ($values)
                            <div class="bg-surface-container-low p-5 sm:p-6 rounded-2xl">
                                <h3 class="font-headline font-bold text-primary mb-3">{{ $content['values_title'] ?? 'Nilai Utama' }}</h3>
                                <ul class="font-body text-on-surface-variant space-y-2">
                                    @foreach ($values as $value)
                                        <li class="flex items-start gap-2">
                                            <span class="w-1.5 h-1.5 bg-secondary rounded-full shrink-0 mt-2"></span>
                                            {{ is_array($value) ? ($value['label'] ?? '') : $value }}
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
                                <p class="font-headline text-2xl md:text-3xl font-extrabold text-primary">{{ $stat['value'] ?? '' }}</p>
                                <p class="font-body text-xs md:text-sm text-on-surface-variant">{{ $stat['label'] ?? '' }}</p>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>
