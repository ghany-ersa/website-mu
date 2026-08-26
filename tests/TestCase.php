<?php

namespace Tests;

use App\Models\Plan;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Every test that uses RefreshDatabase gets a freshly migrated database with no plans in
     * it — but Organization creation now always sets plan_id (see
     * OrganizationController::store()'s hardcoded plan_id 1), and that column is a foreign
     * key, so any test that creates an organization would otherwise fail with a constraint
     * violation.
     *
     * Note: this can't be RefreshDatabase's afterRefreshingDatabase() hook — that trait
     * method is defined (as a no-op) directly on the trait each test class `use`s, so it
     * always wins over an override placed here on the parent TestCase; PHP resolves a
     * class's own trait methods before inherited ones, regardless of what looks like an
     * "override" in this file. Seeding from setUp() instead works because RefreshDatabase's
     * migration runs as part of parent::setUp() (via setUpTraits()), which every test's
     * setUp() invokes before reaching its own body.
     */
    protected function setUp(): void
    {
        parent::setUp();

        if (in_array(\Illuminate\Foundation\Testing\RefreshDatabase::class, class_uses_recursive(static::class), true) && ! Plan::exists()) {
            $this->seed(PlanSeeder::class);
        }
    }
}
