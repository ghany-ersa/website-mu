@extends('errors.layout')

@section('badge', 'Sesi Berakhir')
@section('code', '419')
@section('title', 'Sesi Anda Sudah Kedaluwarsa')
@section('message', 'Halaman ini dibiarkan terbuka terlalu lama, sehingga sesi keamanannya berakhir. Silakan muat ulang halaman sebelumnya lalu kirim ulang isian Anda.')

@section('actions')
    <a href="{{ url()->previous() }}" class="bg-white hover:bg-gray-50 text-gray-800 border border-gray-200 px-6 py-3.5 rounded-full text-sm font-bold shadow-soft transition-all">
        Coba Lagi
    </a>
@endsection
