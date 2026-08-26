@extends('layouts.organization')

@section('title', 'Jaringan AUM/Ortom — '.$organization->name.' — Website-mu')

@section('content')
    <div class="max-w-3xl mx-auto">
        <x-crud.back-link
            :href="$fromBuilder ? route('organizations.builder.edit', array_filter(['organization' => $organization, 'section' => request('section')])) : route('organizations.show', $organization)"
            :label="$fromBuilder ? 'Kembali ke Page Builder' : 'Kembali ke '.$organization->name" />

        <x-crud.index-header title="Jaringan AUM/Ortom" :subtitle="$organization->name">
            <x-slot:actions>
                <a href="{{ route('organizations.networks.create', $organization) }}{{ $builderQuery }}"
                   class="px-4 py-2 rounded-full bg-primary text-white text-sm font-semibold hover:opacity-90 shrink-0">
                    + Tambah Jaringan
                </a>
            </x-slot:actions>
        </x-crud.index-header>

        <x-ui.list-panel>
            @forelse ($networks as $network)
                <div class="p-5 flex items-center justify-between gap-4">
                    <div class="min-w-0">
                        <p class="font-semibold text-gray-800 truncate">{{ $network->name }}</p>
                        @if ($network->type)
                            <p class="text-sm text-gray-500 truncate">{{ $network->type }}</p>
                        @endif
                    </div>
                    <x-crud.row-actions
                        :edit-href="route('organizations.networks.edit', [$organization, $network]).$builderQuery"
                        :delete-action="route('organizations.networks.destroy', [$organization, $network])"
                        confirm-message="Hapus item ini?"
                        :from-builder="$fromBuilder"
                        :section="request('section')" />
                </div>
            @empty
                <x-ui.empty-state message="Belum ada jaringan AUM/Ortom. Tambahkan yang pertama." />
            @endforelse
        </x-ui.list-panel>
    </div>
@endsection
