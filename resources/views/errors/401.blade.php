@extends('errors.layout')

@section('badge', 'Perlu Masuk')
@section('code', '401')
@section('title', 'Anda Belum Masuk')
@section('message', 'Halaman ini hanya bisa diakses setelah Anda masuk ke akun website-mu.id. Silakan masuk terlebih dahulu untuk melanjutkan.')

@section('actions')
    <a href="{{ route('login') }}" class="bg-white hover:bg-gray-50 text-gray-800 border border-gray-200 px-6 py-3.5 rounded-full text-sm font-bold shadow-soft transition-all">
        Masuk ke Akun
    </a>
@endsection
