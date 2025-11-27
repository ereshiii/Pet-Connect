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
            $table->string('clinic_photo')->nullable()->after('clinic_description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clinic_registrations', function (Blueprint $table) {
            $table->dropColumn('clinic_photo');
        });
    }
};
