<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class MockPaymentController extends Controller
{
    /**
     * Show mock payment authorization page (simulates GCash/GrabPay/Maya)
     */
    public function showAuthorizePage(Request $request)
    {
        $source = $request->query('source');
        $type = $request->query('type', 'gcash');
        
        // Get the callback URL from session or generate one
        $callbackUrl = route('payment.callback');
        
        return Inertia::render('MockPayment/Authorize', [
            'sourceId' => $source,
            'paymentType' => $type,
            'callbackUrl' => $callbackUrl,
            'amount' => 1499.00, // Example amount
        ]);
    }

    /**
     * Handle mock payment completion
     */
    public function complete(Request $request)
    {
        $status = $request->input('status', 'success');
        $sourceId = $request->input('source');
        
        // Store the source as chargeable in session
        if ($status === 'success') {
            session(['mock_payment_status_' . $sourceId => 'chargeable']);
        }
        
        // Redirect back to callback URL with GET parameters (no CSRF needed)
        return redirect()->route('payment.callback', [
            'source' => $sourceId,
            'status' => $status === 'success' ? 'success' : 'failed',
        ]);
    }
}
