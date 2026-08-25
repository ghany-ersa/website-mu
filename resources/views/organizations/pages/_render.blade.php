{{-- Renders an organization-owned page's sections using the same section partial
     convention as templates/preview.blade.php ($section['key'] -> templates.sections.{key}).
     Expects: $organization (Organization), $page (OrganizationPage, sections eager-loaded).
     Uses sectionsWithFooterLast() rather than the raw sections() relation so the footer
     always renders last regardless of its `order` column — see OrganizationPage for why. --}}
@foreach ($page->sectionsWithFooterLast() as $section)
    @if ($section->is_visible)
        <div id="canvas-section-{{ $section->id }}">
            @includeFirst(['templates.sections.'.$section->key, 'templates.sections._missing'], ['section' => $section, 'organization' => $organization, 'page' => $page])
        </div>
    @endif
@endforeach
