{{-- NewsArticle structured data for Google/rich results - appended after the post body markup. --}}
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'NewsArticle',
    'headline' => $title,
    'description' => $description,
    'image' => $image ? [$image] : [],
    'datePublished' => $publishedAt?->toIso8601String(),
    'publisher' => [
        '@type' => 'Organization',
        'name' => $organization->name,
        'logo' => $organization->logo ? [
            '@type' => 'ImageObject',
            'url' => $organization->logo,
        ] : null,
    ],
], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) !!}
</script>
