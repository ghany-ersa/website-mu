@extends('layouts.account')

@section('title', 'Ubah Kata Sandi - Website-mu')

@section('content')
    <div class="max-w-md">
        <h1 class="text-2xl font-extrabold text-primary mb-2">Ubah Kata Sandi</h1>
        <p class="text-sm text-gray-500 mb-8">
            Masukkan kata sandi Anda saat ini, lalu kata sandi baru yang ingin digunakan.
        </p>

        <div class="bg-white rounded-2xl shadow-soft p-6">
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
