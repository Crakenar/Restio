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
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->integer('max_users')->default(6)->after('interval'); // Default to free tier (5 + owner)
            $table->text('description')->nullable()->after('max_users');
            $table->json('features')->nullable()->after('description');
            $table->boolean('is_popular')->default(false)->after('features');
            $table->integer('sort_order')->default(0)->after('is_popular');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn(['max_users', 'description', 'features', 'is_popular', 'sort_order']);
        });
    }
};
