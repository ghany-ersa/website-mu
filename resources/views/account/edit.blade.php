@extends('layouts.account')

@section('title', 'Pengaturan Akun - Website-mu')

@section('content')
    <div class="max-w-md">
        <h1 class="text-2xl font-extrabold text-primary mb-2">Pengaturan Akun</h1>
        <p class="text-sm text-gray-500 mb-8">{{ $user->name }} &middot; {{ $user->email }}</p>

        {{-- Nomor WhatsApp --}}
        <div class="bg-white rounded-2xl shadow-soft p-6 mb-6">
            <h2 class="font-bold text-gray-800">Nomor WhatsApp</h2>
            <p class="text-xs text-gray-400 mt-0.5 mb-4">
                Dipakai tim kami untuk menghubungi Anda, misalnya saat membantu reset kata sandi.
            </p>

            <form action="{{ route('account.whatsapp.update') }}" method="POST">
                @csrf
                @method('PATCH')

                <div class="mb-4">
                    <x-form.field name="whatsapp" type="tel" label="Nomor WhatsApp" :value="$user->whatsapp ? '0'.substr($user->whatsapp, 2) : null"
                                  required inputmode="numeric" autocomplete="tel" placeholder="08xxxxxxxxxx" />
                </div>

                <button type="submit" class="px-5 py-2.5 rounded-full bg-primary text-white text-sm font-semibold hover:bg-primary/90 transition-colors cursor-pointer">
                    Simpan Nomor
                </button>
            </form>
        </div>

        {{-- Kata Sandi --}}
        <div class="bg-white rounded-2xl shadow-soft p-6">
            <h2 class="font-bold text-gray-800 mb-4">Ubah Kata Sandi</h2>

            <form action="{{ route('account.password.update') }}" method="POST">
                @csrf
                @method('PATCH')

                <div class="mb-4">
                    <x-form.field name="current_password" type="password" label="Kata Sandi Saat Ini" required autocomplete="current-password" />
                </div>

                <div class="mb-4">
                    <x-form.field name="password" type="password" label="Kata Sandi Baru" required autocomplete="new-password" />
                </div>

                <div class="mb-6">
                    <x-form.field name="password_confirmation" type="password" label="Konfirmasi Kata Sandi Baru" required autocomplete="new-password" />
                </div>

                <button type="submit" class="px-5 py-2.5 rounded-full bg-primary text-white text-sm font-semibold hover:bg-primary/90 transition-colors cursor-pointer">
                    Simpan Kata Sandi
                </button>
            </form>
        </div>
    </div>
@endsection
