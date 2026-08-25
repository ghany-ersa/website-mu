@php
    $content = $section['content'] ?? [];
    $waNumber = $content['wa_number'] ?? ($organization->whatsapp ?? null);
    $orgName = $template->structure['sample_org_name'] ?? null
        ?? $organization->name ?? null
        ?? '[Nama Organisasi]';
    $waMessage = $content['wa_message'] ?? config('page-builder.sections.ppdb.defaults.wa_message');
    $waMessage = str_replace('{org_name}', $orgName, $waMessage);
    $waHref = \App\Services\WhatsAppNumber::href($waNumber, $waMessage);
@endphp

<section class="relative overflow-hidden py-16 bg-secondary">
    <div class="absolute -top-16 -right-16 w-64 h-64 rounded-full bg-white/10 animate-blob"></div>

    <div class="relative max-w-6xl mx-auto px-6 text-center text-white">
        <h2 class="reveal text-3xl font-extrabold mb-4">{{ $content['title'] ?? 'Penerimaan Peserta Didik Baru' }}</h2>
        <p class="reveal max-w-2xl mx-auto mb-2 text-white/90" style="transition-delay: 80ms">
            {{ $content['body'] ?? 'Informasi jalur, jadwal, dan syarat pendaftaran.' }}
        </p>
        <p class="reveal text-sm text-white/70 mb-6" style="transition-delay: 120ms">
            {{ $content['deadline'] ?? 'Pendaftaran dibuka hingga [tanggal]' }}
        </p>
        @if ($waHref)
            <a href="{{ $waHref }}" target="_blank" rel="noopener"
                class="reveal inline-block px-6 py-3 rounded-brand bg-white text-secondary font-semibold transition-transform duration-200 hover:scale-105"
                style="transition-delay: 160ms">
                {{ $content['cta_label'] ?? 'Daftar Sekarang' }}
            </a>
        @else
            <button type="button" disabled
                class="reveal px-6 py-3 rounded-brand bg-white/40 text-secondary font-semibold cursor-not-allowed"
                style="transition-delay: 160ms">
                {{ $content['cta_label'] ?? 'Daftar Sekarang' }}
            </button>
        @endif
    </div>
</section>
