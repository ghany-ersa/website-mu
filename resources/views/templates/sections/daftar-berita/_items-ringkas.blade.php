{{--
    Card markup for daftar-berita/ringkas.blade.php's item list - extracted so
    OrganizationSiteController::loadMoreBerita() can render additional batches with the exact
    same markup the initial page load used, instead of duplicating it in JS. $items is the
    batch to render (either the initial page's or one "Muat Lebih Banyak" batch); $startIndex
    keeps the stagger transition-delay continuous across batches instead of restarting at 0ms
    for every "Muat Lebih Banyak" click.
--}}
@foreach ($items as $item)
    <article class="reveal group py-3.5 border-b border-gray-200" style="transition-delay: {{ ($startIndex + $loop->index) * 50 }}ms">
        <a href="{{ $item['url'] ?? '#' }}" class="flex items-center gap-4">
            <div class="w-24 h-16 shrink-0 overflow-hidden bg-gray-100 rounded-brand">
                @if (! empty($item['image']))
                    <img src="{{ $item['image'] }}" alt="{{ $item['title'] ?? '' }}" class="w-full h-full object-cover">
                @endif
            </div>
            <div class="min-w-0">
                <div class="flex items-center gap-2 mb-0.5">
                    <span class="text-[11px] text-secondary font-bold uppercase tracking-wide">{{ $item['category'] ?? 'Kategori' }}</span>
                    @if (! empty($item['date']))
                        <span class="text-[11px] text-gray-400">&middot; {{ $item['date'] }}</span>
                    @endif
                </div>
                <h3 class="text-sm font-semibold text-gray-800 leading-snug transition-colors group-hover:text-primary line-clamp-2">
                    {{ $item['title'] ?? 'Judul berita contoh '.$loop->iteration }}
                </h3>
            </div>
        </a>
    </article>
@endforeach
