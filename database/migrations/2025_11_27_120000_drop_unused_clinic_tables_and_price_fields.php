<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Remove unused tables (clinic_equipment, clinic_addresses) and
     * remove payment-related fields (base_price) since no payment is involved.
     */
    public function up(): void
    {
        // Drop clinic_equipment table (not used in the application)
        if (Schema::hasTable('clinic_equipment')) {
            Schema::dropIfExists('clinic_equipment');
        }

        // Drop clinic_addresses table (address is stored directly in clinic_registrations)
        if (Schema::hasTable('clinic_addresses')) {
            Schema::dropIfExists('clinic_addresses');
        }

        // Remove base_price from clinic_services (no payment involved)
        if (Schema::hasTable('clinic_services') && Schema::hasColumn('clinic_services', 'base_price')) {
            Schema::table('clinic_services', function (Blueprint $table) {
                $table->dropColumn('base_price');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate clinic_equipment table
        Schema::create('clinic_equipment', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained('clinic_registrations')->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('category', ['diagnostic', 'surgical', 'monitoring', 'treatment', 'safety', 'other']);
            $table->string('model')->nullable();
            $table->string('manufacturer')->nullable();
            $table->date('purchase_date')->nullable();
            $table->date('last_maintenance')->nullable();
            $table->date('next_maintenance')->nullable();
            $table->enum('status', ['working', 'maintenance', 'broken', 'retired']);
            $table->timestamps();
            
            $table->index(['clinic_id', 'category', 'status']);
        });

        // Recreate clinic_addresses table
        Schema::create('clinic_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained('clinic_registrations')->onDelete('cascade');
            $table->enum('type', ['main', 'branch', 'mobile_service_area']);
            $table->string('address_line_1');
            $table->string('address_line_2')->nullable();
            $table->string('city');
            $table->string('state');
            $table->string('postal_code');
            $table->string('country')->default('Philippines');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
            
            $table->index(['clinic_id', 'type']);
            $table->index(['latitude', 'longitude']);
        });

        // Add back base_price to clinic_services
        if (Schema::hasTable('clinic_services') && !Schema::hasColumn('clinic_services', 'base_price')) {
            Schema::table('clinic_services', function (Blueprint $table) {
                $table->decimal('base_price', 10, 2)->nullable()->after('category');
            });
        }
    }
};
