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
        Schema::table('pets', function (Blueprint $table) {
            $table->string('breed')->nullable()->after('species'); // For string breed storage
            $table->decimal('age', 4, 1)->nullable()->after('birth_date'); // For direct age storage
            $table->string('microchip_id')->nullable()->after('microchip_number'); // Alternative microchip field
            $table->text('allergies')->nullable()->after('special_needs'); // Store allergies as text
            $table->text('current_medications')->nullable()->after('allergies'); // Store medications as text
            $table->text('medical_conditions')->nullable()->after('current_medications'); // Store conditions as text
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pets', function (Blueprint $table) {
            $table->dropColumn([
                'breed',
                'age',
                'microchip_id',
                'allergies',
                'current_medications',
                'medical_conditions'
            ]);
        });
    }
};
