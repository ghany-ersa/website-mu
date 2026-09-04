@extends('layouts.app')

@section('title', 'Daftar - Website-mu')

@section('content')
    <div class="max-w-md mx-auto">
        <h1 class="text-2xl font-extrabold text-primary mb-1 text-center">Buat Akun</h1>
        <p class="text-gray-500 text-center mb-8">Kelola website organisasi Muhammadiyah Anda.</p>

        <form action="{{ route('register') }}" method="POST" class="bg-white rounded-2xl shadow-soft p-6 space-y-4">
            @csrf

            <div>
                <label for="name" class="block text-sm font-semibold text-gray-700 mb-1">Nama</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required autofocus
                       class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30">
            </div>

            <div>
                <label for="email" class="block text-sm font-semibold text-gray-700 mb-1">Email</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" required
                       class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30">
            </div>

            <div>
                <label for="password" class="block text-sm font-semibold text-gray-700 mb-1">Kata Sandi</label>
                <input type="password" name="password" id="password" required
                       class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30">
            </div>

            <div>
                <label for="password_confirmation" class="block text-sm font-semibold text-gray-700 mb-1">Konfirmasi Kata Sandi</label>
                <input type="password" name="password_confirmation" id="password_confirmation" required
                       class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30">
            </div>

            <button type="submit" class="w-full py-2.5 rounded-full bg-primary text-white text-sm font-semibold">
                Daftar
            </button>
        </form>

        <p class="text-center text-sm text-gray-500 mt-6">
            Sudah punya akun? <a href="{{ route('login') }}" class="text-primary font-semibold hover:underline">Masuk</a>
        </p>
    </div>
@endsection
