@extends('layouts.organization')

@section('title', ($achievement->exists ? 'Edit Prestasi' : 'Tambah Prestasi').' - '.$organization->name.' - Website-mu')

@section('content')
    @php
        // old() wins on a validation bounce so the chosen pupils survive it; otherwise the
        // saved pivot. Cast to int so the checkbox comparison below matches by value.
        $selectedStudentIds = collect(old('student_ids', $achievement->students->pluck('id')->all()))
            ->map(fn ($id) => (int) $id)
            ->all();
    @endphp

    <div class="max-w-3xl mx-auto">
        <x-crud.back-link
            :href="route('organizations.achievements.index', $organization).$builderQuery"
            label="Kembali ke Prestasi" />

        <x-crud.page-header
            :title="$achievement->exists ? 'Edit Prestasi' : 'Tambah Prestasi'"
            :subtitle="$organization->name" />

        <x-form.shell
            :action="$achievement->exists ? route('organizations.achievements.update', [$organization, $achievement]) : route('organizations.achievements.store', $organization)"
            :method="$achievement->exists ? 'PATCH' : 'POST'"
            :from-builder="$fromBuilder"
            :section="request('section')">

            <x-ui.card>
                <x-form.field name="title" label="Judul Prestasi" :value="$achievement->title" required
                    placeholder="mis. Juara 1 Olimpiade Matematika" />

                <div class="grid gap-4 sm:grid-cols-2">
                    <x-form.select-field name="category" label="Kategori" :selected="$achievement->category"
                        :options="['' => 'Tanpa kategori'] + array_combine(\App\Models\Achievement::CATEGORIES, \App\Models\Achievement::CATEGORIES)" />

                    <x-form.select-field name="level" label="Tingkat" :selected="$achievement->level"
                        :options="['' => 'Tanpa tingkat'] + array_combine(\App\Models\Achievement::LEVELS, \App\Models\Achievement::LEVELS)" />
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <x-form.field name="rank" label="Peringkat" :value="$achievement->rank" placeholder="mis. Juara 1" />
                    <x-form.field name="year" label="Tahun" :value="$achievement->year" placeholder="mis. 2026" />
                </div>

                <x-form.textarea-field name="description" label="Deskripsi" :value="$achievement->description" rows="3"
                    placeholder="Penjelasan singkat (opsional)" />

                <x-form.image-picker :organization="$organization" name="image" label="Foto"
                    :value="$achievement->image" category="prestasi" />

                {{-- Banyak siswa per prestasi, bukan satu: lomba beregu seperti cerdas cermat
                     memang dimenangkan bersama. Nama yang dicentang akan tampil sebagai tautan
                     ke halaman profil masing-masing. --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Siswa Peraih</label>
                    @if ($students->isEmpty())
                        <p class="text-sm text-gray-400 rounded-xl border border-dashed border-gray-200 px-4 py-6 text-center">
                            Belum ada data siswa.
                            <a href="{{ route('organizations.students.create', $organization) }}{{ $builderQuery }}"
                               class="font-semibold text-primary hover:underline">Tambah siswa dulu</a>
                            jika prestasi ini ingin dikaitkan ke nama siswa.
                        </p>
                    @else
                        <div x-data="{ q: '' }">
                            <input type="search" x-model="q" placeholder="Cari nama siswa…"
                                   class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm mb-2 focus:outline-none focus:ring-2 focus:ring-primary/30">
                            <div class="max-h-56 overflow-y-auto rounded-xl border border-gray-200 divide-y divide-gray-100">
                                @foreach ($students as $student)
                                    <label class="flex items-center gap-3 px-3 py-2.5 cursor-pointer hover:bg-gray-50"
                                           x-show="q === '' || {{ Js::from(mb_strtolower($student->name.' '.$student->grade)) }}.includes(q.toLowerCase())">
                                        <input type="checkbox" name="student_ids[]" value="{{ $student->id }}"
                                               class="rounded border-gray-300 text-primary focus:ring-primary/30"
                                               @checked(in_array($student->id, $selectedStudentIds, true))>
                                        <span class="min-w-0">
                                            <span class="block text-sm text-gray-700 truncate">{{ $student->name }}</span>
                                            @if ($student->grade)
                                                <span class="block text-xs text-gray-400">{{ $student->grade }}</span>
                                            @endif
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    <p class="text-xs text-gray-400 mt-1.5">Boleh dikosongkan untuk prestasi sekolah yang bukan atas nama siswa tertentu.</p>
                    @error('student_ids.*') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <label class="flex items-start gap-3 cursor-pointer pt-1">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" class="mt-0.5 rounded border-gray-300 text-primary focus:ring-primary/30"
                        @checked(old('is_active', $achievement->exists ? $achievement->is_active : true))>
                    <span>
                        <span class="block text-sm font-semibold text-gray-700">Tampilkan di situs</span>
                        <span class="block text-xs text-gray-400">Nonaktifkan untuk menyembunyikan tanpa menghapus data.</span>
                    </span>
                </label>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <a href="{{ route('organizations.achievements.index', $organization) }}{{ $builderQuery }}"
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
