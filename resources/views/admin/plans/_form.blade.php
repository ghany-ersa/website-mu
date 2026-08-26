@if ($errors->any())
    <div class="mb-6 rounded-lg bg-red-50 border border-red-200 text-red-600 px-4 py-3 text-sm">
        <ul class="list-disc list-inside space-y-1">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@php
    $limitFor = fn (string $key) => old("limits.$key", ($plan ?? null)?->limits->firstWhere('key', $key)?->max_count);
@endphp

<div class="bg-white rounded-2xl shadow-soft p-6 space-y-6 mb-6">
    <div class="grid md:grid-cols-2 gap-6">
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1" for="name">Nama Paket</label>
            <input type="text" name="name" id="name" value="{{ old('name', $plan->name ?? '') }}"
                   class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30">
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1" for="key">
                Key <span class="font-normal text-gray-400">(dipakai di kode, mis. "organization")</span>
            </label>
            <input type="text" name="key" id="key" value="{{ old('key', $plan->key ?? '') }}"
                   class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-primary/30">
        </div>
    </div>

    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1" for="price_monthly">Harga per Bulan (Rp)</label>
        <input type="number" name="price_monthly" id="price_monthly" min="0" step="1"
               value="{{ old('price_monthly', $plan->price_monthly ?? '') }}"
               class="w-full sm:w-64 rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30">
    </div>

    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1" for="description">Deskripsi</label>
        <p class="text-xs text-gray-400 mb-2">Tampil sebagai tagline singkat di halaman langganan &amp; landing page.</p>
        <textarea name="description" id="description" rows="2"
                  class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30">{{ old('description', $plan->description ?? '') }}</textarea>
    </div>

    <label class="flex items-center gap-2 text-sm font-semibold text-gray-700">
        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $plan->is_active ?? true))
               class="rounded border-gray-300 text-primary focus:ring-primary/30">
        Aktif (ditawarkan ke organisasi)
    </label>
</div>

<div class="bg-white rounded-2xl shadow-soft p-6 mb-6">
    <h2 class="font-bold text-gray-800">Limit Konten</h2>
    <p class="text-xs text-gray-400 mt-0.5 mb-4">Kosongkan untuk membuat tanpa batas (unlimited).</p>

    <div class="grid sm:grid-cols-2 gap-4">
        @foreach ($limitKeys as $key => $label)
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1" for="limits_{{ $key }}">{{ $label }}</label>
                <input type="number" name="limits[{{ $key }}]" id="limits_{{ $key }}" min="0" step="1"
                       placeholder="Tanpa batas"
                       value="{{ $limitFor($key) }}"
                       class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30">
            </div>
        @endforeach
    </div>
</div>
