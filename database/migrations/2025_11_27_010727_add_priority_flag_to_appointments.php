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
        Schema::table('appointments', function (Blueprint $table) {
            $table->boolean('is_priority')->default(false)->after('is_disputed');
            $table->string('priority_reason')->nullable()->after('is_priority');
            
            // Add index for efficient priority booking queries
            $table->index(['clinic_id', 'is_priority', 'scheduled_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropIndex(['clinic_id', 'is_priority', 'scheduled_at']);
            $table->dropColumn(['is_priority', 'priority_reason']);
        });
    }
};
