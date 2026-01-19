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
        Schema::table('company_has_subscriptions', function (Blueprint $table) {
            $table->string('stripe_subscription_id')->nullable()->after('subscription_id');
            $table->string('stripe_invoice_id')->nullable()->after('stripe_subscription_id');
            $table->string('invoice_url')->nullable()->after('stripe_invoice_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('company_has_subscriptions', function (Blueprint $table) {
            $table->dropColumn(['stripe_subscription_id', 'stripe_invoice_id', 'invoice_url']);
        });
    }
};
