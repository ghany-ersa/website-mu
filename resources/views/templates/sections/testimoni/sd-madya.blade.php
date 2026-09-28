{{-- The editorial cut of the alumni testimonials, matching the rest of the sd-madya family:
     a navy field, a gold eyebrow, and the quotes on a horizontal slide rail at every width - the
     same plain CSS scroll-snap the prestasi filter and the sd-madya galeri use, driven by a
     swipe, a trackpad or the keyboard, with no JS at all. Testimonials grow without bound as an
     organization collects more, so a rail keeps twelve quotes from becoming six screens of
     scrolling.

     Where `standar` sets each quote on a light card, this one inverts: translucent white cards on
     the primary colour, with the speaker's photo larger and the quote given room to breathe.

     Auto-binds to the organization's testimonials when $organization is in scope; falls back to
     $content['items'] in template-preview context - see daftar-berita/standar.blade.php. --}}
@php
    $content = $section['content'] ?? [];
    $limit = $content['limit'] ?? null;

    $items = isset($organization)
        ? $organization->testimonials()
            ->active()
            ->when($limit, fn ($query) => $query->limit((int) $limit))
            ->get()
            ->map(fn ($testimonial) => [
                'quote' => $testimonial->quote,
                'name' => $testimonial->name,
                'role' => $testimonial->role,
                'image' => $testimonial->photo,
            ])->all()
        : ($content['items'] ?? [
            ['quote' => 'Sekolah ini memberikan landasan karakter yang sangat kuat bagi saya. Kedisiplinan dan nilai keislaman yang diajarkan terbawa hingga karir saya sekarang.', 'name' => '[Nama Alumni]', 'role' => 'Alumni'],
            ['quote' => 'Gurunya sangat perhatian dan mampu menemukan bakat saya yang saat itu belum terlihat.', 'name' => '[Nama Alumni]', 'role' => 'Alumni'],
            ['quote' => 'Ekosistem belajarnya kompetitif namun tetap suportif. Saya diajarkan untuk tidak pernah takut gagal.', 'name' => '[Nama Alumni]', 'role' => 'Alumni'],
        ]);
@endphp

<section class="section-y bg-primary">
    <div class="shell">
        <div class="text-center mb-6 sm:mb-8 space-y-2.5">
            @if (! empty($content['label']))
                <span class="reveal block font-headline text-secondary font-bold tracking-[0.2em] uppercase text-[11px]">
                    {{ $content['label'] }}
                </span>
            @endif
            <h2 class="reveal font-headline font-extrabold t-h2 text-white text-balance">
                {{ $content['title'] ?? 'Apa Kata Mereka?' }}
            </h2>
            <div class="w-14 h-0.5 bg-secondary mx-auto"></div>
        </div>

        @if (empty($items))
            <p class="text-center font-body text-white/70">Belum ada testimoni.</p>
        @else
            {{-- The negative margin lets the rail bleed into the shell's gutter so a card can sit
                 flush to the screen edge while the padding keeps the first one aligned with the
                 heading above it. --}}
            {{-- `reveal` on the RAIL, not per card - see testimoni/standar.blade.php: a card
                 outside the horizontal scroll never intersects the viewport, so a per-card
                 reveal left the carousel blank until it was scrolled. --}}
            <div class="reveal flex gap-5 overflow-x-auto snap-x snap-mandatory hide-scrollbar -mx-5 px-5 sm:-mx-6 sm:px-6 md:mx-0 md:px-0 pb-1">
                @foreach ($items as $index => $item)
                    <figure class="relative snap-start shrink-0 w-[85%] sm:w-[calc((100%-1.25rem)/2)] lg:w-[calc((100%-2.5rem)/3)] bg-white/10 rounded-2xl p-5 sm:p-6 flex flex-col border border-white/10"
                            >
                        <span aria-hidden="true" class="absolute top-4 right-5 font-headline text-5xl leading-none text-secondary/30 select-none">&rdquo;</span>

                        <blockquote class="font-body italic text-white/85 text-sm leading-relaxed relative z-10 mb-5">
                            &ldquo;{{ $item['quote'] ?? '' }}&rdquo;
                        </blockquote>

                        <figcaption class="flex items-center gap-3 mt-auto pt-4 border-t border-white/10">
                            <div class="w-11 h-11 rounded-full overflow-hidden bg-white/15 shrink-0 flex items-center justify-center">
                                @if (! empty($item['image']))
                                    <img src="{{ $item['image'] }}" alt="{{ $item['name'] ?? '' }}" loading="lazy" class="w-full h-full object-cover">
                                @else
                                    <svg class="w-6 h-6 text-white/50" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="M12 12a5 5 0 1 0 0-10 5 5 0 0 0 0 10Zm0 2c-4.42 0-8 2.24-8 5v1h16v-1c0-2.76-3.58-5-8-5Z" />
                                    </svg>
                                @endif
                            </div>
                            <div class="min-w-0">
                                <p class="font-headline font-bold text-sm text-white">{{ $item['name'] ?? '' }}</p>
                                @if (! empty($item['role']))
                                    <p class="font-body text-xs text-secondary">{{ $item['role'] }}</p>
                                @endif
                            </div>
                        </figcaption>
                    </figure>
                @endforeach
            </div>
        @endif
    </div>
</section>
