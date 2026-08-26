<div x-data
    x-show="$store.confirmModal.open" x-cloak
    class="fixed inset-0 z-[100] bg-gray-900/50 backdrop-blur-sm flex items-center justify-center p-4"
    @keydown.escape.window="$store.confirmModal.settle(false)">
    <div @click.outside="$store.confirmModal.settle(false)"
        class="bg-white rounded-2xl w-full max-w-sm shadow-2xl p-6 text-center">
        <div class="w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-4"
            :class="$store.confirmModal.danger ? 'bg-red-100 text-red-500' : 'bg-amber-100 text-amber-500'">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
            </svg>
        </div>
        <h3 class="font-bold text-gray-800 mb-1" x-text="$store.confirmModal.title"></h3>
        <p class="text-sm text-gray-500 mb-6" x-text="$store.confirmModal.message"></p>
        <div class="flex items-center justify-center gap-3">
            <button type="button" @click="$store.confirmModal.settle(false)"
                class="px-4 py-2.5 rounded-full text-gray-600 text-sm font-semibold hover:bg-gray-100 transition-colors"
                x-text="$store.confirmModal.cancelLabel">
            </button>
            <button type="button" @click="$store.confirmModal.settle(true)"
                class="px-4 py-2.5 rounded-full text-white text-sm font-semibold transition-colors"
                :class="$store.confirmModal.danger ? 'bg-red-500 hover:bg-red-600' : 'bg-primary hover:bg-secondary'"
                x-text="$store.confirmModal.confirmLabel">
            </button>
        </div>
    </div>
</div>
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.store('confirmModal', {
            open: false,
            title: 'Konfirmasi',
            message: '',
            confirmLabel: 'Ya, Lanjutkan',
            cancelLabel: 'Batal',
            danger: true,
            _resolve: null,
            ask(message, options = {}) {
                this.title = options.title || 'Konfirmasi';
                this.message = message;
                this.confirmLabel = options.confirmLabel || 'Ya, Lanjutkan';
                this.cancelLabel = options.cancelLabel || 'Batal';
                this.danger = options.danger ?? true;
                this.open = true;
                return new Promise((resolve) => { this._resolve = resolve; });
            },
            settle(value) {
                this.open = false;
                if (this._resolve) {
                    this._resolve(value);
                    this._resolve = null;
                }
            },
        });
    });

    function confirmAction(message, options = {}) {
        return Alpine.store('confirmModal').ask(message, options);
    }
</script>
