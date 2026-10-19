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
        Schema::create('password_reset_logs', function (Blueprint $table) {
            $table->id();
            // The admin/CS who generated the link - there's no email service (see CLAUDE.md),
            // so every reset link is generated from the admin panel and relayed to the user
            // manually over WhatsApp, unlike plan_override_logs this always has an actor.
            $table->foreignId('admin_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('target_user_id')->constrained('users')->cascadeOnDelete();
            // Nullable: the link is generated from an organization member list, but a target
            // user can belong to several organizations (or none, for a platform admin account) -
            // this records which one the admin was looking at, not an ownership constraint.
            $table->foreignId('organization_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('used_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('password_reset_logs');
    }
};
