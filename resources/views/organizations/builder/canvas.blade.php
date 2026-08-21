{{--
    Isolated document loaded into the builder's canvas <iframe> (organizations/builder/edit.blade.php),
    rendered in the organization's own brand colors. Kept as its own document (not @include'd inline
    in the builder page) specifically so it can carry a different Tailwind theme than the builder
    chrome around it, which stays platform-branded. Shares its <html> scaffold with the public tenant
    site (organizations/public/show.blade.php) via _document.blade.php so the two can't diverge —
    this file stays free to grow builder-only concerns (e.g. edit-mode overlays) without leaking
    onto the public site.
--}}
@include('organizations.pages._document', [
    'organization' => $organization,
    'page' => $page,
    'body' => view('organizations.pages._render', [
        'organization' => $organization,
        'page' => $page,
    ])->render(),
])
