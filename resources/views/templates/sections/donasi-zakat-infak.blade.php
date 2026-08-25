@php
    $content = $section['content'] ?? [];
    $waNumber = $content['wa_number'] ?? ($organization->whatsapp ?? null);
    $orgName = $template->structure['sample_org_name'] ?? null
        ?? $organization->name ?? null
        ?? '[Nama Organisasi]';
    $waMessage = $content['wa_message'] ?? config('page-builder.sections.donasi-zakat-infak.defaults.wa_message');
    $waMessage = str_replace('{org_name}', $orgName, $waMessage);
    $waHref = \App\Services\WhatsAppNumber::href($waNumber, $waMessage);
@endphp

<section class="py-16">
    <div class="max-w-4xl mx-auto px-6">
        <div class="reveal grid md:grid-cols-2 items-center bg-secondary/10 border border-secondary/30 rounded-brand overflow-hidden">
            @if (! empty($content['image']))
                <img src="{{ $content['image'] }}" alt="{{ $content['title'] ?? '' }}" class="w-full h-full object-cover aspect-video md:aspect-auto">
            @endif
            <div class="p-8 text-center md:text-left">
                <h2 class="text-2xl font-extrabold text-secondary mb-3">
                    {{ $content['title'] ?? 'Donasi, Zakat, dan Infak' }}
                </h2>
                <p class="text-gray-600 mb-6">
                    {{ $content['body'] ?? 'Salurkan donasi melalui QRIS atau tautan Lazismu.' }}
                </p>
                @if ($waHref)
                    <a href="{{ $waHref }}" target="_blank" rel="noopener"
                        class="inline-block px-6 py-3 rounded-brand bg-secondary text-white font-semibold transition-transform duration-200 hover:scale-105">
                        Donasi Sekarang
                    </a>
                @else
                    <button type="button" disabled
                        class="px-6 py-3 rounded-brand bg-secondary/40 text-white font-semibold cursor-not-allowed">
                        Donasi Sekarang
                    </button>
                @endif
            </div>
        </div>
    </div>
</section>
