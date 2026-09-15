<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seeds the platform's own blog articles (App\Models\Article, unrelated to tenant
 * organizations) so the admin article list and public blog have something to show. The
 * factory's sample pool mirrors the actual articles published in production, so this seeds
 * exactly those 4 - all published, since none of the real articles are drafts. Authored by the
 * same admin@website-mu.id user OrganizationSeeder uses.
 */
class ArticleSeeder extends Seeder
{
    public function run(): void
    {
        $author = User::where('email', 'admin@website-mu.id')->first();

        Article::factory()
            ->count(4)
            ->published()
            ->create(['author_id' => $author?->id]);
    }
}
