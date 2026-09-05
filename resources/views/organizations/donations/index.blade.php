@extends('layouts.organization')

@section('title', 'Program Donasi - '.$organization->name.' - Website-mu')

@section('content')
    <div class="max-w-3xl mx-auto">
        <x-crud.back-link
            :href="$fromBuilder ? route('organizations.builder.edit', array_filter(['organization' => $organization, 'section' => request('section')])) : route('organizations.show', $organization)"
            :label="$fromBuilder ? 'Kembali ke Page Builder' : 'Kembali ke '.$organization->name" />

        <x-crud.index-header title="Program Donasi" :subtitle="$organization->name">
            <x-slot:actions>
                <a href="{{ route('organizations.donations.create', $organization) }}{{ $builderQuery }}"
                   class="px-4 py-2 rounded-full bg-primary text-white text-sm font-semibold hover:opacity-90 shrink-0">
                    + Tambah Program
                </a>
            </x-slot:actions>
        </x-crud.index-header>

        <div class="bg-white rounded-2xl shadow-soft divide-y divide-gray-100">
            @forelse ($programs as $program)
                @php
                    $collected = $program->collectedAmount();
                    $percent = $program->progressPercent();
                    [$statusLabel, $statusClass] = match ($program->status()) {
                        'upcoming' => ['Akan Datang', 'bg-gray-100 text-gray-600'],
                        'completed' => ['Selesai', 'bg-sky-50 text-sky-700'],
                        'expired' => ['Berakhir', 'bg-red-50 text-red-600'],
                        default => ['Aktif', 'bg-emerald-50 text-emerald-700'],
                    };
                @endphp
                <div class="p-5 flex items-start gap-4">
                    <div class="w-14 h-14 rounded-xl overflow-hidden bg-gray-100 shrink-0">
                        @if ($program->cover_photo)
                            <img src="{{ $program->cover_photo }}" alt="{{ $program->name }}" class="w-full h-full object-cover">
                        @endif
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2 flex-wrap">
                            <p class="font-semibold text-gray-800 truncate">{{ $program->name }}</p>
                            <span class="text-xs font-semibold px-2 py-0.5 rounded-full {{ $statusClass }}">{{ $statusLabel }}</span>
                        </div>

                        <div class="mt-2 h-1.5 w-full bg-gray-100 rounded-full overflow-hidden">
                            <div class="h-full bg-primary rounded-full" style="width: {{ $percent }}%"></div>
                        </div>
                        <p class="mt-1.5 text-xs text-gray-500">
                            Rp {{ number_format($collected, 0, ',', '.') }}
                            dari Rp {{ number_format($program->target_amount, 0, ',', '.') }}
                            &middot; {{ $program->transactions_count }} transaksi
                        </p>
                    </div>
                    <x-crud.row-actions
                        :edit-href="route('organizations.donations.edit', [$organization, $program]).$builderQuery"
                        :delete-action="route('organizations.donations.destroy', [$organization, $program])"
                        confirm-message="Hapus program donasi ini beserta seluruh riwayat transaksinya?"
                        :from-builder="$fromBuilder"
                        :section="request('section')" />
                </div>
            @empty
                <x-ui.empty-state message="Belum ada program donasi. Tambahkan yang pertama." />
            @endforelse
        </div>
    </div>
@endsection
