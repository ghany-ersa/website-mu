@extends('layouts.organization')

@section('title', 'Galeri — '.$organization->name.' — Website-mu')

@section('content')
    <div class="max-w-3xl mx-auto">
        <x-crud.back-link
            :href="$fromBuilder ? route('organizations.builder.edit', array_filter(['organization' => $organization, 'section' => request('section')])) : route('organizations.show', $organization)"
            :label="$fromBuilder ? 'Kembali ke Page Builder' : 'Kembali ke '.$organization->name" />

        <x-crud.index-header title="Galeri" :subtitle="$organization->name">
            <x-slot:actions>
                <a href="{{ route('organizations.gallery.create', $organization) }}{{ $builderQuery }}"
                   class="px-4 py-2 rounded-full bg-primary text-white text-sm font-semibold hover:opacity-90 shrink-0">
                    + Tambah Foto
                </a>
            </x-slot:actions>
        </x-crud.index-header>

        <x-crud.reorder-list
            id="gallery-list"
            item-attribute="data-photo-id"
            :reorder-route="route('organizations.gallery.reorder', $organization)"
            payload-key="photo_ids"
            :has-items="$photos->isNotEmpty()">
            @forelse ($photos as $photo)
                <div class="p-5 flex items-center gap-4" data-photo-id="{{ $photo->id }}">
                    <span class="cursor-move text-gray-300 hover:text-gray-500 shrink-0 touch-none select-none">⠿</span>
                    <div class="w-12 h-12 rounded-xl overflow-hidden bg-gray-100 shrink-0">
                        <img src="{{ $photo->url }}" alt="{{ $photo->caption }}" class="w-full h-full object-cover">
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="font-semibold text-gray-800 truncate">{{ $photo->caption ?: 'Tanpa keterangan' }}</p>
                    </div>
                    <x-crud.row-actions
                        :edit-href="route('organizations.gallery.edit', [$organization, $photo]).$builderQuery"
                        :delete-action="route('organizations.gallery.destroy', [$organization, $photo])"
                        confirm-message="Hapus foto ini?"
                        :from-builder="$fromBuilder"
                        :section="request('section')" />
                </div>
            @empty
                <x-ui.empty-state message="Belum ada foto. Tambahkan yang pertama." />
            @endforelse
        </x-crud.reorder-list>
    </div>
@endsection
