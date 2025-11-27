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
        Schema::table('users', function (Blueprint $table) {
            $table->string('payment_provider')->nullable()->after('pm_last_four')->comment('Payment provider: stripe, paymongo');
            $table->json('payment_provider_metadata')->nullable()->after('payment_provider')->comment('Additional payment provider data');
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->string('payment_provider')->nullable()->after('stripe_status')->comment('Payment provider used');
            $table->json('payment_provider_metadata')->nullable()->after('payment_provider')->comment('Provider-specific data');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['payment_provider', 'payment_provider_metadata']);
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn(['payment_provider', 'payment_provider_metadata']);
        });
    }
};
