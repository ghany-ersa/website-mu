<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('organization_domains', function (Blueprint $table) {
            $table->id();
            // unique: one custom domain per organization for now. A separate table rather than a
            // column on organizations so allowing a second domain later (e.g. .id alongside
            // .or.id) is dropping this index, not a schema migration of organizations.
            $table->foreignId('organization_id')->unique()->constrained()->cascadeOnDelete();
            // Normalized by OrganizationDomain::normalize() - lowercase, punycode, no scheme,
            // port, path or trailing dot - so this unique index actually catches duplicates.
            $table->string('hostname')->unique();
            // Off until an admin activates it after a passing connection check: the domain has
            // to be attached to the hosting (cPanel alias + AutoSSL) by hand first, and serving
            // or redirecting to it before then would take the tenant's site down.
            $table->boolean('is_active')->default(false);
            $table->boolean('last_check_ok')->nullable();
            $table->string('last_check_result')->nullable();
            $table->timestamp('last_checked_at')->nullable();
            $table->foreignId('activated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('activated_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organization_domains');
    }
};
