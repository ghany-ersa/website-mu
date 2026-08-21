@php
    $orgName = $section['content']['org_name']
        ?? $template->structure['sample_org_name'] ?? null
        ?? $organization->name ?? null
        ?? '[Nama Organisasi]';
    $orgLogo = $organization->logo ?? null;
@endphp

<footer class="bg-gray-900 text-gray-400 py-10">
    <div class="max-w-6xl mx-auto px-6 flex flex-col md:flex-row items-center justify-between gap-4 text-sm">
        <div class="flex items-center gap-2">
            @if ($orgLogo)
                <img src="{{ $orgLogo }}" alt="{{ $orgName }}" class="w-7 h-7 rounded-full object-contain bg-white">
            @endif
            <span class="text-white font-semibold">{{ $orgName }}</span>
        </div>
        <span>&copy; {{ date('Y') }} {{ $orgName }}. Seluruh hak cipta dilindungi.</span>
    </div>
</footer>
