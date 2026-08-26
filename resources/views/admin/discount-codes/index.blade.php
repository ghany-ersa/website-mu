@extends('layouts.admin')

@section('title', 'Kode Diskon')

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-3 mb-8">
        <div>
            <h1 class="text-2xl font-extrabold text-primary">Kode Diskon</h1>
            <p class="text-sm text-gray-500 mt-1">{{ $discountCodes->total() }} kode &mdash; kelola voucher yang bisa dipakai organisasi saat memilih paket.</p>
        </div>
        <a href="{{ route('admin.discount-codes.create') }}" class="px-4 py-2 rounded-full bg-primary text-white text-sm font-semibold">
            + Kode Baru
        </a>
    </div>

    <x-crud.search-form placeholder="Cari kode..." />

    <div class="bg-white rounded-2xl shadow-soft overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                <tr>
                    <th class="px-5 py-3">Kode</th>
                    <th class="px-5 py-3">Nilai</th>
                    <th class="px-5 py-3">Pemakaian</th>
                    <th class="px-5 py-3">Masa Berlaku</th>
                    <th class="px-5 py-3">Status</th>
                    <th class="px-5 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($discountCodes as $discountCode)
                    <tr>
                        <td class="px-5 py-4">
                            <p class="font-mono font-semibold text-gray-800">{{ $discountCode->code }}</p>
                        </td>
                        <td class="px-5 py-4 text-gray-600">
                            {{ $discountCode->type === \App\Enums\DiscountCodeType::Percent ? $discountCode->value.'%' : 'Rp '.number_format($discountCode->value, 0, ',', '.') }}
                        </td>
                        <td class="px-5 py-4 text-gray-600">
                            {{ $discountCode->used_count }}{{ $discountCode->max_uses ? ' / '.$discountCode->max_uses : '' }}
                        </td>
                        <td class="px-5 py-4 text-gray-600 text-xs">
                            @if ($discountCode->valid_from || $discountCode->valid_until)
                                {{ $discountCode->valid_from?->translatedFormat('d M Y') ?? '...' }}
                                &ndash;
                                {{ $discountCode->valid_until?->translatedFormat('d M Y') ?? '...' }}
                            @else
                                Selamanya
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            @if ($discountCode->isUsable())
                                <span class="px-2 py-1 rounded-full bg-secondary/10 text-secondary text-xs font-semibold">Aktif</span>
                            @elseif ($discountCode->is_active)
                                <span class="px-2 py-1 rounded-full bg-amber-100 text-amber-700 text-xs font-semibold">Habis/Kadaluarsa</span>
                            @else
                                <span class="px-2 py-1 rounded-full bg-gray-100 text-gray-500 text-xs font-semibold">Nonaktif</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-right space-x-3">
                            <a href="{{ route('admin.discount-codes.edit', $discountCode) }}" class="text-gray-600 font-medium hover:underline">Edit</a>
                            <form action="{{ route('admin.discount-codes.destroy', $discountCode) }}" method="POST" class="inline"
                                  x-data @submit.prevent="if (await confirmAction(`Hapus kode ${@json($discountCode->code)}?`)) $el.submit()">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-500 font-medium hover:underline">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-5 py-10 text-center text-gray-400">
                            @if (request('q'))
                                Tidak ada kode yang cocok dengan pencarian.
                            @else
                                Belum ada kode diskon.
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $discountCodes->onEachSide(1)->links() }}
    </div>
@endsection
