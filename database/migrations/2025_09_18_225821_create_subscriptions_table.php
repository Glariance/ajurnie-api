<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Stripe references
            $table->string('stripe_subscription_id')->nullable()->index();
            $table->string('price_id')->nullable()->index();     // price_xxx from Stripe

            // Your app-level snapshot
            $table->enum('plan', ['novice', 'trainer'])->nullable()->index();
            $table->enum('interval', ['monthly', 'yearly'])->nullable()->index();

            // Status & lifecycle
            $table->string('status')->nullable()->index();       // active, trialing, canceled, incomplete, etc.
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('current_period_end')->nullable();
            $table->timestamp('canceled_at')->nullable();

            // Founding vs post-founding snapshot
            $table->enum('membership_type', ['founding', 'post_founding'])->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
