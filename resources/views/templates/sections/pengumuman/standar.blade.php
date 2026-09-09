{{-- Auto-binds to the organization's published announcements (most recent first, regardless
     of valid_until) when $organization is in scope (tenant page render); falls back to
     $content['items'] sample data in template-preview context - see daftar-berita.blade.php
     for the full rationale. --}}
@php
    $content = $section['content'] ?? [];
    // A blank/unset `limit` means "show all" rather than falling back to a default cap - null
    // is the right value for that on both branches below: Builder::take(null) omits the SQL
    // LIMIT clause entirely, and array_slice(..., 0, null) returns every item.
    $limit = filled($content['limit'] ?? null) ? (int) $content['limit'] : null;
    $items = isset($organization)
        ? $organization->announcements()->published()->take($limit)->get()->map(fn ($announcement) => [
            'title' => $announcement->title,
            'priority' => $announcement->priority,
            'valid_until' => $announcement->valid_until?->translatedFormat('d M Y'),
            'url' => \Illuminate\Support\Facades\Route::has('tenant.announcements.show')
                ? route('tenant.announcements.show', ['organization_slug' => $organization->slug, 'announcement' => $announcement->id])
                : '#',
        ])
        // array_fill's placeholder-card count only needs a number when there's neither a real
        // `items` sample list nor a `limit` to size it by - 3 keeps that specific edge case's
        // look unchanged; it never caps a real (or genuinely limitless) `items` list.
        : array_slice($content['items'] ?? array_fill(0, $limit ?? 3, []), 0, $limit);
    $priorityColor = fn ($p) => match ($p) {
        'Tinggi' => 'border-red-400 bg-red-50',
        'Sedang' => 'border-secondary bg-secondary/5',
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
                <a href="{{ $item['url'] ?? '#' }}"
                   class="reveal block border-l-4 {{ $priorityColor($item['priority'] ?? null) }} rounded-r-brand p-4 transition-transform duration-300 hover:translate-x-1"
                   style="transition-delay: {{ $loop->index * 80 }}ms">
                    <p class="font-semibold text-gray-800">{{ $item['title'] ?? 'Pengumuman contoh '.$loop->iteration }}</p>
                    @if (! empty($item['valid_until']))
                        <p class="text-sm text-gray-500">Berlaku hingga {{ $item['valid_until'] }}</p>
                    @endif
                </a>
            @endforeach
        </div>
    </div>
</section>
