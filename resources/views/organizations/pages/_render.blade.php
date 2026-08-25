{{-- Renders an organization-owned page's sections using the same section partial
     convention as templates/preview.blade.php ($section['key'] -> templates.sections.{key}).
     Expects: $organization (Organization), $page (OrganizationPage, sections eager-loaded).
     Uses sectionsInDisplayOrder() rather than the raw sections() relation so header/footer
     always render first/last regardless of the `order` column — see OrganizationPage for why. --}}
@foreach ($page->sectionsInDisplayOrder() as $section)
    @if ($section->is_visible)
        <div id="canvas-section-{{ $section->id }}">
            @includeFirst(['templates.sections.'.$section->key, 'templates.sections._missing'], ['section' => $section, 'organization' => $organization, 'page' => $page])
        </div>
    @endif
@endforeach
