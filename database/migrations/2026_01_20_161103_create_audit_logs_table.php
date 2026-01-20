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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('event'); // created, updated, deleted, approved, rejected, etc.
            $table->morphs('auditable'); // The model being audited
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // Who performed the action
            $table->foreignId('company_id')->constrained()->cascadeOnDelete(); // Multi-tenant scoping
            $table->json('old_values')->nullable(); // Before values
            $table->json('new_values')->nullable(); // After values
            $table->json('metadata')->nullable(); // Additional context (IP, user agent, etc.)
            $table->timestamps();

            // Indexes for efficient querying
            $table->index(['auditable_type', 'auditable_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index(['company_id', 'created_at']);
            $table->index(['event', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
