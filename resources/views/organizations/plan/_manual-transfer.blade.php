{{--
    Bank transfer / QRIS instructions for a Pending request. Rendered two ways from
    plan/edit.blade.php: on its own when config('billing.manual_transfer.only') is set (Midtrans
    disabled), or behind a disclosure below the Snap button otherwise. Expects $organization and
    $pendingRequest.
--}}
@php
    // Resolved here rather than inline so the QRIS block can be skipped entirely when the image
    // hasn't been dropped into public/ yet - showing a broken <img> would read as a site bug.
    $qrisPath = config('billing.manual_transfer.qris_image');
    $hasQris = filled($qrisPath) && is_file(public_path($qrisPath));
@endphp

<div class="bg-white/70 rounded-2xl p-5 space-y-4">
    <p class="text-xs text-amber-700 leading-relaxed">
        Bayar sejumlah
        <span class="font-bold text-amber-900">Rp {{ number_format($pendingRequest->gatewayAmount(), 0, ',', '.') }}</span>
        @if ($hasQris)
            via QRIS atau transfer bank, lalu konfirmasi via WhatsApp dan tekan tombol di bawah.
        @else
            ke rekening berikut, lalu konfirmasi via WhatsApp dan tekan tombol di bawah.
        @endif
    </p>

    {{-- Two columns once there's room for them: the QRIS card is portrait and dominates the
         panel when stacked above the bank details. Single column on mobile, and no grid at all
         when there's no QR to pair the bank details with. --}}
    <div @class(['grid sm:grid-cols-2 sm:gap-5' => $hasQris])>
        @if ($hasQris)
            <div class="flex flex-col items-center gap-2 pb-4 mb-4 border-b border-amber-200/70 sm:pb-0 sm:mb-0 sm:border-b-0 sm:border-r sm:pr-5">
                <p class="text-xs font-semibold text-amber-800 self-start">Scan QRIS</p>
                {{-- Height follows the image: the official QRIS card is portrait, so a fixed
                     square box would letterbox it and shrink the scannable area. --}}
                <img src="{{ asset($qrisPath) }}" alt="Kode QRIS pembayaran"
                     class="w-full max-w-[11rem] h-auto rounded-xl bg-white p-1.5 border border-amber-200/70">
                <p class="text-[11px] text-amber-700 text-center leading-relaxed">
                    GoPay, OVO, Dana, ShopeePay, m-banking.
                    @if (config('billing.manual_transfer.qris_merchant_name'))
                        <br>
                        <span class="text-amber-600">Merchant: {{ config('billing.manual_transfer.qris_merchant_name') }}</span>
                    @endif
                </p>
            </div>
        @endif

        <div>
            @if ($hasQris)
                <p class="text-xs font-semibold text-amber-800 mb-2.5">Atau transfer bank</p>
            @endif
            <div class="space-y-2.5">
                <div class="flex items-center justify-between gap-3">
                    <span class="text-xs text-amber-700 shrink-0">Bank</span>
                    <span class="text-sm font-bold text-amber-900 text-right">{{ config('billing.manual_transfer.bank_name') }}</span>
                </div>
                <div class="flex items-center justify-between gap-3" x-data="{ copied: false }">
                    <span class="text-xs text-amber-700 shrink-0">No. Rekening</span>
                    <button type="button"
                            @click="navigator.clipboard.writeText('{{ config('billing.manual_transfer.account_number') }}'); copied = true; setTimeout(() => copied = false, 2000)"
                            class="flex items-center gap-1.5 text-sm font-bold text-amber-900 hover:text-amber-700 transition-colors">
                        <span class="tabular-nums">{{ config('billing.manual_transfer.account_number') }}</span>
                        <svg x-show="!copied" class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 0 1-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 0 1 1.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 0 0-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 0 1-1.125-1.125v-9.25m10.5 0h-3.375c-.621 0-1.125.504-1.125 1.125v3.375" />
                        </svg>
                        <svg x-show="copied" x-cloak class="w-3.5 h-3.5 shrink-0 text-secondary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                        </svg>
                    </button>
                </div>
                <div class="flex items-center justify-between gap-3">
                    <span class="text-xs text-amber-700 shrink-0">Atas Nama</span>
                    <span class="text-sm font-bold text-amber-900 text-right">{{ config('billing.manual_transfer.account_holder') }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="flex flex-col sm:flex-row gap-2.5 pt-1">
        <a href="https://wa.me/{{ config('billing.manual_transfer.whatsapp') }}?text={{ urlencode('Halo, saya sudah membayar paket '.$pendingRequest->requestedPlan->name.' ('.$pendingRequest->duration_months.' bulan) sebesar Rp '.number_format($pendingRequest->gatewayAmount(), 0, ',', '.').' untuk organisasi '.$organization->name.'.') }}"
           target="_blank" rel="noopener"
           class="flex-1 text-center px-5 py-2.5 rounded-full bg-secondary hover:bg-green-700 text-white text-xs font-bold transition-colors">
            Konfirmasi via WhatsApp
        </a>
        <form action="{{ route('organizations.plan.confirm-manual-payment', [$organization, $pendingRequest]) }}" method="POST" class="flex-1"
              x-data @submit.prevent="if (await confirmAction('Konfirmasi bahwa Anda sudah melakukan pembayaran? Paket aktif setelah admin memverifikasi.', { danger: false })) $el.submit()">
            @csrf
            <button type="submit" class="w-full px-5 py-2.5 rounded-full bg-white border border-amber-300 text-amber-800 text-xs font-bold hover:bg-amber-50 transition-colors">
                Saya Sudah Bayar
            </button>
        </form>
    </div>
</div>
