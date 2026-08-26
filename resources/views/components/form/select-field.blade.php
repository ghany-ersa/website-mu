@props([
    'name',
    'label',
    'options',
    'selected' => null,
])

<div>
    <label for="{{ $name }}" class="block text-sm font-semibold text-gray-700 mb-1">{{ $label }}</label>
    <select name="{{ $name }}" id="{{ $name }}"
            {{ $attributes->merge(['class' => 'w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30']) }}>
        @foreach ($options as $value => $optionLabel)
            <option value="{{ $value }}" @selected(old($name, $selected) === $value)>{{ $optionLabel }}</option>
        @endforeach
    </select>
    @error($name)
        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
    @enderror
</div>
