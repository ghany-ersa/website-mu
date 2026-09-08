<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTemplateRequest;
use App\Http\Requests\UpdateTemplateRequest;
use App\Models\OrganizationType;
use App\Models\Template;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\Exceptions\DecoderException;
use Intervention\Image\ImageManager;

class TemplateController extends Controller
{
    /**
     * Longest side, in pixels, a thumbnail is downscaled to. Smaller than MediaController's
     * 1920 because these only ever render as cards in the template picker grid.
     */
    private const THUMBNAIL_MAX_DIMENSION = 800;

    /**
     * WebP re-encode quality (0-100), matching MediaController.
     */
    private const WEBP_QUALITY = 80;

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $search = trim((string) request('q'));
        $typeId = request('organization_type_id');

        $templates = Template::query()
            ->with('organizationType')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%");
                });
            })
            ->when($typeId, fn ($query) => $query->where('organization_type_id', $typeId))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.templates.index', [
            'templates' => $templates,
            'organizationTypes' => OrganizationType::orderBy('name')->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('admin.templates.create', [
            'organizationTypes' => OrganizationType::orderBy('name')->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTemplateRequest $request): RedirectResponse
    {
        $template = Template::create($this->prepare($request));

        return redirect()
            ->route('admin.templates.edit', $template)
            ->with('status', 'Template berhasil dibuat.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Template $template): View
    {
        return view('admin.templates.edit', [
            'template' => $template,
            'organizationTypes' => OrganizationType::orderBy('name')->get(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTemplateRequest $request, Template $template): RedirectResponse
    {
        $template->update($this->prepare($request, $template));

        return redirect()
            ->route('admin.templates.edit', $template)
            ->with('status', 'Template berhasil disimpan.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Template $template): RedirectResponse
    {
        $this->deleteThumbnail($template);
        $template->delete();

        return redirect()
            ->route('admin.templates.index')
            ->with('status', 'Template berhasil dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function prepare(StoreTemplateRequest|UpdateTemplateRequest $request, ?Template $template = null): array
    {
        $attributes = [
            ...$request->safe()->except(['structure', 'is_active', 'is_exclusive', 'thumbnail', 'remove_thumbnail']),
            'structure' => json_decode((string) $request->validated('structure'), true),
            'is_active' => $request->boolean('is_active'),
            'is_exclusive' => $request->boolean('is_exclusive'),
        ];

        // A new upload replaces whatever the template had; ticking "remove" clears it. Absent
        // both, thumbnail_path is left out of the array entirely so an edit that doesn't touch
        // the field keeps the existing image instead of nulling it.
        if ($request->hasFile('thumbnail')) {
            $attributes['thumbnail_path'] = $this->storeThumbnail($request->file('thumbnail'));
            $this->deleteThumbnail($template);
        } elseif ($request->boolean('remove_thumbnail')) {
            $attributes['thumbnail_path'] = null;
            $this->deleteThumbnail($template);
        }

        return $attributes;
    }

    /**
     * Re-encode an uploaded thumbnail to WebP and store it on the media disk, returning its
     * path. Mirrors MediaController's upload handling, but templates are platform-level (not
     * owned by an organization) so the file lands outside the per-organization tree and gets
     * no Media row.
     */
    private function storeThumbnail(UploadedFile $file): string
    {
        try {
            $image = (new ImageManager(new Driver))->decodePath($file->getRealPath());
        } catch (DecoderException) {
            throw ValidationException::withMessages([
                'thumbnail' => 'Gambar tidak bisa diproses. Coba simpan ulang sebagai JPG atau PNG lalu unggah lagi.',
            ]);
        }

        $image->scaleDown(width: self::THUMBNAIL_MAX_DIMENSION, height: self::THUMBNAIL_MAX_DIMENSION);
        $encoded = $image->encode(new WebpEncoder(quality: self::WEBP_QUALITY));

        $path = 'templates/thumbnails/'.Str::uuid().'.webp';
        Storage::disk(config('media.disk'))->put($path, (string) $encoded, 'public');

        return $path;
    }

    /**
     * Delete a template's stored thumbnail file. Absolute URLs are left alone - those predate
     * uploading and point at files this app doesn't own.
     */
    private function deleteThumbnail(?Template $template): void
    {
        $path = $template?->thumbnail_path;

        if (blank($path) || Str::startsWith($path, ['http://', 'https://', '/'])) {
            return;
        }

        Storage::disk(config('media.disk'))->delete($path);
    }
}
