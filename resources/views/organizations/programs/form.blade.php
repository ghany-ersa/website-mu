@extends('layouts.app')

@php
    $type = $program->exists ? $program->type : $type;
    $label = $type === 'layanan' ? 'Layanan' : 'Program';
@endphp

@section('title', ($program->exists ? 'Edit '.$label : 'Tambah '.$label).' — '.$organization->name.' — Website-mu')

@php
    $fromBuilder = request('from') === 'builder';
    $indexQuery = '?type='.$type.($fromBuilder ? '&from=builder'.(request('section') ? '&section='.request('section') : '') : '');
@endphp

@section('content')
    <div class="max-w-3xl mx-auto"
        x-data="{
            icon: @js(old('icon', $program->icon ?? '')),
            options: ['📚', '🎓', '🏫', '✏️', '📖', '🕌', '🕋', '📿', '🤲', '☪️', '🏥', '⚕️', '💊', '🩺', '❤️', '🤝', '🧕', '👨‍👩‍👧‍👦', '🍲', '🤲🏽', '💰', '🎁', '📦', '🚑', '🏠', '🌱', '♻️', '💧', '🎯', '⭐', '🏆', '📢', '📅', '⏰', '💻', '📱', '🎨', '⚽', '🏃', '🧘'],
        }">
        <a href="{{ route('organizations.programs.index', $organization) }}{{ $indexQuery }}"
           class="inline-flex items-center gap-1.5 text-sm font-medium text-gray-500 hover:text-primary transition-colors mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4">
                <path fill-rule="evenodd" d="M17 10a.75.75 0 0 1-.75.75H5.612l4.158 3.96a.75.75 0 1 1-1.04 1.08l-5.5-5.25a.75.75 0 0 1 0-1.08l5.5-5.25a.75.75 0 1 1 1.04 1.08L5.612 9.25H16.25A.75.75 0 0 1 17 10Z" clip-rule="evenodd" />
            </svg>
            Kembali ke {{ $label === 'Layanan' ? 'Layanan' : 'Program Unggulan' }}
        </a>
        <h1 class="text-2xl font-extrabold text-primary mb-2">{{ $program->exists ? 'Edit '.$label : 'Tambah '.$label }}</h1>
        <p class="text-sm text-gray-500 mb-8">{{ $organization->name }}</p>

        <form action="{{ $program->exists ? route('organizations.programs.update', [$organization, $program]) : route('organizations.programs.store', $organization).'?type='.$type }}"
              method="POST">
            @csrf
            @if ($program->exists) @method('PATCH') @endif
            @if ($fromBuilder)
                <input type="hidden" name="from" value="builder">
                <input type="hidden" name="section" value="{{ request('section') }}">
            @endif

            <div class="bg-white rounded-2xl shadow-soft p-6 space-y-5">
                <div>
                    <label for="title" class="block text-sm font-semibold text-gray-700 mb-1">Judul</label>
                    <input type="text" name="title" id="title" value="{{ old('title', $program->title) }}" required
                           class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30">
                </div>

                <div>
                    <label for="description" class="block text-sm font-semibold text-gray-700 mb-1">Deskripsi</label>
                    <textarea name="description" id="description" rows="3"
                              class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30">{{ old('description', $program->description) }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Ikon (opsional)</label>
                    <input type="hidden" name="icon" x-model="icon">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-11 h-11 rounded-lg bg-secondary/10 text-secondary flex items-center justify-center font-bold text-lg shrink-0">
                            <span x-text="icon"></span>
                        </div>
                        <button type="button" x-show="icon" x-cloak @click="icon = ''"
                                class="text-xs font-semibold text-gray-400 hover:text-red-500 transition">Hapus ikon</button>
                    </div>
                    <div class="grid grid-cols-8 sm:grid-cols-10 gap-1.5">
                        <template x-for="option in options" :key="option">
                            <button type="button" @click="icon = option"
                                    :class="icon === option ? 'bg-primary/10 ring-2 ring-primary' : 'bg-gray-50 hover:bg-gray-100'"
                                    class="aspect-square rounded-lg flex items-center justify-center text-lg transition">
                                <span x-text="option"></span>
                            </button>
                        </template>
                    </div>
                    <p class="text-xs text-gray-400 mt-2">Atau ketik/tempel emoji lain:
                        <input type="text" x-model="icon" maxlength="10" placeholder="😀"
                               class="ml-1 w-16 rounded-lg border border-gray-200 px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30">
                    </p>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <a href="{{ route('organizations.programs.index', $organization) }}{{ $indexQuery }}"
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
