@extends('layouts.organization')

@section('title', ($agenda->exists ? 'Edit Agenda' : 'Tambah Agenda').' — '.$organization->name.' — Website-mu')

@section('content')
    <div class="max-w-3xl mx-auto">
        <x-crud.back-link
            :href="route('organizations.agendas.index', $organization).$builderQuery"
            label="Kembali ke Agenda" />

        <x-crud.page-header
            :title="$agenda->exists ? 'Edit Agenda' : 'Tambah Agenda'"
            :subtitle="$organization->name" />

        <x-form.shell
            :action="$agenda->exists ? route('organizations.agendas.update', [$organization, $agenda]) : route('organizations.agendas.store', $organization)"
            :method="$agenda->exists ? 'PATCH' : 'POST'"
            :from-builder="$fromBuilder"
            :section="request('section')">

            <x-ui.card>
                <x-form.field name="title" label="Judul" :value="$agenda->title" required />

                <x-form.field type="datetime-local" name="starts_at" label="Tanggal &amp; Waktu"
                    :value="$agenda->starts_at?->format('Y-m-d\TH:i')" required />

                <x-form.field name="location" label="Lokasi" :value="$agenda->location" />

                <x-form.field name="contact_person" label="Narahubung" :value="$agenda->contact_person" />

                <x-form.textarea-field name="description" label="Deskripsi" :value="$agenda->description" />

                <x-form.field type="url" name="registration_url" label="Tautan Pendaftaran"
                    :value="$agenda->registration_url" placeholder="https://…" />

                <x-form.select-field name="status" label="Status"
                    :options="collect(\App\Enums\PublishStatus::cases())->mapWithKeys(fn ($status) => [$status->value => $status->label()])"
                    :selected="$agenda->status?->value ?? 'draft'" />

                <div class="flex items-center justify-end gap-3 pt-2">
                    <a href="{{ route('organizations.agendas.index', $organization) }}{{ $builderQuery }}"
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
