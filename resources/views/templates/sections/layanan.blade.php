@include(\App\Services\SectionVariantResolver::resolve('program-unggulan', $section['variant'] ?? null, template: $template ?? null, organization: $organization ?? null), [
    'section' => $section,
    'organization' => $organization ?? null,
    'template' => $template ?? null,
    'programType' => 'layanan',
])
