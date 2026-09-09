{{-- Auto-binds to the organization's facilities (organizations.facilities.* CMS) when
     $organization is in scope; falls back to $content['items'] sample data in
     template-preview context - see struktur-pengurus/standar.blade.php for the pattern.

     Photos are click-to-zoom via the shared x-tenant.lightbox component (same {image,
     caption} Alpine contract as galeri/standar.blade.php), matching the nurul-huda site
     where facility photos open full-size rather than being decoration only. --}}
@php
    $content = $section['content'] ?? [];
    $limit = (int) ($content['limit'] ?? 6);

    $items = isset($organization)
        ? $organization->facilities()->get()->map(fn ($facility) => [
            'name' => $facility->name,
            'photo' => $facility->photo,
            'description' => $facility->description,
        ])
        : ($content['items'] ?? [
            ['name' => 'Ruang Sholat Utama', 'photo' => null, 'description' => null],
            ['name' => 'Tempat Wudhu', 'photo' => null, 'description' => null],
            ['name' => 'Area Parkir', 'photo' => null, 'description' => null],
        ]);

    $items = collect($items)->take($limit)->values();

    $photos = $items->map(fn ($item) => [
        'image' => $item['photo'] ?? null,
        'caption' => $item['name'] ?? null,
    ])->values();
@endphp

<section class="py-16 bg-slate-50" x-data="{ lightboxOpen: false, activeIndex: 0, photos: {{ Js::from($photos) }} }">
    <div class="max-w-5xl mx-auto px-6">
        <h2 class="reveal text-2xl sm:text-3xl font-bold text-primary text-center">
            {{ $content['title'] ?? 'Fasilitas Masjid' }}
        </h2>

        <div class="mt-8 grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($items as $item)
                <article class="reveal bg-white rounded-2xl shadow-sm hover:shadow-md transition overflow-hidden border border-slate-100 flex flex-col" style="transition-delay: {{ $loop->index * 80 }}ms">
                    @if (! empty($item['photo']))
                        <button type="button"
                                @click="activeIndex = {{ $loop->index }}; lightboxOpen = true"
                                class="aspect-[4/3] bg-slate-100 overflow-hidden block w-full cursor-zoom-in">
                            <img src="{{ $item['photo'] }}" alt="{{ $item['name'] }}" loading="lazy"
                                 class="w-full h-full object-cover object-center hover:scale-105 transition duration-500">
                        </button>
                    @endif
                    <div class="p-5 flex-1 flex flex-col">
                        <h3 class="font-semibold text-slate-900">{{ $item['name'] }}</h3>
                        @if (! empty($item['description']))
                            <p class="mt-2 text-sm text-slate-600 leading-relaxed">{{ $item['description'] }}</p>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
    </div>

    <x-tenant.lightbox />
</section>
