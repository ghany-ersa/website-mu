@extends('layouts.organization')

@section('title', 'Langganan — '.$organization->name.' — Website-mu')

@section('content')
    @php
        $usageLabels = [
            'posts' => 'Berita',
            'agendas' => 'Agenda',
            'announcements' => 'Pengumuman',
            'officers' => 'Data Pengurus',
            'programs' => 'Program/Layanan',
            'gallery_photos' => 'Foto Galeri',
            'sections_total' => 'Komponen di Situs',
        ];
        $usageIcons = [
            'posts' => 'M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z',
            'agendas' => 'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5',
            'announcements' => 'M10.34 15.84c-.688-.06-1.386-.09-2.09-.09H7.5a4.5 4.5 0 1 1 0-9h.75c.704 0 1.402-.03 2.09-.09m0 9.18c.253.962.584 1.892.985 2.783.247.55.06 1.21-.463 1.511l-.657.38c-.551.318-1.26.117-1.527-.461a20.6 20.6 0 0 1-1.44-4.282m3.102.069a18.03 18.03 0 0 1-.59-4.59c0-1.586.205-3.124.59-4.59m0 9.18a23.848 23.848 0 0 1 8.835 2.535M10.34 6.66a23.847 23.847 0 0 0 8.835-2.535m0 0A23.74 23.74 0 0 0 18.795 3m.38 1.125a23.91 23.91 0 0 1 1.014 5.395m-1.014 8.855c-.118.38-.245.754-.38 1.125m.38-1.125a23.91 23.91 0 0 0 1.014-5.395m0-3.46c.495.413.811 1.035.811 1.73 0 .695-.316 1.317-.811 1.73m0-3.46a24.347 24.347 0 0 1 0 3.46',
            'officers' => 'M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z',
            'programs' => 'M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 1 1-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 0 0 4.486-6.336l-3.276 3.277a3.004 3.004 0 0 1-2.25-2.25l3.276-3.276a4.5 4.5 0 0 0-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437 5.61 5.61a1 1 0 0 1 0 1.414l-.708.707a1 1 0 0 1-1.414 0l-5.61-5.61',
            'gallery_photos' => 'M2.25 15.75l5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3 8.25v8.25a1.5 1.5 0 0 0 1.5 1.5h15a1.5 1.5 0 0 0 1.5-1.5V8.25m-18 0V6a1.5 1.5 0 0 1 1.5-1.5h15a1.5 1.5 0 0 1 1.5 1.5v2.25m-18 0h18M9.75 6.75h.008v.008H9.75V6.75Z',
            'sections_total' => 'M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z',
        ];
    @endphp

    <div class="max-w-5xl mx-auto">
        <a href="{{ route('organizations.show', $organization) }}"
           class="inline-flex items-center gap-1.5 text-sm font-medium text-gray-500 hover:text-primary transition-colors mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4">
                <path fill-rule="evenodd" d="M17 10a.75.75 0 0 1-.75.75H5.612l4.158 3.96a.75.75 0 1 1-1.04 1.08l-5.5-5.25a.75.75 0 0 1 0-1.08l5.5-5.25a.75.75 0 1 1 1.04 1.08L5.612 9.25H16.25A.75.75 0 0 1 17 10Z" clip-rule="evenodd" />
            </svg>
            Kembali ke {{ $organization->name }}
        </a>

        <div class="mb-8">
            <span class="text-primary font-bold tracking-wider uppercase text-xs bg-blue-50 px-3 py-1 rounded-full">Langganan</span>
            <h1 class="text-3xl font-extrabold text-gray-900 mt-3">Kelola Paket {{ $organization->name }}</h1>
            <p class="text-sm text-gray-500 mt-1">Pantau pemakaian kuota dan pilih paket yang paling sesuai kebutuhan organisasi.</p>
        </div>

        {{-- Usage overview: visual progress bars instead of plain numbers, so a near-full quota is obvious at a glance. --}}
        <div class="bg-white rounded-[1.5rem] sm:rounded-[2rem] shadow-soft border border-gray-100 p-5 sm:p-6 md:p-8 mb-6 sm:mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1 mb-3">
                <div class="min-w-0">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Paket Aktif</p>
                    <p class="text-lg sm:text-xl font-extrabold text-gradient truncate">{{ $organization->plan?->name ?? 'Belum Diatur' }}</p>
                </div>
                @if ($organization->plan)
                    <span class="text-xs font-bold text-gray-400 shrink-0">{{ $organization->plan->formattedPrice() }}</span>
                @endif
            </div>

            @if ($organization->plan)
                <div class="mb-6">
                    @if ($organization->hasPaidForCurrentPlan())
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-secondary/10 text-secondary text-xs font-bold px-3 py-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                            </svg>
                            Aktif sampai {{ $organization->plan_expires_at->translatedFormat('d M Y') }}
                        </span>
                    @elseif ($organization->planIsExpired())
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-red-100 text-red-700 text-xs font-bold px-3 py-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                            </svg>
                            Berakhir sejak {{ $organization->plan_expires_at->translatedFormat('d M Y') }}
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-100 text-amber-700 text-xs font-bold px-3 py-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2" />
                                <circle cx="12" cy="12" r="9" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            Menunggu Konfirmasi Pembayaran
                        </span>
                    @endif
                </div>
            @endif

            <div class="grid sm:grid-cols-2 gap-4">
                @foreach ($usage as $key => $stat)
                    @php
                        $used = $stat['used'];
                        $limit = $stat['limit'];
                        $remaining = $stat['remaining'];
                        $isOverLimit = $limit !== null && $used > $limit;
                        $isNearFull = ! $isOverLimit && $remaining !== null && $remaining <= 1;
                        $pct = ($limit && $limit > 0) ? min(100, round(($used / $limit) * 100)) : ($isOverLimit ? 100 : 0);
                    @endphp
                    <div class="rounded-2xl px-4 py-3.5 {{ $isOverLimit ? 'bg-red-50 ring-1 ring-red-200' : 'bg-gray-50/80' }}">
                        <div class="flex items-center justify-between mb-2">
                            <span class="inline-flex items-center gap-2 text-sm font-semibold {{ $isOverLimit ? 'text-red-700' : 'text-gray-700' }}">
                                <svg class="w-4 h-4 {{ $isOverLimit ? 'text-red-400' : 'text-gray-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $usageIcons[$key] ?? '' }}" />
                                </svg>
                                {{ $usageLabels[$key] ?? $key }}
                            </span>
                            <span class="inline-flex items-center gap-1 text-xs font-bold {{ $isOverLimit ? 'text-red-600' : ($isNearFull ? 'text-red-500' : 'text-gray-500') }}">
                                @if ($isOverLimit)
                                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495ZM10 5a.75.75 0 0 1 .75.75v3.5a.75.75 0 0 1-1.5 0v-3.5A.75.75 0 0 1 10 5Zm0 8a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd" />
                                    </svg>
                                    {{ $used }}/{{ $limit }} &mdash; {{ $used - $limit }} kelebihan
                                @else
                                    {{ $remaining === null ? 'Tanpa Batas' : "Sisa {$remaining}" }}
                                @endif
                            </span>
                        </div>
                        @if ($limit !== null)
                            <div class="h-1.5 rounded-full bg-gray-200 overflow-hidden">
                                <div class="h-full rounded-full transition-all {{ $isOverLimit ? 'bg-red-500' : ($isNearFull ? 'bg-red-300' : 'bg-gradient-to-r from-primary to-secondary') }}"
                                     style="width: {{ $pct }}%"></div>
                            </div>
                        @else
                            <div class="h-1.5 rounded-full bg-gradient-to-r from-primary/30 to-secondary/30"></div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        @if ($pendingRequest && $pendingRequest->status === \App\Enums\PlanChangeRequestStatus::PaymentConfirmed)
            <div class="rounded-[2rem] p-6 md:p-8 mb-6 bg-gradient-to-br from-blue-50 to-indigo-50 border border-blue-200/70 flex items-start gap-4">
                <div class="w-11 h-11 rounded-2xl bg-blue-100 text-blue-500 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </div>
                <div>
                    <p class="font-bold text-blue-900">Menunggu Verifikasi Admin</p>
                    <p class="text-sm text-blue-700 mt-1 leading-relaxed">
                        Terima kasih, konfirmasi pembayaran Anda untuk paket <span class="font-semibold">{{ $pendingRequest->requestedPlan->name }}</span>
                        ({{ $pendingRequest->duration_months }} bulan &mdash; Rp {{ number_format($pendingRequest->totalPrice(), 0, ',', '.') }}
                        @if ($pendingRequest->discount_amount > 0)
                            , voucher "{{ $pendingRequest->discountCode?->code }}" diterapkan
                        @endif
                        )
                        sudah kami terima dan sedang diverifikasi admin. Paket akan aktif begitu diverifikasi &mdash; biasanya dalam 1x24 jam.
                    </p>
                </div>
            </div>
        @elseif ($pendingRequest)
            <div class="rounded-[2rem] p-6 md:p-8 mb-6 bg-gradient-to-br from-amber-50 to-orange-50 border border-amber-200/70">
                <div class="flex items-start gap-4 mb-5">
                    <div class="w-11 h-11 rounded-2xl bg-amber-100 text-amber-500 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2" />
                            <circle cx="12" cy="12" r="9" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </div>
                    <div>
                        <p class="font-bold text-amber-900">Selesaikan Pembayaran</p>
                        <p class="text-sm text-amber-700 mt-1 leading-relaxed">
                            Permintaan pindah ke paket <span class="font-semibold">{{ $pendingRequest->requestedPlan->name }}</span>
                            ({{ $pendingRequest->duration_months }} bulan) telah dibuat. Selesaikan pembayaran berikut, lalu konfirmasi di bawah.
                        </p>
                    </div>
                </div>

                <div class="bg-white/70 rounded-2xl p-5 space-y-4" x-data="{ copied: null }">
                    @if ($pendingRequest->discount_amount > 0)
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-amber-700">
                                Subtotal
                                @if ($pendingRequest->discountCode)
                                    &middot; voucher "{{ $pendingRequest->discountCode->code }}"
                                @endif
                            </span>
                            <span class="text-amber-700">
                                <span class="line-through text-amber-400">Rp {{ number_format($pendingRequest->subtotal(), 0, ',', '.') }}</span>
                                &minus; Rp {{ number_format($pendingRequest->discount_amount, 0, ',', '.') }}
                            </span>
                        </div>
                    @endif
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-amber-800 uppercase tracking-wide">Total Tagihan</span>
                        <span class="text-lg font-extrabold text-amber-900">Rp {{ number_format($pendingRequest->totalPrice(), 0, ',', '.') }}</span>
                    </div>

                    <div class="space-y-2">
                        @foreach (config('billing.bank_transfers', []) as $i => $account)
                            <div class="flex items-center justify-between gap-3 rounded-xl bg-white px-4 py-3 border border-amber-100">
                                <div class="min-w-0">
                                    <p class="text-xs text-gray-500">{{ $account['bank'] }} &middot; a.n. {{ $account['account_name'] }}</p>
                                    <p class="font-mono font-semibold text-gray-800 truncate">{{ $account['account_number'] }}</p>
                                </div>
                                <button type="button"
                                    @click="navigator.clipboard.writeText('{{ $account['account_number'] }}'); copied = {{ $i }}; setTimeout(() => copied = null, 1500)"
                                    class="shrink-0 px-3 py-1.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-700 hover:bg-amber-200 transition-colors">
                                    <span x-show="copied !== {{ $i }}">Salin</span>
                                    <span x-show="copied === {{ $i }}" x-cloak>Tersalin!</span>
                                </button>
                            </div>
                        @endforeach
                    </div>
                </div>

                <form action="{{ route('organizations.plan.confirm-payment', [$organization, $pendingRequest]) }}" method="POST"
                    x-data @submit.prevent="if (await confirmAction('Konfirmasi bahwa Anda sudah melakukan pembayaran sejumlah Rp {{ number_format($pendingRequest->totalPrice(), 0, ',', '.') }}?', { danger: false, confirmLabel: 'Ya, Sudah Bayar' })) $el.submit()"
                    class="mt-5">
                    @csrf
                    <button type="submit"
                        class="w-full sm:w-auto px-6 py-3 rounded-full bg-amber-500 hover:bg-amber-600 text-white text-sm font-bold shadow-soft transition-colors">
                        Saya Sudah Bayar
                    </button>
                </form>
            </div>
        @else
            <form action="{{ route('organizations.plan.store', $organization) }}" method="POST"
                  x-data='{
                      "selected": {{ $organization->plan_id ?? 'null' }},
                      "confirming": false,
                      "plans": @json($plansForConfirm),
                      "duration": 3,
                      "discountCode": "",
                      "appliedDiscount": null,
                      "applyingDiscount": false,
                      "discountError": "",
                      "planExpiresAt": {{ $planExpiresAt ? '"'.$planExpiresAt.'"' : 'null' }},
                      "applyDiscountUrl": "{{ route('organizations.plan.apply-discount', $organization) }}",
                      "csrfToken": "{{ csrf_token() }}",
                      price(id) { return this.plans[id]?.prices?.[this.duration] ?? 0; },
                      discountPercent(id, months = this.duration) { return this.plans[id]?.discounts?.[months] ?? 0; },
                      savings(id) { return this.plans[id]?.savings?.[this.duration] ?? 0; },
                      formatRupiah(n) { return n === 0 ? "Gratis" : ("Rp " + n.toLocaleString("id-ID")); },
                      finalTotal(id) { return Math.max(0, this.price(id) - (this.appliedDiscount?.amount ?? 0)); },
                      activeUntilLabel() {
                          const baseline = (this.planExpiresAt && new Date(this.planExpiresAt) > new Date()) ? new Date(this.planExpiresAt) : new Date();
                          baseline.setMonth(baseline.getMonth() + this.duration);
                          return baseline.toLocaleDateString("id-ID", { day: "numeric", month: "long", year: "numeric" });
                      },
                      async applyDiscount() {
                          if (! this.discountCode.trim() || ! this.selected) return;
                          this.applyingDiscount = true;
                          this.discountError = "";
                          let res;
                          try {
                              res = await fetch(this.applyDiscountUrl, {
                                  method: "POST",
                                  headers: {
                                      "Content-Type": "application/json",
                                      "Accept": "application/json",
                                      "X-CSRF-TOKEN": this.csrfToken,
                                  },
                                  body: JSON.stringify({ discount_code: this.discountCode, plan_id: this.selected, duration_months: this.duration }),
                              });
                          } catch (e) {
                              this.discountError = "Tidak dapat terhubung ke internet. Periksa koneksi Anda dan coba lagi.";
                              this.appliedDiscount = null;
                              this.applyingDiscount = false;
                              return;
                          }
                          try {
                              const data = await res.json();
                              if (! res.ok) {
                                  this.discountError = data.errors?.discount_code?.[0] ?? "Kode diskon tidak valid atau sudah tidak berlaku.";
                                  this.appliedDiscount = null;
                                  return;
                              }
                              this.appliedDiscount = data;
                          } catch (e) {
                              this.discountError = "Yaaah... Coba voucher lagi nanti, ya.";
                              this.appliedDiscount = null;
                          } finally {
                              this.applyingDiscount = false;
                          }
                      }
                  }'
                  x-init="$watch('selected', () => { appliedDiscount = null; discountError = '' }); $watch('duration', () => { appliedDiscount = null; discountError = '' })">
                @csrf

                {{-- Mobile: horizontal snap-scroll carousel (one card per swipe). Desktop (sm+): plain 2-col grid, no scrolling needed. --}}
                <div class="flex sm:grid sm:grid-cols-3 gap-4 sm:gap-6 overflow-x-auto sm:overflow-visible snap-x snap-mandatory sm:snap-none -mx-5 p-5 sm:mx-0 sm:px-0 pb-2 sm:pb-0 [&::-webkit-scrollbar]:hidden [-ms-overflow-style:none] [scrollbar-width:none]">
                    @foreach ($plans as $plan)
                        @php
                            $isActive = $organization->plan_id === $plan->id;
                            $isFeatured = $plan->id === $plans->last()?->id;
                        @endphp
                        <label
                            @click="selected = {{ $plan->id }}"
                            class="group relative flex flex-col shrink-0 w-[85vw] sm:w-auto snap-center rounded-[1.5rem] sm:rounded-[2rem] p-5 sm:p-7 cursor-pointer transition-all duration-300 sm:hover:-translate-y-1 outline outline-2 outline-offset-2 outline-transparent
                                {{ $isFeatured ? 'bg-gradient-to-br from-primary via-primary to-secondary text-white shadow-float' : 'bg-white border border-gray-100 shadow-soft hover:shadow-float' }}"
                            :class="selected === {{ $plan->id }} ? '!outline-secondary sm:scale-[1.02]' : ''">

                            {{-- Selected checkmark badge — always visible in the corner once chosen, independent of card color, so selection reads instantly regardless of the featured gradient background. --}}
                            <div x-show="selected === {{ $plan->id }}" x-cloak
                                class="absolute -top-2.5 -right-2.5 sm:-top-3 sm:-right-3 w-8 h-8 sm:w-9 sm:h-9 rounded-full bg-secondary text-white flex items-center justify-center shadow-lg ring-4 ring-white">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" />
                                </svg>
                            </div>

                            <input type="radio" name="plan_id" value="{{ $plan->id }}" class="sr-only" {{ $isActive ? 'checked' : '' }}>

                            <div class="flex flex-wrap items-center justify-between gap-2 mb-4">
                                <span class="inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider px-3 py-1 rounded-full
                                    {{ $isFeatured ? 'bg-white/15 text-white' : 'bg-gray-100 text-gray-500' }}">
                                    {{ $plan->name }}
                                </span>
                                @if ($isActive)
                                    <span class="inline-flex items-center gap-1 text-xs font-extrabold px-3 py-1 rounded-full {{ $isFeatured ? 'bg-white text-primary' : 'bg-secondary/10 text-secondary' }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $isFeatured ? 'bg-primary' : 'bg-secondary' }} animate-pulse"></span>
                                        Aktif
                                    </span>
                                @elseif ($isFeatured)
                                    <span class="text-xs font-bold text-amber-200">✦ Rekomendasi</span>
                                @endif
                            </div>

                            <p class="text-3xl sm:text-4xl font-extrabold {{ $isFeatured ? 'text-white' : 'text-gray-900' }}">
                                @if ($plan->price_monthly === 0)
                                    Gratis
                                @else
                                    Rp {{ number_format($plan->price_monthly, 0, ',', '.') }}
                                    <span class="text-xs sm:text-sm font-semibold {{ $isFeatured ? 'text-white/70' : 'text-gray-400' }}">/bulan</span>
                                @endif
                            </p>
                            @if ($plan->price_monthly > 0)
                                <p class="text-xs {{ $isFeatured ? 'text-white/70' : 'text-gray-400' }} mt-0.5">
                                    Total <span x-text="duration"></span> bulan:
                                    <span class="font-semibold" x-text="formatRupiah(price({{ $plan->id }}))"></span>
                                    <template x-if="discountPercent({{ $plan->id }}) > 0">
                                        <span class="font-bold {{ $isFeatured ? 'text-white' : 'text-secondary' }}" x-text="' — hemat ' + discountPercent({{ $plan->id }}) + '%'"></span>
                                    </template>
                                </p>
                            @endif
                            <p class="text-sm mt-2 mb-5 sm:mb-6 {{ $isFeatured ? 'text-white/80' : 'text-gray-500' }}">{{ $plan->description }}</p>

                            <ul class="space-y-2.5 sm:space-y-3 text-sm mb-6 sm:mb-8 flex-1">
                                @foreach ($plan->pricingFeatures() as $feature)
                                    <li class="flex items-start gap-2.5 {{ $feature['available'] ? ($isFeatured ? 'text-white/90' : 'text-gray-600') : ($isFeatured ? 'text-white/50' : 'text-gray-400') }}">
                                        @if ($feature['available'])
                                            <svg class="w-4 h-4 mt-0.5 shrink-0 {{ $isFeatured ? 'text-white' : 'text-secondary' }}" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" />
                                            </svg>
                                        @else
                                            <svg class="w-4 h-4 mt-0.5 shrink-0 {{ $isFeatured ? 'text-white/50' : 'text-red-400' }}" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z" clip-rule="evenodd" />
                                            </svg>
                                        @endif
                                        <span class="min-w-0">{{ $feature['label'] }}</span>
                                    </li>
                                @endforeach
                            </ul>

                            <div class="flex items-center gap-2.5 pt-4 border-t {{ $isFeatured ? 'border-white/20' : 'border-gray-100' }}">
                                <span class="w-5 h-5 rounded-full border-2 flex items-center justify-center shrink-0 transition-colors
                                    {{ $isFeatured ? 'border-white' : 'border-gray-300 group-hover:border-primary' }}"
                                    x-show="selected !== {{ $plan->id }}"></span>
                                <span class="w-5 h-5 rounded-full flex items-center justify-center shrink-0 {{ $isFeatured ? 'bg-white' : 'bg-primary' }}"
                                    x-show="selected === {{ $plan->id }}" x-cloak>
                                    <svg class="w-3 h-3 {{ $isFeatured ? 'text-primary' : 'text-white' }}" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                                <span class="text-sm font-bold" :class="selected === {{ $plan->id }} ? '{{ $isFeatured ? 'text-white' : 'text-primary' }}' : '{{ $isFeatured ? 'text-white/80 font-semibold' : 'text-gray-500 font-semibold' }}'">
                                    <span x-show="selected === {{ $plan->id }}" x-cloak>✓ Terpilih</span>
                                    <span x-show="selected !== {{ $plan->id }}">Pilih paket ini</span>
                                </span>
                            </div>
                        </label>
                    @endforeach
                </div>

                {{-- Swipe dots: mobile-only hint that more cards exist off-screen. Purely visual, doesn't drive scroll. --}}
                <div class="flex sm:hidden items-center justify-center gap-1.5 mt-3">
                    @foreach ($plans as $plan)
                        <span class="w-1.5 h-1.5 rounded-full {{ $organization->plan_id === $plan->id ? 'bg-primary w-4' : 'bg-gray-300' }} transition-all"></span>
                    @endforeach
                </div>

                {{-- Duration + voucher + live total, shown after the plan is picked so the
                     summary always reflects a concrete plan rather than an empty placeholder. --}}
                <div class="bg-white rounded-[1.5rem] sm:rounded-[2rem] shadow-soft border border-gray-100 p-5 sm:p-6 md:p-8 mt-6 sm:mt-8">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2.5">Durasi Langganan</p>
                    <div class="grid grid-cols-3 gap-2 sm:gap-3">
                        @foreach ([3, 6, 12] as $months)
                            <label
                                @click="duration = {{ $months }}"
                                class="relative flex flex-col items-center justify-center gap-1 rounded-xl border-2 py-2.5 sm:py-3 text-xs sm:text-sm font-bold text-center cursor-pointer transition-colors"
                                :class="duration === {{ $months }} ? 'border-primary bg-primary/5 text-primary' : 'border-gray-200 text-gray-500 hover:border-gray-300'">
                                <input type="radio" name="duration_months" value="{{ $months }}" class="sr-only" {{ $months === 3 ? 'checked' : '' }}>
                                {{ $months }} Bulan
                                <template x-if="selected && discountPercent(selected, {{ $months }}) > 0">
                                    <span class="text-[9px] sm:text-[10px] font-extrabold px-1.5 py-0.5 rounded-full bg-secondary/10 text-secondary" x-text="'Hemat ' + discountPercent(selected, {{ $months }}) + '%'"></span>
                                </template>
                            </label>
                        @endforeach
                    </div>

                    <div class="mt-5">
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2" for="discount_code">
                            Kode Voucher <span class="font-normal normal-case text-gray-400">(opsional)</span>
                        </label>
                        <div class="flex items-center gap-2">
                            <input type="text" name="discount_code" id="discount_code"
                                   x-model="discountCode"
                                   @input="appliedDiscount = null; discountError = ''"
                                   value="{{ old('discount_code') }}"
                                   placeholder="Masukkan kode jika ada"
                                   class="w-full sm:w-64 rounded-xl border border-gray-200 px-3.5 py-2.5 text-sm uppercase focus:outline-none focus:ring-2 focus:ring-primary/30">
                            <button type="button" @click="applyDiscount()"
                                    :disabled="!discountCode.trim() || !selected || applyingDiscount || appliedDiscount"
                                    class="shrink-0 px-4 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                                <span x-show="!applyingDiscount" x-text="appliedDiscount ? 'Diterapkan' : 'Terapkan'"></span>
                                <span x-show="applyingDiscount" x-cloak>Memeriksa&hellip;</span>
                            </button>
                        </div>
                        <p x-show="!selected" class="text-xs text-gray-400 mt-1.5">Pilih paket terlebih dahulu untuk menerapkan voucher.</p>
                        <template x-if="appliedDiscount">
                            <p class="text-xs text-secondary font-semibold mt-1.5">
                                Voucher "<span x-text="appliedDiscount.code"></span>" diterapkan &mdash; hemat <span x-text="formatRupiah(appliedDiscount.amount)"></span>.
                            </p>
                        </template>
                        <p x-show="discountError" x-text="discountError" class="text-xs text-red-500 mt-1.5"></p>
                        @error('discount_code') <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p> @enderror
                    </div>

                    {{-- Live summary for whichever plan is currently selected — this is the
                         "how much, until when" preview that used to only appear inside the
                         confirmation modal after the fact. Stacked (not side-by-side) below sm
                         so neither the price nor the date gets squeezed on narrow screens. --}}
                    <template x-if="selected">
                        <div class="mt-5 pt-5 border-t border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                            <div>
                                <p class="text-xs text-gray-400">Total untuk <span x-text="plans[selected]?.name"></span> &middot; <span x-text="duration"></span> bulan</p>
                                <p class="text-xl font-extrabold text-gray-900 flex flex-wrap items-baseline gap-x-2 gap-y-0.5">
                                    <template x-if="appliedDiscount">
                                        <span class="text-sm font-semibold text-gray-400 line-through" x-text="formatRupiah(price(selected))"></span>
                                    </template>
                                    <span x-text="formatRupiah(finalTotal(selected))"></span>
                                    <template x-if="savings(selected) > 0">
                                        <span class="text-xs font-bold text-secondary" x-text="'Hemat Rp ' + savings(selected).toLocaleString('id-ID')"></span>
                                    </template>
                                    <template x-if="appliedDiscount">
                                        <span class="text-xs font-bold text-amber-600" x-text="'+ voucher Rp ' + appliedDiscount.amount.toLocaleString('id-ID')"></span>
                                    </template>
                                </p>
                            </div>
                            <div class="sm:text-right">
                                <p class="text-xs text-gray-400">Aktif sampai</p>
                                <p class="text-sm font-bold text-secondary" x-text="activeUntilLabel()"></p>
                            </div>
                        </div>
                    </template>
                </div>

                @error('plan_id') <p class="text-xs text-red-500 mt-4">{{ $message }}</p> @enderror
                @error('duration_months') <p class="text-xs text-red-500 mt-4">{{ $message }}</p> @enderror

                <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4 mt-6 sm:mt-8 bg-gray-50 rounded-2xl px-5 sm:px-6 py-4">
                    <p class="text-xs text-gray-500 text-center sm:text-left">
                        Permintaan pergantian paket akan menunggu konfirmasi pembayaran dari admin sebelum aktif.
                    </p>
                    <div class="flex items-center gap-3 shrink-0">
                        <a href="{{ route('organizations.show', $organization) }}"
                           class="flex-1 sm:flex-initial text-center px-5 py-2.5 rounded-full text-gray-600 text-sm font-semibold hover:bg-gray-200 transition-colors">
                            Batal
                        </a>
                        <button type="button" @click="confirming = true" :disabled="!selected"
                                class="flex-1 sm:flex-initial px-6 py-2.5 rounded-full bg-primary hover:bg-secondary text-white text-sm font-bold shadow-soft hover:shadow-float transition-all disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-primary disabled:hover:shadow-soft">
                            Ajukan Paket
                        </button>
                    </div>
                </div>

                {{-- Confirmation modal: shown before the request actually submits, so the owner
                     sees exactly which plan/price they're committing to before it goes to admin. --}}
                <div x-show="confirming" x-cloak
                    class="fixed inset-0 z-[100] bg-gray-900/50 backdrop-blur-sm flex items-center justify-center p-4"
                    @keydown.escape.window="confirming = false">
                    <div @click.outside="confirming = false"
                        class="bg-white rounded-2xl w-full max-w-sm shadow-2xl p-6 text-center">
                        <div class="w-12 h-12 rounded-full bg-blue-50 text-primary flex items-center justify-center mx-auto mb-4">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                        </div>
                        <h3 class="font-bold text-gray-800 mb-1">Konfirmasi Perubahan Paket</h3>
                        <template x-if="selected">
                            <div class="text-sm text-gray-500 mb-6 text-left space-y-3">
                                <p>
                                    Anda akan mengajukan perubahan ke paket
                                    <span class="font-semibold text-gray-800" x-text="plans[selected]?.name"></span>
                                    selama <span class="font-semibold text-gray-800" x-text="duration"></span> bulan.
                                </p>
                                <div class="bg-gray-50 rounded-xl p-3.5 space-y-1.5">
                                    <div class="flex items-center justify-between">
                                        <span>Subtotal</span>
                                        <span class="font-semibold text-gray-800" x-text="formatRupiah(price(selected))"></span>
                                    </div>
                                    <template x-if="appliedDiscount">
                                        <div class="flex items-center justify-between text-xs text-amber-600">
                                            <span>Voucher "<span x-text="appliedDiscount.code"></span>"</span>
                                            <span x-text="'- ' + formatRupiah(appliedDiscount.amount)"></span>
                                        </div>
                                    </template>
                                    <template x-if="!appliedDiscount && discountCode.trim() !== ''">
                                        <div class="flex items-center justify-between text-xs text-red-500">
                                            <span>Kode voucher "<span x-text="discountCode.toUpperCase()"></span>"</span>
                                            <span>belum diterapkan</span>
                                        </div>
                                    </template>
                                    <div class="flex items-center justify-between pt-1.5 border-t border-gray-200">
                                        <span class="font-semibold text-gray-700">Total</span>
                                        <span class="font-semibold text-gray-800" x-text="formatRupiah(finalTotal(selected))"></span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="font-semibold text-gray-700">Aktif sampai</span>
                                        <span class="font-semibold text-secondary" x-text="activeUntilLabel()"></span>
                                    </div>
                                </div>
                                <p class="text-xs">Permintaan ini menunggu konfirmasi pembayaran dari admin sebelum aktif.</p>
                            </div>
                        </template>
                        <div class="flex items-center justify-center gap-3">
                            <button type="button" @click="confirming = false"
                                class="px-4 py-2.5 rounded-full text-gray-600 text-sm font-semibold hover:bg-gray-100 transition-colors">
                                Batal
                            </button>
                            <button type="submit"
                                class="px-4 py-2.5 rounded-full bg-primary text-white text-sm font-semibold hover:bg-secondary transition-colors">
                                Ya, Ajukan
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        @endif
    </div>
@endsection
