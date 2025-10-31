<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // For SQLite, we need to recreate the table with the new enum
        // For other databases, you might be able to use ALTER TABLE
        
        // Create a temporary table with the new enum in the exact same column order
        Schema::create('users_temp', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
            $table->text('two_factor_secret')->nullable();
            $table->text('two_factor_recovery_codes')->nullable();
            $table->timestamp('two_factor_confirmed_at')->nullable();
            $table->enum('account_type', ['user', 'clinic', 'admin'])->default('user');
            $table->enum('clinic_registration_status', ['unregistered', 'incomplete', 'pending', 'approved', 'rejected'])->default('unregistered');
            $table->json('clinic_registration_data')->nullable();
            $table->timestamp('clinic_registration_submitted_at')->nullable();
            $table->timestamp('clinic_registration_approved_at')->nullable();
            $table->text('clinic_rejection_reason')->nullable();
            $table->boolean('is_admin')->default(false);
            $table->string('username')->nullable();
            $table->string('phone')->nullable();
            $table->string('address_line_1')->nullable();
            $table->string('address_line_2')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country')->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_relationship')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['male', 'female', 'other', 'prefer_not_to_say'])->nullable();
            $table->text('bio')->nullable();
        });

        // Copy data from the old table to the new table
        DB::statement('INSERT INTO users_temp SELECT * FROM users');

        // Drop the old table
        Schema::drop('users');

        // Rename the new table
        Schema::rename('users_temp', 'users');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Create a temporary table with the old enum
        Schema::create('users_temp', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('username')->nullable();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->enum('account_type', ['user', 'clinic'])->default('user');
            $table->string('password');
            $table->string('phone')->nullable();
            $table->string('address_line_1')->nullable();
            $table->string('address_line_2')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country')->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_relationship')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['male', 'female', 'other', 'prefer_not_to_say'])->nullable();
            $table->text('bio')->nullable();
            $table->enum('clinic_registration_status', ['unregistered', 'incomplete', 'pending', 'approved', 'rejected'])->default('unregistered');
            $table->json('clinic_registration_data')->nullable();
            $table->timestamp('clinic_registration_submitted_at')->nullable();
            $table->timestamp('clinic_registration_approved_at')->nullable();
            $table->text('clinic_rejection_reason')->nullable();
            $table->boolean('is_admin')->default(false);
            $table->rememberToken();
            $table->timestamps();
        });

        // Copy data, but change any 'admin' account types to 'user'
        DB::statement("INSERT INTO users_temp SELECT id, name, username, email, email_verified_at, CASE WHEN account_type = 'admin' THEN 'user' ELSE account_type END, password, phone, address_line_1, address_line_2, city, state, postal_code, country, emergency_contact_name, emergency_contact_relationship, emergency_contact_phone, date_of_birth, gender, bio, clinic_registration_status, clinic_registration_data, clinic_registration_submitted_at, clinic_registration_approved_at, clinic_rejection_reason, is_admin, remember_token, created_at, updated_at FROM users");

        // Drop the old table
        Schema::drop('users');

        // Rename the new table
        Schema::rename('users_temp', 'users');
    }
};
