<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Fix clinic_reviews foreign key that references deleted clinics table.
     * Since SQLite doesn't support dropping foreign keys, we'll handle this carefully.
     */
    public function up(): void
    {
        $driver = DB::getDriverName();
        
        if ($driver === 'sqlite') {
            // For SQLite: Rename table, recreate without bad FK, migrate data back
            if (Schema::hasTable('clinic_reviews')) {
                // Drop temporary table if it exists from a failed attempt
                DB::statement('DROP TABLE IF EXISTS clinic_reviews_new');
                
                // Create new table without the problematic foreign key
                Schema::create('clinic_reviews_new', function (Blueprint $table) {
                    $table->id();
                    $table->foreignId('clinic_registration_id')->nullable()->constrained('clinic_registrations')->onDelete('cascade');
                    $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                    $table->foreignId('appointment_id')->nullable()->constrained('appointments')->onDelete('set null');
                    $table->integer('rating')->unsigned()->default(5);
                    $table->text('comment')->nullable();
                    $table->text('response')->nullable();
                    $table->timestamp('response_date')->nullable();
                    $table->foreignId('responded_by')->nullable()->constrained('users')->onDelete('set null');
                    $table->boolean('is_verified')->default(false);
                    $table->boolean('is_featured')->default(false);
                    $table->json('helpful_votes')->nullable();
                    $table->timestamps();

                    // Indexes
                    $table->index(['clinic_registration_id', 'rating']);
                    $table->index(['clinic_registration_id', 'created_at']);
                    $table->index(['user_id', 'created_at']);
                    
                    // Unique constraint - one review per user per clinic
                    $table->unique(['clinic_registration_id', 'user_id']);
                });

                // Copy data from old table to new table, excluding clinic_id since it's being removed
                DB::statement('INSERT INTO clinic_reviews_new (id, clinic_registration_id, user_id, appointment_id, rating, comment, response, response_date, responded_by, is_verified, is_featured, helpful_votes, created_at, updated_at) SELECT id, clinic_registration_id, user_id, appointment_id, rating, comment, response, response_date, responded_by, is_verified, is_featured, helpful_votes, created_at, updated_at FROM clinic_reviews');

                // Drop old table and rename new one
                Schema::dropIfExists('clinic_reviews');
                Schema::rename('clinic_reviews_new', 'clinic_reviews');
            }
        } else {
            // For other databases (MySQL, PostgreSQL): Modify the table
            if (Schema::hasTable('clinic_reviews')) {
                Schema::table('clinic_reviews', function (Blueprint $table) {
                    // Drop the bad foreign key if it exists
                    try {
                        $table->dropForeign(['clinic_id']);
                    } catch (\Exception $e) {
                        // Foreign key might not exist or already dropped
                    }
                    
                    // Drop clinic_id column if it exists
                    if (Schema::hasColumn('clinic_reviews', 'clinic_id')) {
                        $table->dropColumn('clinic_id');
                    }
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration is a fix and shouldn't be reversed
        // Reversing would re-introduce the problematic foreign key
    }
};
