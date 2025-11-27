<?php

namespace App\Http\Controllers\ClinicSettings;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class PaymentMethodsController extends Controller
{
    public function index(): Response
    {
        $user = Auth::user();
        
        // Get saved payment methods from payment_methods table
        $paymentMethods = $user->paymentMethods()->orderByDesc('is_default')->orderByDesc('created_at')->get()->map(function ($method) {
            return [
                'id' => $method->id,
                'type' => $method->type,
                'last_four' => $method->last_four,
                'brand' => $method->brand,
                'exp_month' => $method->exp_month,
                'exp_year' => $method->exp_year,
                'is_default' => $method->is_default,
                'created_at' => $method->created_at,
            ];
        });
        
        return Inertia::render('2clinicPages/settings/PaymentMethods', [
            'paymentMethods' => $paymentMethods,
        ]);
    }
}
