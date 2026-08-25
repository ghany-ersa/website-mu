@extends('layouts.organization')

@section('title', ($announcement->exists ? 'Edit Pengumuman' : 'Tambah Pengumuman').' — '.$organization->name.' — Website-mu')

@php
    $fromBuilder = request('from') === 'builder';
    $builderQuery = $fromBuilder ? '?from=builder'.(request('section') ? '&section='.request('section') : '') : '';
@endphp

@section('content')
    <div class="max-w-3xl mx-auto">
        <a href="{{ route('organizations.announcements.index', $organization) }}{{ $builderQuery }}"
           class="inline-flex items-center gap-1.5 text-sm font-medium text-gray-500 hover:text-primary transition-colors mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4">
                <path fill-rule="evenodd" d="M17 10a.75.75 0 0 1-.75.75H5.612l4.158 3.96a.75.75 0 1 1-1.04 1.08l-5.5-5.25a.75.75 0 0 1 0-1.08l5.5-5.25a.75.75 0 1 1 1.04 1.08L5.612 9.25H16.25A.75.75 0 0 1 17 10Z" clip-rule="evenodd" />
            </svg>
            Kembali ke Pengumuman
        </a>
        <h1 class="text-2xl font-extrabold text-primary mb-2">{{ $announcement->exists ? 'Edit Pengumuman' : 'Tambah Pengumuman' }}</h1>
        <p class="text-sm text-gray-500 mb-8">{{ $organization->name }}</p>

        <form action="{{ $announcement->exists ? route('organizations.announcements.update', [$organization, $announcement]) : route('organizations.announcements.store', $organization) }}"
              method="POST">
            @csrf
            @if ($announcement->exists) @method('PATCH') @endif
            @if ($fromBuilder)
                <input type="hidden" name="from" value="builder">
                <input type="hidden" name="section" value="{{ request('section') }}">
            @endif

            <div class="bg-white rounded-2xl shadow-soft p-6 space-y-5">
                <div>
                    <label for="title" class="block text-sm font-semibold text-gray-700 mb-1">Judul</label>
                    <input type="text" name="title" id="title" value="{{ old('title', $announcement->title) }}" required
                           class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30">
                </div>

                <div>
                    <label for="body" class="block text-sm font-semibold text-gray-700 mb-1">Isi Pengumuman</label>
                    <textarea name="body" id="body" rows="4"
                              class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30">{{ old('body', $announcement->body) }}</textarea>
                </div>

                <div>
                    <label for="priority" class="block text-sm font-semibold text-gray-700 mb-1">Prioritas</label>
                    <select name="priority" id="priority"
                            class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30">
                        @foreach (['Rendah', 'Sedang', 'Tinggi'] as $priority)
                            <option value="{{ $priority }}" @selected(old('priority', $announcement->priority ?? 'Rendah') === $priority)>
                                {{ $priority }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="valid_until" class="block text-sm font-semibold text-gray-700 mb-1">Berlaku Hingga</label>
                    <input type="date" name="valid_until" id="valid_until"
                           value="{{ old('valid_until', $announcement->valid_until?->format('Y-m-d')) }}"
                           class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30">
                </div>

                <div>
                    <label for="status" class="block text-sm font-semibold text-gray-700 mb-1">Status</label>
                    <select name="status" id="status"
                            class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30">
                        @foreach (\App\Enums\PublishStatus::cases() as $status)
                            <option value="{{ $status->value }}" @selected(old('status', $announcement->status?->value ?? 'draft') === $status->value)>
                                {{ $status->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <a href="{{ route('organizations.announcements.index', $organization) }}{{ $builderQuery }}"
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
