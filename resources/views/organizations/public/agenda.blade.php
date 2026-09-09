{{--
    Public detail page for a single published agenda item - served at
    {slug}.{tenant domain}/agenda/{agenda} (see routes/web.php, OrganizationSiteController::agenda()).
    Shares the same <html> scaffold as the tenant homepage via _document.blade.php, but overrides
    its SEO tags (title/description/image) to describe this agenda item instead of the
    organization, and appends Event structured data so the acara can surface in Google's event
    results rather than only as a plain link.
--}}
@php
    // Reused three times over (meta description, JSON-LD, and as the og: fallback), so it is
    // built once here. Falls back to the facts of the event when the organization left the
    // description empty, which is common for a recurring kajian.
    $agendaDescription = $agenda->description
        ? \Illuminate\Support\Str::limit(strip_tags($agenda->description), 160)
        : $agenda->title.' pada '.$agenda->starts_at->translatedFormat('d M Y').(($agenda->location) ? ' di '.$agenda->location : '');

    $agendaUrl = route('tenant.agendas.show', [
        'organization_slug' => $organization->slug,
        'agenda' => $agenda->id,
    ]);
@endphp
@include('organizations.pages._document', [
    'organization' => $organization,
    'page' => (object) ['sections' => collect()],
    'metaTitle' => $agenda->title.' - '.$organization->name,
    'metaDescription' => $agendaDescription,
    // The poster doubles as the share card image, so a kajian flyer pasted into WhatsApp or
    // Facebook previews as the flyer itself. Null falls back to _document's own hero image.
    'metaImage' => $agenda->poster,
    'body' => view('organizations.public._agenda-body', [
        'organization' => $organization,
        'agenda' => $agenda,
    ])->render()
        .view('organizations.public._agenda-jsonld', [
            'organization' => $organization,
            'agenda' => $agenda,
            'description' => $agendaDescription,
            'url' => $agendaUrl,
        ])->render(),
])
