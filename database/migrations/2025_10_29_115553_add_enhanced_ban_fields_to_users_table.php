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
            $table->string('ban_duration')->nullable()->after('ban_reason');
            $table->timestamp('ban_expires_at')->nullable()->after('ban_duration');
            $table->text('ban_notes')->nullable()->after('ban_expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['ban_duration', 'ban_expires_at', 'ban_notes']);
        });
    }
};
