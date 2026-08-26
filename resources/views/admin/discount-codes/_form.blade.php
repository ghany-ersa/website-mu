@if ($errors->any())
    <div class="mb-6 rounded-lg bg-red-50 border border-red-200 text-red-600 px-4 py-3 text-sm">
        <ul class="list-disc list-inside space-y-1">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="bg-white rounded-2xl shadow-soft p-6 space-y-6 mb-6">
    <div class="grid md:grid-cols-2 gap-6">
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1" for="code">Kode</label>
            <input type="text" name="code" id="code" value="{{ old('code', $discountCode->code ?? '') }}"
                   placeholder="mis. HEMAT20"
                   class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm font-mono uppercase focus:outline-none focus:ring-2 focus:ring-primary/30">
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1" for="type">Jenis Diskon</label>
            <select name="type" id="type"
                    class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30">
                @foreach (\App\Enums\DiscountCodeType::cases() as $type)
                    <option value="{{ $type->value }}" @selected(old('type', $discountCode->type?->value ?? 'percent') === $type->value)>
                        {{ $type->label() }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1" for="value">
            Nilai <span class="font-normal text-gray-400">(persen 1-100, atau nominal rupiah jika nominal tetap)</span>
        </label>
        <input type="number" name="value" id="value" min="1" step="1"
               value="{{ old('value', $discountCode->value ?? '') }}"
               class="w-full sm:w-64 rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30">
    </div>

    <div class="grid md:grid-cols-2 gap-6">
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1" for="max_uses">
                Batas Pemakaian <span class="font-normal text-gray-400">(kosongkan untuk tanpa batas)</span>
            </label>
            <input type="number" name="max_uses" id="max_uses" min="1" step="1"
                   value="{{ old('max_uses', $discountCode->max_uses ?? '') }}"
                   class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30">
            @isset($discountCode)
                <p class="text-xs text-gray-400 mt-1">Sudah dipakai {{ $discountCode->used_count }} kali.</p>
            @endisset
        </div>
    </div>

    <div class="grid md:grid-cols-2 gap-6">
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1" for="valid_from">
                Berlaku Mulai <span class="font-normal text-gray-400">(opsional)</span>
            </label>
            <input type="date" name="valid_from" id="valid_from"
                   value="{{ old('valid_from', $discountCode->valid_from?->format('Y-m-d') ?? '') }}"
                   class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30">
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1" for="valid_until">
                Berlaku Sampai <span class="font-normal text-gray-400">(opsional)</span>
            </label>
            <input type="date" name="valid_until" id="valid_until"
                   value="{{ old('valid_until', $discountCode->valid_until?->format('Y-m-d') ?? '') }}"
                   class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30">
        </div>
    </div>

    <label class="flex items-center gap-2 text-sm font-semibold text-gray-700">
        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $discountCode->is_active ?? true))
               class="rounded border-gray-300 text-primary focus:ring-primary/30">
        Aktif (bisa dipakai organisasi)
    </label>
</div>
