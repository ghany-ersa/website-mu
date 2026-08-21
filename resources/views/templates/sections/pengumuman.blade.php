{{-- Auto-binds to the organization's published announcements (most recent first, regardless
     of valid_until) when $organization is in scope (tenant page render); falls back to
     $content['items'] sample data in template-preview context — see daftar-berita.blade.php
     for the full rationale. --}}
@php
    $content = $section['content'] ?? [];
    $limit = $content['limit'] ?? 3;
    $items = isset($organization)
        ? $organization->announcements()->published()->take($limit)->get()->map(fn ($announcement) => [
            'title' => $announcement->title,
            'priority' => $announcement->priority,
            'valid_until' => $announcement->valid_until?->translatedFormat('d M Y'),
        ])
        : ($content['items'] ?? array_fill(0, $limit, []));
    $priorityColor = fn ($p) => match ($p) {
        'Tinggi' => 'border-red-400 bg-red-50',
        'Sedang' => 'border-accent bg-amber-50',
        default => 'border-gray-300 bg-gray-50',
    };
@endphp

<section class="py-16">
    <div class="max-w-6xl mx-auto px-6">
        <h2 class="reveal text-3xl font-extrabold text-primary mb-10">
            {{ $content['title'] ?? 'Pengumuman' }}
        </h2>
        <div class="space-y-3">
            @foreach ($items as $item)
                <div class="reveal border-l-4 {{ $priorityColor($item['priority'] ?? null) }} rounded-r-xl p-4 transition-transform duration-300 hover:translate-x-1"
                     style="transition-delay: {{ $loop->index * 80 }}ms">
                    <p class="font-semibold text-gray-800">{{ $item['title'] ?? 'Pengumuman contoh '.$loop->iteration }}</p>
                    <p class="text-sm text-gray-500">Berlaku hingga {{ $item['valid_until'] ?? '[tanggal]' }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>
