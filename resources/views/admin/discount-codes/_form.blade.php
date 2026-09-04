@if ($errors->any())
    <div class="mb-6 rounded-lg bg-red-50 border border-red-200 text-red-600 px-4 py-3 text-sm">
        <ul class="list-disc list-inside space-y-1">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="bg-white rounded-2xl shadow-soft p-6 space-y-6 mb-6">
    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1" for="code">Kode</label>
        <input type="text" name="code" id="code" value="{{ old('code', $discountCode?->code ?? '') }}"
               placeholder="mis. HEMAT20" autocomplete="off"
               class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm font-mono uppercase focus:outline-none focus:ring-2 focus:ring-primary/30">
    </div>

    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1" for="type">Jenis Diskon</label>
        <select name="type" id="type"
                class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30">
            @foreach (\App\Enums\DiscountCodeType::cases() as $type)
                <option value="{{ $type->value }}" @selected(old('type', $discountCode?->type?->value ?? 'percent') === $type->value)>
                    {{ $type->label() }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1" for="value">
            Nilai <span class="font-normal text-gray-400" id="value_hint">(persen 1-100)</span>
        </label>
        <div class="relative w-full sm:w-64">
            <span id="value_prefix" class="absolute inset-y-0 left-0 flex items-center pl-3 text-sm text-gray-400 pointer-events-none"></span>
            <input type="number" name="value" id="value" min="1" step="1"
                   value="{{ old('value', $discountCode?->value ?? '') }}"
                   class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30">
            <span id="value_postfix" class="absolute inset-y-0 right-0 flex items-center pr-3 text-sm text-gray-400 pointer-events-none"></span>
        </div>
    </div>

    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1" for="max_uses">
            Batas Pemakaian <span class="font-normal text-gray-400">(kosongkan untuk tanpa batas)</span>
        </label>
        <input type="number" name="max_uses" id="max_uses" min="1" step="1"
               value="{{ old('max_uses', $discountCode?->max_uses ?? '') }}"
               class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30">
        @isset($discountCode)
            <p class="text-xs text-gray-400 mt-1">Sudah dipakai {{ $discountCode->used_count }} kali.</p>
        @endisset
    </div>

    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1" for="valid_range">
            Masa Berlaku <span class="font-normal text-gray-400">(opsional - kosongkan untuk tanpa batas waktu)</span>
        </label>
        <input type="text" id="valid_range" placeholder="Pilih rentang tanggal" autocomplete="off" readonly
               class="w-full sm:w-80 rounded-lg border border-gray-200 px-3 py-2 text-sm bg-white cursor-pointer focus:outline-none focus:ring-2 focus:ring-primary/30">
        <input type="hidden" name="valid_from" id="valid_from" value="{{ old('valid_from', $discountCode?->valid_from?->format('Y-m-d') ?? '') }}">
        <input type="hidden" name="valid_until" id="valid_until" value="{{ old('valid_until', $discountCode?->valid_until?->format('Y-m-d') ?? '') }}">
    </div>

    <label class="flex items-center gap-2 text-sm font-semibold text-gray-700">
        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $discountCode?->is_active ?? true))
               class="rounded border-gray-300 text-primary focus:ring-primary/30">
        Aktif (bisa dipakai organisasi)
    </label>
</div>

<script>
    (function () {
        const typeSelect = document.getElementById('type');
        const valueInput = document.getElementById('value');
        const valuePrefix = document.getElementById('value_prefix');
        const valuePostfix = document.getElementById('value_postfix');
        const valueHint = document.getElementById('value_hint');

        const syncValueAffix = () => {
            const isPercent = typeSelect.value === 'percent';

            valuePrefix.textContent = isPercent ? '' : 'Rp';
            valuePostfix.textContent = isPercent ? '%' : '';
            valueInput.style.paddingLeft = isPercent ? '' : '2.25rem';
            valueInput.style.paddingRight = isPercent ? '2rem' : '';
            valueInput.max = isPercent ? 100 : '';
            valueHint.textContent = isPercent ? '(persen 1-100)' : '(nominal rupiah)';
        };

        typeSelect.addEventListener('change', syncValueAffix);
        syncValueAffix();
    })();
</script>

<script>
    (function () {
        const codeInput = document.getElementById('code');

        codeInput.addEventListener('input', () => {
            const cursorFromEnd = codeInput.value.length - codeInput.selectionEnd;

            const sanitized = codeInput.value
                .toUpperCase()
                .replace(/\s+/g, '')
                .replace(/[^A-Z0-9_-]/g, '');

            if (sanitized !== codeInput.value) {
                codeInput.value = sanitized;
                const pos = Math.max(0, sanitized.length - cursorFromEnd);
                codeInput.setSelectionRange(pos, pos);
            }
        });
    })();
</script>

{{-- Litepicker: dependency-free date range picker (no jQuery/moment.js), bundled via
     Vite/npm and exposed as window.Litepicker (see resources/js/app.js). --}}
<script>
    (function () {
        const rangeInput = document.getElementById('valid_range');
        const fromInput = document.getElementById('valid_from');
        const untilInput = document.getElementById('valid_until');

        const formatDisplay = (date) => date.format('DD MMM YYYY');

        const picker = new Litepicker({
            element: rangeInput,
            singleMode: false,
            format: 'YYYY-MM-DD',
            startDate: fromInput.value || undefined,
            endDate: untilInput.value || undefined,
            setup: (picker) => {
                if (fromInput.value && untilInput.value) {
                    rangeInput.value = `${formatDisplay(picker.getStartDate())} - ${formatDisplay(picker.getEndDate())}`;
                }

                picker.on('selected', (start, end) => {
                    fromInput.value = start.format('YYYY-MM-DD');
                    untilInput.value = end.format('YYYY-MM-DD');
                    rangeInput.value = `${formatDisplay(start)} - ${formatDisplay(end)}`;
                });

                picker.on('clear:selection', () => {
                    fromInput.value = '';
                    untilInput.value = '';
                    rangeInput.value = '';
                });
            },
        });

        // Litepicker has no built-in clear button; a plain button is enough since this field
        // is optional (unlike code/type/value, which are required and always have a value).
        rangeInput.insertAdjacentHTML('afterend', '<button type="button" id="valid_range_clear" class="text-xs text-gray-400 hover:text-red-500 mt-1">Hapus rentang tanggal</button>');
        document.getElementById('valid_range_clear').addEventListener('click', () => {
            picker.clearSelection();
        });
    })();
</script>
