@props([
    'editHref',
    'deleteAction',
    'confirmMessage',
    'fromBuilder' => false,
    'section' => null,
])

<div class="flex items-center gap-3 shrink-0">
    <a href="{{ $editHref }}" class="text-primary text-sm font-semibold hover:underline">Edit</a>
    <form action="{{ $deleteAction }}" method="POST"
          x-data @submit.prevent="if (await confirmAction('{{ $confirmMessage }}')) $el.submit()">
        @csrf
        @method('DELETE')
        @if ($fromBuilder)
            <input type="hidden" name="from" value="builder">
            <input type="hidden" name="section" value="{{ $section }}">
        @endif
        <button type="submit" class="text-red-500 text-sm font-medium hover:underline">Hapus</button>
    </form>
</div>
