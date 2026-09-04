@extends('layouts.admin')

@section('title', 'Artikel')

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-3 mb-8">
        <div>
            <h1 class="text-2xl font-extrabold text-primary">Artikel</h1>
            <p class="text-sm text-gray-500 mt-1">{{ $articles->total() }} artikel blog website-mu.id.</p>
        </div>
        <a href="{{ route('admin.articles.create') }}" class="px-4 py-2 rounded-full bg-primary text-white text-sm font-semibold">
            + Artikel Baru
        </a>
    </div>

    <x-crud.search-form placeholder="Cari judul artikel..." />

    <div class="bg-white rounded-2xl shadow-soft overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                <tr>
                    <th class="px-5 py-3">#</th>
                    <th class="px-5 py-3">Judul</th>
                    <th class="px-5 py-3">Kategori</th>
                    <th class="px-5 py-3">Status</th>
                    <th class="px-5 py-3">Terbit</th>
                    <th class="px-5 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($articles as $article)
                    <tr>
                        <td class="px-5 py-4 text-gray-400">
                            {{ $articles->firstItem() + $loop->index }}
                        </td>
                        <td class="px-5 py-4">
                            <p class="font-semibold text-gray-800">{{ $article->title }}</p>
                            <p class="text-xs text-gray-400">{{ $article->slug }}</p>
                        </td>
                        <td class="px-5 py-4 text-gray-600">
                            {{ $article->category ?? '—' }}
                        </td>
                        <td class="px-5 py-4">
                            <x-ui.status-badge :status="$article->status" />
                        </td>
                        <td class="px-5 py-4 text-gray-500">
                            {{ $article->published_at?->translatedFormat('d M Y') ?? '—' }}
                        </td>
                        <td class="px-5 py-4 text-right space-x-3">
                            <a href="{{ route('admin.articles.edit', $article) }}" class="text-gray-600 font-medium hover:underline">Edit</a>
                            <form action="{{ route('admin.articles.destroy', $article) }}" method="POST" class="inline"
                                  x-data @submit.prevent="if (await confirmAction(`Hapus artikel ${@json($article->title)}?`)) $el.submit()">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-500 font-medium hover:underline">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-5 py-10 text-center text-gray-400">
                            @if (request('q'))
                                Tidak ada artikel yang cocok dengan pencarian.
                            @else
                                Belum ada artikel.
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $articles->onEachSide(1)->links() }}
    </div>
@endsection
