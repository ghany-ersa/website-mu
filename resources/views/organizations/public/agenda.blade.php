{{--
    Public detail page for a single published agenda item - served at
    {slug}.{tenant domain}/agenda/{agenda} (see routes/web.php, OrganizationSiteController::agenda()).
    Shares the same <html> scaffold as the tenant homepage via _document.blade.php, but overrides
    its SEO tags (title/description) to describe this agenda item instead of the organization.
--}}
@include('organizations.pages._document', [
    'organization' => $organization,
    'page' => (object) ['sections' => collect()],
    'metaTitle' => $agenda->title.' - '.$organization->name,
    'metaDescription' => $agenda->description
        ? \Illuminate\Support\Str::limit(strip_tags($agenda->description), 160)
        : $agenda->title.' pada '.$agenda->starts_at->translatedFormat('d M Y').(($agenda->location) ? ' di '.$agenda->location : ''),
    'body' => view('organizations.public._agenda-body', [
        'organization' => $organization,
        'agenda' => $agenda,
    ])->render(),
])
