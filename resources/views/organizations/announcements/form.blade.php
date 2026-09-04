@extends('layouts.organization')

@section('title', ($announcement->exists ? 'Edit Pengumuman' : 'Tambah Pengumuman').' - '.$organization->name.' - Website-mu')

@section('content')
    <div class="max-w-3xl mx-auto">
        <x-crud.back-link
            :href="route('organizations.announcements.index', $organization).$builderQuery"
            label="Kembali ke Pengumuman" />

        <x-crud.page-header
            :title="$announcement->exists ? 'Edit Pengumuman' : 'Tambah Pengumuman'"
            :subtitle="$organization->name" />

        <x-form.shell
            :action="$announcement->exists ? route('organizations.announcements.update', [$organization, $announcement]) : route('organizations.announcements.store', $organization)"
            :method="$announcement->exists ? 'PATCH' : 'POST'"
            :from-builder="$fromBuilder"
            :section="request('section')">

            <x-ui.card>
                <x-form.field name="title" label="Judul" :value="$announcement->title" required />

                <x-form.richtext-field name="body" label="Isi Pengumuman" :value="$announcement->body" />

                <x-form.select-field name="priority" label="Prioritas"
                    :options="collect(['Rendah', 'Sedang', 'Tinggi'])->mapWithKeys(fn ($priority) => [$priority => $priority])"
                    :selected="$announcement->priority ?? 'Rendah'" />

                <x-form.field type="date" name="valid_until" label="Berlaku Hingga"
                    :value="$announcement->valid_until?->format('Y-m-d')" />

                <x-form.select-field name="status" label="Status"
                    :options="collect(\App\Enums\PublishStatus::cases())->mapWithKeys(fn ($status) => [$status->value => $status->label()])"
                    :selected="$announcement->status?->value ?? 'draft'" />

                <div class="flex items-center justify-end gap-3 pt-2">
                    <a href="{{ route('organizations.announcements.index', $organization) }}{{ $builderQuery }}"
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
