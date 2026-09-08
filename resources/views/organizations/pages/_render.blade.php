{{-- Renders an organization-owned page's sections using the same section partial
     convention as templates/preview.blade.php ($section['key'] -> templates.sections.{key}).
     Expects: $organization (Organization), $page (OrganizationPage, sections eager-loaded).
     Uses sectionsInDisplayOrder() rather than the raw sections() relation so header/footer
     always render first/last regardless of the `order` column - see OrganizationPage for why. --}}
@php
    $orderedSections = $page->sectionsInDisplayOrder();
    $sectionVariantRows = \App\Services\SectionVariantResolver::variantsForKeys($orderedSections->pluck('key'));
@endphp
@foreach ($orderedSections as $section)
    @if ($section->is_visible && empty(config("page-builder.sections.{$section->key}.hidden")))
        <div id="{{ \App\Services\SectionAnchor::id($section->id) }}" class="scroll-mt-24">
            @includeFirst([
                \App\Services\SectionVariantResolver::resolveFrom($sectionVariantRows, $section->key, $section->variant),
                'templates.sections._missing',
            ], ['section' => $section, 'organization' => $organization, 'page' => $page])
        </div>
    @endif
@endforeach
