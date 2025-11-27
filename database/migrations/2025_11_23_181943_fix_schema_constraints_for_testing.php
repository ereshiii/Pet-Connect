<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Fixes schema constraints discovered during Phase 11 testing:
     * 1. clinic_staff.start_date - Make nullable
     * 2. appointments.pet_id - Make nullable for general consultations
     * 3. appointments.scheduled_at - Make nullable (use appointment_date/time instead)
     * 4. payments.payment_date - Add default to now()
     * 5. payments.invoice_id - Make nullable (not all payments have invoices)
     * 6. invoices.patient_id - Make nullable (not all invoices have patients)
     * 7. patient_edit_logs.patient_id - Make nullable
     * 8. notifications.is_read - Add missing column
     * 9. user_addresses.state - Make nullable for Philippine regions
     */
    public function up(): void
    {
        // Fix 1: clinic_staff.start_date - Make nullable
        Schema::table('clinic_staff', function (Blueprint $table) {
            $table->date('start_date')->nullable()->change();
        });

        // Fix 2 & 3: appointments - Make pet_id and scheduled_at nullable
        Schema::table('appointments', function (Blueprint $table) {
            $table->foreignId('pet_id')->nullable()->change();
            $table->dateTime('scheduled_at')->nullable()->change();
        });

        // Fix 4 & 5: payments - Add default payment_date and make invoice_id nullable
        Schema::table('payments', function (Blueprint $table) {
            $table->timestamp('payment_date')->nullable()->useCurrent()->change();
            $table->foreignId('invoice_id')->nullable()->change();
        });

        // Fix 6: invoices.patient_id and owner_id - Make nullable
        Schema::table('invoices', function (Blueprint $table) {
            $table->foreignId('patient_id')->nullable()->change();
            $table->foreignId('owner_id')->nullable()->change();
        });

        // Fix 7: patient_edit_logs.patient_id - Make nullable
        Schema::table('patient_edit_logs', function (Blueprint $table) {
            $table->foreignId('patient_id')->nullable()->change();
        });

        // Fix 8: notifications.is_read - Add missing column
        if (!Schema::hasColumn('notifications', 'is_read')) {
            Schema::table('notifications', function (Blueprint $table) {
                $table->boolean('is_read')->default(false)->after('data');
                if (!Schema::hasColumn('notifications', 'read_at')) {
                    $table->timestamp('read_at')->nullable()->after('is_read');
                }
            });
        }

        // Fix 9: user_addresses.state - Make nullable for Philippine regions
        Schema::table('user_addresses', function (Blueprint $table) {
            $table->string('state')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverse Fix 1
        Schema::table('clinic_staff', function (Blueprint $table) {
            $table->date('start_date')->nullable(false)->change();
        });

        // Reverse Fix 2 & 3
        Schema::table('appointments', function (Blueprint $table) {
            $table->foreignId('pet_id')->nullable(false)->change();
            $table->dateTime('scheduled_at')->nullable(false)->change();
        });

        // Reverse Fix 4 & 5
        Schema::table('payments', function (Blueprint $table) {
            $table->timestamp('payment_date')->nullable(false)->change();
            $table->foreignId('invoice_id')->nullable(false)->change();
        });

        // Reverse Fix 6
        Schema::table('invoices', function (Blueprint $table) {
            $table->foreignId('patient_id')->nullable(false)->change();
            $table->foreignId('owner_id')->nullable(false)->change();
        });

        // Reverse Fix 7
        Schema::table('patient_edit_logs', function (Blueprint $table) {
            $table->foreignId('patient_id')->nullable(false)->change();
        });

        // Reverse Fix 8
        if (Schema::hasColumn('notifications', 'is_read')) {
            Schema::table('notifications', function (Blueprint $table) {
                // Drop index first if it exists
                $indexes = Schema::getConnection()
                    ->getDoctrineSchemaManager()
                    ->listTableIndexes('notifications');
                
                if (isset($indexes['notifications_user_id_read_at_index'])) {
                    $table->dropIndex('notifications_user_id_read_at_index');
                }
                
                $table->dropColumn(['is_read', 'read_at']);
            });
        }

        // Reverse Fix 9
        Schema::table('user_addresses', function (Blueprint $table) {
            $table->string('state')->nullable(false)->change();
        });
    }
};
