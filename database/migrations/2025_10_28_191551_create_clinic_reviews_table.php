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
        Schema::create('clinic_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained('clinics')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->integer('rating')->unsigned()->default(5);
            $table->text('comment')->nullable();
            $table->text('response')->nullable(); // Clinic response to review
            $table->timestamp('response_date')->nullable();
            $table->foreignId('responded_by')->nullable()->constrained('users')->onDelete('set null');
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->json('helpful_votes')->nullable(); // Array of user IDs who found this helpful
            $table->timestamps();

            // Indexes for performance
            $table->index(['clinic_id', 'rating']);
            $table->index(['clinic_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
            
            // Ensure one review per user per clinic
            $table->unique(['clinic_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clinic_reviews');
    }
};
