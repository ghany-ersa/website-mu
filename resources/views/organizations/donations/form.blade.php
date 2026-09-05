@extends('layouts.organization')

@section('title', ($program->exists ? 'Edit Program Donasi' : 'Tambah Program Donasi').' - '.$organization->name.' - Website-mu')

@section('content')
    <div class="max-w-3xl mx-auto">
        <x-crud.back-link
            :href="route('organizations.donations.index', $organization).$builderQuery"
            label="Kembali ke Program Donasi" />

        <x-crud.page-header
            :title="$program->exists ? 'Edit Program Donasi' : 'Tambah Program Donasi'"
            :subtitle="$organization->name" />

        <x-form.shell
            :action="$program->exists ? route('organizations.donations.update', [$organization, $program]) : route('organizations.donations.store', $organization)"
            :method="$program->exists ? 'PATCH' : 'POST'"
            :from-builder="$fromBuilder"
            :section="request('section')">

            <x-ui.card>
                <x-form.field name="name" label="Nama Program" :value="$program->name" required
                    placeholder="mis. Wakaf Pembangunan Masjid" />

                <x-form.textarea-field name="description" label="Deskripsi (opsional)" :value="$program->description"
                    rows="4" placeholder="Jelaskan peruntukan dana program ini." />

                <x-form.field name="target_amount" label="Target Dana (Rp)" type="number"
                    :value="$program->target_amount" required min="1" placeholder="0" />

                <div class="grid sm:grid-cols-2 gap-4">
                    <x-form.field name="starts_at" label="Mulai (opsional)" type="date"
                        :value="$program->starts_at?->format('Y-m-d')" />

                    <x-form.field name="ends_at" label="Berakhir (opsional)" type="date"
                        :value="$program->ends_at?->format('Y-m-d')" />
                </div>

                <x-form.image-picker
                    :organization="$organization"
                    name="cover_photo"
                    label="Foto Sampul"
                    :value="$program->cover_photo"
                    category="donasi"
                    aspect="aspect-[16/9] w-full sm:w-80" />

                <div class="flex items-center justify-end gap-3 pt-2">
                    <a href="{{ route('organizations.donations.index', $organization) }}{{ $builderQuery }}"
                       class="px-5 py-2.5 rounded-full text-gray-600 text-sm font-semibold hover:bg-gray-100 transition-colors">
                        Batal
                    </a>
                    <button type="submit" class="px-5 py-2.5 rounded-full bg-primary text-white text-sm font-semibold">
                        Simpan
                    </button>
                </div>
            </x-ui.card>
        </x-form.shell>

        @if ($program->exists)
            @php
                $transactions = $program->transactions()->orderByDesc('donated_at')->get();
                $collected = $transactions->sum('amount');
            @endphp

            {{-- Transactions live on the edit screen rather than their own page: they only make
                 sense in the context of one program, and recording a donation is the most
                 frequent action here - the progress bar on the public site is their sum. --}}
            <div class="mt-8">
                <div class="flex items-end justify-between gap-3 flex-wrap mb-3">
                    <div>
                        <h2 class="font-bold text-gray-800">Riwayat Donasi</h2>
                        <p class="text-xs text-gray-500 mt-0.5">
                            Terkumpul Rp {{ number_format($collected, 0, ',', '.') }}
                            dari target Rp {{ number_format($program->target_amount, 0, ',', '.') }}
                        </p>
                    </div>
                    <a href="{{ route('organizations.donations.transactions.create', [$organization, $program]) }}"
                       class="px-4 py-2 rounded-full bg-primary text-white text-sm font-semibold hover:opacity-90 shrink-0">
                        + Catat Donasi
                    </a>
                </div>

                <div class="bg-white rounded-2xl shadow-soft divide-y divide-gray-100">
                    @forelse ($transactions as $transaction)
                        <div class="px-5 py-3.5 flex items-center gap-3">
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-medium text-gray-800 truncate">
                                    {{ $transaction->donor_name ?: 'Hamba Allah' }}
                                </p>
                                <p class="text-xs text-gray-500">{{ $transaction->donated_at->translatedFormat('d M Y') }}</p>
                            </div>
                            <p class="text-sm font-semibold text-gray-800 whitespace-nowrap">
                                Rp {{ number_format($transaction->amount, 0, ',', '.') }}
                            </p>
                            <form method="POST"
                                  action="{{ route('organizations.donations.transactions.destroy', [$organization, $program, $transaction]) }}"
                                  x-data @submit.prevent="if (await confirmAction('Hapus transaksi donasi ini?')) $el.submit()">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-gray-300 hover:text-red-500 transition p-1" title="Hapus">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm4-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </form>
                        </div>
                    @empty
                        <x-ui.empty-state message="Belum ada donasi tercatat untuk program ini." />
                    @endforelse
                </div>
            </div>
        @endif
    </div>
@endsection
