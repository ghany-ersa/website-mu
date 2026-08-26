@props(['title', 'subtitle'])

<div class="flex items-center justify-between gap-4 mb-8">
    <div>
        <h1 class="text-2xl font-extrabold text-primary">{{ $title }}</h1>
        <p class="text-sm text-gray-500">{{ $subtitle }}</p>
    </div>
    {{ $actions }}
</div>
