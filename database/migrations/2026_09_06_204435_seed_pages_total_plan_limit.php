<?php

use App\Models\Plan;
use App\Models\PlanLimit;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Adds the 'pages_total' limit to every existing plan (starter/premium -> 1 page,
     * eksklusif -> 10), gating the page builder's multi-page switcher/creation to the
     * Eksklusif tier. PlanSeeder is updated in step with this for fresh installs, but
     * plan_limits rows already live in every existing database need this migration too.
     */
    public function up(): void
    {
        $limits = [
            'starter' => 1,
            'premium' => 1,
            'eksklusif' => 10,
        ];

        foreach ($limits as $key => $maxCount) {
            Plan::where('key', $key)->first()?->limits()->updateOrCreate(
                ['key' => 'pages_total'],
                ['max_count' => $maxCount],
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        PlanLimit::where('key', 'pages_total')->delete();
    }
};
