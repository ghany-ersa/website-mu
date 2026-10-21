{{--
    Nudge for accounts created before registration required a WhatsApp number (see the
    add_whatsapp_to_users_table migration). Non-blocking on purpose: a hard gate would lock
    existing users out of their own dashboard mid-task. Hidden on the account page itself, where
    the form to fill it in is already on screen.
--}}
@if (auth()->check() && blank(auth()->user()->whatsapp) && ! request()->routeIs('account.edit'))
    <div class="mb-6 rounded-lg bg-amber-50 border border-amber-200 text-amber-800 px-4 py-3 text-sm flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <p>Lengkapi nomor WhatsApp Anda agar tim kami bisa menghubungi Anda, misalnya saat Anda lupa kata sandi.</p>
        <a href="{{ route('account.edit') }}"
           class="shrink-0 self-start sm:self-auto px-4 py-2 rounded-full bg-primary text-white text-xs font-semibold hover:bg-primary/90 transition-colors">
            Lengkapi Sekarang
        </a>
    </div>
@endif
