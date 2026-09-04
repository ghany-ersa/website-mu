@extends('layouts.organization')

@section('title', 'Berita - '.$organization->name.' - Website-mu')

@section('content')
    <div class="max-w-3xl mx-auto">
        <x-crud.back-link
            :href="$fromBuilder ? route('organizations.builder.edit', array_filter(['organization' => $organization, 'section' => request('section')])) : route('organizations.show', $organization)"
            :label="$fromBuilder ? 'Kembali ke Page Builder' : 'Kembali ke '.$organization->name" />

        <x-crud.index-header title="Berita" :subtitle="$organization->name">
            <x-slot:actions>
                <a href="{{ route('organizations.posts.create', $organization) }}{{ $builderQuery }}"
                   class="px-4 py-2 rounded-full bg-primary text-white text-sm font-semibold hover:opacity-90 shrink-0">
                    + Tulis Berita
                </a>
            </x-slot:actions>
        </x-crud.index-header>

        <x-ui.list-panel>
            @forelse ($posts as $post)
                <div class="p-5 flex items-center justify-between gap-4">
                    <div class="min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <x-ui.status-badge :status="$post->status" />
                            @if ($post->category)
                                <span class="text-xs text-gray-400">{{ $post->category }}</span>
                            @endif
                        </div>
                        <p class="font-semibold text-gray-800 truncate">{{ $post->title }}</p>
                    </div>
                    <x-crud.row-actions
                        :edit-href="route('organizations.posts.edit', [$organization, $post]).$builderQuery"
                        :delete-action="route('organizations.posts.destroy', [$organization, $post])"
                        confirm-message="Hapus berita ini?"
                        :from-builder="$fromBuilder"
                        :section="request('section')" />
                </div>
            @empty
                <x-ui.empty-state message="Belum ada berita. Tulis yang pertama." />
            @endforelse
        </x-ui.list-panel>
    </div>
@endsection
