@extends('layouts.organization')

@section('title', 'Galeri — '.$organization->name.' — Website-mu')

@php
    $fromBuilder = request('from') === 'builder';
    $builderQuery = $fromBuilder ? '?from=builder'.(request('section') ? '&section='.request('section') : '') : '';
@endphp

@section('content')
    <div class="max-w-3xl mx-auto">
        <a href="{{ $fromBuilder ? route('organizations.builder.edit', array_filter(['organization' => $organization, 'section' => request('section')])) : route('organizations.show', $organization) }}"
           class="inline-flex items-center gap-1.5 text-sm font-medium text-gray-500 hover:text-primary transition-colors mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4">
                <path fill-rule="evenodd" d="M17 10a.75.75 0 0 1-.75.75H5.612l4.158 3.96a.75.75 0 1 1-1.04 1.08l-5.5-5.25a.75.75 0 0 1 0-1.08l5.5-5.25a.75.75 0 1 1 1.04 1.08L5.612 9.25H16.25A.75.75 0 0 1 17 10Z" clip-rule="evenodd" />
            </svg>
            {{ $fromBuilder ? 'Kembali ke Page Builder' : 'Kembali ke '.$organization->name }}
        </a>

        <div class="flex items-center justify-between gap-4 mb-8">
            <div>
                <h1 class="text-2xl font-extrabold text-primary">Galeri</h1>
                <p class="text-sm text-gray-500">{{ $organization->name }}</p>
            </div>
            <a href="{{ route('organizations.gallery.create', $organization) }}{{ $builderQuery }}"
               class="px-4 py-2 rounded-full bg-primary text-white text-sm font-semibold hover:opacity-90 shrink-0">
                + Tambah Foto
            </a>
        </div>

        @if ($photos->isNotEmpty())
            <p class="text-xs text-gray-400 mb-2">Seret <span class="font-semibold">⠿</span> untuk mengubah urutan tampil.</p>
        @endif

        <div id="gallery-list" class="bg-white rounded-2xl shadow-soft divide-y divide-gray-100">
            @forelse ($photos as $photo)
                <div class="p-5 flex items-center gap-4" data-photo-id="{{ $photo->id }}">
                    <span class="cursor-move text-gray-300 hover:text-gray-500 shrink-0 touch-none select-none">⠿</span>
                    <div class="w-12 h-12 rounded-xl overflow-hidden bg-gray-100 shrink-0">
                        <img src="{{ $photo->url }}" alt="{{ $photo->caption }}" class="w-full h-full object-cover">
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="font-semibold text-gray-800 truncate">{{ $photo->caption ?: 'Tanpa keterangan' }}</p>
                    </div>
                    <div class="flex items-center gap-3 shrink-0">
                        <a href="{{ route('organizations.gallery.edit', [$organization, $photo]) }}{{ $builderQuery }}"
                           class="text-primary text-sm font-semibold hover:underline">Edit</a>
                        <form action="{{ route('organizations.gallery.destroy', [$organization, $photo]) }}" method="POST"
                              onsubmit="return confirm('Hapus foto ini?');">
                            @csrf
                            @method('DELETE')
                            @if ($fromBuilder)
                                <input type="hidden" name="from" value="builder">
                                <input type="hidden" name="section" value="{{ request('section') }}">
                            @endif
                            <button type="submit" class="text-red-500 text-sm font-medium hover:underline">Hapus</button>
                        </form>
                    </div>
                </div>
            @empty
                <p class="p-8 text-center text-sm text-gray-400">Belum ada foto. Tambahkan yang pertama.</p>
            @endforelse
        </div>

        @if ($photos->isNotEmpty())
            <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
            <script>
                new Sortable(document.getElementById('gallery-list'), {
                    handle: '.cursor-move',
                    animation: 150,
                    onEnd() {
                        const ids = [...document.querySelectorAll('#gallery-list [data-photo-id]')]
                            .map((el) => el.dataset.photoId);

                        fetch(@json(route('organizations.gallery.reorder', $organization)), {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': @json(csrf_token()),
                            },
                            body: JSON.stringify({ photo_ids: ids }),
                        });
                    },
                });
            </script>
        @endif
    </div>
@endsection
