<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\InvoiceItem;
use App\Models\ClinicRegistration;
use App\Models\Appointment;
use App\Models\ClinicService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Carbon\Carbon;

class ClinicBillingController extends Controller
{
    /**
     * Display billing dashboard with invoices, payments, and financial stats.
     */
    public function index(Request $request): Response
    {
        $user = Auth::user();
        
        if (!$user->isClinic()) {
            abort(403, 'Access denied. Clinic account required.');
        }

        $clinicRegistration = $user->clinicRegistration;
        
        if (!$clinicRegistration) {
            abort(404, 'Clinic registration not found.');
        }

        $clinicId = $clinicRegistration->id;

        // Get filter parameters
        $status = $request->get('status', 'all');
        $dateRange = $request->get('date_range', 'this_month');
        $search = $request->get('search', '');

        // Build invoices query
        $invoicesQuery = Invoice::with(['patient', 'owner', 'items.service', 'payments'])
            ->forClinic($clinicId);

        // Apply status filter
        if ($status !== 'all') {
            $invoicesQuery->where('status', $status);
        }

        // Apply search filter
        if ($search) {
            $invoicesQuery->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('patient', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('owner', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Get invoices (simplified - no pagination for now)
        $invoices = $invoicesQuery->orderBy('invoice_date', 'desc')
            ->get();

        // Transform invoices data
        $transformedInvoices = $invoices->map(function ($invoice) {
            return [
                'id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'patient_name' => $invoice->patient->name ?? 'Unknown',
                'owner_name' => $invoice->owner->name ?? 'Unknown',
                'services' => $invoice->items->pluck('name')->toArray(),
                'subtotal' => $invoice->subtotal,
                'tax' => $invoice->tax_amount,
                'total' => $invoice->total_amount,
                'paid_amount' => $invoice->paid_amount,
                'balance_due' => $invoice->balance_due,
                'status' => $invoice->status,
                'date' => $invoice->invoice_date->format('Y-m-d'),
                'due_date' => $invoice->due_date->format('Y-m-d'),
                'formatted_total' => $invoice->formatted_total,
                'formatted_balance' => $invoice->formatted_balance,
                'is_overdue' => $invoice->is_overdue,
                'days_overdue' => $invoice->days_overdue,
                'created_at' => $invoice->created_at,
            ];
        });

        // Get recent payments
        $recentPayments = Payment::with('invoice')
            ->forClinic($clinicId)
            ->completed()
            ->orderBy('payment_date', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($payment) {
                return [
                    'id' => $payment->id,
                    'invoice_id' => $payment->invoice_id,
                    'amount' => $payment->amount,
                    'method' => $payment->method_display,
                    'date' => $payment->payment_date->format('Y-m-d'),
                    'reference' => $payment->reference_number,
                    'formatted_amount' => $payment->formatted_amount,
                ];
            });

        // Get financial statistics
        $stats = $this->getFinancialStats($clinicId, $dateRange);

        return Inertia::render('2clinicPages/billing/BillingDashboard', [
            'invoices' => $transformedInvoices,
            'recent_payments' => $recentPayments,
            'stats' => $stats,
            'filters' => [
                'status' => $status,
                'date_range' => $dateRange,
                'search' => $search,
            ],
            'clinic' => [
                'id' => $clinicRegistration->id,
                'name' => $clinicRegistration->clinic_name,
            ],
        ]);
    }

    /**
     * Create a new invoice for an appointment.
     */
    public function createInvoice(Request $request)
    {
        $user = Auth::user();
        $clinicRegistration = $user->clinicRegistration;

        $request->validate([
            'appointment_id' => 'required|exists:appointments,id',
            'items' => 'required|array|min:1',
            'items.*.service_id' => 'nullable|exists:clinic_services,id',
            'items.*.name' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount_amount' => 'nullable|numeric|min:0',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'discount_amount' => 'nullable|numeric|min:0',
            'due_date' => 'required|date|after_or_equal:today',
            'notes' => 'nullable|string',
        ]);

        $appointment = Appointment::with(['pet.owner'])
            ->where('clinic_id', $clinicRegistration->id)
            ->findOrFail($request->appointment_id);

        try {
            DB::beginTransaction();

            // Create invoice
            $invoice = Invoice::create([
                'clinic_id' => $clinicRegistration->id,
                'appointment_id' => $appointment->id,
                'patient_id' => $appointment->pet_id,
                'owner_id' => $appointment->pet->owner_id,
                'invoice_date' => Carbon::today(),
                'due_date' => $request->due_date,
                'tax_rate' => $request->tax_rate ?? 0,
                'discount_amount' => $request->discount_amount ?? 0,
                'notes' => $request->notes,
                'total_amount' => 0, // Will be calculated after items
            ]);

            // Generate invoice number
            $invoice->invoice_number = $invoice->generateInvoiceNumber();
            $invoice->save();

            // Create invoice items
            foreach ($request->items as $index => $itemData) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'service_id' => $itemData['service_id'] ?? null,
                    'name' => $itemData['name'],
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'discount_amount' => $itemData['discount_amount'] ?? 0,
                    'sort_order' => $index,
                ]);
            }

            // Calculate totals
            $invoice->refresh();
            $invoice->calculateTotals();
            $invoice->save();

            DB::commit();

            return back()->with('success', 'Invoice created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to create invoice: ' . $e->getMessage()]);
        }
    }

    /**
     * Record a payment for an invoice.
     */
    public function recordPayment(Request $request, $invoiceId)
    {
        $user = Auth::user();
        $clinicRegistration = $user->clinicRegistration;

        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'method' => 'required|in:cash,card,bank_transfer,gcash,paymaya,check,other',
            'payment_date' => 'required|date|before_or_equal:today',
            'reference_number' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $invoice = Invoice::forClinic($clinicRegistration->id)
            ->findOrFail($invoiceId);

        if ($invoice->status === 'paid') {
            return back()->withErrors(['error' => 'Invoice is already fully paid.']);
        }

        if ($request->amount > $invoice->balance_due) {
            return back()->withErrors(['error' => 'Payment amount exceeds balance due.']);
        }

        try {
            Payment::create([
                'invoice_id' => $invoice->id,
                'clinic_id' => $clinicRegistration->id,
                'amount' => $request->amount,
                'method' => $request->method,
                'payment_date' => $request->payment_date,
                'reference_number' => $request->reference_number,
                'processed_by' => $user->name,
                'notes' => $request->notes,
            ]);

            return back()->with('success', 'Payment recorded successfully.');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to record payment: ' . $e->getMessage()]);
        }
    }

    /**
     * Update invoice status.
     */
    public function updateStatus(Request $request, $id)
    {
        $user = Auth::user();
        $clinicRegistration = $user->clinicRegistration;

        $request->validate([
            'status' => 'required|in:draft,sent,paid,overdue,cancelled',
        ]);

        $invoice = Invoice::forClinic($clinicRegistration->id)
            ->findOrFail($id);

        $invoice->update(['status' => $request->status]);

        if ($request->status === 'sent') {
            $invoice->update(['sent_at' => now()]);
        }

        return back()->with('success', 'Invoice status updated successfully.');
    }

    /**
     * Get financial statistics for the clinic.
     */
    private function getFinancialStats($clinicId, $dateRange): array
    {
        $dateFilter = $this->getDateFilter($dateRange);

        $baseQuery = Invoice::forClinic($clinicId);

        // Total revenue (all time)
        $totalRevenue = (clone $baseQuery)->where('status', 'paid')->sum('total_amount');

        // Revenue for the selected period
        $periodRevenue = (clone $baseQuery)
            ->where('status', 'paid')
            ->whereBetween('invoice_date', $dateFilter)
            ->sum('total_amount');

        // Pending invoices count
        $pendingInvoices = (clone $baseQuery)->pending()->count();

        // Overdue amount
        $overdueAmount = (clone $baseQuery)->overdue()->sum('balance_due');

        // Additional stats
        $totalInvoices = (clone $baseQuery)->count();
        $avgInvoiceAmount = $totalInvoices > 0 ? $totalRevenue / $totalInvoices : 0;

        // Monthly revenue trend (last 6 months)
        $monthlyRevenue = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $revenue = (clone $baseQuery)
                ->where('status', 'paid')
                ->whereYear('invoice_date', $month->year)
                ->whereMonth('invoice_date', $month->month)
                ->sum('total_amount');
            
            $monthlyRevenue[] = [
                'month' => $month->format('M Y'),
                'revenue' => $revenue,
            ];
        }

        return [
            'total_revenue' => $totalRevenue,
            'period_revenue' => $periodRevenue,
            'monthly_revenue' => $periodRevenue, // For compatibility with existing component
            'pending_invoices' => $pendingInvoices,
            'overdue_amount' => $overdueAmount,
            'total_invoices' => $totalInvoices,
            'avg_invoice_amount' => $avgInvoiceAmount,
            'monthly_trend' => $monthlyRevenue,
        ];
    }

    /**
     * Get date filter based on range.
     */
    private function getDateFilter($range): array
    {
        $now = Carbon::now();

        switch ($range) {
            case 'today':
                return [$now->copy()->startOfDay(), $now->copy()->endOfDay()];
            case 'this_week':
                return [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()];
            case 'this_month':
                return [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()];
            case 'last_month':
                return [
                    $now->copy()->subMonth()->startOfMonth(),
                    $now->copy()->subMonth()->endOfMonth()
                ];
            case 'this_quarter':
                return [$now->copy()->startOfQuarter(), $now->copy()->endOfQuarter()];
            case 'this_year':
                return [$now->copy()->startOfYear(), $now->copy()->endOfYear()];
            default:
                return [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()];
        }
    }
}
