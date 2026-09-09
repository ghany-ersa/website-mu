{{--
    Zakat calculator ported from the nurul-huda project's donation-programs/index.blade.php:
    two tabs (maal / penghasilan), rupiah inputs that reformat as you type, and a live 2.5%
    result that stays at zero until the nisab threshold is met. Entirely client-side (Alpine),
    so it works on the tenant domain's read-only, session-less request path.

    Nisab is derived from an editable gold price + gram threshold rather than hardcoded, since
    the gold price moves and each organization should be able to keep its own figure current.
--}}
@php
    $content = $section['content'] ?? [];
    $goldPrice = (int) ($content['gold_price_per_gram'] ?? 1_500_000);
    $nisabGrams = (int) ($content['nisab_grams'] ?? 85);
    $nisabAmount = $goldPrice * $nisabGrams;
    $waNumber = $content['wa_number'] ?? ($organization->whatsapp ?? null);
    $orgName = $template->structure['sample_org_name'] ?? null
        ?? $organization->name ?? null
        ?? '[Nama Organisasi]';
    $waMessage = str_replace('{org_name}', $orgName, $content['wa_message'] ?? 'Assalamu\'alaikum, saya ingin bertanya seputar zakat di {org_name}.');
    $waHref = \App\Services\WhatsAppNumber::href($waNumber, $waMessage);
@endphp

<section class="py-16 bg-slate-50">
    <div class="max-w-2xl mx-auto px-6">
        <div class="text-center">
            <h2 class="reveal text-2xl sm:text-3xl font-bold text-primary">
                {{ $content['title'] ?? 'Kalkulator Zakat' }}
            </h2>
            <p class="reveal mt-2 text-slate-600 text-sm">
                Nisab dihitung setara {{ $nisabGrams }} gram emas (estimasi Rp {{ number_format($goldPrice, 0, ',', '.') }}/gram).
            </p>
        </div>

        <div x-data="{
                tab: 'maal',
                hartaMaal: '',
                penghasilanBulanan: '',
                nisabAmount: {{ $nisabAmount }},
                zakatRate: 0.025,
                parseRupiah(value) { return Number(String(value).replace(/\D/g, '')) || 0; },
                get zakatMaal() {
                    const harta = this.parseRupiah(this.hartaMaal);
                    return harta >= this.nisabAmount ? Math.round(harta * this.zakatRate) : 0;
                },
                get zakatPenghasilan() {
                    const penghasilan = this.parseRupiah(this.penghasilanBulanan);
                    return penghasilan * 12 >= this.nisabAmount ? Math.round(penghasilan * this.zakatRate) : 0;
                },
                formatRupiah(value) { return 'Rp ' + Math.round(value).toLocaleString('id-ID'); },
                formatInput(field) {
                    const digits = this.parseRupiah(this[field]);
                    this[field] = digits === 0 ? '' : digits.toLocaleString('id-ID');
                }
             }"
             class="reveal mt-8 bg-white border border-slate-100 rounded-2xl shadow-md overflow-hidden">

            <div class="flex border-b border-slate-100">
                <button type="button" @click="tab = 'maal'"
                        :class="tab === 'maal' ? 'text-secondary border-secondary' : 'text-slate-500 border-transparent'"
                        class="flex-1 py-3 text-sm font-semibold border-b-2 transition">Zakat Maal</button>
                <button type="button" @click="tab = 'penghasilan'"
                        :class="tab === 'penghasilan' ? 'text-secondary border-secondary' : 'text-slate-500 border-transparent'"
                        class="flex-1 py-3 text-sm font-semibold border-b-2 transition">Zakat Penghasilan</button>
            </div>

            <div class="p-5 sm:p-6" x-show="tab === 'maal'">
                <label for="zakat-harta" class="text-xs text-slate-500 uppercase tracking-wide">Total Harta (tersimpan &ge; 1 tahun)</label>
                <div class="mt-1 flex items-stretch border border-slate-200 rounded-xl overflow-hidden focus-within:ring-2 focus-within:ring-secondary/40">
                    <span class="inline-flex items-center px-4 bg-slate-50 text-slate-500 font-medium border-r border-slate-200">Rp</span>
                    <input id="zakat-harta" type="text" inputmode="numeric" x-model="hartaMaal" @input="formatInput('hartaMaal')"
                           placeholder="0" class="w-full px-4 py-3 text-slate-900 focus:outline-none">
                </div>
                <div class="mt-4 p-4 rounded-xl bg-slate-50">
                    <p class="text-xs text-slate-500 uppercase tracking-wide">Zakat yang wajib dibayar (2.5%)</p>
                    <p class="mt-1 text-2xl font-bold text-primary" x-text="formatRupiah(zakatMaal)"></p>
                    <p class="mt-1 text-xs text-slate-500" x-show="hartaMaal && zakatMaal === 0" x-cloak>
                        Harta belum mencapai nisab, belum wajib zakat.
                    </p>
                </div>
            </div>

            <div class="p-5 sm:p-6" x-show="tab === 'penghasilan'" x-cloak>
                <label for="zakat-penghasilan" class="text-xs text-slate-500 uppercase tracking-wide">Penghasilan per Bulan</label>
                <div class="mt-1 flex items-stretch border border-slate-200 rounded-xl overflow-hidden focus-within:ring-2 focus-within:ring-secondary/40">
                    <span class="inline-flex items-center px-4 bg-slate-50 text-slate-500 font-medium border-r border-slate-200">Rp</span>
                    <input id="zakat-penghasilan" type="text" inputmode="numeric" x-model="penghasilanBulanan" @input="formatInput('penghasilanBulanan')"
                           placeholder="0" class="w-full px-4 py-3 text-slate-900 focus:outline-none">
                </div>
                <div class="mt-4 p-4 rounded-xl bg-slate-50">
                    <p class="text-xs text-slate-500 uppercase tracking-wide">Zakat yang wajib dibayar per bulan (2.5%)</p>
                    <p class="mt-1 text-2xl font-bold text-primary" x-text="formatRupiah(zakatPenghasilan)"></p>
                    <p class="mt-1 text-xs text-slate-500" x-show="penghasilanBulanan && zakatPenghasilan === 0" x-cloak>
                        Penghasilan setahun belum mencapai nisab, belum wajib zakat.
                    </p>
                </div>
            </div>

            @if ($waHref)
                <div class="p-5 sm:p-6 pt-0">
                    <a href="{{ $waHref }}" target="_blank" rel="noopener"
                       class="w-full inline-flex items-center justify-center gap-2 bg-green-500 hover:bg-green-600 active:scale-[.98] text-white font-semibold py-4 rounded-xl shadow-lg shadow-green-500/30 transition">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163a11.867 11.867 0 01-1.587-5.946C.16 5.335 5.495 0 12.05 0a11.817 11.817 0 018.413 3.488 11.824 11.824 0 013.48 8.414c-.003 6.557-5.338 11.892-11.893 11.892a11.9 11.9 0 01-5.688-1.448L.057 24zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884a9.86 9.86 0 001.51 5.26l-.999 3.648 3.978-1.607z"/></svg>
                        {{ $content['cta_label'] ?? 'Konsultasi Zakat' }}
                    </a>
                </div>
            @endif
        </div>
    </div>
</section>
