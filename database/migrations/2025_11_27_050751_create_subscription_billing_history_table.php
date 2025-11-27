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
        Schema::create('subscription_billing_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_id')->constrained('subscriptions')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->string('billing_period'); // e.g., "Nov 2025", "2025-11 to 2025-12"
            $table->date('billing_date');
            $table->date('period_start');
            $table->date('period_end');
            $table->enum('status', ['paid', 'pending', 'failed'])->default('paid');
            $table->string('payment_method')->nullable(); // mock_card, paymongo, etc.
            $table->string('transaction_id')->nullable();
            $table->foreignId('mock_card_id')->nullable()->constrained('mock_payment_cards')->onDelete('set null');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['subscription_id', 'billing_date']);
            $table->index(['user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_billing_history');
    }
};
