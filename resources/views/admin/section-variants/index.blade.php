@extends('layouts.admin')

@section('title', 'Variant Tampilan')

@section('content')
    <div class="mb-8">
        <h1 class="text-2xl font-extrabold text-primary">Variant Tampilan</h1>
        <p class="text-sm text-gray-500 mt-1">
            Daftar desain per widget halaman, dikelompokkan per widget. Aktifkan "Eksklusif" pada
            variant yang hanya boleh dipilih organisasi dengan paket berentitlement template eksklusif.
            Nonaktifkan "Tampilkan" untuk menyembunyikan variant yang masih diuji dari pilihan
            organisasi baru - preview tetap bisa diakses, dan organisasi yang sudah memakainya
            tidak terpengaruh.
        </p>
    </div>

    <div class="relative flex-1 min-w-[200px] max-w-sm mb-6">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4 text-gray-400 absolute left-3.5 top-1/2 -translate-y-1/2">
            <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 1 0 0 11 5.5 5.5 0 0 0 0-11ZM2 9a7 7 0 1 1 12.452 4.391l3.328 3.329a.75.75 0 1 1-1.06 1.06l-3.329-3.328A7 7 0 0 1 2 9Z" clip-rule="evenodd" />
        </svg>
        <input type="search" id="variant-search" placeholder="Cari widget atau variant..."
               class="w-full pl-10 pr-4 py-2.5 rounded-full border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
    </div>

    <div class="bg-white rounded-2xl shadow-soft overflow-x-auto">
        <table class="w-full text-sm text-left" style="min-width: 640px">
            <colgroup>
                <col class="w-[26%]">
                <col class="w-[22%]">
                <col class="w-[12%]">
                <col class="w-[12%]">
                <col class="w-[12%]">
                <col class="w-[16%]">
            </colgroup>
            <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                <tr>
                    <th class="px-5 py-3">Widget</th>
                    <th class="px-5 py-3">Variant</th>
                    <th class="px-5 py-3">Status</th>
                    <th class="px-5 py-3">Tampilkan</th>
                    <th class="px-5 py-3">Eksklusif</th>
                    <th class="px-5 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100" id="variant-groups">
                @foreach ($variantsBySection as $sectionKey => $variants)
                    @php
                        $exclusiveCount = $variants->where('is_exclusive', true)->count();
                        $sorted = $variants->sortByDesc('is_default');
                        $rowCount = $sorted->count();
                    @endphp
                    @foreach ($sorted as $variant)
                        <tr class="variant-row"
                            data-section-key="{{ strtolower($sectionKey) }}"
                            data-variant-key="{{ strtolower($variant->variant_key) }}">
                            @if ($loop->first)
                                <td class="px-5 py-3 align-top border-r border-gray-100" rowspan="{{ $rowCount }}">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="font-semibold text-gray-800">{{ $sectionKey }}</span>
                                        <span class="px-2 py-0.5 rounded-full bg-gray-100 text-gray-500 text-xs font-semibold whitespace-nowrap">
                                            {{ $rowCount }} variant
                                        </span>
                                        @if ($exclusiveCount > 0)
                                            <span class="px-2 py-0.5 rounded-full bg-amber-50 text-amber-600 text-xs font-semibold whitespace-nowrap">
                                                {{ $exclusiveCount }} eksklusif
                                            </span>
                                        @endif
                                    </div>
                                </td>
                            @endif
                            <td class="px-5 py-3 font-medium text-gray-800">
                                {{ $variant->variant_key }}
                                @if (! $variant->is_visible)
                                    <span class="ml-1 px-2 py-0.5 rounded-full bg-gray-100 text-gray-500 text-xs font-semibold whitespace-nowrap">Disembunyikan</span>
                                @endif
                            </td>
                            <td class="px-5 py-3">
                                @if ($variant->is_default)
                                    <span class="px-2 py-1 rounded-full bg-secondary/10 text-secondary text-xs font-semibold whitespace-nowrap">Default</span>
                                @else
                                    <span class="text-gray-300">-</span>
                                @endif
                            </td>
                            {{-- Both toggles post through the SAME form (via the HTML5 form="..."
                                 attribute, since a <form> can't wrap multiple <td> siblings as a
                                 valid child of <tr>) so flipping either one always submits both
                                 fields' current state together - two separate auto-submitting
                                 forms would let changing one silently reset the other to its
                                 unchecked default (no hidden field for it in that form at all). --}}
                            @php $formId = 'variant-form-'.$variant->id; @endphp
                            <td class="px-5 py-3">
                                <form id="{{ $formId }}" action="{{ route('admin.section-variants.update', $variant) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                </form>
                                <input type="hidden" name="is_visible" value="0" form="{{ $formId }}">
                                <label class="inline-flex items-center gap-2 cursor-pointer" title="{{ $variant->is_default ? 'Variant default tidak bisa disembunyikan' : '' }}">
                                    <input type="checkbox" name="is_visible" value="1" form="{{ $formId }}" @checked($variant->is_visible)
                                           onchange="document.getElementById('{{ $formId }}').requestSubmit()"
                                           {{ $variant->is_default ? 'disabled' : '' }}
                                           class="rounded border-gray-300 text-primary focus:ring-primary/30 disabled:opacity-40 disabled:cursor-not-allowed">
                                </label>
                            </td>
                            <td class="px-5 py-3">
                                <input type="hidden" name="is_exclusive" value="0" form="{{ $formId }}">
                                <label class="inline-flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" name="is_exclusive" value="1" form="{{ $formId }}" @checked($variant->is_exclusive)
                                           onchange="document.getElementById('{{ $formId }}').requestSubmit()"
                                           class="rounded border-gray-300 text-primary focus:ring-primary/30">
                                </label>
                            </td>
                            <td class="px-5 py-3 text-right">
                                <a href="{{ route('admin.section-variants.preview', $variant) }}" target="_blank" class="text-primary font-medium hover:underline whitespace-nowrap">
                                    Preview
                                </a>
                            </td>
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>

        <p id="variant-empty" class="hidden text-center text-gray-400 py-10">
            Tidak ada widget atau variant yang cocok dengan pencarian.
        </p>
    </div>

    <script>
        document.getElementById('variant-search').addEventListener('input', function (e) {
            const query = e.target.value.trim().toLowerCase();
            const rows = Array.from(document.querySelectorAll('#variant-groups > tr'));

            const visibleSections = new Set(
                rows
                    .filter((row) => row.dataset.sectionKey.includes(query) || row.dataset.variantKey.includes(query))
                    .map((row) => row.dataset.sectionKey)
            );

            rows.forEach((row) => {
                row.classList.toggle('hidden', !visibleSections.has(row.dataset.sectionKey));
            });

            document.getElementById('variant-empty').classList.toggle('hidden', visibleSections.size > 0);
        });
    </script>
@endsection
