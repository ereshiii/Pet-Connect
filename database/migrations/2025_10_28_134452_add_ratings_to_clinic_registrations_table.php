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
        Schema::table('clinic_registrations', function (Blueprint $table) {
            $table->decimal('rating', 3, 2)->default(0.00)->after('longitude');
            $table->integer('total_reviews')->default(0)->after('rating');
            $table->boolean('is_featured')->default(false)->after('total_reviews');
            $table->boolean('is_open_24_7')->default(false)->after('is_featured');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clinic_registrations', function (Blueprint $table) {
            $table->dropColumn(['rating', 'total_reviews', 'is_featured', 'is_open_24_7']);
        });
    }
};
