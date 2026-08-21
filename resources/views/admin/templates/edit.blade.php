@extends('layouts.admin')

@section('title', 'Edit '.$template->name)

@section('content')
    <div class="flex items-center justify-between mb-8">
        <h1 class="text-2xl font-extrabold text-primary">Edit Template</h1>
        <div class="flex items-center gap-4 text-sm">
            <a href="{{ route('templates.preview', $template->slug) }}" target="_blank" class="text-primary hover:underline">Preview &rarr;</a>
            <a href="{{ route('admin.templates.index') }}" class="text-gray-500 hover:underline">&larr; Kembali</a>
        </div>
    </div>

    <form action="{{ route('admin.templates.update', $template) }}" method="POST">
        @csrf
        @method('PUT')
        @include('admin.templates._form')

        <div class="mt-6 flex justify-end">
            <button type="submit" class="px-6 py-2.5 rounded-full bg-primary text-white text-sm font-semibold">
                Simpan Perubahan
            </button>
        </div>
    </form>
@endsection
