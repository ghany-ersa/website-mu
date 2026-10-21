@extends('errors.layout')

@section('badge', 'Perlu Pembayaran')
@section('code', '402')
@section('title', 'Fitur Ini Perlu Paket Infak')
@section('message', 'Halaman atau fitur yang Anda tuju tersedia pada paket Infak tertentu. Pilihan paket Infak bisa ditinjau dari dashboard organisasi Anda.')

{{-- Points at the dashboard, not the homepage: pricing isn't shown on the marketing page, only
     per organization under Kelola Paket. Guests land on login first via the auth middleware. --}}
@section('actions')
    <a href="{{ route('organizations.index') }}" class="bg-white hover:bg-gray-50 text-gray-800 border border-gray-200 px-6 py-3.5 rounded-full text-sm font-bold shadow-soft transition-all">
        Buka Dashboard
    </a>
@endsection
