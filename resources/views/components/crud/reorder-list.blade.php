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
    <script>
        // Deferred until DOMContentLoaded: window.Sortable is set by an ES module (loaded by
        // the Vite directive in this page's layout, resources/js/organization.js), and modules
        // only execute after the document is parsed - calling it inline here would throw
        // "Sortable is not defined" and silently drop drag-to-reorder.
        document.addEventListener('DOMContentLoaded', () => {
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
        });
    </script>
@endif
