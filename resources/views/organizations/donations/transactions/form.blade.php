@extends('layouts.organization')

@section('title', 'Catat Donasi - '.$donation->name.' - Website-mu')

@section('content')
    <div class="max-w-3xl mx-auto">
        <x-crud.back-link
            :href="route('organizations.donations.edit', [$organization, $donation])"
            label="Kembali ke {{ $donation->name }}" />

        <x-crud.page-header title="Catat Donasi" :subtitle="$donation->name" />

        <form method="POST" action="{{ route('organizations.donations.transactions.store', [$organization, $donation]) }}">
            @csrf

            <x-ui.card>
                <x-form.field name="donor_name" label="Nama Donatur (opsional)"
                    placeholder="Kosongkan untuk menampilkan “Hamba Allah”" />

                <x-form.field name="amount" label="Jumlah (Rp)" type="number" required min="1" placeholder="0" />

                <x-form.field name="donated_at" label="Tanggal Donasi" type="date" required
                    :value="now()->format('Y-m-d')" />

                <div class="flex items-center justify-end gap-3 pt-2">
                    <a href="{{ route('organizations.donations.edit', [$organization, $donation]) }}"
                       class="px-5 py-2.5 rounded-full text-gray-600 text-sm font-semibold hover:bg-gray-100 transition-colors">
                        Batal
                    </a>
                    <button type="submit" class="px-5 py-2.5 rounded-full bg-primary text-white text-sm font-semibold">
                        Simpan
                    </button>
                </div>
            </x-ui.card>
        </form>
    </div>
@endsection
