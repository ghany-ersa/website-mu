{{-- Port of the "Alumni Testimonials" block on comprof-SD-Madya's index.vue: a centred header
     with a short gold rule under it, then three quote cards, each with a big decorative quote
     mark and the speaker's photo, name and role.

     Content-only - there's no CMS model behind this. Testimonials are written once when the
     site is set up and rarely change, so they live in the section's own `items` rather than
     earning a table and a CRUD screen of their own. --}}
@php
    $content = $section['content'] ?? [];

    $items = $content['items'] ?? [
        ['quote' => 'Sekolah ini memberikan landasan karakter yang sangat kuat bagi saya. Kedisiplinan dan nilai keislaman yang diajarkan terbawa hingga karir saya sekarang.', 'name' => '[Nama Alumni]', 'role' => 'Alumni'],
        ['quote' => 'Gurunya sangat perhatian dan mampu menemukan bakat saya yang saat itu belum terlihat.', 'name' => '[Nama Alumni]', 'role' => 'Alumni'],
        ['quote' => 'Ekosistem belajarnya kompetitif namun tetap suportif. Saya diajarkan untuk tidak pernah takut gagal.', 'name' => '[Nama Alumni]', 'role' => 'Alumni'],
    ];
@endphp

<section class="py-14 sm:py-20 md:py-24 bg-surface-container-low">
    <div class="max-w-7xl mx-auto px-5 sm:px-6">
        <div class="text-center mb-10 sm:mb-12 md:mb-16 space-y-3 sm:space-y-4">
            @if (! empty($content['label']))
                <span class="reveal block font-headline text-secondary font-bold tracking-[0.2em] uppercase text-xs">
                    {{ $content['label'] }}
                </span>
            @endif
            <h2 class="reveal font-headline font-bold text-3xl md:text-4xl text-primary">
                {{ $content['title'] ?? 'Apa Kata Mereka?' }}
            </h2>
            <div class="w-20 h-1 bg-secondary mx-auto"></div>
        </div>

        {{-- 2-up at tablet, 3-up only once there is room: three quote cards at 768px squeeze
             the attribution line until names truncate. --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6 md:gap-8">
            @foreach ($items as $index => $item)
                <figure class="reveal relative bg-surface-container-lowest rounded-2xl p-6 sm:p-8 shadow-ambient flex flex-col"
                        style="transition-delay: {{ min($index * 80, 240) }}ms">
                    {{-- Oversized quote mark, low contrast: decoration, so it's hidden from
                         assistive tech rather than read out as punctuation. --}}
                    <span aria-hidden="true" class="absolute top-6 right-6 font-headline text-6xl leading-none text-secondary/25 select-none">&rdquo;</span>

                    <blockquote class="font-body italic text-on-surface-variant leading-relaxed relative z-10 mb-6">
                        "{{ $item['quote'] ?? '' }}"
                    </blockquote>

                    <figcaption class="flex items-center gap-3 mt-auto">
                        <div class="w-11 h-11 rounded-full overflow-hidden bg-surface-container-high shrink-0 flex items-center justify-center">
                            @if (! empty($item['image']))
                                <img src="{{ $item['image'] }}" alt="{{ $item['name'] ?? '' }}" loading="lazy" class="w-full h-full object-cover">
                            @else
                                <x-tenant.icon name="person" class="text-on-surface-variant" />
                            @endif
                        </div>
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
    </div>
</section>
