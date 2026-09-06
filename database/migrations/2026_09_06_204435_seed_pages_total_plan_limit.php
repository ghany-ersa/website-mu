<?php

use App\Models\Plan;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Adds the 'pages_total' limit to every existing plan (starter/organization -> 1 page,
     * professional -> 10), gating the page builder's multi-page switcher/creation to the
     * Professional tier. PlanSeeder is updated in step with this for fresh installs, but
     * plan_limits rows already live in every existing database need this migration too.
     */
    public function up(): void
    {
        $limits = [
            'starter' => 1,
            'organization' => 1,
            'professional' => 10,
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
        \App\Models\PlanLimit::where('key', 'pages_total')->delete();
    }
};
