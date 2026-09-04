{{--
    Public tenant site - served at {slug}.{tenant domain} (see routes/web.php's guarded
    Route::domain() group and OrganizationSiteController::show()). Shares its <html> scaffold
    with the builder canvas (organizations/builder/canvas.blade.php) via _document.blade.php so
    the two can't diverge - this file stays free to grow public-only concerns later (e.g. JSON-LD)
    without leaking into the builder iframe.
--}}
@include('organizations.pages._document', [
    'organization' => $organization,
    'page' => $page,
    'body' => view('organizations.pages._render', [
        'organization' => $organization,
        'page' => $page,
    ])->render(),
])
