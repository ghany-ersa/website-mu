@extends('layouts.admin')

@section('title', 'Edit '.$plan->name)

@section('content')
    <div class="flex items-center justify-between mb-8">
        <h1 class="text-2xl font-extrabold text-primary">Edit Paket</h1>
        <a href="{{ route('admin.plans.index') }}" class="text-sm text-gray-500 hover:underline">&larr; Kembali</a>
    </div>

    <form action="{{ route('admin.plans.update', $plan) }}" method="POST">
        @csrf
        @method('PUT')
        @include('admin.plans._form')

        <div class="mt-6 flex justify-end">
            <button type="submit" class="px-6 py-2.5 rounded-full bg-primary text-white text-sm font-semibold">
                Simpan Perubahan
            </button>
        </div>
    </form>
@endsection
