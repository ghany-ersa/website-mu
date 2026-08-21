@extends('layouts.admin')

@section('title', 'Template')

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-3 mb-8">
        <h1 class="text-2xl font-extrabold text-primary">Template</h1>
        <a href="{{ route('admin.templates.create') }}" class="px-4 py-2 rounded-full bg-primary text-white text-sm font-semibold">
            + Template Baru
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-soft overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                <tr>
                    <th class="px-5 py-3">Nama</th>
                    <th class="px-5 py-3">Jenis Organisasi</th>
                    <th class="px-5 py-3">Status</th>
                    <th class="px-5 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($templates as $template)
                    <tr>
                        <td class="px-5 py-4">
                            <p class="font-semibold text-gray-800">{{ $template->name }}</p>
                            <p class="text-xs text-gray-400">{{ $template->slug }}</p>
                        </td>
                        <td class="px-5 py-4 text-gray-600">
                            {{ $template->organizationType?->name ?? '—' }}
                        </td>
                        <td class="px-5 py-4">
                            @if ($template->is_active)
                                <span class="px-2 py-1 rounded-full bg-secondary/10 text-secondary text-xs font-semibold">Aktif</span>
                            @else
                                <span class="px-2 py-1 rounded-full bg-gray-100 text-gray-500 text-xs font-semibold">Nonaktif</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-right space-x-3">
                            <a href="{{ route('templates.preview', $template->slug) }}" target="_blank" class="text-primary font-medium hover:underline">Preview</a>
                            <a href="{{ route('admin.templates.edit', $template) }}" class="text-gray-600 font-medium hover:underline">Edit</a>
                            <form action="{{ route('admin.templates.destroy', $template) }}" method="POST" class="inline"
                                  onsubmit="return confirm('Hapus template {{ $template->name }}?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-500 font-medium hover:underline">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-5 py-10 text-center text-gray-400">Belum ada template.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
