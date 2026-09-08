@extends('layouts.organization')

@section('title', 'Data Siswa - '.$organization->name.' - Website-mu')

@section('content')
    <div class="max-w-3xl mx-auto">
        <x-crud.back-link
            :href="$fromBuilder ? route('organizations.builder.edit', array_filter(['organization' => $organization, 'section' => request('section')])) : route('organizations.show', $organization)"
            :label="$fromBuilder ? 'Kembali ke Page Builder' : 'Kembali ke '.$organization->name" />

        <x-crud.index-header title="Data Siswa" :subtitle="$organization->name">
            <x-slot:actions>
                <a href="{{ route('organizations.students.create', $organization) }}{{ $builderQuery }}"
                   class="px-4 py-2 rounded-full bg-primary text-white text-sm font-semibold hover:opacity-90 shrink-0">
                    + Tambah Siswa
                </a>
            </x-slot:actions>
        </x-crud.index-header>

        <p class="text-sm text-gray-500 mb-4">
            Siswa yang ditampilkan punya halaman profil sendiri berisi daftar prestasinya.
            Kaitkan siswa ke prestasi lewat menu <span class="font-semibold">Prestasi</span>.
        </p>

        <div class="bg-white rounded-2xl border border-gray-100 divide-y divide-gray-100 overflow-hidden">
            @forelse ($students as $student)
                <div class="p-5 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl overflow-hidden bg-gray-100 shrink-0 flex items-center justify-center text-sm font-bold text-gray-400">
                        @if ($student->photo)
                            <img src="{{ $student->photo }}" alt="{{ $student->name }}" class="w-full h-full object-cover">
                        @else
                            {{ $student->initials() }}
                        @endif
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="font-semibold text-gray-800 truncate">
                            {{ $student->name }}
                            @unless ($student->is_active)
                                <span class="ml-1 align-middle text-[10px] font-bold uppercase tracking-wide text-gray-400 bg-gray-100 px-1.5 py-0.5 rounded">Disembunyikan</span>
                            @endunless
                        </p>
                        <p class="text-sm text-gray-500 truncate">
                            {{ $student->grade ?: 'Tanpa kelas' }} &middot; {{ $student->achievements_count }} prestasi
                        </p>
                    </div>
                    <x-crud.row-actions
                        :edit-href="route('organizations.students.edit', [$organization, $student]).$builderQuery"
                        :delete-action="route('organizations.students.destroy', [$organization, $student])"
                        confirm-message="Hapus siswa ini? Prestasinya tetap tersimpan, hanya namanya yang dilepas."
                        :from-builder="$fromBuilder"
                        :section="request('section')" />
                </div>
            @empty
                <x-ui.empty-state message="Belum ada siswa. Tambahkan yang pertama." />
            @endforelse
        </div>
    </div>
@endsection
