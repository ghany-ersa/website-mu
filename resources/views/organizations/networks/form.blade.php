@extends('layouts.organization')

@section('title', ($network->exists ? 'Edit Jaringan' : 'Tambah Jaringan').' — '.$organization->name.' — Website-mu')

@section('content')
    <div class="max-w-3xl mx-auto">
        <x-crud.back-link
            :href="route('organizations.networks.index', $organization).$builderQuery"
            label="Kembali ke Jaringan AUM/Ortom" />

        <x-crud.page-header
            :title="$network->exists ? 'Edit Jaringan' : 'Tambah Jaringan'"
            :subtitle="$organization->name" />

        <x-form.shell
            :action="$network->exists ? route('organizations.networks.update', [$organization, $network]) : route('organizations.networks.store', $organization)"
            :method="$network->exists ? 'PATCH' : 'POST'"
            :from-builder="$fromBuilder"
            :section="request('section')">

            <x-ui.card>
                <x-form.field name="name" label="Nama" :value="$network->name" required
                    placeholder="mis. SD Muhammadiyah 1, Aisyiyah, IPM" />

                <x-form.field name="type" label="Jenis" :value="$network->type"
                    placeholder="mis. AUM Pendidikan, AUM Kesehatan, Ortom" />

                <div class="flex items-center justify-end gap-3 pt-2">
                    <a href="{{ route('organizations.networks.index', $organization) }}{{ $builderQuery }}"
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
