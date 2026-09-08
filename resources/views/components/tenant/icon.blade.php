@props([
    'name',
    'filled' => false,
])

{{--
    A Material Symbols icon, the icon set used by the sections ported from the standalone
    comprof-SD-Madya project (its AppIcon.vue component). The glyph is selected by ligature -
    the icon's name is literally the element's text - so `name` is a Material Symbols key such
    as "school", "emoji_events", or "arrow_forward".

    `filled` picks the solid cut of the same variable font rather than a second family; use it
    for icons sitting on a colored chip, where the outlined version reads as too faint.

    The font only loads for documents whose template declares a font pairing (see
    organizations/pages/_brand-head.blade.php) - which is exactly the set of templates whose
    sections use this component. In any other template the ligature would render as its own
    literal name, so don't reach for this in the shared `standar` variants; those use inline
    SVG.

    Size and color come from the surrounding text, so set them with normal utilities:
        <x-tenant.icon name="stars" filled class="text-secondary text-xl" />
--}}
<span aria-hidden="true"
      {{ $attributes->merge(['class' => $filled ? 'material-symbols-filled' : 'material-symbols-outlined']) }}
>{{ $name }}</span>
