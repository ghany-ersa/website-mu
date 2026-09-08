@extends('layouts.organization')

@section('title', 'Prestasi - '.$organization->name.' - Website-mu')

@section('content')
    <div class="max-w-3xl mx-auto">
        <x-crud.back-link
            :href="$fromBuilder ? route('organizations.builder.edit', array_filter(['organization' => $organization, 'section' => request('section')])) : route('organizations.show', $organization)"
            :label="$fromBuilder ? 'Kembali ke Page Builder' : 'Kembali ke '.$organization->name" />

        <x-crud.index-header title="Prestasi" :subtitle="$organization->name">
            <x-slot:actions>
                <a href="{{ route('organizations.students.index', $organization) }}{{ $builderQuery }}"
                   class="px-4 py-2 rounded-full border border-gray-200 text-gray-600 text-sm font-semibold hover:border-primary/40 hover:text-primary transition shrink-0">
                    Data Siswa
                </a>
                <a href="{{ route('organizations.achievements.create', $organization) }}{{ $builderQuery }}"
                   class="px-4 py-2 rounded-full bg-primary text-white text-sm font-semibold hover:opacity-90 shrink-0">
                    + Tambah Prestasi
                </a>
            </x-slot:actions>
        </x-crud.index-header>

        <div class="bg-white rounded-2xl border border-gray-100 divide-y divide-gray-100 overflow-hidden">
            @forelse ($achievements as $achievement)
                <div class="p-5 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl overflow-hidden bg-gray-100 shrink-0 flex items-center justify-center">
                        @if ($achievement->image)
                            <img src="{{ $achievement->image }}" alt="{{ $achievement->title }}" class="w-full h-full object-cover">
                        @else
                            <span class="text-lg">🏆</span>
                        @endif
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="font-semibold text-gray-800 truncate">
                            {{ $achievement->title }}
                            @unless ($achievement->is_active)
                                <span class="ml-1 align-middle text-[10px] font-bold uppercase tracking-wide text-gray-400 bg-gray-100 px-1.5 py-0.5 rounded">Disembunyikan</span>
                            @endunless
                        </p>
                        <p class="text-sm text-gray-500 truncate">
                            {{ collect([$achievement->category, $achievement->level, $achievement->year])->filter()->implode(' · ') ?: 'Tanpa kategori' }}
                        </p>
                        @if ($achievement->students->isNotEmpty())
                            <p class="text-xs text-gray-400 truncate mt-0.5">
                                {{ $achievement->students->pluck('name')->implode(', ') }}
                            </p>
                        @endif
                    </div>
                    <x-crud.row-actions
                        :edit-href="route('organizations.achievements.edit', [$organization, $achievement]).$builderQuery"
                        :delete-action="route('organizations.achievements.destroy', [$organization, $achievement])"
                        confirm-message="Hapus prestasi ini?"
                        :from-builder="$fromBuilder"
                        :section="request('section')" />
                </div>
            @empty
                <x-ui.empty-state message="Belum ada prestasi. Tambahkan yang pertama." />
            @endforelse
        </div>
    </div>
@endsection
