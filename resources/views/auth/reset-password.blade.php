@extends('layouts.app')

@section('title', 'Atur Kata Sandi Baru - Website-mu')

@section('content')
    <div class="max-w-md mx-auto">
        <h1 class="text-2xl font-extrabold text-primary mb-1 text-center">Atur Kata Sandi Baru</h1>
        <p class="text-gray-500 text-center mb-8">Masukkan kata sandi baru untuk akun Anda.</p>

        <form action="{{ route('password.update') }}" method="POST" class="bg-white rounded-2xl shadow-soft p-6 space-y-4">
            @csrf

            <input type="hidden" name="token" value="{{ $token }}">

            <div>
                <label for="email" class="block text-sm font-semibold text-gray-700 mb-1">Email</label>
                <input type="email" name="email" id="email" value="{{ old('email', $email) }}" required autofocus
                       class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30">
            </div>

            <div>
                <label for="password" class="block text-sm font-semibold text-gray-700 mb-1">Kata Sandi Baru</label>
                <input type="password" name="password" id="password" required autocomplete="new-password"
                       class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30">
            </div>

            <div>
                <label for="password_confirmation" class="block text-sm font-semibold text-gray-700 mb-1">Konfirmasi Kata Sandi Baru</label>
                <input type="password" name="password_confirmation" id="password_confirmation" required autocomplete="new-password"
                       class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30">
            </div>

            <button type="submit" class="w-full py-2.5 rounded-full bg-primary text-white text-sm font-semibold">
                Simpan Kata Sandi Baru
            </button>
        </form>
    </div>
@endsection
