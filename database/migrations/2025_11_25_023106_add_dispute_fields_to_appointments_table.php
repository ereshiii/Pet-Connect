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
            $table->timestamp('dispute_window_ends_at')->nullable()->after('status');
            $table->boolean('is_disputed')->default(false)->after('dispute_window_ends_at');
            $table->text('dispute_reason')->nullable()->after('is_disputed');
            $table->timestamp('disputed_at')->nullable()->after('dispute_reason');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn(['dispute_window_ends_at', 'is_disputed', 'dispute_reason', 'disputed_at']);
        });
    }
};
