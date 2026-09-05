{{--
    Public detail page for a single donation program - served at
    {slug}.{tenant domain}/donasi/{program_slug} (see routes/web.php,
    OrganizationSiteController::donationProgram()). Shares the same <html> scaffold as the
    tenant homepage via _document.blade.php, but overrides its SEO tags to describe this
    program instead of the organization.
--}}
@include('organizations.pages._document', [
    'organization' => $organization,
    'page' => (object) ['sections' => collect()],
    'metaTitle' => $program->name.' - '.$organization->name,
    'metaDescription' => $program->description
        ? \Illuminate\Support\Str::limit(strip_tags($program->description), 160)
        : 'Salurkan donasi untuk program '.$program->name.' di '.$organization->name.'.',
    'metaImage' => $program->cover_photo,
    'body' => view('organizations.public._donation-program-body', [
        'organization' => $organization,
        'program' => $program,
    ])->render(),
])
