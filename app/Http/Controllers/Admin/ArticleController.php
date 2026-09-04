<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PublishStatus;
use App\Http\Controllers\Concerns\SanitizesRichText;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreArticleRequest;
use App\Http\Requests\UpdateArticleRequest;
use App\Models\Article;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ArticleController extends Controller
{
    use SanitizesRichText;

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $search = trim((string) request('q'));

        $articles = Article::query()
            ->with('author')
            ->when($search !== '', fn ($query) => $query->where('title', 'like', "%{$search}%"))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.articles.index', [
            'articles' => $articles,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('admin.articles.create', [
            'article' => new Article,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreArticleRequest $request): RedirectResponse
    {
        $article = Article::create($this->prepare($request));

        return redirect()
            ->route('admin.articles.edit', $article)
            ->with('status', 'Artikel berhasil dibuat.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Article $article): View
    {
        return view('admin.articles.edit', [
            'article' => $article,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateArticleRequest $request, Article $article): RedirectResponse
    {
        $article->update($this->prepare($request, $article));

        return redirect()
            ->route('admin.articles.edit', $article)
            ->with('status', 'Artikel berhasil disimpan.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Article $article): RedirectResponse
    {
        $article->delete();

        return redirect()
            ->route('admin.articles.index')
            ->with('status', 'Artikel berhasil dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function prepare(StoreArticleRequest|UpdateArticleRequest $request, ?Article $article = null): array
    {
        $data = $request->safe()->except(['body']);
        $data['body'] = $this->sanitizeRichText($request->validated('body'));
        $data['author_id'] ??= Auth::id();
        $data['published_at'] = $data['status'] === PublishStatus::Published->value
            ? ($article?->published_at ?? now())
            : null;

        return $data;
    }
}
