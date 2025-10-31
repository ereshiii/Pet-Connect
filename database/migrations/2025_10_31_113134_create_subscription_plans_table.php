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
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., 'Premium Pet Owner', 'Professional Clinic'
            $table->string('slug')->unique(); // e.g., 'premium-pet-owner', 'professional-clinic'
            $table->enum('type', ['user', 'clinic']); // Plan type
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2); // Monthly price in PHP
            $table->decimal('annual_price', 10, 2)->nullable(); // Annual price (optional discount)
            $table->string('stripe_price_id')->nullable(); // Stripe price ID for Cashier
            $table->string('stripe_annual_price_id')->nullable(); // Annual price ID
            $table->json('features'); // Array of features included
            $table->json('limits'); // Array of limits (e.g., max_pets, max_appointments)
            $table->boolean('is_active')->default(true);
            $table->integer('trial_days')->default(0); // Free trial days
            $table->decimal('transaction_fee_percentage', 5, 2)->nullable(); // For clinic plans
            $table->decimal('transaction_fee_fixed', 10, 2)->nullable(); // Fixed fee per transaction
            $table->integer('sort_order')->default(0); // For display ordering
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_plans');
    }
};
