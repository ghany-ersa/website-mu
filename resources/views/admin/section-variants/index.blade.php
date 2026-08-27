@extends('layouts.admin')

@section('title', 'Variant Tampilan')

@section('content')
    <div class="mb-8">
        <h1 class="text-2xl font-extrabold text-primary">Variant Tampilan</h1>
        <p class="text-sm text-gray-500 mt-1">
            Daftar desain per komponen halaman. Aktifkan "Eksklusif" pada variant yang hanya boleh
            dipilih organisasi dengan paket berentitlement template eksklusif.
        </p>
    </div>

    <div class="bg-white rounded-2xl shadow-soft overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                <tr>
                    <th class="px-5 py-3">#</th>
                    <th class="px-5 py-3">Komponen</th>
                    <th class="px-5 py-3">Variant</th>
                    <th class="px-5 py-3">Tampilan Default</th>
                    <th class="px-5 py-3">Eksklusif</th>
                    <th class="px-5 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @php $rowNumber = 0; @endphp
                @foreach ($variantsBySection as $sectionKey => $variants)
                    @foreach ($variants as $variant)
                        @php $rowNumber++; @endphp
                        <tr>
                            <td class="px-5 py-4 text-gray-400">{{ $rowNumber }}</td>
                            <td class="px-5 py-4 text-gray-600">{{ $sectionKey }}</td>
                            <td class="px-5 py-4 font-medium text-gray-800">{{ $variant->variant_key }}</td>
                            <td class="px-5 py-4">
                                @if ($variant->is_default)
                                    <span class="px-2 py-1 rounded-full bg-secondary/10 text-secondary text-xs font-semibold">Default</span>
                                @else
                                    <span class="text-gray-300">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                <form action="{{ route('admin.section-variants.update', $variant) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="is_exclusive" value="0">
                                    <label class="inline-flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" name="is_exclusive" value="1" @checked($variant->is_exclusive)
                                               onchange="this.form.requestSubmit()"
                                               class="rounded border-gray-300 text-primary focus:ring-primary/30">
                                    </label>
                                </form>
                            </td>
                            <td class="px-5 py-4 text-right">
                                <a href="{{ route('admin.section-variants.preview', $variant) }}" target="_blank" class="text-primary font-medium hover:underline">
                                    Preview
                                </a>
                            </td>
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
