{{--
    Card markup for daftar-berita/standar.blade.php's item grid - extracted so
    OrganizationSiteController::loadMoreBerita() can render additional batches with the exact
    same markup the initial page load used, instead of duplicating it in JS. $items is the
    batch to render (either the initial page's or one "Muat Lebih Banyak" batch); $startIndex
    keeps the stagger transition-delay continuous across batches instead of restarting at 0ms
    for every "Muat Lebih Banyak" click.
--}}
@foreach ($items as $item)
    <article class="reveal group bg-white rounded-brand overflow-hidden shadow-soft transition-all duration-300 hover:-translate-y-1 hover:shadow-float"
              style="transition-delay: {{ ($startIndex + $loop->index) * 80 }}ms">
        <a href="{{ $item['url'] ?? '#' }}" class="contents">
            <div class="aspect-[16/10] overflow-hidden bg-gray-100">
                @if (! empty($item['image']))
                    <img src="{{ $item['image'] }}" alt="{{ $item['title'] ?? '' }}"
                         class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                @else
                    <div class="w-full h-full flex items-center justify-center text-gray-400 text-sm">Gambar</div>
                @endif
            </div>
            <div class="p-4">
                <span class="text-[11px] text-secondary font-bold uppercase tracking-wide">{{ $item['category'] ?? 'Kategori' }}</span>
                <h3 class="text-sm font-semibold text-gray-800 leading-snug mt-1 transition-colors group-hover:text-primary">
                    {{ $item['title'] ?? 'Judul berita contoh '.$loop->iteration }}
                </h3>
                @if (! empty($item['date']))
                    <span class="text-xs text-gray-400 mt-2 block">{{ $item['date'] }}</span>
                @endif
            </div>
        </a>
    </article>
@endforeach
