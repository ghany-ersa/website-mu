@extends('layouts.app')

@section('title', 'Lupa Kata Sandi - Website-mu')

@section('content')
    <div class="max-w-md mx-auto">
        <h1 class="text-2xl font-extrabold text-primary mb-1 text-center">Lupa Kata Sandi</h1>
        <p class="text-gray-500 text-center mb-8">
            Sistem kami belum terhubung ke layanan email, jadi reset kata sandi dibantu langsung oleh tim kami
            via WhatsApp. Masukkan email akun Anda, lalu lanjutkan ke WhatsApp.
        </p>

        <form
            class="bg-white rounded-2xl shadow-soft p-6 space-y-4"
            x-data="{ email: '' }"
            x-on:submit.prevent="window.open('https://wa.me/6285183220977?text=' + encodeURIComponent('Assalamualaikum, saya lupa kata sandi akun website-mu.id dengan email ' + email + '. Mohon bantuan untuk reset kata sandi.'), '_blank')"
        >
            <div>
                <label for="email" class="block text-sm font-semibold text-gray-700 mb-1">Email Akun Anda</label>
                <input x-model="email" type="email" id="email" required autofocus
                       class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30">
            </div>

            <button type="submit" class="w-full py-2.5 rounded-full bg-primary text-white text-sm font-semibold">
                Ubah Kata Sandi
            </button>
        </form>

        <p class="text-center text-sm text-gray-500 mt-6">
            Sudah ingat kata sandi? <a href="{{ route('login') }}" class="text-primary font-semibold hover:underline">Masuk</a>
        </p>
    </div>
@endsection
