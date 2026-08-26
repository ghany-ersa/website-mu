@extends('layouts.admin')

@section('title', 'Permintaan Paket')

@section('content')
    <div class="mb-8">
        <h1 class="text-2xl font-extrabold text-primary">Permintaan Pergantian Paket</h1>
        <p class="text-sm text-gray-500 mt-1">Setujui setelah pembayaran dikonfirmasi manual di luar sistem.</p>
    </div>

    <div class="bg-white rounded-2xl shadow-soft overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                <tr>
                    <th class="px-5 py-3">Organisasi</th>
                    <th class="px-5 py-3">Diajukan Oleh</th>
                    <th class="px-5 py-3">Paket Diminta</th>
                    <th class="px-5 py-3">Status</th>
                    <th class="px-5 py-3">Tanggal</th>
                    <th class="px-5 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($requests as $request)
                    <tr>
                        <td class="px-5 py-4">
                            <p class="font-semibold text-gray-800">{{ $request->organization->name }}</p>
                            <p class="text-xs text-gray-400">Paket saat ini: {{ $request->organization->plan?->name ?? '—' }}</p>
                        </td>
                        <td class="px-5 py-4 text-gray-600">
                            <p>{{ $request->requestedBy->name }}</p>
                            <p class="text-xs text-gray-400">{{ $request->requestedBy->email }}</p>
                        </td>
                        <td class="px-5 py-4 font-semibold text-gray-800">
                            {{ $request->requestedPlan->name }} &times; {{ $request->duration_months }} bulan
                            <p class="text-xs font-normal text-gray-400">
                                {{ $request->requestedPlan->formattedPrice() }}
                                &mdash; Total Rp {{ number_format($request->totalPrice(), 0, ',', '.') }}
                            </p>
                        </td>
                        <td class="px-5 py-4">
                            @switch($request->status)
                                @case(\App\Enums\PlanChangeRequestStatus::Pending)
                                    <span class="px-2 py-1 rounded-full bg-amber-100 text-amber-600 text-xs font-semibold">{{ $request->status->label() }}</span>
                                    @break
                                @case(\App\Enums\PlanChangeRequestStatus::PaymentConfirmed)
                                    <span class="px-2 py-1 rounded-full bg-blue-100 text-blue-600 text-xs font-semibold">{{ $request->status->label() }}</span>
                                    <p class="text-xs text-gray-400 mt-1">Dikonfirmasi {{ $request->payment_confirmed_at?->translatedFormat('d M Y, H:i') }}</p>
                                    @break
                                @case(\App\Enums\PlanChangeRequestStatus::Approved)
                                    <span class="px-2 py-1 rounded-full bg-secondary/10 text-secondary text-xs font-semibold">{{ $request->status->label() }}</span>
                                    @break
                                @default
                                    <span class="px-2 py-1 rounded-full bg-red-50 text-red-500 text-xs font-semibold">{{ $request->status->label() }}</span>
                            @endswitch
                        </td>
                        <td class="px-5 py-4 text-gray-500 text-xs">
                            {{ $request->created_at->translatedFormat('d M Y, H:i') }}
                        </td>
                        <td class="px-5 py-4 text-right">
                            @if (in_array($request->status, [\App\Enums\PlanChangeRequestStatus::Pending, \App\Enums\PlanChangeRequestStatus::PaymentConfirmed]))
                                <div class="flex items-center justify-end gap-2">
                                    <form action="{{ route('admin.plan-change-requests.reject', $request) }}" method="POST"
                                          x-data @submit.prevent="if (await confirmAction('Tolak permintaan ini?')) $el.submit()">
                                        @csrf
                                        <button type="submit" class="px-3 py-1.5 rounded-full text-gray-500 text-xs font-semibold hover:bg-gray-100 transition-colors">
                                            Tolak
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.plan-change-requests.approve', $request) }}" method="POST"
                                          x-data @submit.prevent="if (await confirmAction('Setujui dan aktifkan paket ini? Pastikan pembayaran sudah dikonfirmasi.', { danger: false })) $el.submit()">
                                        @csrf
                                        <button type="submit" class="px-3 py-1.5 rounded-full bg-secondary text-white text-xs font-semibold hover:bg-green-700 transition-colors">
                                            Setujui
                                        </button>
                                    </form>
                                </div>
                            @else
                                <span class="text-xs text-gray-400">
                                    Diproses oleh {{ $request->reviewedBy?->name ?? '—' }}
                                </span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-5 py-10 text-center text-gray-400">
                            Belum ada permintaan pergantian paket.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
