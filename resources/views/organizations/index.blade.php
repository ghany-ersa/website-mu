@extends('layouts.app')

@section('title', 'Organisasi Saya — Website-mu')

@section('content')
    <div class="flex items-center justify-between mb-8">
        <h1 class="text-2xl font-extrabold text-primary">Organisasi Saya</h1>
        <a href="{{ route('organizations.create') }}" class="px-4 py-2 rounded-full bg-primary text-white text-sm font-semibold">
            + Buat Organisasi
        </a>
    </div>

    @if ($organizations->isEmpty())
        <div class="bg-white rounded-2xl shadow-soft p-10 text-center text-gray-400">
            Anda belum tergabung di organisasi manapun. Buat organisasi pertama Anda.
        </div>
    @else
        <div class="grid md:grid-cols-2 gap-4">
            @foreach ($organizations as $organization)
                <a href="{{ route('organizations.show', $organization) }}"
                   class="bg-white rounded-2xl shadow-soft p-5 hover:shadow-float transition-shadow">
                    <div class="flex items-center justify-between mb-2">
                        <p class="font-bold text-gray-800">{{ $organization->name }}</p>
                        <span class="px-2 py-1 rounded-full bg-primary/10 text-primary text-xs font-semibold">
                            {{ $organization->pivot->role }}
                        </span>
                    </div>
                    <p class="text-sm text-gray-500">{{ $organization->organizationType?->name }}</p>
                </a>
            @endforeach
        </div>
    @endif
@endsection
