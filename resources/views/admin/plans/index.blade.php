@extends('layouts.admin')

@section('title', 'Paket Langganan')

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-3 mb-8">
        <div>
            <h1 class="text-2xl font-extrabold text-primary">Paket Langganan</h1>
            <p class="text-sm text-gray-500 mt-1">Kelola harga dan limit konten tiap paket.</p>
        </div>
        <a href="{{ route('admin.plans.create') }}" class="px-4 py-2 rounded-full bg-primary text-white text-sm font-semibold">
            + Paket Baru
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-soft overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                <tr>
                    <th class="px-5 py-3">Paket</th>
                    <th class="px-5 py-3">Harga</th>
                    <th class="px-5 py-3">Organisasi</th>
                    <th class="px-5 py-3">Status</th>
                    <th class="px-5 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($plans as $plan)
                    <tr>
                        <td class="px-5 py-4">
                            <p class="font-semibold text-gray-800">{{ $plan->name }}</p>
                            <p class="text-xs text-gray-400 font-mono">{{ $plan->key }}</p>
                        </td>
                        <td class="px-5 py-4 text-gray-600">
                            {{ $plan->formattedPrice() }}
                        </td>
                        <td class="px-5 py-4 text-gray-600">
                            {{ $plan->organizations()->count() }} organisasi
                        </td>
                        <td class="px-5 py-4">
                            @if ($plan->is_active)
                                <span class="px-2 py-1 rounded-full bg-secondary/10 text-secondary text-xs font-semibold">Aktif</span>
                            @else
                                <span class="px-2 py-1 rounded-full bg-gray-100 text-gray-500 text-xs font-semibold">Nonaktif</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-right space-x-3">
                            <a href="{{ route('admin.plans.edit', $plan) }}" class="text-gray-600 font-medium hover:underline">Edit</a>
                            <form action="{{ route('admin.plans.destroy', $plan) }}" method="POST" class="inline"
                                  x-data @submit.prevent="if (await confirmAction(`Hapus paket ${@json($plan->name)}?`)) $el.submit()">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-500 font-medium hover:underline">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-5 py-10 text-center text-gray-400">Belum ada paket.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
