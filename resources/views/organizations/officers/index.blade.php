@extends('layouts.organization')

@section('title', 'Pengurus — '.$organization->name.' — Website-mu')

@section('content')
    <div class="max-w-3xl mx-auto">
        <x-crud.back-link
            :href="$fromBuilder ? route('organizations.builder.edit', array_filter(['organization' => $organization, 'section' => request('section')])) : route('organizations.show', $organization)"
            :label="$fromBuilder ? 'Kembali ke Page Builder' : 'Kembali ke '.$organization->name" />

        <x-crud.index-header title="Struktur Pengurus" :subtitle="$organization->name">
            <x-slot:actions>
                <a href="{{ route('organizations.officers.create', $organization) }}{{ $builderQuery }}"
                   class="px-4 py-2 rounded-full bg-primary text-white text-sm font-semibold hover:opacity-90 shrink-0">
                    + Tambah Pengurus
                </a>
            </x-slot:actions>
        </x-crud.index-header>

        <x-crud.reorder-list
            id="officer-list"
            item-attribute="data-officer-id"
            :reorder-route="route('organizations.officers.reorder', $organization)"
            payload-key="officer_ids"
            :has-items="$officers->isNotEmpty()">
            @forelse ($officers as $officer)
                <div class="p-5 flex items-center gap-4" data-officer-id="{{ $officer->id }}">
                    <span class="cursor-move text-gray-300 hover:text-gray-500 shrink-0 touch-none select-none">⠿</span>
                    <div class="w-12 h-12 rounded-xl overflow-hidden bg-gray-100 shrink-0">
                        @if ($officer->photo)
                            <img src="{{ $officer->photo }}" alt="{{ $officer->name }}" class="w-full h-full object-cover">
                        @endif
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="font-semibold text-gray-800 truncate">{{ $officer->name }}</p>
                        <p class="text-sm text-gray-500 truncate">{{ $officer->role }}</p>
                    </div>
                    <x-crud.row-actions
                        :edit-href="route('organizations.officers.edit', [$organization, $officer]).$builderQuery"
                        :delete-action="route('organizations.officers.destroy', [$organization, $officer])"
                        confirm-message="Hapus pengurus ini?"
                        :from-builder="$fromBuilder"
                        :section="request('section')" />
                </div>
            @empty
                <x-ui.empty-state message="Belum ada pengurus. Tambahkan yang pertama." />
            @endforelse
        </x-crud.reorder-list>
    </div>
@endsection
