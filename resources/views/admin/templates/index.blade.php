@extends('layouts.admin')

@section('title', 'Template')

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-3 mb-8">
        <div>
            <h1 class="text-2xl font-extrabold text-primary">Template</h1>
            <p class="text-sm text-gray-500 mt-1">{{ $templates->total() }} template.</p>
        </div>
        <a href="{{ route('admin.templates.create') }}" class="px-4 py-2 rounded-full bg-primary text-white text-sm font-semibold">
            + Template Baru
        </a>
    </div>

    <x-crud.search-form placeholder="Cari nama atau slug template...">
        <select name="organization_type_id" onchange="this.form.submit()"
                class="px-4 py-2.5 rounded-full border border-gray-200 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
            <option value="">Semua Jenis</option>
            @foreach ($organizationTypes as $type)
                <option value="{{ $type->id }}" @selected(request('organization_type_id') == $type->id)>{{ $type->name }}</option>
            @endforeach
        </select>
    </x-crud.search-form>

    <div class="bg-white rounded-2xl shadow-soft overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                <tr>
                    <th class="px-5 py-3">#</th>
                    <th class="px-5 py-3">Nama</th>
                    <th class="px-5 py-3">Jenis Organisasi</th>
                    <th class="px-5 py-3">Status</th>
                    <th class="px-5 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($templates as $template)
                    <tr>
                        <td class="px-5 py-4 text-gray-400">
                            {{ $templates->firstItem() + $loop->index }}
                        </td>
                        <td class="px-5 py-4">
                            <p class="font-semibold text-gray-800">{{ $template->name }}</p>
                            <p class="text-xs text-gray-400">{{ $template->slug }}</p>
                        </td>
                        <td class="px-5 py-4 text-gray-600">
                            {{ $template->organizationType?->name ?? '—' }}
                        </td>
                        <td class="px-5 py-4 space-x-1.5">
                            @if ($template->is_active)
                                <span class="px-2 py-1 rounded-full bg-secondary/10 text-secondary text-xs font-semibold">Aktif</span>
                            @else
                                <span class="px-2 py-1 rounded-full bg-gray-100 text-gray-500 text-xs font-semibold">Nonaktif</span>
                            @endif
                            @if ($template->is_exclusive)
                                <span class="px-2 py-1 rounded-full bg-amber-100 text-amber-700 text-xs font-semibold">Eksklusif</span>
                            @endif
                            @if ($template->is_featured)
                                <span class="px-2 py-1 rounded-full bg-blue-100 text-blue-700 text-xs font-semibold">Landing Page</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-right space-x-3">
                            <a href="{{ route('admin.templates.design', $template) }}" class="text-secondary font-medium hover:underline">Edit Visual</a>
                            <a href="{{ route('templates.preview', $template->slug) }}" target="_blank" class="text-primary font-medium hover:underline">Preview</a>
                            <a href="{{ route('admin.templates.edit', $template) }}" class="text-gray-600 font-medium hover:underline">Edit</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-5 py-10 text-center text-gray-400">
                            @if (request('q') || request('organization_type_id'))
                                Tidak ada template yang cocok dengan pencarian.
                            @else
                                Belum ada template.
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $templates->onEachSide(1)->links() }}
    </div>
@endsection
