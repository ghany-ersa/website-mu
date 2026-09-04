<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\View\View;

class ArticleController extends Controller
{
    /**
     * List published articles - the platform's own blog/news, not tenant content.
     */
    public function index(): View
    {
        $search = trim((string) request('q'));
        $category = trim((string) request('category'));

        $articles = Article::query()
            ->published()
            ->when($search !== '', fn ($query) => $query->where('title', 'like', "%{$search}%"))
            ->when($category !== '', fn ($query) => $query->where('category', $category))
            ->orderByDesc('published_at')
            ->paginate(9)
            ->withQueryString();

        $categories = Article::published()
            ->whereNotNull('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        return view('articles.index', [
            'articles' => $articles,
            'categories' => $categories,
        ]);
    }

    public function show(Article $article): View
    {
        abort_unless($article->status->value === 'published' && $article->published_at?->isPast(), 404);

        $related = Article::published()
            ->where('id', '!=', $article->id)
            ->when($article->category, fn ($query) => $query->where('category', $article->category))
            ->orderByDesc('published_at')
            ->take(3)
            ->get();

        return view('articles.show', [
            'article' => $article,
            'related' => $related,
        ]);
    }
}
