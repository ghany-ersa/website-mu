@extends('layouts.organization')

@section('title', 'Pengumuman — '.$organization->name.' — Website-mu')

@section('content')
    <div class="max-w-3xl mx-auto">
        <x-crud.back-link
            :href="$fromBuilder ? route('organizations.builder.edit', array_filter(['organization' => $organization, 'section' => request('section')])) : route('organizations.show', $organization)"
            :label="$fromBuilder ? 'Kembali ke Page Builder' : 'Kembali ke '.$organization->name" />

        <x-crud.index-header title="Pengumuman" :subtitle="$organization->name">
            <x-slot:actions>
                <a href="{{ route('organizations.announcements.create', $organization) }}{{ $builderQuery }}"
                   class="px-4 py-2 rounded-full bg-primary text-white text-sm font-semibold hover:opacity-90 shrink-0">
                    + Tambah Pengumuman
                </a>
            </x-slot:actions>
        </x-crud.index-header>

        <x-ui.list-panel>
            @forelse ($announcements as $announcement)
                <div class="p-5 flex items-center justify-between gap-4">
                    <div class="min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <x-ui.status-badge :status="$announcement->status" />
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-700">
                                {{ $announcement->priority }}
                            </span>
                            @if ($announcement->valid_until)
                                <span class="text-xs text-gray-400">Berlaku hingga {{ $announcement->valid_until->translatedFormat('d M Y') }}</span>
                            @endif
                        </div>
                        <p class="font-semibold text-gray-800 truncate">{{ $announcement->title }}</p>
                    </div>
                    <x-crud.row-actions
                        :edit-href="route('organizations.announcements.edit', [$organization, $announcement]).$builderQuery"
                        :delete-action="route('organizations.announcements.destroy', [$organization, $announcement])"
                        confirm-message="Hapus pengumuman ini?"
                        :from-builder="$fromBuilder"
                        :section="request('section')" />
                </div>
            @empty
                <x-ui.empty-state message="Belum ada pengumuman. Tambahkan yang pertama." />
            @endforelse
        </x-ui.list-panel>
    </div>
@endsection
