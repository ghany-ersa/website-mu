@extends('layouts.organization')

@section('title', 'Berita — '.$organization->name.' — Website-mu')

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
                <h1 class="text-2xl font-extrabold text-primary">Berita</h1>
                <p class="text-sm text-gray-500">{{ $organization->name }}</p>
            </div>
            <a href="{{ route('organizations.posts.create', $organization) }}{{ $builderQuery }}"
               class="px-4 py-2 rounded-full bg-primary text-white text-sm font-semibold hover:opacity-90 shrink-0">
                + Tulis Berita
            </a>
        </div>

        <div class="bg-white rounded-2xl shadow-soft divide-y divide-gray-100">
            @forelse ($posts as $post)
                <div class="p-5 flex items-center justify-between gap-4">
                    <div class="min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $post->status->value === 'published' ? 'bg-secondary/10 text-secondary' : 'bg-gray-100 text-gray-500' }}">
                                {{ $post->status->label() }}
                            </span>
                            @if ($post->category)
                                <span class="text-xs text-gray-400">{{ $post->category }}</span>
                            @endif
                        </div>
                        <p class="font-semibold text-gray-800 truncate">{{ $post->title }}</p>
                    </div>
                    <div class="flex items-center gap-3 shrink-0">
                        <a href="{{ route('organizations.posts.edit', [$organization, $post]) }}{{ $builderQuery }}"
                           class="text-primary text-sm font-semibold hover:underline">Edit</a>
                        <form action="{{ route('organizations.posts.destroy', [$organization, $post]) }}" method="POST"
                              x-data @submit.prevent="if (await confirmAction('Hapus berita ini?')) $el.submit()">
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
                <p class="p-8 text-center text-sm text-gray-400">Belum ada berita. Tulis yang pertama.</p>
            @endforelse
        </div>
    </div>
@endsection
