@extends('layouts.organization')

@section('title', 'Testimoni - '.$organization->name.' - Website-mu')

@section('content')
    <div class="max-w-3xl mx-auto">
        <x-crud.back-link
            :href="$fromBuilder ? route('organizations.builder.edit', array_filter(['organization' => $organization, 'section' => request('section')])) : route('organizations.show', $organization)"
            :label="$fromBuilder ? 'Kembali ke Page Builder' : 'Kembali ke '.$organization->name" />

        <x-crud.index-header title="Testimoni" :subtitle="$organization->name">
            <x-slot:actions>
                <a href="{{ route('organizations.testimonials.create', $organization) }}{{ $builderQuery }}"
                   class="px-4 py-2 rounded-full bg-primary text-white text-sm font-semibold hover:opacity-90 shrink-0">
                    + Tambah Testimoni
                </a>
            </x-slot:actions>
        </x-crud.index-header>

        <div class="bg-white rounded-2xl border border-gray-100 divide-y divide-gray-100 overflow-hidden">
            @forelse ($testimonials as $testimonial)
                <div class="p-5 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-full overflow-hidden bg-gray-100 shrink-0 flex items-center justify-center">
                        @if ($testimonial->photo)
                            <img src="{{ $testimonial->photo }}" alt="{{ $testimonial->name }}" class="w-full h-full object-cover">
                        @else
                            <span class="text-lg">💬</span>
                        @endif
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="font-semibold text-gray-800 truncate">
                            {{ $testimonial->name }}
                            @unless ($testimonial->is_active)
                                <span class="ml-1 align-middle text-[10px] font-bold uppercase tracking-wide text-gray-400 bg-gray-100 px-1.5 py-0.5 rounded">Disembunyikan</span>
                            @endunless
                        </p>
                        <p class="text-sm text-gray-500 truncate">{{ $testimonial->role ?: 'Tanpa keterangan' }}</p>
                        <p class="text-xs text-gray-400 truncate mt-0.5">{{ \Illuminate\Support\Str::limit($testimonial->quote, 90) }}</p>
                    </div>
                    <x-crud.row-actions
                        :edit-href="route('organizations.testimonials.edit', [$organization, $testimonial]).$builderQuery"
                        :delete-action="route('organizations.testimonials.destroy', [$organization, $testimonial])"
                        confirm-message="Hapus testimoni ini?"
                        :from-builder="$fromBuilder"
                        :section="request('section')" />
                </div>
            @empty
                <x-ui.empty-state message="Belum ada testimoni. Tambahkan yang pertama." />
            @endforelse
        </div>
    </div>
@endsection
