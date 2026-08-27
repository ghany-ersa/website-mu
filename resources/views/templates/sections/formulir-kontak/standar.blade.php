@php
    $content = $section['content'] ?? [];
    $waNumber = $content['wa_number'] ?? ($organization->whatsapp ?? null);
    $orgName = $template->structure['sample_org_name'] ?? null
        ?? $organization->name ?? null
        ?? '[Nama Organisasi]';
    $waMessage = $content['wa_message'] ?? config('page-builder.sections.formulir-kontak.defaults.wa_message');
    $waMessage = str_replace('{org_name}', $orgName, $waMessage);
    $waHref = \App\Services\WhatsAppNumber::href($waNumber, $waMessage);
@endphp

<section class="py-16 bg-softBg">
    <div class="max-w-xl mx-auto px-6 text-center">
        <h2 class="reveal text-3xl font-extrabold text-primary mb-2">
            {{ $content['title'] ?? 'Hubungi Kami' }}
        </h2>
        <p class="reveal text-gray-500 mb-8" style="transition-delay: 60ms">
            {{ $content['subtitle'] ?? 'Ada pertanyaan? Kirim pesan langsung ke kami melalui WhatsApp.' }}
        </p>
        @if ($waHref)
            <a href="{{ $waHref }}" target="_blank" rel="noopener"
                class="reveal inline-block w-full py-3 rounded-brand bg-secondary text-white font-semibold transition-transform duration-200 hover:scale-[1.02]"
                style="transition-delay: 120ms">
                Hubungi via WhatsApp
            </a>
        @else
            <button type="button" disabled
                class="reveal w-full py-3 rounded-brand bg-secondary/40 text-white font-semibold cursor-not-allowed"
                style="transition-delay: 120ms">
                Hubungi via WhatsApp
            </button>
        @endif
    </div>
</section>
