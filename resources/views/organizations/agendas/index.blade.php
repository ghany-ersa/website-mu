@extends('layouts.organization')

@section('title', 'Agenda - '.$organization->name.' - Website-mu')

@section('content')
    <div class="max-w-3xl mx-auto">
        <x-crud.back-link
            :href="$fromBuilder ? route('organizations.builder.edit', array_filter(['organization' => $organization, 'section' => request('section')])) : route('organizations.show', $organization)"
            :label="$fromBuilder ? 'Kembali ke Page Builder' : 'Kembali ke '.$organization->name" />

        <x-crud.index-header title="Agenda" :subtitle="$organization->name">
            <x-slot:actions>
                <a href="{{ route('organizations.agendas.create', $organization) }}{{ $builderQuery }}"
                   class="px-4 py-2 rounded-full bg-primary text-white text-sm font-semibold hover:opacity-90 shrink-0">
                    + Tambah Agenda
                </a>
            </x-slot:actions>
        </x-crud.index-header>

        <x-ui.list-panel>
            @forelse ($agendas as $agenda)
                <div class="p-5 flex items-center justify-between gap-4">
                    <div class="min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <x-ui.status-badge :status="$agenda->status" />
                            <span class="text-xs text-gray-400">{{ $agenda->starts_at->translatedFormat('d M Y, H:i') }}</span>
                        </div>
                        <p class="font-semibold text-gray-800 truncate">{{ $agenda->title }}</p>
                        @if ($agenda->location)
                            <p class="text-xs text-gray-400 truncate">{{ $agenda->location }}</p>
                        @endif
                    </div>
                    <x-crud.row-actions
                        :edit-href="route('organizations.agendas.edit', [$organization, $agenda]).$builderQuery"
                        :delete-action="route('organizations.agendas.destroy', [$organization, $agenda])"
                        confirm-message="Hapus agenda ini?"
                        :from-builder="$fromBuilder"
                        :section="request('section')" />
                </div>
            @empty
                <x-ui.empty-state message="Belum ada agenda. Tambahkan yang pertama." />
            @endforelse
        </x-ui.list-panel>
    </div>
@endsection
