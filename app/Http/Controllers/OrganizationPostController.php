<?php

namespace App\Http\Controllers;

use App\Enums\PublishStatus;
use App\Http\Controllers\Concerns\BuilderAware;
use App\Http\Controllers\Concerns\SanitizesRichText;
use App\Models\Organization;
use App\Models\Post;
use App\Services\PlanLimitService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class OrganizationPostController extends Controller
{
    use BuilderAware;
    use SanitizesRichText;

    public function __construct(private readonly PlanLimitService $planLimitService) {}

    public function index(Request $request, Organization $organization): View
    {
        abort_unless($organization->hasSection('daftar-berita'), 404);
        $this->authorize('viewAny', [Post::class, $organization]);

        return view('organizations.posts.index', [
            'organization' => $organization,
            'posts' => $organization->posts()->get(),
            ...$this->builderViewData($request),
        ]);
    }

    public function create(Request $request, Organization $organization): View|RedirectResponse
    {
        abort_unless($organization->hasSection('daftar-berita'), 404);
        $this->authorize('create', [Post::class, $organization]);

        if (! $this->planLimitService->canCreate($organization, 'posts')) {
            return redirect()
                ->route('organizations.posts.index', $organization)
                ->with('warning', 'Batas jumlah berita paket Anda sudah tercapai. Upgrade paket untuk menambah lagi.');
        }

        return view('organizations.posts.form', [
            'organization' => $organization,
            'post' => new Post,
            ...$this->builderViewData($request),
        ]);
    }

    public function store(Request $request, Organization $organization): RedirectResponse
    {
        abort_unless($organization->hasSection('daftar-berita'), 404);
        $this->authorize('create', [Post::class, $organization]);

        if (! $this->planLimitService->canCreate($organization, 'posts')) {
            return redirect()
                ->route('organizations.posts.index', $organization)
                ->with('warning', 'Batas jumlah berita paket Anda sudah tercapai. Upgrade paket untuk menambah lagi.');
        }

        $validated = $this->validated($request);

        $organization->posts()->create([
            ...$validated,
            'author_id' => Auth::id(),
            'slug' => $this->uniqueSlug($organization, $validated['title']),
            'published_at' => $validated['status'] === PublishStatus::Published->value ? now() : null,
        ]);

        return redirect()
            ->route('organizations.posts.index', $this->builderIndexParams($request, ['organization' => $organization]))
            ->with('status', 'Berita berhasil disimpan.');
    }

    public function edit(Request $request, Organization $organization, Post $post): View
    {
        $this->authorize('update', $post);
        $this->ensureBelongsToOrganization($organization, $post);

        return view('organizations.posts.form', [
            'organization' => $organization,
            'post' => $post,
            ...$this->builderViewData($request),
        ]);
    }

    public function update(Request $request, Organization $organization, Post $post): RedirectResponse
    {
        $this->authorize('update', $post);
        $this->ensureBelongsToOrganization($organization, $post);

        $validated = $this->validated($request);

        $post->update([
            ...$validated,
            'published_at' => $validated['status'] === PublishStatus::Published->value
                ? ($post->published_at ?? now())
                : null,
        ]);

        return redirect()
            ->route('organizations.posts.index', $this->builderIndexParams($request, ['organization' => $organization]))
            ->with('status', 'Berita berhasil diperbarui.');
    }

    public function destroy(Request $request, Organization $organization, Post $post): RedirectResponse
    {
        $this->authorize('delete', $post);
        $this->ensureBelongsToOrganization($organization, $post);

        $post->delete();

        return redirect()
            ->route('organizations.posts.index', $this->builderIndexParams($request, ['organization' => $organization]))
            ->with('status', 'Berita berhasil dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:100'],
            'image' => ['nullable', 'string'],
            'body' => ['nullable', 'string'],
            'status' => ['required', 'in:draft,published'],
        ]);

        if (array_key_exists('body', $data)) {
            $data['body'] = $this->sanitizeRichText($data['body']);
        }

        return $data;
    }

    private function uniqueSlug(Organization $organization, string $title): string
    {
        $base = str($title)->slug();
        $slug = (string) $base;
        $suffix = 1;

        while ($organization->posts()->where('slug', $slug)->exists()) {
            $slug = "{$base}-".++$suffix;
        }

        return $slug;
    }

    private function ensureBelongsToOrganization(Organization $organization, Post $post): void
    {
        abort_unless($post->organization_id === $organization->id, 404);
    }
}
