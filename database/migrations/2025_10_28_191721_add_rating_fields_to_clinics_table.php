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
        Schema::table('clinics', function (Blueprint $table) {
            $table->decimal('average_rating', 3, 2)->default(0.00)->after('status');
            $table->unsignedInteger('total_reviews')->default(0)->after('average_rating');
            $table->timestamp('last_review_at')->nullable()->after('total_reviews');
            
            // Add indexes for rating queries
            $table->index(['average_rating', 'status']);
            $table->index(['total_reviews', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clinics', function (Blueprint $table) {
            $table->dropIndex(['average_rating', 'status']);
            $table->dropIndex(['total_reviews', 'status']);
            $table->dropColumn(['average_rating', 'total_reviews', 'last_review_at']);
        });
    }
};
