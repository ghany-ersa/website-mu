@php
    $priorityColor = match ($announcement->priority) {
        'Tinggi' => 'border-red-400 bg-red-50',
        'Sedang' => 'border-accent bg-amber-50',
        default => 'border-gray-300 bg-gray-50',
    };
@endphp

<article class="py-16">
    <div class="max-w-3xl mx-auto px-6">
        <a href="{{ route('tenant.home', ['organization_slug' => $organization->slug]) }}" class="text-sm text-secondary font-semibold hover:underline">&larr; Kembali ke beranda</a>

        <div class="border-l-4 {{ $priorityColor }} rounded-r-xl p-6 mt-6">
            <h1 class="text-2xl font-extrabold text-primary mb-2">{{ $announcement->title }}</h1>
            @if ($announcement->valid_until)
                <p class="text-sm text-gray-500 mb-4">Berlaku hingga {{ $announcement->valid_until->translatedFormat('d M Y') }}</p>
            @endif
            <div class="prose max-w-none text-gray-700 leading-relaxed">
                {!! $announcement->body !!}
            </div>
        </div>
    </div>
</article>
