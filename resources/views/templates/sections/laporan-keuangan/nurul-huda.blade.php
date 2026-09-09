{{-- Auto-binds to the organization's financial_reports (organizations.financial-reports.*
     CMS) when $organization is in scope; falls back to placeholder figures in
     template-preview context.

     Mirrors the nurul-huda project's financial-reports/index.blade.php: year pills, a
     three-up income/expense/balance summary, then one collapsible panel per month listing
     each category - so "transparan" is something a visitor can actually inspect rather than
     two headline numbers. --}}
@php
    $content = $section['content'] ?? [];
    $monthNames = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

    if (isset($organization)) {
        $reports = $organization->financialReports()->get();
        $availableYears = $reports->pluck('period_year')->unique()->sortDesc()->values();
        $selectedYear = (int) ($availableYears->first() ?? now()->year);
        $yearReports = $reports->where('period_year', $selectedYear);
    } else {
        $reports = collect();
        $availableYears = collect([now()->year]);
        $selectedYear = (int) now()->year;
        $yearReports = collect();
    }

    $totalIncome = $yearReports->where('type', 'income')->sum('amount');
    $totalExpense = $yearReports->where('type', 'expense')->sum('amount');

    // Newest month first, same as the source site's ordering.
    $monthlySummary = $yearReports
        ->groupBy('period_month')
        ->sortKeysDesc()
        ->map(fn ($rows) => [
            'income' => $rows->where('type', 'income')->sum('amount'),
            'expense' => $rows->where('type', 'expense')->sum('amount'),
            'rows' => $rows,
        ]);

    $isPreview = ! isset($organization) || $yearReports->isEmpty();
@endphp

<section class="py-16 bg-slate-50">
    <div class="max-w-4xl mx-auto px-6">
        <div class="text-center">
            <h2 class="reveal text-2xl sm:text-3xl font-bold text-primary">
                {{ $content['title'] ?? 'Laporan Keuangan' }}
            </h2>
            <p class="reveal mt-3 text-slate-600 leading-relaxed max-w-2xl mx-auto">
                Rekapitulasi pemasukan dan pengeluaran kas masjid, dilaporkan secara terbuka setiap bulan.
            </p>
        </div>

        @if ($isPreview)
            <p class="mt-10 text-center text-slate-500">Laporan keuangan belum tersedia.</p>
        @else
            @if ($availableYears->count() > 1)
                <div class="reveal mt-8 flex justify-center gap-2 flex-wrap">
                    @foreach ($availableYears as $year)
                        <span @class([
                            'px-4 py-2 rounded-full text-sm font-semibold',
                            'bg-secondary text-white' => $year === $selectedYear,
                            'bg-white text-slate-600 border border-slate-200' => $year !== $selectedYear,
                        ])>{{ $year }}</span>
                    @endforeach
                </div>
            @endif

            <div class="reveal mt-8 grid grid-cols-3 gap-3 sm:gap-4">
                <div class="bg-white border border-slate-100 rounded-2xl p-4 sm:p-5 shadow-sm text-center">
                    <p class="text-xs text-slate-500 uppercase tracking-wide">Pemasukan</p>
                    <p class="mt-1 text-sm sm:text-lg font-bold text-emerald-600">Rp {{ number_format($totalIncome, 0, ',', '.') }}</p>
                </div>
                <div class="bg-white border border-slate-100 rounded-2xl p-4 sm:p-5 shadow-sm text-center">
                    <p class="text-xs text-slate-500 uppercase tracking-wide">Pengeluaran</p>
                    <p class="mt-1 text-sm sm:text-lg font-bold text-red-600">Rp {{ number_format($totalExpense, 0, ',', '.') }}</p>
                </div>
                <div class="bg-white border border-slate-100 rounded-2xl p-4 sm:p-5 shadow-sm text-center">
                    <p class="text-xs text-slate-500 uppercase tracking-wide">Saldo</p>
                    <p class="mt-1 text-sm sm:text-lg font-bold text-primary">Rp {{ number_format($totalIncome - $totalExpense, 0, ',', '.') }}</p>
                </div>
            </div>

            <div class="mt-10 space-y-4" x-data="{ openMonth: {{ $monthlySummary->keys()->first() ?? 'null' }} }">
                @foreach ($monthlySummary as $month => $summary)
                    @php $balance = $summary['income'] - $summary['expense']; @endphp
                    <div class="reveal bg-white border border-slate-100 rounded-2xl shadow-sm overflow-hidden">
                        <button type="button" @click="openMonth = openMonth === {{ $month }} ? null : {{ $month }}"
                                class="w-full flex items-center justify-between p-5 text-left">
                            <div>
                                <p class="font-semibold text-slate-900">{{ $monthNames[$month] }} {{ $selectedYear }}</p>
                                <p class="text-xs text-slate-500 mt-0.5">
                                    Saldo bulan ini:
                                    <span class="font-semibold {{ $balance >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                                        Rp {{ number_format($balance, 0, ',', '.') }}
                                    </span>
                                </p>
                            </div>
                            <svg class="w-5 h-5 text-slate-400 transition-transform shrink-0"
                                 :class="openMonth === {{ $month }} ? 'rotate-180' : ''"
                                 fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div x-show="openMonth === {{ $month }}" x-cloak
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0"
                             x-transition:enter-end="opacity-100"
                             class="border-t border-slate-100 px-5 py-4">
                            <div class="grid sm:grid-cols-2 gap-6">
                                @foreach (['income' => 'Pemasukan', 'expense' => 'Pengeluaran'] as $type => $label)
                                    @php $typeRows = $summary['rows']->where('type', $type); @endphp
                                    @if ($typeRows->isNotEmpty())
                                        <div>
                                            <p class="text-xs font-semibold uppercase tracking-wide {{ $type === 'income' ? 'text-emerald-600' : 'text-red-600' }}">
                                                {{ $label }}
                                            </p>
                                            <ul class="mt-2 space-y-1.5">
                                                @foreach ($typeRows as $row)
                                                    <li class="flex justify-between text-sm gap-4">
                                                        <span class="text-slate-600">
                                                            {{ $row->category }}
                                                            @if ($row->transacted_at)
                                                                <span class="text-slate-400">&middot; {{ $row->transacted_at->translatedFormat('d M') }}</span>
                                                            @endif
                                                        </span>
                                                        <span class="font-medium text-slate-900 whitespace-nowrap">Rp {{ number_format($row->amount, 0, ',', '.') }}</span>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>
