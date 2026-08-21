@extends('layouts.app')

@section('title', 'Masuk — Website-mu')

@section('content')
    <div class="max-w-md mx-auto">
        <h1 class="text-2xl font-extrabold text-primary mb-1 text-center">Masuk</h1>
        <p class="text-gray-500 text-center mb-8">Kelola website organisasi Anda.</p>

        <form action="{{ route('login') }}" method="POST" class="bg-white rounded-2xl shadow-soft p-6 space-y-4">
            @csrf

            <div>
                <label for="email" class="block text-sm font-semibold text-gray-700 mb-1">Email</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus
                       class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30">
            </div>

            <div>
                <label for="password" class="block text-sm font-semibold text-gray-700 mb-1">Kata Sandi</label>
                <input type="password" name="password" id="password" required
                       class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30">
            </div>

            <label class="flex items-center gap-2 text-sm text-gray-600">
                <input type="checkbox" name="remember" class="rounded border-gray-300 text-primary focus:ring-primary/30">
                Ingat saya
            </label>

            <button type="submit" class="w-full py-2.5 rounded-full bg-primary text-white text-sm font-semibold">
                Masuk
            </button>
        </form>

        <p class="text-center text-sm text-gray-500 mt-6">
            Belum punya akun? <a href="{{ route('register') }}" class="text-primary font-semibold hover:underline">Daftar</a>
        </p>
    </div>
@endsection
