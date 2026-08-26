@props([
    'name',
    'label',
    'value' => null,
    'rows' => 3,
])

<div>
    <label for="{{ $name }}" class="block text-sm font-semibold text-gray-700 mb-1">{{ $label }}</label>
    <textarea name="{{ $name }}" id="{{ $name }}" rows="{{ $rows }}"
              {{ $attributes->merge(['class' => 'w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30']) }}>{{ old($name, $value) }}</textarea>
    @error($name)
        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
    @enderror
</div>
