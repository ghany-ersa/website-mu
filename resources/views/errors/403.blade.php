@extends('errors.layout')

@section('badge', 'Akses Ditolak')
@section('code', '403')
@section('title', 'Anda Tidak Punya Akses ke Halaman Ini')
{{-- Deliberately a fixed message rather than $exception->getMessage(): authorization
     failures can carry internal wording that shouldn't reach the visitor. --}}
@section('message', 'Halaman ini hanya bisa dibuka oleh pengguna dengan izin tertentu. Jika Anda merasa seharusnya punya akses, hubungi pengelola organisasi Anda.')
