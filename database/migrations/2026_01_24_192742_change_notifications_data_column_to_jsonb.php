<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, convert any existing text data to valid JSON
        \DB::statement("UPDATE notifications SET data = CASE WHEN data::text ~ '^[\\[\\{]' THEN data ELSE '{}' END WHERE data IS NOT NULL");

        // Change column type from text to jsonb
        \DB::statement('ALTER TABLE notifications ALTER COLUMN data TYPE jsonb USING data::jsonb');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Change column back to text
        \DB::statement('ALTER TABLE notifications ALTER COLUMN data TYPE text');
    }
};
