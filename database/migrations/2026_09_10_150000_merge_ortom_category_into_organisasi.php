<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Folds the 'ortom' organization-type category into 'persyarikatan', the value now backing
     * OrganizationCategory::Organisasi - see that enum's doc comment for why the two groups
     * merged into one.
     *
     * Only the `category` column on organization_types changes; no row is added, removed, or
     * repointed, so every organization and template keeps the exact type it had. The picker
     * simply stops showing two single-item groups.
     *
     * The stored value stays the string 'persyarikatan' rather than becoming 'organisasi': the
     * enum case was renamed but its backing value deliberately was not, so the far more numerous
     * Muhammadiyah-side rows need no rewrite at all and this migration touches only the handful
     * of Aisyiyah/Ortom ones. Renaming the value too would have meant rewriting every row for a
     * purely cosmetic gain.
     *
     * Written with the query builder rather than the OrganizationType model on purpose: the
     * model casts `category` to OrganizationCategory, and reading rows whose stored value a
     * future version of the enum no longer declares would throw before this could fix them.
     */
    public function up(): void
    {
        DB::table('organization_types')
            ->where('category', 'ortom')
            ->update(['category' => 'persyarikatan']);
    }

    /**
     * Sends Aisyiyah back to its own 'ortom' group. Identified by slug, since after up() there
     * is nothing in the data itself distinguishing a former-Ortom row from a persyarikatan one -
     * which is the point of the merge, and the reason this reversal is best-effort: a row added
     * as an Ortom after this migration ran cannot be told apart and stays under 'persyarikatan'.
     */
    public function down(): void
    {
        DB::table('organization_types')
            ->whereIn('slug', ['aisyiyah', 'pimpinan-cabang-aisyiyah'])
            ->update(['category' => 'ortom']);
    }
};
