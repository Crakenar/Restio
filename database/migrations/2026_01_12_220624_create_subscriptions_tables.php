<?php

use App\Enum\SubscriptionInterval;
use App\Enum\SubscriptionStatus;
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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->decimal('price', 10, 2);
            $table->string('currency')->default('EUR');
            $table->enum('interval', SubscriptionInterval::cases());
            $table->timestamps();
        });

        Schema::create('company_has_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained();
            $table->foreignId('subscription_id')->constrained();
            $table->enum('status', SubscriptionStatus::cases());
            $table->timestamp('starts_at');
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_has_subscriptions');
        Schema::dropIfExists('subscriptions');
    }
};
