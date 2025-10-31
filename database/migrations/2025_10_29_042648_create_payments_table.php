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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->onDelete('cascade');
            $table->foreignId('clinic_id')->constrained('clinic_registrations')->onDelete('cascade');
            $table->string('payment_number')->unique(); // unique payment reference
            
            // Payment details
            $table->decimal('amount', 10, 2);
            $table->enum('method', ['cash', 'card', 'bank_transfer', 'gcash', 'paymaya', 'check', 'other']);
            $table->enum('status', ['pending', 'completed', 'failed', 'refunded', 'cancelled'])->default('completed');
            
            // Payment information
            $table->date('payment_date');
            $table->string('reference_number')->nullable(); // bank ref, transaction ID, check number, etc.
            $table->string('processed_by')->nullable(); // staff member who processed
            
            // Additional details
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable(); // for payment gateway data, etc.
            
            // Refund tracking
            $table->decimal('refunded_amount', 10, 2)->default(0);
            $table->timestamp('refunded_at')->nullable();
            $table->text('refund_reason')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['invoice_id']);
            $table->index(['clinic_id', 'payment_date']);
            $table->index(['method']);
            $table->index(['status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
