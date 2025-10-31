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
            $table->dropForeign(['registration_id']);
            $table->foreignId('registration_id')->nullable()->change();
            $table->foreign('registration_id')->references('id')->on('clinic_registrations')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clinics', function (Blueprint $table) {
            $table->dropForeign(['registration_id']);
            $table->foreignId('registration_id')->nullable(false)->change();
            $table->foreign('registration_id')->references('id')->on('clinic_registrations')->onDelete('cascade');
        });
    }
};
