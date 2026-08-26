@props(['placeholder' => 'Cari...'])

<form method="GET" class="flex flex-wrap items-center gap-3 mb-6">
    <div class="relative flex-1 min-w-[200px] max-w-sm">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4 text-gray-400 absolute left-3.5 top-1/2 -translate-y-1/2">
            <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 1 0 0 11 5.5 5.5 0 0 0 0-11ZM2 9a7 7 0 1 1 12.452 4.391l3.328 3.329a.75.75 0 1 1-1.06 1.06l-3.329-3.328A7 7 0 0 1 2 9Z" clip-rule="evenodd" />
        </svg>
        <input type="search" name="q" value="{{ request('q') }}" placeholder="{{ $placeholder }}"
               class="w-full pl-10 pr-4 py-2.5 rounded-full border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
    </div>

    {{ $slot ?? '' }}

    @if (request('q') || collect(request()->except(['q', 'page']))->filter()->isNotEmpty())
        <a href="{{ request()->url() }}" class="text-sm font-medium text-gray-400 hover:text-gray-600">Reset</a>
    @endif
</form>
