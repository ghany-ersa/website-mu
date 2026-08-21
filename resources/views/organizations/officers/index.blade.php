@extends('layouts.app')

@section('title', 'Pengurus — '.$organization->name.' — Website-mu')

@php
    $fromBuilder = request('from') === 'builder';
    $builderQuery = $fromBuilder ? '?from=builder'.(request('section') ? '&section='.request('section') : '') : '';
@endphp

@section('content')
    <a href="{{ $fromBuilder ? route('organizations.builder.edit', array_filter(['organization' => $organization, 'section' => request('section')])) : route('organizations.show', $organization) }}"
       class="inline-flex items-center gap-1.5 text-sm font-medium text-gray-500 hover:text-primary transition-colors mb-4">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4">
            <path fill-rule="evenodd" d="M17 10a.75.75 0 0 1-.75.75H5.612l4.158 3.96a.75.75 0 1 1-1.04 1.08l-5.5-5.25a.75.75 0 0 1 0-1.08l5.5-5.25a.75.75 0 1 1 1.04 1.08L5.612 9.25H16.25A.75.75 0 0 1 17 10Z" clip-rule="evenodd" />
        </svg>
        {{ $fromBuilder ? 'Kembali ke Page Builder' : 'Kembali ke '.$organization->name }}
    </a>

    <div class="flex items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-extrabold text-primary">Struktur Pengurus</h1>
            <p class="text-sm text-gray-500">{{ $organization->name }}</p>
        </div>
        <a href="{{ route('organizations.officers.create', $organization) }}{{ $builderQuery }}"
           class="px-4 py-2 rounded-full bg-primary text-white text-sm font-semibold hover:opacity-90 shrink-0">
            + Tambah Pengurus
        </a>
    </div>

    @if ($officers->isNotEmpty())
        <p class="text-xs text-gray-400 mb-2">Seret <span class="font-semibold">⠿</span> untuk mengubah urutan tampil.</p>
    @endif

    <div id="officer-list" class="bg-white rounded-2xl shadow-soft divide-y divide-gray-100">
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
                <div class="flex items-center gap-3 shrink-0">
                    <a href="{{ route('organizations.officers.edit', [$organization, $officer]) }}{{ $builderQuery }}"
                       class="text-primary text-sm font-semibold hover:underline">Edit</a>
                    <form action="{{ route('organizations.officers.destroy', [$organization, $officer]) }}" method="POST"
                          onsubmit="return confirm('Hapus pengurus ini?');">
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
            <p class="p-8 text-center text-sm text-gray-400">Belum ada pengurus. Tambahkan yang pertama.</p>
        @endforelse
    </div>

    @if ($officers->isNotEmpty())
        <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
        <script>
            new Sortable(document.getElementById('officer-list'), {
                handle: '.cursor-move',
                animation: 150,
                onEnd() {
                    const ids = [...document.querySelectorAll('#officer-list [data-officer-id]')]
                        .map((el) => el.dataset.officerId);

                    fetch(@json(route('organizations.officers.reorder', $organization)), {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': @json(csrf_token()),
                        },
                        body: JSON.stringify({ officer_ids: ids }),
                    });
                },
            });
        </script>
    @endif
@endsection
