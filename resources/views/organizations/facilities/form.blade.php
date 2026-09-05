@extends('layouts.organization')

@section('title', ($facility->exists ? 'Edit Fasilitas' : 'Tambah Fasilitas').' - '.$organization->name.' - Website-mu')

@section('content')
    <div class="max-w-3xl mx-auto">
        <x-crud.back-link
            :href="route('organizations.facilities.index', $organization).$builderQuery"
            label="Kembali ke Fasilitas" />

        <x-crud.page-header
            :title="$facility->exists ? 'Edit Fasilitas' : 'Tambah Fasilitas'"
            :subtitle="$organization->name" />

        <x-form.shell
            :action="$facility->exists ? route('organizations.facilities.update', [$organization, $facility]) : route('organizations.facilities.store', $organization)"
            :method="$facility->exists ? 'PATCH' : 'POST'"
            :from-builder="$fromBuilder"
            :section="request('section')">

            <x-ui.card>
                <x-form.field name="name" label="Nama Fasilitas" :value="$facility->name" required
                    placeholder="mis. Tempat Wudhu, Area Parkir" />

                <x-form.textarea-field name="description" label="Keterangan (opsional)" :value="$facility->description"
                    placeholder="Penjelasan singkat tentang fasilitas ini." />

                <x-form.image-picker
                    :organization="$organization"
                    name="photo"
                    label="Foto"
                    :value="$facility->photo"
                    category="fasilitas"
                    aspect="aspect-[4/3] w-full sm:w-64" />

                <div class="flex items-center justify-end gap-3 pt-2">
                    <a href="{{ route('organizations.facilities.index', $organization) }}{{ $builderQuery }}"
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
