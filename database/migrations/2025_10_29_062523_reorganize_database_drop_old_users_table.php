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
        // For SQLite, we need to disable foreign key checks temporarily
        DB::statement('PRAGMA foreign_keys=OFF');
        
        // Step 1: Drop the old unorganized users table
        Schema::dropIfExists('users');
        
        // Step 2: Create new organized users table
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->enum('account_type', ['user', 'clinic', 'admin'])->default('user');
            $table->boolean('is_admin')->default(false);
            $table->boolean('is_verified')->default(false);
            $table->string('verification_token')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->string('two_factor_secret', 255)->nullable();
            $table->text('two_factor_recovery_codes')->nullable();
            $table->timestamp('two_factor_confirmed_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
            
            $table->index(['email']);
            $table->index(['account_type']);
            $table->index(['is_admin']);
        });
        
        // Re-enable foreign key checks
        DB::statement('PRAGMA foreign_keys=ON');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration is destructive and cannot be easily reversed
        // You would need to restore from backup if needed
        throw new Exception('This migration cannot be reversed. Please restore from backup if needed.');
    }
};
