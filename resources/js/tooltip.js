/**
 * Instant tooltips for [data-tip] elements.
 *
 * A CSS ::after tooltip can't be used here: every list that needs one (the builder's section
 * list, the "Tambah Section" picker) scrolls, and `overflow-y: auto` clips descendants on BOTH
 * axes - no z-index escapes that. So the bubble lives in a single element appended to <body>
 * and is positioned against the trigger's viewport rect instead.
 *
 * The native `title` attribute isn't an option either: browsers delay it ~1s with no way to
 * tune that, which reads as "no tooltip" when scanning a list.
 */
const OFFSET = 6;
const EDGE_PADDING = 8;

let bubble = null;
let activeTarget = null;

function ensureBubble() {
    if (bubble) {
        return bubble;
    }

    bubble = document.createElement('div');
    bubble.className = 'app-tooltip';
    bubble.setAttribute('role', 'tooltip');
    document.body.appendChild(bubble);

    return bubble;
}

function show(target) {
    const text = target.getAttribute('data-tip');

    if (!text) {
        return;
    }

    activeTarget = target;

    const el = ensureBubble();
    el.textContent = text;
    el.classList.add('is-visible');

    position(target, el);
}

function position(target, el) {
    const rect = target.getBoundingClientRect();

    // Measure after the text is set but before committing a position, so width/height
    // reflect this tooltip's own content.
    el.style.left = '0px';
    el.style.top = '0px';
    const { width, height } = el.getBoundingClientRect();

    let left = rect.left;
    let top = rect.bottom + OFFSET;

    // Keep it inside the viewport horizontally...
    const maxLeft = window.innerWidth - width - EDGE_PADDING;
    left = Math.max(EDGE_PADDING, Math.min(left, maxLeft));

    // ...and flip above the trigger when there isn't room below.
    if (top + height > window.innerHeight - EDGE_PADDING) {
        top = rect.top - height - OFFSET;
    }

    el.style.left = `${Math.round(left)}px`;
    el.style.top = `${Math.round(top)}px`;
}

function hide() {
    activeTarget = null;

    if (bubble) {
        bubble.classList.remove('is-visible');
    }
}

function targetFrom(event) {
    return event.target instanceof Element ? event.target.closest('[data-tip]') : null;
}

// Delegated so tooltips work on markup Alpine renders later (the picker's x-for list).
document.addEventListener('pointerover', (event) => {
    // Touch taps also fire pointerover; showing a bubble there would fight the tap itself.
    if (event.pointerType === 'touch') {
        return;
    }

    const target = targetFrom(event);

    if (target && target !== activeTarget) {
        show(target);
    }
});

document.addEventListener('pointerout', (event) => {
    const target = targetFrom(event);

    if (target && target === activeTarget && !target.contains(event.relatedTarget)) {
        hide();
    }
});

// A tooltip anchored to a viewport rect goes stale the moment anything moves.
document.addEventListener('scroll', hide, true);
window.addEventListener('resize', hide);
document.addEventListener('click', hide);
