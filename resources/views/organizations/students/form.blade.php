@extends('layouts.organization')

@section('title', ($student->exists ? 'Edit Siswa' : 'Tambah Siswa').' - '.$organization->name.' - Website-mu')

@section('content')
    <div class="max-w-3xl mx-auto">
        <x-crud.back-link
            :href="route('organizations.students.index', $organization).$builderQuery"
            label="Kembali ke Data Siswa" />

        <x-crud.page-header
            :title="$student->exists ? 'Edit Siswa' : 'Tambah Siswa'"
            :subtitle="$organization->name" />

        <x-form.shell
            :action="$student->exists ? route('organizations.students.update', [$organization, $student]) : route('organizations.students.store', $organization)"
            :method="$student->exists ? 'PATCH' : 'POST'"
            :from-builder="$fromBuilder"
            :section="request('section')">

            <x-ui.card>
                <x-form.field name="name" label="Nama Lengkap" :value="$student->name" required />

                <div class="grid gap-4 sm:grid-cols-3">
                    <x-form.field name="grade" label="Kelas" :value="$student->grade" placeholder="mis. Kelas 6" />
                    <x-form.field name="enrolled_year" label="Tahun Masuk" :value="$student->enrolled_year" placeholder="mis. 2020" />
                    <x-form.field name="graduated_year" label="Tahun Lulus" :value="$student->graduated_year" placeholder="opsional" />
                </div>

                <x-form.field name="quote" label="Kutipan" :value="$student->quote"
                    placeholder="Kalimat singkat dari siswa (opsional)" />

                <x-form.textarea-field name="bio" label="Tentang" :value="$student->bio" rows="3"
                    placeholder="Perkenalan singkat (opsional)" />

                {{-- Satu baris dipisah koma, bukan repeater: lebih cepat diisi dan kolomnya
                     tetap tersimpan sebagai JSON. --}}
                <x-form.field name="hobbies" label="Minat" :value="collect($student->hobbies ?? [])->implode(', ')"
                    placeholder="Pisahkan dengan koma, mis. Membaca, Catur, Sepak Bola" />

                <x-form.image-picker :organization="$organization" name="photo" label="Foto"
                    :value="$student->photo" category="siswa" aspect="aspect-square w-32" />

                <label class="flex items-start gap-3 cursor-pointer pt-1">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" class="mt-0.5 rounded border-gray-300 text-primary focus:ring-primary/30"
                        @checked(old('is_active', $student->exists ? $student->is_active : true))>
                    <span>
                        <span class="block text-sm font-semibold text-gray-700">Tampilkan di situs</span>
                        <span class="block text-xs text-gray-400">Jika dinonaktifkan, halaman profilnya juga ikut disembunyikan.</span>
                    </span>
                </label>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <a href="{{ route('organizations.students.index', $organization) }}{{ $builderQuery }}"
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
