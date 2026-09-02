@extends('layouts.admin')

@section('title', 'Permintaan Paket')

@section('content')
    <div class="mb-8">
        <h1 class="text-2xl font-extrabold text-primary">Permintaan Pergantian Paket</h1>
        <p class="text-sm text-gray-500 mt-1">{{ $requests->total() }} permintaan &mdash; disetujui otomatis begitu Midtrans mengonfirmasi pembayaran.</p>
    </div>

    <x-crud.search-form placeholder="Cari nama organisasi atau pemohon...">
        <select name="status" onchange="this.form.submit()"
                class="px-4 py-2.5 rounded-full border border-gray-200 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
            <option value="">Semua Status</option>
            @foreach ($statuses as $statusOption)
                <option value="{{ $statusOption->value }}" @selected(request('status') === $statusOption->value)>{{ $statusOption->label() }}</option>
            @endforeach
        </select>
    </x-crud.search-form>

    <div class="bg-white rounded-2xl shadow-soft overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                <tr>
                    <th class="px-5 py-3">#</th>
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
                        <td class="px-5 py-4 text-gray-400">
                            {{ $requests->firstItem() + $loop->index }}
                        </td>
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
                                @case(\App\Enums\PlanChangeRequestStatus::PaymentReceivedNeedsReview)
                                    <span class="px-2 py-1 rounded-full bg-orange-100 text-orange-600 text-xs font-semibold">{{ $request->status->label() }}</span>
                                    <p class="text-xs text-gray-400 mt-1">
                                        Dibayar {{ $request->midtrans_paid_at?->translatedFormat('d M Y, H:i') }}
                                        &middot; {{ $request->approve_attempts }}&times; gagal
                                    </p>
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
                            @if ($request->status === \App\Enums\PlanChangeRequestStatus::Pending)
                                <form action="{{ route('admin.plan-change-requests.reject', $request) }}" method="POST"
                                      x-data @submit.prevent="if (await confirmAction('Batalkan permintaan yang belum dibayar ini?')) $el.submit()">
                                    @csrf
                                    <button type="submit" class="px-3 py-1.5 rounded-full text-gray-500 text-xs font-semibold hover:bg-gray-100 transition-colors">
                                        Batalkan
                                    </button>
                                </form>
                            @elseif ($request->status === \App\Enums\PlanChangeRequestStatus::PaymentReceivedNeedsReview)
                                <div class="flex flex-col items-end gap-1.5">
                                    @if ($request->approve_error)
                                        <p class="text-xs text-red-500 max-w-xs text-right truncate" title="{{ $request->approve_error }}">{{ $request->approve_error }}</p>
                                    @endif
                                    @if ($request->canRetryApprove())
                                        <form action="{{ route('admin.plan-change-requests.retry-approve', $request) }}" method="POST"
                                              x-data @submit.prevent="if (await confirmAction('Coba setujui ulang paket ini?', { danger: false })) $el.submit()">
                                            @csrf
                                            <button type="submit" class="px-3 py-1.5 rounded-full bg-secondary text-white text-xs font-semibold hover:bg-green-700 transition-colors">
                                                Coba Lagi
                                            </button>
                                        </form>
                                    @else
                                        <a href="{{ route('admin.organizations.show', $request->organization) }}"
                                           class="text-xs text-primary font-semibold hover:underline">
                                            Batas percobaan tercapai &mdash; ubah paket manual
                                        </a>
                                    @endif
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
                        <td colspan="7" class="px-5 py-10 text-center text-gray-400">
                            @if (request('q') || request('status'))
                                Tidak ada permintaan yang cocok dengan pencarian.
                            @else
                                Belum ada permintaan pergantian paket.
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $requests->onEachSide(1)->links() }}
    </div>
@endsection
