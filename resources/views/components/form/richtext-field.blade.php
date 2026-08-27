@props([
    'name',
    'label',
    'value' => null,
])

<div data-richtext-root class="richtext-editor">
    <label for="{{ $name }}" class="block text-sm font-semibold text-gray-700 mb-1">{{ $label }}</label>

    <div class="rounded-lg border border-gray-200 focus-within:ring-2 focus-within:ring-primary/30 overflow-hidden">
        <div data-richtext-toolbar class="flex flex-wrap gap-1 border-b border-gray-200 bg-gray-50 px-2 py-1.5">
            <button type="button" data-command="bold" aria-label="Tebal" title="Tebal"><strong>B</strong></button>
            <button type="button" data-command="italic" aria-label="Miring" title="Miring"><em>I</em></button>
            <button type="button" data-command="underline" aria-label="Garis bawah" title="Garis bawah"><span class="underline">U</span></button>
            <button type="button" data-command="strike" aria-label="Coret" title="Coret"><span class="line-through">S</span></button>
            <span class="w-px bg-gray-200 mx-0.5"></span>
            <button type="button" data-command="h2" aria-label="Judul 2" title="Judul 2">H2</button>
            <button type="button" data-command="h3" aria-label="Judul 3" title="Judul 3">H3</button>
            <span class="w-px bg-gray-200 mx-0.5"></span>
            <button type="button" data-command="bulletList" aria-label="Daftar bertitik" title="Daftar bertitik">&bull; List</button>
            <button type="button" data-command="orderedList" aria-label="Daftar bernomor" title="Daftar bernomor">1. List</button>
            <button type="button" data-command="blockquote" aria-label="Kutipan" title="Kutipan">&ldquo;&rdquo;</button>
            <span class="w-px bg-gray-200 mx-0.5"></span>
            <button type="button" data-command="link" aria-label="Tautan" title="Tautan">&#128279;</button>
        </div>
        <div data-richtext-mount class="min-h-[8rem] px-3 py-2 text-sm"></div>
    </div>

    <textarea name="{{ $name }}" id="{{ $name }}" data-richtext-input class="hidden">{{ old($name, $value) }}</textarea>

    @error($name)
        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
    @enderror
</div>
