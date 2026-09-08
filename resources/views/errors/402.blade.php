@extends('errors.layout')

@section('badge', 'Perlu Pembayaran')
@section('code', '402')
@section('title', 'Fitur Ini Perlu Paket Berlangganan')
@section('message', 'Halaman atau fitur yang Anda tuju tersedia pada paket berlangganan tertentu. Silakan tinjau pilihan paket kami untuk melanjutkan.')

@section('actions')
    <a href="{{ url('/#harga') }}" class="bg-white hover:bg-gray-50 text-gray-800 border border-gray-200 px-6 py-3.5 rounded-full text-sm font-bold shadow-soft transition-all">
        Lihat Paket
    </a>
@endsection
