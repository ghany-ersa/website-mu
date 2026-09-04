<article class="py-16" @if ($post->image) x-data="{ lightboxOpen: false, activeIndex: 0, photos: {{ Js::from([['image' => $post->image, 'caption' => null]]) }} }" @endif>
    <div class="max-w-3xl mx-auto px-6">
        <a href="{{ route('tenant.home', ['organization_slug' => $organization->slug]) }}" class="text-sm text-secondary font-semibold hover:underline">&larr; Kembali ke beranda</a>

        <div class="flex items-center gap-2 mt-6 mb-2">
            @if ($post->category)
                <span class="text-xs text-secondary font-semibold">{{ $post->category }}</span>
            @endif
            @if ($post->published_at)
                <span class="text-xs text-gray-400">&middot; {{ $post->published_at->translatedFormat('d M Y') }}</span>
            @endif
        </div>

        <h1 class="text-3xl font-extrabold text-primary mb-6">{{ $post->title }}</h1>

        @if ($post->image)
            <button type="button" @click="activeIndex = 0; lightboxOpen = true"
                    class="block w-full aspect-video rounded-2xl overflow-hidden bg-gray-100 mb-8">
                <img src="{{ $post->image }}" alt="{{ $post->title }}" class="w-full h-full object-cover cursor-zoom-in">
            </button>
        @endif

        <div class="prose max-w-none text-gray-700 leading-relaxed">
            {!! $post->body !!}
        </div>
    </div>

    @if ($post->image)
        <x-tenant.lightbox />
    @endif
</article>
