{{--
    Public detail page for a single published announcement — served at
    {slug}.{tenant domain}/pengumuman/{announcement} (see routes/web.php, OrganizationSiteController::announcement()).
    Shares the same <html> scaffold as the tenant homepage via _document.blade.php, but overrides
    its SEO tags (title/description) to describe this announcement instead of the organization.
--}}
@include('organizations.pages._document', [
    'organization' => $organization,
    'page' => (object) ['sections' => collect()],
    'metaTitle' => $announcement->title.' — '.$organization->name,
    'metaDescription' => \Illuminate\Support\Str::limit(strip_tags($announcement->body), 160),
    'body' => view('organizations.public._announcement-body', [
        'organization' => $organization,
        'announcement' => $announcement,
    ])->render(),
])
