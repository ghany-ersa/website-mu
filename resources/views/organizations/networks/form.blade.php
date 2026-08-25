@extends('layouts.organization')

@section('title', ($network->exists ? 'Edit Jaringan' : 'Tambah Jaringan').' — '.$organization->name.' — Website-mu')

@php
    $fromBuilder = request('from') === 'builder';
    $builderQuery = $fromBuilder ? '?from=builder'.(request('section') ? '&section='.request('section') : '') : '';
@endphp

@section('content')
    <div class="max-w-3xl mx-auto">
        <a href="{{ route('organizations.networks.index', $organization) }}{{ $builderQuery }}"
           class="inline-flex items-center gap-1.5 text-sm font-medium text-gray-500 hover:text-primary transition-colors mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4">
                <path fill-rule="evenodd" d="M17 10a.75.75 0 0 1-.75.75H5.612l4.158 3.96a.75.75 0 1 1-1.04 1.08l-5.5-5.25a.75.75 0 0 1 0-1.08l5.5-5.25a.75.75 0 1 1 1.04 1.08L5.612 9.25H16.25A.75.75 0 0 1 17 10Z" clip-rule="evenodd" />
            </svg>
            Kembali ke Jaringan AUM/Ortom
        </a>
        <h1 class="text-2xl font-extrabold text-primary mb-2">{{ $network->exists ? 'Edit Jaringan' : 'Tambah Jaringan' }}</h1>
        <p class="text-sm text-gray-500 mb-8">{{ $organization->name }}</p>

        <form action="{{ $network->exists ? route('organizations.networks.update', [$organization, $network]) : route('organizations.networks.store', $organization) }}"
              method="POST">
            @csrf
            @if ($network->exists) @method('PATCH') @endif
            @if ($fromBuilder)
                <input type="hidden" name="from" value="builder">
                <input type="hidden" name="section" value="{{ request('section') }}">
            @endif

            <div class="bg-white rounded-2xl shadow-soft p-6 space-y-5">
                <div>
                    <label for="name" class="block text-sm font-semibold text-gray-700 mb-1">Nama</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $network->name) }}" required
                           placeholder="mis. SD Muhammadiyah 1, Aisyiyah, IPM"
                           class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30">
                </div>

                <div>
                    <label for="type" class="block text-sm font-semibold text-gray-700 mb-1">Jenis</label>
                    <input type="text" name="type" id="type" value="{{ old('type', $network->type) }}"
                           placeholder="mis. AUM Pendidikan, AUM Kesehatan, Ortom"
                           class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30">
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <a href="{{ route('organizations.networks.index', $organization) }}{{ $builderQuery }}"
                       class="px-5 py-2.5 rounded-full text-gray-600 text-sm font-semibold hover:bg-gray-100 transition-colors">
                        Batal
                    </a>
                    <button type="submit" class="px-5 py-2.5 rounded-full bg-primary text-white text-sm font-semibold">
                        Simpan
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection
