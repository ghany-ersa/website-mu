@props([
    'id',
    'itemAttribute',
    'reorderRoute',
    'payloadKey',
    'hasItems',
])

@if ($hasItems)
    <p class="text-xs text-gray-400 mb-2">Seret <span class="font-semibold">⠿</span> untuk mengubah urutan tampil.</p>
@endif

<div id="{{ $id }}" class="bg-white rounded-2xl shadow-soft divide-y divide-gray-100">
    {{ $slot }}
</div>

@if ($hasItems)
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
    <script>
        new Sortable(document.getElementById(@json($id)), {
            handle: '.cursor-move',
            animation: 150,
            onEnd() {
                const listId = @json($id);
                const itemAttribute = @json($itemAttribute);
                const payloadKey = @json($payloadKey);

                const ids = [...document.querySelectorAll(`#${listId} [${itemAttribute}]`)]
                    .map((el) => el.getAttribute(itemAttribute));

                fetch(@json($reorderRoute), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': @json(csrf_token()),
                    },
                    body: JSON.stringify({ [payloadKey]: ids }),
                });
            },
        });
    </script>
@endif
