@extends('layouts.organization')

@php
    $type = $program->exists ? $program->type : $type;
@endphp

@section('title', ($program->exists ? 'Edit '.$label : 'Tambah '.$label).' — '.$organization->name.' — Website-mu')

@php
    $indexQuery = '?type='.$type.($fromBuilder ? '&from=builder'.(request('section') ? '&section='.request('section') : '') : '');
@endphp

@section('content')
    <div class="max-w-3xl mx-auto"
        x-data="{
            icon: @js(old('icon', $program->icon ?? '')),
            options: ['📚', '🎓', '🏫', '✏️', '📖', '🕌', '🕋', '📿', '🤲', '☪️', '🏥', '⚕️', '💊', '🩺', '❤️', '🤝', '🧕', '👨‍👩‍👧‍👦', '🍲', '🤲🏽', '💰', '🎁', '📦', '🚑', '🏠', '🌱', '♻️', '💧', '🎯', '⭐', '🏆', '📢', '📅', '⏰', '💻', '📱', '🎨', '⚽', '🏃', '🧘'],
        }">
        <x-crud.back-link
            :href="route('organizations.programs.index', $organization).$indexQuery"
            :label="'Kembali ke '.($label === 'Layanan' ? 'Layanan' : 'Program Unggulan')" />

        <x-crud.page-header
            :title="$program->exists ? 'Edit '.$label : 'Tambah '.$label"
            :subtitle="$organization->name" />

        <x-form.shell
            :action="$program->exists ? route('organizations.programs.update', [$organization, $program]) : route('organizations.programs.store', $organization).'?type='.$type"
            :method="$program->exists ? 'PATCH' : 'POST'"
            :from-builder="$fromBuilder"
            :section="request('section')">

            <x-ui.card>
                <x-form.field name="title" label="Judul" :value="$program->title" required />

                <x-form.textarea-field name="description" label="Deskripsi" :value="$program->description" />

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
            </x-ui.card>
        </x-form.shell>
    </div>
@endsection
