{{-- Auto-binds to the organization's officers when $organization is in scope (tenant page
     render); falls back to $content['items'] sample data in template-preview context —
     see daftar-berita.blade.php for the full rationale. --}}
@php
    $content = $section['content'] ?? [];
    $items = isset($organization)
        ? $organization->officers()->get()->map(fn ($officer) => [
            'name' => $officer->name,
            'role' => $officer->role,
            'photo' => $officer->photo,
        ])
        : ($content['items'] ?? [
            ['name' => '[Nama]', 'role' => 'Ketua'],
            ['name' => '[Nama]', 'role' => 'Sekretaris'],
            ['name' => '[Nama]', 'role' => 'Bendahara'],
            ['name' => '[Nama]', 'role' => 'Anggota'],
        ]);

    // Same {image, caption} shape the lightbox component expects (see
    // templates/sections/galeri/standar.blade.php) - caption here is the officer's role.
    $photos = collect($items)->map(fn ($item) => [
        'image' => $item['photo'] ?? null,
        'caption' => $item['name'] ?? null,
    ])->values();
@endphp

<section class="py-24" x-data="{ lightboxOpen: false, activeIndex: 0, photos: {{ Js::from($photos) }} }">
    <div class="max-w-6xl mx-auto px-6">
        <div class="reveal text-center mb-16">
            <span class="block w-10 h-px bg-secondary mx-auto mb-6"></span>
            <h2 class="text-3xl md:text-4xl text-primary">
                {{ $content['title'] ?? 'Struktur Pengurus' }}
            </h2>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-x-8 gap-y-12">
            @foreach ($items as $item)
                <div class="reveal text-center" style="transition-delay: {{ $loop->index * 80 }}ms">
                    <button type="button"
                            @click="activeIndex = {{ $loop->index }}; lightboxOpen = true"
                            {{ empty($item['photo']) ? 'disabled' : '' }}
                            class="block w-full aspect-square rounded-brand overflow-hidden bg-gray-100 mb-4 border-b-2 border-secondary disabled:cursor-default">
                        @if (! empty($item['photo']))
                            <img src="{{ $item['photo'] }}" alt="{{ $item['name'] }}" class="w-full h-full object-cover cursor-zoom-in">
                        @endif
                    </button>
                    <p class="font-semibold text-primary text-sm">{{ $item['name'] }}</p>
                    <p class="text-xs text-secondary uppercase tracking-wide mt-1">{{ $item['role'] }}</p>
                </div>
            @endforeach
        </div>
    </div>

    <x-tenant.lightbox />
</section>
