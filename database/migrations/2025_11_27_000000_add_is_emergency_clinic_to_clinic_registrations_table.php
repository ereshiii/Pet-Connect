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
            $table->boolean('is_emergency_clinic')->default(false)->after('is_open_24_7');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clinic_registrations', function (Blueprint $table) {
            $table->dropColumn('is_emergency_clinic');
        });
    }
};
