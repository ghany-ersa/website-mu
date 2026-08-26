@extends('layouts.admin')

@section('title', 'Edit '.$discountCode->code)

@section('content')
    <div class="flex items-center justify-between mb-8">
        <h1 class="text-2xl font-extrabold text-primary">Edit Kode Diskon</h1>
        <a href="{{ route('admin.discount-codes.index') }}" class="text-sm text-gray-500 hover:underline">&larr; Kembali</a>
    </div>

    <form action="{{ route('admin.discount-codes.update', $discountCode) }}" method="POST">
        @csrf
        @method('PUT')
        @include('admin.discount-codes._form')

        <div class="mt-6 flex justify-end">
            <button type="submit" class="px-6 py-2.5 rounded-full bg-primary text-white text-sm font-semibold">
                Simpan Perubahan
            </button>
        </div>
    </form>
@endsection
