@extends('layouts.admin')

@section('title', 'Edit Artikel')

@section('content')
    <div class="flex items-center justify-between mb-8">
        <h1 class="text-2xl font-extrabold text-primary">Edit Artikel</h1>
        <div class="flex items-center gap-4">
            @if ($article->status?->value === 'published')
                <a href="{{ route('articles.show', $article) }}" target="_blank" class="text-sm text-primary font-medium hover:underline">Lihat di situs &rarr;</a>
            @endif
            <a href="{{ route('admin.articles.index') }}" class="text-sm text-gray-500 hover:underline">&larr; Kembali</a>
        </div>
    </div>

    <form action="{{ route('admin.articles.update', $article) }}" method="POST">
        @csrf
        @method('PATCH')
        @include('admin.articles._form')

        <div class="mt-6 flex justify-end">
            <button type="submit" class="px-6 py-2.5 rounded-full bg-primary text-white text-sm font-semibold">
                Simpan Perubahan
            </button>
        </div>
    </form>
@endsection
