@extends('layouts.organization')

@section('title', ($report->exists ? 'Edit Catatan Keuangan' : 'Tambah Catatan Keuangan').' - '.$organization->name.' - Website-mu')


@section('content')
    <div class="max-w-3xl mx-auto">
        <x-crud.back-link
            :href="route('organizations.financial-reports.index', $organization).$builderQuery"
            label="Kembali ke Laporan Keuangan" />

        <x-crud.page-header
            :title="$report->exists ? 'Edit Catatan Keuangan' : 'Tambah Catatan Keuangan'"
            :subtitle="$organization->name" />

        <x-form.shell
            :action="$report->exists ? route('organizations.financial-reports.update', [$organization, $report]) : route('organizations.financial-reports.store', $organization)"
            :method="$report->exists ? 'PATCH' : 'POST'"
            :from-builder="$fromBuilder"
            :section="request('section')">

            <x-ui.card>
                {{-- One date instead of separate month/year selects: the month/year used for
                     grouping is derived from it in the controller, so the two can't disagree. --}}
                <x-form.field name="transacted_at" label="Tanggal" type="date" required
                    :value="$report->transacted_at?->format('Y-m-d') ?? now()->format('Y-m-d')" />

                <x-form.select-field name="type" label="Jenis"
                    :options="['income' => 'Pemasukan', 'expense' => 'Pengeluaran']"
                    :selected="$report->type ?? 'income'" />

                <x-form.field name="category" label="Kategori" :value="$report->category" required
                    placeholder="mis. Infak Jum'at, Listrik, Gaji Marbot" />

                <x-form.field name="amount" label="Jumlah (Rp)" type="number" :value="$report->amount" required
                    min="0" placeholder="0" />

                <div class="flex items-center justify-end gap-3 pt-2">
                    <a href="{{ route('organizations.financial-reports.index', $organization) }}{{ $builderQuery }}"
                       class="px-5 py-2.5 rounded-full text-gray-600 text-sm font-semibold hover:bg-gray-100 transition-colors">
                        Batal
                    </a>
                    <button type="submit" class="px-5 py-2.5 rounded-full bg-primary text-white text-sm font-semibold">
                        Simpan
                    </button>
                </div>
            </x-ui.card>
        </x-form.shell>
    </div>
@endsection
