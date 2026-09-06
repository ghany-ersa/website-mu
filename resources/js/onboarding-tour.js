import { driver } from 'driver.js';
import 'driver.js/dist/driver.css';
import '../css/onboarding-tour.css';

const TOUR_STEPS = {
    dashboard: [
        {
            popover: {
                title: 'Selamat datang di website-mu',
                description: 'Ini dashboard organisasi Anda. Yuk lihat sekilas apa saja yang bisa Anda lakukan di sini.',
            },
        },
        {
            element: '#onboarding-checklist',
            popover: {
                title: 'Langkah Awal',
                description: '4 langkah ini membantu situs Anda siap dipublikasikan: atur brand, isi kontak, susun halaman, lalu publish.',
                side: 'bottom',
            },
        },
        {
            element: '#btn-open-builder',
            popover: {
                title: 'Buka Builder',
                description: 'Di sinilah Anda menyusun tampilan situs dengan drag-and-drop, mengedit section demi section.',
                side: 'bottom',
            },
        },
        {
            element: '#btn-publish-status',
            popover: {
                title: 'Publish Situs',
                description: 'Setelah puas dengan hasilnya, tekan tombol ini untuk menerbitkan situs Anda ke publik.',
                side: 'bottom',
            },
        },
    ],
    builder: [
        {
            popover: {
                title: 'Editor Drag-and-Drop',
                description: 'Ini adalah builder halaman. Mari kenali bagian-bagian utamanya.',
            },
        },
        {
            element: '#section-sidebar',
            popover: {
                title: 'Daftar Section',
                description: 'Semua section halaman Anda ada di sini. Seret untuk mengubah urutan tampil di situs.',
                side: 'right',
            },
        },
        {
            element: '#btn-add-section',
            popover: {
                title: 'Tambah Section',
                description: 'Tekan tombol ini untuk menambahkan komponen baru ke halaman, seperti galeri atau agenda.',
                side: 'bottom',
            },
        },
        {
            element: '#canvas-frame',
            popover: {
                title: 'Pratinjau Halaman',
                description: 'Ini tampilan situs Anda yang sebenarnya, sesuai brand dan konten yang sudah diatur.',
                side: 'left',
            },
        },
        {
            element: '#properties-panel',
            popover: {
                title: 'Panel Pengaturan',
                description: 'Pilih sebuah section untuk mengedit isinya di sini - teks, gambar, dan pengaturan lainnya.',
                side: 'left',
            },
        },
        {
            element: '#page-switcher',
            popover: {
                title: 'Ganti Halaman',
                description: 'Jika situs Anda punya lebih dari satu halaman, gunakan menu ini untuk berpindah di antaranya.',
                side: 'bottom',
            },
        },
    ],
};

// Mobile: sidebar/canvas/properties share the same screen space and swap via
// activePanel (see edit.blade.php's root x-data), so a step's target can be
// hidden when the tour tries to highlight it. Force the right panel visible
// just before each step renders.
const BUILDER_STEP_PANEL = {
    '#section-sidebar': 'sections',
    '#btn-add-section': 'sections',
    '#canvas-frame': 'canvas',
};

function activateBuilderPanelForStep(element) {
    if (!element || typeof element.getAttribute !== 'function') return;
    const panel = BUILDER_STEP_PANEL[`#${element.id}`];
    if (!panel) return;

    const root = document.querySelector('body[x-data]');
    if (root && window.Alpine) {
        window.Alpine.$data(root).activePanel = panel;
    }
}

function markTourSeen(tour) {
    fetch(window.onboardingTourUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            'X-CSRF-TOKEN': window.csrfToken,
        },
        body: JSON.stringify({ tour }),
    }).catch(() => {});
}

export function startOnboardingTour(tour) {
    const allSteps = TOUR_STEPS[tour];
    if (!allSteps) return;

    // Some targets are conditional (e.g. the "Langkah Awal" checklist disappears once
    // every step is done, and the publish button can be absent while plan rules are
    // violated) - skip steps whose element isn't actually on the page right now.
    const steps = allSteps.filter((step) => !step.element || document.querySelector(step.element));
    if (!steps.length) return;

    const driverObj = driver({
        showProgress: true,
        // driver.js ties Escape, clicking the backdrop, AND the close (x) button to
        // this single allowClose flag - false disables all three, including
        // hiding the button (it's left in the DOM as display:none, not removed).
        // Only the backdrop/Escape should be inert (accidental dismissal); the
        // close button must stay the one deliberate way to exit. So: turn
        // allowClose off, then un-hide + rewire the button ourselves on every
        // render (driver.js recreates the popover element each step).
        allowClose: false,
        // driver.js's own delegated click listener on the popover checks for this
        // config and, when present, calls it directly instead of emitting the
        // closeClick event that allowClose:false swallows - so this (not a
        // listener we attach ourselves) is what makes the button work again.
        onCloseClick: () => {
            markTourSeen(tour);
            driverObj.destroy();
        },
        onPopoverRender: (popover) => {
            const btn = popover.closeButton;
            btn.style.display = 'flex';
            btn.disabled = false;
            btn.classList.remove('driver-popover-btn-disabled');
        },
        // A step with no `element` (the tour's welcome step) highlights driver.js's
        // own zero-size dummy node centered on screen. Its cutout in the overlay is
        // still drawn stagePadding px larger than that 0x0 box, so a default padding
        // leaves a small stray patch of page background visible mid-screen. 0 removes
        // that; steps with a real element look unaffected since those already have
        // their own padding/border-radius as cards.
        stagePadding: 0,
        nextBtnText: 'Lanjut',
        prevBtnText: 'Kembali',
        doneBtnText: 'Selesai',
        progressText: '{{current}} / {{total}}',
        onHighlightStarted: (element) => activateBuilderPanelForStep(element),
        onDestroyStarted: () => {
            markTourSeen(tour);
            driverObj.destroy();
        },
        steps,
    });

    driverObj.drive();
}

export function autoStartOnboardingTour(tour, alreadySeen) {
    if (alreadySeen) return;
    // Let Alpine finish its init() (e.g. selectSection from a ?section= redirect)
    // before spotlighting, so the tour doesn't fight with initial panel state.
    requestAnimationFrame(() => startOnboardingTour(tour));
}

window.startOnboardingTour = startOnboardingTour;
window.autoStartOnboardingTour = autoStartOnboardingTour;
