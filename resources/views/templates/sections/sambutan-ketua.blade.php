@php
    $content = $section['content'] ?? [];
    $organization = $organization ?? null;
    // See hero.blade.php's header comment for the shared $isExclusive resolution rationale.
    $isExclusive = isset($template)
        ? ($template->structure['exclusive'] ?? false)
        : ($organization->template?->structure['exclusive'] ?? false);
@endphp

@if ($isExclusive)
    <section class="py-24 bg-softBg">
        <div class="max-w-4xl mx-auto px-6 text-center">
            <div class="reveal w-14 h-14 rounded-full bg-white shadow-soft flex items-center justify-center mx-auto mb-8 overflow-hidden">
                @if (! empty($content['photo']))
                    <img src="{{ $content['photo'] }}" alt="{{ $content['nama'] ?? '' }}" class="w-full h-full object-cover">
                @else
                    <span class="text-secondary text-2xl leading-none">&ldquo;</span>
                @endif
            </div>
            <p class="reveal text-xl md:text-2xl text-gray-700 leading-relaxed mb-8" style="transition-delay: 80ms">
                &ldquo;{{ $content['sambutan'] ?? 'Sambutan singkat dari ketua organisasi.' }}&rdquo;
            </p>
            <div class="reveal flex items-center justify-center gap-3" style="transition-delay: 160ms">
                <span class="w-8 h-px bg-secondary"></span>
                <div class="text-left">
                    <p class="font-semibold text-primary">{{ $content['nama'] ?? '[Nama Ketua]' }}</p>
                    <p class="text-sm text-gray-500">{{ $content['jabatan'] ?? 'Ketua Organisasi' }}</p>
                </div>
            </div>
        </div>
    </section>
@else
    <section class="py-16">
        <div class="max-w-6xl mx-auto px-6 grid md:grid-cols-3 gap-10 items-center">
            <div class="reveal aspect-square rounded-brand overflow-hidden shadow-soft">
                @if (! empty($content['photo']))
                    <img src="{{ $content['photo'] }}" alt="{{ $content['nama'] ?? '' }}" class="w-full h-full object-cover">
                @else
                    <div class="w-full h-full bg-gray-100 flex items-center justify-center text-gray-400 text-sm">Foto</div>
                @endif
            </div>
            <div class="reveal md:col-span-2" style="transition-delay: 100ms">
                <p class="text-secondary font-semibold mb-2">Sambutan</p>
                <h2 class="text-2xl md:text-3xl font-extrabold text-primary mb-2">
                    {{ $content['nama'] ?? '[Nama Ketua]' }}
                </h2>
                <p class="text-sm text-gray-500 mb-4">{{ $content['jabatan'] ?? 'Ketua Organisasi' }}</p>
                <p class="text-gray-600 leading-relaxed">
                    {{ $content['sambutan'] ?? 'Sambutan singkat dari ketua organisasi.' }}
                </p>
            </div>
        </div>
    </section>
@endif
