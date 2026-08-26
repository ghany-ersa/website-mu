@extends('layouts.admin')

@section('title', 'Kode Diskon Baru')

@section('content')
    <div class="flex items-center justify-between mb-8">
        <h1 class="text-2xl font-extrabold text-primary">Kode Diskon Baru</h1>
        <a href="{{ route('admin.discount-codes.index') }}" class="text-sm text-gray-500 hover:underline">&larr; Kembali</a>
    </div>

    <form action="{{ route('admin.discount-codes.store') }}" method="POST">
        @csrf
        @php($discountCode = null)
        @include('admin.discount-codes._form')

        <div class="mt-6 flex justify-end">
            <button type="submit" class="px-6 py-2.5 rounded-full bg-primary text-white text-sm font-semibold">
                Simpan Kode
            </button>
        </div>
    </form>
@endsection
