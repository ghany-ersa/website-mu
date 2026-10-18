{{--
    Floating WhatsApp support bubble, bottom-right. Included in every top-level layout
    except the page builder (organizations/builder/edit.blade.php renders its own
    standalone <html> and never includes this) - the builder has its own autosave on
    every widget edit, and a fixed-position element over the canvas risked eating
    clicks meant for the drag/drop and sidebar controls.
--}}
<a
    href="https://wa.me/6285183220977?text={{ urlencode('Assalamualaikum, saya butuh bantuan terkait website-mu.id.') }}"
    target="_blank"
    rel="noopener"
    class="fixed bottom-5 right-5 z-50 flex items-center gap-2 bg-[#25D366] hover:bg-[#1ebc59] text-white pl-3 pr-4 py-3 rounded-full shadow-float transition-all"
    aria-label="Hubungi kami via WhatsApp"
>
    <svg class="w-6 h-6 shrink-0" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
        <path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91c0 1.85.5 3.58 1.42 5.08L2 22l5.19-1.52a9.86 9.86 0 0 0 4.85 1.26h.01c5.46 0 9.9-4.45 9.9-9.91C21.95 6.37 17.5 2 12.04 2zm0 17.93h-.01a8.2 8.2 0 0 1-4.17-1.14l-.3-.18-3.09.9.9-3-.2-.31a8.2 8.2 0 0 1-1.26-4.3c0-4.54 3.7-8.24 8.24-8.24a8.2 8.2 0 0 1 5.83 2.41 8.17 8.17 0 0 1 2.4 5.83c0 4.54-3.7 8.03-8.24 8.03zm4.52-6.16c-.25-.12-1.47-.72-1.7-.81-.23-.08-.4-.12-.56.12-.17.25-.65.81-.8.98-.14.17-.3.18-.55.06-1.49-.74-2.47-1.33-3.46-3.01-.26-.45.26-.42.75-1.4.08-.17.04-.31-.04-.44-.08-.12-.56-1.36-.77-1.86-.2-.49-.4-.42-.56-.42-.14 0-.3-.01-.46-.01-.17 0-.44.06-.67.31-.23.25-.88.86-.88 2.1 0 1.24.9 2.44 1.03 2.61.12.17 1.71 2.62 4.16 3.57 2.05.8 2.48.65 2.92.6.44-.04 1.47-.6 1.68-1.18.2-.57.2-1.06.14-1.18-.06-.11-.23-.17-.49-.3z"/>
    </svg>
    <span class="text-sm font-bold hidden sm:inline">Butuh Bantuan?</span>
</a>
