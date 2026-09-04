<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seeds sample blog articles (App\Models\Article is the platform's own blog, unrelated to
 * tenant organizations) so the admin article list and public blog have something to show.
 * Authored by the same admin@website-mu.id user OrganizationSeeder uses, mixing published and
 * draft so both admin CRUD and the public-facing views have representative data.
 */
class ArticleSeeder extends Seeder
{
    public function run(): void
    {
        $author = User::where('email', 'admin@website-mu.id')->first();

        Article::factory()
            ->count(8)
            ->published()
            ->create(['author_id' => $author?->id]);

        Article::factory()
            ->count(3)
            ->create(['author_id' => $author?->id]);
    }
}
