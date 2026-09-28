@extends('layouts.organization')

@section('title', ($testimonial->exists ? 'Edit Testimoni' : 'Tambah Testimoni').' - '.$organization->name.' - Website-mu')

@section('content')
    <div class="max-w-3xl mx-auto">
        <x-crud.back-link
            :href="route('organizations.testimonials.index', $organization).$builderQuery"
            label="Kembali ke Testimoni" />

        <x-crud.page-header
            :title="$testimonial->exists ? 'Edit Testimoni' : 'Tambah Testimoni'"
            :subtitle="$organization->name" />

        <x-form.shell
            :action="$testimonial->exists ? route('organizations.testimonials.update', [$organization, $testimonial]) : route('organizations.testimonials.store', $organization)"
            :method="$testimonial->exists ? 'PATCH' : 'POST'"
            :from-builder="$fromBuilder"
            :section="request('section')">

            <x-ui.card>
                <x-form.textarea-field name="quote" label="Kutipan" :value="$testimonial->quote" rows="4" required
                    placeholder="Tuliskan testimoni apa adanya, dengan izin yang bersangkutan." />

                <x-form.field name="name" label="Nama" :value="$testimonial->name" required
                    placeholder="Nama orang yang memberi testimoni" />

                <x-form.field name="role" label="Keterangan" :value="$testimonial->role"
                    placeholder="Alumni Tahun 2005 / Wali Murid Kelas IV" />

                {{-- category 'pengurus': the accepted upload categories are a fixed list on
                     MediaController, and these are people photos, same as officer headshots. --}}
                <x-form.image-picker :organization="$organization" name="photo" label="Foto"
                    :value="$testimonial->photo" category="pengurus" />

                <label class="flex items-start gap-3 cursor-pointer pt-1">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" class="mt-0.5 rounded border-gray-300 text-primary focus:ring-primary/30"
                        @checked(old('is_active', $testimonial->exists ? $testimonial->is_active : true))>
                    <span>
                        <span class="block text-sm font-semibold text-gray-700">Tampilkan di situs</span>
                        <span class="block text-xs text-gray-400">Nonaktifkan untuk menyembunyikan tanpa menghapus data - berguna bila yang bersangkutan menarik izinnya.</span>
                    </span>
                </label>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <a href="{{ route('organizations.testimonials.index', $organization) }}{{ $builderQuery }}"
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
