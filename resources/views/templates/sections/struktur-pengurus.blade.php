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
@endphp

<section class="py-16 bg-softBg">
    <div class="max-w-6xl mx-auto px-6">
        <h2 class="reveal text-3xl font-extrabold text-primary mb-10 text-center">
            {{ $content['title'] ?? 'Struktur Pengurus' }}
        </h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
            @foreach ($items as $item)
                <div class="reveal group text-center" style="transition-delay: {{ $loop->index * 100 }}ms">
                    <div class="aspect-square rounded-brand overflow-hidden bg-gray-100 mb-3 ring-2 ring-transparent transition-all duration-300 group-hover:-translate-y-1 group-hover:shadow-float group-hover:ring-secondary">
                        @if (! empty($item['photo']))
                            <img src="{{ $item['photo'] }}" alt="{{ $item['name'] }}" class="w-full h-full object-cover">
                        @endif
                    </div>
                    <p class="font-semibold text-gray-800 text-sm">{{ $item['name'] }}</p>
                    <p class="text-xs text-gray-500">{{ $item['role'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>
