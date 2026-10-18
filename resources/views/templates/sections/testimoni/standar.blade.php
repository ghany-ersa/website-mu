{{-- The plain cut of the testimonials, and the one any plan may use - the editorial treatment
     (navy field, snap rail, oversized quote marks, photo chips) is the `sekolahmu` variant's job.

     Plain cards on a horizontal carousel: a hairline border and a rounded corner on the section's
     own background, with no shadow, no decorative quote glyph and no avatar placeholder. The card
     gives each quote a clear edge without competing with it. An organization on a lower plan is
     the one least likely to have headshots, so the photo renders only when there is one - a row of
     empty grey circles reads as content that failed to load rather than as a design.

     The quotes sit on a horizontal slide rail - plain CSS scroll-snap, no JS at all: it scrolls
     with a swipe, a trackpad or the keyboard, and each card snaps into place. Same mechanism the
     prestasi filter rail and the sekolahmu galeri already use. Testimonials are the one section
     that grows without bound as an organization collects more, so a rail keeps a page of 12
     quotes from becoming six screens of scrolling.

     Auto-binds to the organization's testimonials when $organization is in scope (tenant page
     render); falls back to $content['items'] in template-preview context, which has no
     organization yet - see daftar-berita/standar.blade.php for the full rationale. --}}
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

<section class="section-y bg-surface">
    <div class="shell">
        <div class="mb-6 sm:mb-8 space-y-1.5">
            @if (! empty($content['label']))
                <span class="reveal block font-headline text-secondary font-bold tracking-[0.2em] uppercase text-[11px]">
                    {{ $content['label'] }}
                </span>
            @endif
            <h2 class="reveal font-headline font-bold t-h2 text-primary">
                {{ $content['title'] ?? 'Apa Kata Mereka?' }}
            </h2>
        </div>

        @if (empty($items))
            <p class="font-body text-on-surface-variant">Belum ada testimoni.</p>
        @else
            {{-- The negative margin lets a card sit flush to the screen edge on a phone while the
                 padding keeps the first one aligned with the heading above it. --}}
            {{-- `reveal` sits on the RAIL, not on each card: the scroll-reveal observer fades an
                 element in when it intersects the viewport, and cards past the first screen sit
                 outside the rail's horizontal scroll, so they never intersect and would stay at
                 opacity 0 - the carousel looked empty until you scrolled it. The rail itself is
                 always in view, so one observer target covers every card. Same reason the galeri
                 and prestasi rails put it on their container. --}}
            <div class="reveal flex gap-5 overflow-x-auto snap-x snap-mandatory hide-scrollbar -mx-5 px-5 sm:-mx-6 sm:px-6 md:mx-0 md:px-0 pb-1">
                @foreach ($items as $index => $item)
                    {{-- flex-col + mt-auto on the caption: cards in a row share the tallest card's
                         height, so without it a short quote leaves its attribution floating in
                         the middle of the card instead of sitting at its foot. --}}
                    <figure class="snap-start shrink-0 w-[85%] sm:w-[calc((100%-1.25rem)/2)] lg:w-[calc((100%-2.5rem)/3)] bg-surface-container-lowest border border-outline-variant/40 rounded-2xl p-5 sm:p-6 flex flex-col"
                            >
                        <blockquote class="font-body text-on-surface text-[15px] leading-relaxed mb-4">
                            &ldquo;{{ $item['quote'] ?? '' }}&rdquo;
                        </blockquote>

                        <figcaption class="flex items-center gap-3 mt-auto pt-4 border-t border-outline-variant/40">
                            @if (! empty($item['image']))
                                <img src="{{ $item['image'] }}" alt="{{ $item['name'] ?? '' }}" loading="lazy"
                                     class="w-9 h-9 rounded-lg object-cover shrink-0">
                            @endif
                            <div class="min-w-0">
                                <p class="font-headline font-bold text-sm text-primary">{{ $item['name'] ?? '' }}</p>
                                @if (! empty($item['role']))
                                    <p class="font-body text-xs text-on-surface-variant">{{ $item['role'] }}</p>
                                @endif
                            </div>
                        </figcaption>
                    </figure>
                @endforeach
            </div>
        @endif
    </div>
</section>
