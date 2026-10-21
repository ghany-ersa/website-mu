<?php

namespace Database\Seeders;

use App\Enums\PublishStatus;
use App\Models\Article;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Base for the blog articles that form an SEO content cluster around one landing page in
 * config/landing-pages.php: each article targets one long-tail query, links to its siblings, and
 * links to the landing page with a keyword anchor.
 *
 * Run by hand (via SeoArticleSeeder), not from DatabaseSeeder, which mirrors the articles already
 * in production. firstOrCreate by slug, so re-running never overwrites an admin's later edits.
 * Seeded as Draft so the copy is reviewed (and given a cover image) before going public.
 *
 * Links in bodies are root-relative ("/website-masjid") rather than route() output: the body is
 * stored HTML, and an absolute URL would bake in whatever APP_URL the seeder ran under. The
 * opening paragraph doubles as the meta description (articles/show.blade.php takes the first
 * 160 characters of the body), so each article leads with its target keyword.
 */
abstract class SeoArticleClusterSeeder extends Seeder
{
    /** @var array<string, string> */
    public const SLUGS = [];

    abstract protected function category(): string;

    /**
     * @return array<int, array{slug: string, title: string, body: string}>
     */
    abstract protected function articles(): array;

    public function run(): void
    {
        $author = User::where('email', 'admin@website-mu.id')->first();

        foreach ($this->articles() as $article) {
            Article::firstOrCreate(
                ['slug' => $article['slug']],
                $article + [
                    'author_id' => $author?->id,
                    'category' => $this->category(),
                    'cover_image' => null,
                    'status' => PublishStatus::Draft,
                    'published_at' => null,
                ],
            );
        }
    }

    protected static function articlePath(string $key): string
    {
        return '/berita/'.static::SLUGS[$key];
    }
}
