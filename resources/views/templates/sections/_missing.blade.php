@php $content = $section['content'] ?? []; @endphp

<section class="py-10 border-y border-dashed border-gray-300 bg-gray-50">
    <div class="max-w-6xl mx-auto px-6 text-center text-gray-400">
        <p class="text-xs uppercase tracking-wide">Belum ada tampilan untuk komponen</p>
        <p class="font-mono text-sm">{{ $section['key'] }}</p>
        @if (! empty($content['title']))
            <p class="mt-1 text-gray-500">{{ $content['title'] }}</p>
        @endif
    </div>
</section>
