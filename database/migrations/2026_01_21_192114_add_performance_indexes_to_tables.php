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
        // Add indexes to vacation_requests table for common queries
        Schema::table('vacation_requests', function (Blueprint $table) {
            // Index for dashboard queries (company + latest)
            $table->index(['company_id', 'created_at'], 'idx_vr_company_created');

            // Index for status queries (pending requests)
            $table->index(['company_id', 'status'], 'idx_vr_company_status');

            // Index for user-specific queries
            $table->index(['user_id', 'status'], 'idx_vr_user_status');

            // Composite index for calendar queries (company + date range)
            $table->index(['company_id', 'start_date', 'end_date'], 'idx_vr_company_dates');
        });

        // Add indexes to users table for common queries
        Schema::table('users', function (Blueprint $table) {
            // Index for company queries
            $table->index(['company_id', 'team_id'], 'idx_users_company_team');
        });

        // Add indexes to teams table
        Schema::table('teams', function (Blueprint $table) {
            // Index for company queries
            $table->index('company_id', 'idx_teams_company');
        });

        // Add indexes to company_settings table
        Schema::table('company_settings', function (Blueprint $table) {
            // Index for company queries
            $table->index('company_id', 'idx_company_settings_company');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vacation_requests', function (Blueprint $table) {
            $table->dropIndex('idx_vr_company_created');
            $table->dropIndex('idx_vr_company_status');
            $table->dropIndex('idx_vr_user_status');
            $table->dropIndex('idx_vr_company_dates');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_users_company_team');
        });

        Schema::table('teams', function (Blueprint $table) {
            $table->dropIndex('idx_teams_company');
        });

        Schema::table('company_settings', function (Blueprint $table) {
            $table->dropIndex('idx_company_settings_company');
        });
    }
};
