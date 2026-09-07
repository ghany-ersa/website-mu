@extends('layouts.app')

@section('title', 'Masuk - Website-mu')

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

            <div x-data="{ show: false }">
                <label for="password" class="block text-sm font-semibold text-gray-700 mb-1">Kata Sandi</label>
                <div class="relative">
                    <input :type="show ? 'text' : 'password'" name="password" id="password" required
                           class="w-full rounded-lg border border-gray-200 px-3 py-2 pr-10 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30">
                    <button type="button" @click="show = !show"
                            class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-400 hover:text-gray-600"
                            :aria-label="show ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'">
                        <svg x-show="!show" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                            <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                        </svg>
                        <svg x-show="show" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M3.707 2.293a1 1 0 00-1.414 1.414l14 14a1 1 0 001.414-1.414l-1.473-1.473A10.014 10.014 0 0019.542 10C18.268 5.943 14.478 3 10 3a9.958 9.958 0 00-4.512 1.074l-1.78-1.781zm4.261 4.26l1.514 1.515a2 2 0 012.45 2.45l1.514 1.514a4 4 0 00-5.478-5.478z" clip-rule="evenodd" />
                            <path d="M2.458 10c.47 1.5 1.34 2.837 2.474 3.881l1.464-1.464a5.977 5.977 0 01-1.334-3.117l-2.604-.7zM10 17c-.35 0-.694-.02-1.03-.058l1.554-1.554a6.01 6.01 0 004.29-4.29l1.554-1.554A9.965 9.965 0 0117 10c-1.274 4.057-5.064 7-9.542 7z" />
                        </svg>
                    </button>
                </div>
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
