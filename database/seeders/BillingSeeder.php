<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\Appointment;
use App\Models\Pet;
use App\Models\ClinicRegistration;
use App\Models\ClinicService;
use Carbon\Carbon;

class BillingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get some existing data to work with
        $clinic = ClinicRegistration::where('status', 'approved')->first();
        
        if (!$clinic) {
            $this->command->warn('No approved clinic found. Please ensure clinic data exists.');
            return;
        }

        $appointments = Appointment::where('clinic_id', $clinic->id)->get();
        $services = ClinicService::where('clinic_id', $clinic->id)->get();
        $pets = Pet::whereHas('owner')->limit(10)->get();

        if ($pets->isEmpty()) {
            $this->command->warn('No pets with owners found. Cannot create billing data.');
            return;
        }

        // Create sample invoices
        $invoiceStatuses = ['draft', 'sent', 'paid', 'overdue', 'partial'];
        $serviceNames = [
            'General Consultation',
            'Vaccination',
            'Dental Cleaning',
            'Surgery',
            'Laboratory Tests',
            'X-Ray Examination',
            'Emergency Treatment',
            'Grooming Service',
            'Deworming',
            'Health Certificate',
        ];
        
        for ($i = 1; $i <= 15; $i++) {
            $pet = $pets->random();
            $status = $invoiceStatuses[array_rand($invoiceStatuses)];
            
            // Create invoice date (last 3 months)
            $invoiceDate = Carbon::now()->subDays(rand(1, 90));
            $dueDate = $invoiceDate->copy()->addDays(30);

            // Generate invoice number
            $year = $invoiceDate->format('Y');
            $sequence = Invoice::where('clinic_id', $clinic->id)
                ->whereYear('invoice_date', $year)
                ->count() + 1;
            $invoiceNumber = sprintf('INV-%s-%s-%04d', 'CLI', $year, $sequence);

            $invoice = Invoice::create([
                'invoice_number' => $invoiceNumber,
                'clinic_id' => $clinic->id,
                'appointment_id' => $appointments->isNotEmpty() ? $appointments->random()->id : null,
                'patient_id' => $pet->id,
                'owner_id' => $pet->owner_id,
                'invoice_date' => $invoiceDate,
                'due_date' => $dueDate,
                'status' => $status,
                'tax_rate' => 12.0, // 12% VAT in Philippines
                'discount_amount' => 0,
                'total_amount' => 0, // Will be calculated
            ]);

            // Create 1-3 invoice items per invoice
            $itemCount = rand(1, 3);
            for ($j = 1; $j <= $itemCount; $j++) {
                $serviceName = $serviceNames[array_rand($serviceNames)];
                $quantity = rand(1, 2);
                $unitPrice = rand(800, 4000); // ₱800 to ₱4,000

                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'service_id' => $services->isNotEmpty() ? $services->random()->id : null,
                    'item_type' => 'service',
                    'name' => $serviceName,
                    'description' => "Professional veterinary " . strtolower($serviceName),
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'discount_amount' => 0,
                    'sort_order' => $j,
                ]);
            }

            // Calculate totals
            $invoice->refresh();
            $invoice->calculateTotals();
            $invoice->save();

            // Create payments for some invoices
            if (in_array($status, ['paid', 'partial'])) {
                $paymentMethods = ['cash', 'card', 'gcash', 'bank_transfer'];
                
                if ($status === 'paid') {
                    // Full payment
                    Payment::create([
                        'invoice_id' => $invoice->id,
                        'clinic_id' => $clinic->id,
                        'amount' => $invoice->total_amount,
                        'method' => $paymentMethods[array_rand($paymentMethods)],
                        'payment_date' => $invoiceDate->copy()->addDays(rand(1, 15)),
                        'reference_number' => 'REF' . rand(100000, 999999),
                        'processed_by' => 'System Admin',
                        'notes' => 'Full payment received',
                    ]);
                } else {
                    // Partial payment
                    $partialAmount = $invoice->total_amount * 0.6; // 60% payment
                    Payment::create([
                        'invoice_id' => $invoice->id,
                        'clinic_id' => $clinic->id,
                        'amount' => $partialAmount,
                        'method' => $paymentMethods[array_rand($paymentMethods)],
                        'payment_date' => $invoiceDate->copy()->addDays(rand(1, 10)),
                        'reference_number' => 'REF' . rand(100000, 999999),
                        'processed_by' => 'System Admin',
                        'notes' => 'Partial payment received',
                    ]);
                }
            }

            // Mark sent invoices as sent
            if (in_array($status, ['sent', 'overdue'])) {
                $invoice->update(['sent_at' => $invoiceDate->copy()->addDay()]);
            }
        }

        $this->command->info('Created 15 sample invoices with items and payments for clinic: ' . $clinic->clinic_name);
    }
}
