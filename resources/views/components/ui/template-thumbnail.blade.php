@props([
    'template',
    'imgClass' => 'w-full h-full object-cover',
    'iconClass' => 'w-8 h-8',
])

{{--
    Shared thumbnail rendering for a Template card, used everywhere one appears (organization-
    creation picker, template-switch picker, the selected-template summary, the admin form
    preview). Two states:

     - Normal template with no thumbnail_path yet: falls back to a generic stock photo (matches
       templates.index/welcome.blade.php's existing $defaultImage) - it hasn't been designed by
       an admin who forgot to upload one, not that it has no design.
     - The "Halaman Kosong" template (BlankTemplateSeeder): a stock photo would be actively
       misleading here, implying a design that doesn't exist. Renders a plain dashed "+" tile
       instead - the same visual language a blank/empty state uses elsewhere in this app - so it
       reads as "intentionally empty", not "thumbnail missing".
--}}
@if ($template->slug === 'halaman-kosong' && ! $template->thumbnailUrl())
    <div {{ $attributes->merge(['class' => 'w-full h-full flex items-center justify-center bg-gray-50 border-2 border-dashed border-gray-200']) }}>
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
             class="{{ $iconClass }} text-gray-300">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
        </svg>
    </div>
@else
    <img src="{{ $template->thumbnailUrl() ?? 'https://images.unsplash.com/photo-1497215728101-856f4ea42174?auto=format&fit=crop&w=800&q=80' }}"
         alt="{{ $template->name }}" {{ $attributes->merge(['class' => $imgClass]) }}>
@endif
