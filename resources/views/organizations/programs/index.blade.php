@extends('layouts.organization')

@section('title', $label.' - '.$organization->name.' - Website-mu')

@php
    $builderQuery = '?type='.$type.($fromBuilder ? '&from=builder'.(request('section') ? '&section='.request('section') : '') : '');
@endphp

@section('content')
    <div class="max-w-3xl mx-auto">
        <x-crud.back-link
            :href="$fromBuilder ? route('organizations.builder.edit', array_filter(['organization' => $organization, 'section' => request('section')])) : route('organizations.show', $organization)"
            :label="$fromBuilder ? 'Kembali ke Page Builder' : 'Kembali ke '.$organization->name" />

        <x-crud.index-header :title="$label" :subtitle="$organization->name">
            <x-slot:actions>
                <a href="{{ route('organizations.programs.create', $organization) }}{{ $builderQuery }}"
                   class="px-4 py-2 rounded-full bg-primary text-white text-sm font-semibold hover:opacity-90 shrink-0">
                    + Tambah {{ $type === 'layanan' ? 'Layanan' : 'Program' }}
                </a>
            </x-slot:actions>
        </x-crud.index-header>

        <x-ui.list-panel>
            @forelse ($programs as $program)
                <div class="p-5 flex items-center gap-4">
                    @if ($program->icon)
                        <div class="w-10 h-10 rounded-lg bg-secondary/10 text-secondary flex items-center justify-center font-bold shrink-0">
                            {{ $program->icon }}
                        </div>
                    @endif
                    <div class="min-w-0 flex-1">
                        <p class="font-semibold text-gray-800 truncate">{{ $program->title }}</p>
                        @if ($program->description)
                            <p class="text-sm text-gray-500 truncate">{{ $program->description }}</p>
                        @endif
                    </div>
                    <x-crud.row-actions
                        :edit-href="route('organizations.programs.edit', [$organization, $program]).$builderQuery"
                        :delete-action="route('organizations.programs.destroy', [$organization, $program])"
                        confirm-message="Hapus item ini?"
                        :from-builder="$fromBuilder"
                        :section="request('section')" />
                </div>
            @empty
                <x-ui.empty-state :message="'Belum ada '.strtolower($label).'. Tambahkan yang pertama.'" />
            @endforelse
        </x-ui.list-panel>
    </div>
@endsection
