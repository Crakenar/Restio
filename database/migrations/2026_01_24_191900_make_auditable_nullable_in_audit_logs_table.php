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
        Schema::table('audit_logs', function (Blueprint $table) {
            // Make auditable columns nullable for system events like login/logout
            $table->string('auditable_type')->nullable()->change();
            $table->unsignedBigInteger('auditable_id')->nullable()->change();

            // Make company_id nullable for system events
            $table->dropForeign(['company_id']);
            $table->foreignId('company_id')->nullable()->change()->constrained()->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->string('auditable_type')->nullable(false)->change();
            $table->unsignedBigInteger('auditable_id')->nullable(false)->change();

            $table->dropForeign(['company_id']);
            $table->foreignId('company_id')->nullable(false)->change()->constrained()->cascadeOnDelete();
        });
    }
};
