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
            $table->enum('clinic_registration_status', ['unregistered', 'incomplete', 'pending', 'approved', 'rejected'])
                  ->default('unregistered')
                  ->after('account_type');
            $table->json('clinic_registration_data')->nullable()->after('clinic_registration_status');
            $table->timestamp('clinic_registration_submitted_at')->nullable()->after('clinic_registration_data');
            $table->timestamp('clinic_registration_approved_at')->nullable()->after('clinic_registration_submitted_at');
            $table->string('clinic_rejection_reason')->nullable()->after('clinic_registration_approved_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'clinic_registration_status',
                'clinic_registration_data',
                'clinic_registration_submitted_at',
                'clinic_registration_approved_at',
                'clinic_rejection_reason'
            ]);
        });
    }
};
