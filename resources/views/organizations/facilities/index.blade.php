@extends('layouts.organization')

@section('title', 'Fasilitas - '.$organization->name.' - Website-mu')

@section('content')
    <div class="max-w-3xl mx-auto">
        <x-crud.back-link
            :href="$fromBuilder ? route('organizations.builder.edit', array_filter(['organization' => $organization, 'section' => request('section')])) : route('organizations.show', $organization)"
            :label="$fromBuilder ? 'Kembali ke Page Builder' : 'Kembali ke '.$organization->name" />

        <x-crud.index-header title="Fasilitas Masjid" :subtitle="$organization->name">
            <x-slot:actions>
                <a href="{{ route('organizations.facilities.create', $organization) }}{{ $builderQuery }}"
                   class="px-4 py-2 rounded-full bg-primary text-white text-sm font-semibold hover:opacity-90 shrink-0">
                    + Tambah Fasilitas
                </a>
            </x-slot:actions>
        </x-crud.index-header>

        <x-crud.reorder-list
            id="facility-list"
            item-attribute="data-facility-id"
            :reorder-route="route('organizations.facilities.reorder', $organization)"
            payload-key="facility_ids"
            :has-items="$facilities->isNotEmpty()">
            @forelse ($facilities as $facility)
                <div class="p-5 flex items-center gap-4" data-facility-id="{{ $facility->id }}">
                    <span class="cursor-move text-gray-300 hover:text-gray-500 shrink-0 touch-none select-none">⠿</span>
                    <div class="w-12 h-12 rounded-xl overflow-hidden bg-gray-100 shrink-0">
                        @if ($facility->photo)
                            <img src="{{ $facility->photo }}" alt="{{ $facility->name }}" class="w-full h-full object-cover">
                        @endif
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="font-semibold text-gray-800 truncate">{{ $facility->name }}</p>
                        @if ($facility->description)
                            <p class="text-sm text-gray-500 truncate">{{ $facility->description }}</p>
                        @endif
                    </div>
                    <x-crud.row-actions
                        :edit-href="route('organizations.facilities.edit', [$organization, $facility]).$builderQuery"
                        :delete-action="route('organizations.facilities.destroy', [$organization, $facility])"
                        confirm-message="Hapus fasilitas ini?"
                        :from-builder="$fromBuilder"
                        :section="request('section')" />
                </div>
            @empty
                <x-ui.empty-state message="Belum ada fasilitas. Tambahkan yang pertama." />
            @endforelse
        </x-crud.reorder-list>
    </div>
@endsection
