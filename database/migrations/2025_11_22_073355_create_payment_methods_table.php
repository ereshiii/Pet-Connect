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
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('type'); // card, gcash, grab_pay, paymaya
            $table->string('provider_id')->nullable(); // PayMongo payment method ID
            $table->string('last_four')->nullable(); // Last 4 digits for cards
            $table->string('brand')->nullable(); // visa, mastercard, etc.
            $table->string('exp_month')->nullable(); // For cards
            $table->string('exp_year')->nullable(); // For cards
            $table->boolean('is_default')->default(false);
            $table->timestamps();
            
            // Index for faster lookups
            $table->index(['user_id', 'is_default']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};
