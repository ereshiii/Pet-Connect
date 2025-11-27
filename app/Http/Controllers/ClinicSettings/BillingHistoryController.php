<?php

namespace App\Http\Controllers\ClinicSettings;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class BillingHistoryController extends Controller
{
    public function index(): Response
    {
        $user = Auth::user();
        
        // Get billing history from payments table for subscription payments
        $billingHistory = Payment::where('user_id', $user->id)
            ->where('payment_type', 'subscription')
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($payment) {
                return [
                    'id' => $payment->id,
                    'invoice_number' => $payment->invoice_number ?? 'INV-' . str_pad($payment->id, 6, '0', STR_PAD_LEFT),
                    'amount' => $payment->amount,
                    'status' => $payment->status,
                    'payment_method' => $payment->payment_method,
                    'created_at' => $payment->created_at,
                    'description' => $payment->description ?? 'Subscription Payment',
                ];
            });
        
        return Inertia::render('2clinicPages/settings/BillingHistory', [
            'billingHistory' => $billingHistory,
        ]);
    }
}
