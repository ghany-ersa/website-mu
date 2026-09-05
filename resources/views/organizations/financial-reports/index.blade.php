@extends('layouts.organization')

@section('title', 'Laporan Keuangan - '.$organization->name.' - Website-mu')

@php
    $monthNames = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
@endphp

@section('content')
    <div class="max-w-3xl mx-auto">
        <x-crud.back-link
            :href="$fromBuilder ? route('organizations.builder.edit', array_filter(['organization' => $organization, 'section' => request('section')])) : route('organizations.show', $organization)"
            :label="$fromBuilder ? 'Kembali ke Page Builder' : 'Kembali ke '.$organization->name" />

        <x-crud.index-header title="Laporan Keuangan" :subtitle="$organization->name">
            <x-slot:actions>
                <a href="{{ route('organizations.financial-reports.create', $organization) }}{{ $builderQuery }}"
                   class="px-4 py-2 rounded-full bg-primary text-white text-sm font-semibold hover:opacity-90 shrink-0">
                    + Tambah Catatan
                </a>
            </x-slot:actions>
        </x-crud.index-header>

        @if ($groupedReports->isEmpty())
            <div class="bg-white rounded-2xl shadow-soft">
                <x-ui.empty-state message="Belum ada catatan keuangan. Tambahkan yang pertama." />
            </div>
        @else
            {{-- Grouped by month, newest first, with each month's balance up front - the same
                 shape the public section renders, so what the admin sees matches the site. --}}
            <div class="space-y-5">
                @foreach ($groupedReports as $rows)
                    @php
                        $first = $rows->first();
                        $income = $rows->where('type', 'income')->sum('amount');
                        $expense = $rows->where('type', 'expense')->sum('amount');
                        $balance = $income - $expense;
                    @endphp
                    <div class="bg-white rounded-2xl shadow-soft overflow-hidden">
                        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between gap-3 flex-wrap">
                            <p class="font-bold text-gray-800">
                                {{ $monthNames[$first->period_month] }} {{ $first->period_year }}
                            </p>
                            <p class="text-xs text-gray-500">
                                Saldo:
                                <span class="font-semibold {{ $balance >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                                    Rp {{ number_format($balance, 0, ',', '.') }}
                                </span>
                            </p>
                        </div>

                        <div class="divide-y divide-gray-100">
                            @foreach ($rows as $report)
                                <div class="px-5 py-3.5 flex items-center gap-3">
                                    <span @class([
                                        'text-xs font-semibold px-2 py-0.5 rounded-full shrink-0',
                                        'bg-emerald-50 text-emerald-700' => $report->type === 'income',
                                        'bg-red-50 text-red-600' => $report->type !== 'income',
                                    ])>
                                        {{ $report->type === 'income' ? 'Masuk' : 'Keluar' }}
                                    </span>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm text-gray-700 truncate">{{ $report->category }}</p>
                                        @if ($report->transacted_at)
                                            <p class="text-xs text-gray-400">{{ $report->transacted_at->translatedFormat('d M Y') }}</p>
                                        @endif
                                    </div>
                                    <p class="text-sm font-semibold text-gray-800 whitespace-nowrap">
                                        Rp {{ number_format($report->amount, 0, ',', '.') }}
                                    </p>
                                    <x-crud.row-actions
                                        :edit-href="route('organizations.financial-reports.edit', [$organization, $report]).$builderQuery"
                                        :delete-action="route('organizations.financial-reports.destroy', [$organization, $report])"
                                        confirm-message="Hapus catatan ini?"
                                        :from-builder="$fromBuilder"
                                        :section="request('section')" />
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endsection
