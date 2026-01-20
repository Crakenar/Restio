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
        Schema::table('vacation_requests', function (Blueprint $table) {
            // Add indexes for frequently queried columns
            $table->index('user_id', 'idx_vacation_requests_user_id');
            $table->index('company_id', 'idx_vacation_requests_company_id');
            $table->index('status', 'idx_vacation_requests_status');
            $table->index(['start_date', 'end_date'], 'idx_vacation_requests_dates');
            $table->index(['company_id', 'status'], 'idx_vacation_requests_company_status');
            $table->index(['user_id', 'status'], 'idx_vacation_requests_user_status');
        });

        Schema::table('users', function (Blueprint $table) {
            // Add indexes for frequently queried columns
            $table->index('company_id', 'idx_users_company_id');
            $table->index('team_id', 'idx_users_team_id');
            $table->index('role', 'idx_users_role');
            $table->index(['company_id', 'role'], 'idx_users_company_role');
        });

        Schema::table('notifications', function (Blueprint $table) {
            // Add indexes for polymorphic relationship and read status
            $table->index(['notifiable_type', 'notifiable_id'], 'idx_notifications_notifiable');
            $table->index(['notifiable_id', 'read_at'], 'idx_notifications_notifiable_read');
        });

        Schema::table('teams', function (Blueprint $table) {
            // Add index for company relationship
            $table->index('company_id', 'idx_teams_company_id');
        });

        Schema::table('company_settings', function (Blueprint $table) {
            // Add index for company relationship
            $table->index('company_id', 'idx_company_settings_company_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vacation_requests', function (Blueprint $table) {
            $table->dropIndex('idx_vacation_requests_user_id');
            $table->dropIndex('idx_vacation_requests_company_id');
            $table->dropIndex('idx_vacation_requests_status');
            $table->dropIndex('idx_vacation_requests_dates');
            $table->dropIndex('idx_vacation_requests_company_status');
            $table->dropIndex('idx_vacation_requests_user_status');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_users_company_id');
            $table->dropIndex('idx_users_team_id');
            $table->dropIndex('idx_users_role');
            $table->dropIndex('idx_users_company_role');
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex('idx_notifications_notifiable');
            $table->dropIndex('idx_notifications_notifiable_read');
        });

        Schema::table('teams', function (Blueprint $table) {
            $table->dropIndex('idx_teams_company_id');
        });

        Schema::table('company_settings', function (Blueprint $table) {
            $table->dropIndex('idx_company_settings_company_id');
        });
    }
};
