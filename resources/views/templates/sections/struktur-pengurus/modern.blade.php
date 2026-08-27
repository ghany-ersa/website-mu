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

<section class="py-24">
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
                    <div class="aspect-square rounded-brand overflow-hidden bg-gray-100 mb-4 border-b-2 border-secondary">
                        @if (! empty($item['photo']))
                            <img src="{{ $item['photo'] }}" alt="{{ $item['name'] }}" class="w-full h-full object-cover">
                        @endif
                    </div>
                    <p class="font-semibold text-primary text-sm">{{ $item['name'] }}</p>
                    <p class="text-xs text-secondary uppercase tracking-wide mt-1">{{ $item['role'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>
