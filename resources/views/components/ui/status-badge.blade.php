@props(['status'])

<span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $status->value === 'published' ? 'bg-secondary/10 text-secondary' : 'bg-gray-100 text-gray-500' }}">
    {{ $status->label() }}
</span>
