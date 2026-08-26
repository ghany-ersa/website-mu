@props([
    'name',
    'label',
    'type' => 'text',
    'value' => null,
    'required' => false,
    'placeholder' => null,
])

<div>
    <label for="{{ $name }}" class="block text-sm font-semibold text-gray-700 mb-1">{{ $label }}</label>
    <input type="{{ $type }}" name="{{ $name }}" id="{{ $name }}" value="{{ old($name, $value) }}"
           @if ($required) required @endif
           @if ($placeholder) placeholder="{{ $placeholder }}" @endif
           {{ $attributes->merge(['class' => 'w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30']) }}>
    @error($name)
        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
    @enderror
</div>
