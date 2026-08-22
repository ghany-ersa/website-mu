{{--
    Public detail page for a single published post — served at
    {slug}.{tenant domain}/berita/{post_slug} (see routes/web.php, OrganizationSiteController::post()).
    Shares the same <html> scaffold as the tenant homepage via _document.blade.php, but overrides
    its SEO tags (title/description/image) to describe this post instead of the organization.
--}}
@include('organizations.pages._document', [
    'organization' => $organization,
    'page' => (object) ['sections' => collect()],
    'metaTitle' => $post->title.' — '.$organization->name,
    'metaDescription' => $post->excerpt ?: \Illuminate\Support\Str::limit(strip_tags($post->body), 160),
    'metaImage' => $post->image,
    'body' => view('organizations.public._post-body', [
        'organization' => $organization,
        'post' => $post,
    ])->render()
        .view('organizations.public._article-jsonld', [
            'organization' => $organization,
            'title' => $post->title,
            'description' => $post->excerpt ?: \Illuminate\Support\Str::limit(strip_tags($post->body), 160),
            'image' => $post->image,
            'publishedAt' => $post->published_at,
        ])->render(),
])
