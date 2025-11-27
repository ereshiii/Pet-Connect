<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Consolidate clinic-related tables to reference clinic_registrations.id
     * instead of clinics.id. This eliminates redundancy and creates a single
     * source of truth for clinic data.
     */
    public function up(): void
    {
        // Step 1: Map clinics.id to clinic_registrations.id for data migration
        // Only include clinics that have a valid registration_id
        $clinicMapping = DB::table('clinics')
            ->select('id as clinic_id', 'registration_id')
            ->whereNotNull('registration_id')
            ->get()
            ->pluck('registration_id', 'clinic_id')
            ->toArray();

        // Step 1a: Delete orphaned records in child tables for clinics without registration
        $orphanedClinicIds = DB::table('clinics')
            ->whereNull('registration_id')
            ->pluck('id')
            ->toArray();

        if (!empty($orphanedClinicIds)) {
            DB::table('clinic_staff')->whereIn('clinic_id', $orphanedClinicIds)->delete();
            DB::table('clinic_services')->whereIn('clinic_id', $orphanedClinicIds)->delete();
            DB::table('clinic_operating_hours')->whereIn('clinic_id', $orphanedClinicIds)->delete();
            DB::table('clinic_addresses')->whereIn('clinic_id', $orphanedClinicIds)->delete();
            DB::table('clinic_equipment')->whereIn('clinic_id', $orphanedClinicIds)->delete();
        }

        // Step 2: Update clinic_staff to reference clinic_registrations
        if (Schema::hasTable('clinic_staff')) {
            // Drop existing foreign key
            Schema::table('clinic_staff', function (Blueprint $table) {
                $table->dropForeign(['clinic_id']);
            });

            // Update clinic_id values to point to clinic_registrations.id
            foreach ($clinicMapping as $oldClinicId => $registrationId) {
                DB::table('clinic_staff')
                    ->where('clinic_id', $oldClinicId)
                    ->update(['clinic_id' => $registrationId]);
            }

            // Add new foreign key to clinic_registrations
            Schema::table('clinic_staff', function (Blueprint $table) {
                $table->foreign('clinic_id')
                    ->references('id')
                    ->on('clinic_registrations')
                    ->onDelete('cascade');
            });
        }

        // Step 3: Update clinic_services to reference clinic_registrations
        if (Schema::hasTable('clinic_services')) {
            Schema::table('clinic_services', function (Blueprint $table) {
                $table->dropForeign(['clinic_id']);
            });

            foreach ($clinicMapping as $oldClinicId => $registrationId) {
                DB::table('clinic_services')
                    ->where('clinic_id', $oldClinicId)
                    ->update(['clinic_id' => $registrationId]);
            }

            Schema::table('clinic_services', function (Blueprint $table) {
                $table->foreign('clinic_id')
                    ->references('id')
                    ->on('clinic_registrations')
                    ->onDelete('cascade');
            });
        }

        // Step 4: Update clinic_operating_hours to reference clinic_registrations
        if (Schema::hasTable('clinic_operating_hours')) {
            Schema::table('clinic_operating_hours', function (Blueprint $table) {
                $table->dropForeign(['clinic_id']);
            });

            foreach ($clinicMapping as $oldClinicId => $registrationId) {
                DB::table('clinic_operating_hours')
                    ->where('clinic_id', $oldClinicId)
                    ->update(['clinic_id' => $registrationId]);
            }

            Schema::table('clinic_operating_hours', function (Blueprint $table) {
                $table->foreign('clinic_id')
                    ->references('id')
                    ->on('clinic_registrations')
                    ->onDelete('cascade');
            });
        }

        // Step 5: Update clinic_addresses to reference clinic_registrations
        if (Schema::hasTable('clinic_addresses')) {
            Schema::table('clinic_addresses', function (Blueprint $table) {
                $table->dropForeign(['clinic_id']);
            });

            foreach ($clinicMapping as $oldClinicId => $registrationId) {
                DB::table('clinic_addresses')
                    ->where('clinic_id', $oldClinicId)
                    ->update(['clinic_id' => $registrationId]);
            }

            Schema::table('clinic_addresses', function (Blueprint $table) {
                $table->foreign('clinic_id')
                    ->references('id')
                    ->on('clinic_registrations')
                    ->onDelete('cascade');
            });
        }

        // Step 6: Update clinic_equipment to reference clinic_registrations
        if (Schema::hasTable('clinic_equipment')) {
            Schema::table('clinic_equipment', function (Blueprint $table) {
                $table->dropForeign(['clinic_id']);
            });

            foreach ($clinicMapping as $oldClinicId => $registrationId) {
                DB::table('clinic_equipment')
                    ->where('clinic_id', $oldClinicId)
                    ->update(['clinic_id' => $registrationId]);
            }

            Schema::table('clinic_equipment', function (Blueprint $table) {
                $table->foreign('clinic_id')
                    ->references('id')
                    ->on('clinic_registrations')
                    ->onDelete('cascade');
            });
        }

        // Step 7: Update clinic_reviews.clinic_id to reference clinic_registrations
        // Note: clinic_reviews already has clinic_registration_id, we're fixing clinic_id
        if (Schema::hasTable('clinic_reviews') && Schema::hasColumn('clinic_reviews', 'clinic_id')) {
            Schema::table('clinic_reviews', function (Blueprint $table) {
                if (DB::getDriverName() !== 'sqlite') {
                    $table->dropForeign(['clinic_id']);
                }
            });

            foreach ($clinicMapping as $oldClinicId => $registrationId) {
                DB::table('clinic_reviews')
                    ->where('clinic_id', $oldClinicId)
                    ->update(['clinic_id' => $registrationId]);
            }

            // Make clinic_id reference clinic_registrations instead of clinics
            Schema::table('clinic_reviews', function (Blueprint $table) {
                $table->foreign('clinic_id')
                    ->references('id')
                    ->on('clinic_registrations')
                    ->onDelete('cascade');
            });
        }

        // Step 8: Drop the clinics table (it's now redundant)
        // All data is in clinic_registrations, all relationships now point there
        if (Schema::hasTable('clinics')) {
            Schema::dropIfExists('clinics');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate clinics table
        Schema::create('clinics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registration_id')->nullable()->constrained('clinic_registrations')->onDelete('set null');
            $table->string('name');
            $table->string('license_number')->unique();
            $table->enum('type', ['general', 'emergency', 'specialty', 'mobile']);
            $table->text('description')->nullable();
            $table->json('services')->nullable();
            $table->json('specialties')->nullable();
            $table->string('phone');
            $table->string('email');
            $table->string('website')->nullable();
            $table->json('social_media')->nullable();
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active');
            $table->decimal('average_rating', 3, 2)->nullable();
            $table->integer('total_reviews')->default(0);
            $table->timestamp('last_review_at')->nullable();
            $table->timestamps();
            
            $table->index(['status', 'type']);
        });

        // Restore clinics from clinic_registrations
        $registrations = DB::table('clinic_registrations')
            ->where('status', 'approved')
            ->get();

        foreach ($registrations as $reg) {
            DB::table('clinics')->insert([
                'id' => $reg->id, // Use same ID for easier reversal
                'registration_id' => $reg->id,
                'name' => $reg->clinic_name,
                'license_number' => 'LIC-' . $reg->id,
                'type' => 'general',
                'description' => $reg->clinic_description,
                'services' => $reg->services,
                'specialties' => json_encode([]),
                'phone' => $reg->phone,
                'email' => $reg->email,
                'website' => $reg->website,
                'social_media' => null,
                'status' => 'active',
                'average_rating' => $reg->rating,
                'total_reviews' => $reg->total_reviews ?? 0,
                'last_review_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Revert all foreign keys back to clinics.id
        foreach (['clinic_staff', 'clinic_services', 'clinic_operating_hours', 'clinic_addresses', 'clinic_equipment'] as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropForeign(['clinic_id']);
                });

                Schema::table($tableName, function (Blueprint $table) {
                    $table->foreign('clinic_id')
                        ->references('id')
                        ->on('clinics')
                        ->onDelete('cascade');
                });
            }
        }

        // Revert clinic_reviews
        if (Schema::hasTable('clinic_reviews') && Schema::hasColumn('clinic_reviews', 'clinic_id')) {
            Schema::table('clinic_reviews', function (Blueprint $table) {
                $table->dropForeign(['clinic_id']);
            });

            Schema::table('clinic_reviews', function (Blueprint $table) {
                $table->foreign('clinic_id')
                    ->references('id')
                    ->on('clinics')
                    ->onDelete('cascade');
            });
        }
    }
};
