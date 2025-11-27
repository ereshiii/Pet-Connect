<?php

use App\Http\Controllers\ClinicSettings\BillingHistoryController;
use App\Http\Controllers\ClinicSettings\ClinicGalleryController;
use App\Http\Controllers\ClinicSettings\ClinicProfileController;
use App\Http\Controllers\ClinicSettings\CurrentPlanController;
use App\Http\Controllers\ClinicSettings\PaymentMethodsController;
use App\Http\Controllers\ClinicSettings\SubscriptionSettingsController;
use App\Http\Controllers\Settings\PasswordController;
use App\Http\Controllers\Settings\ProfileController;
use App\Http\Controllers\Settings\TwoFactorAuthenticationController;
use App\Http\Middleware\EnsureUserIsClinic;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', EnsureUserIsClinic::class])->prefix('clinic/settings')->name('clinic.settings.')->group(function () {
    
    // Default redirect
    Route::redirect('/', '/clinic/settings/profile');

    // Profile (personal)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/photo', [ProfileController::class, 'updatePhoto'])->name('profile.photo');
    
    // Clinic Profile (business)
    Route::get('/clinic-profile', [ClinicProfileController::class, 'edit'])->name('clinic-profile.edit');
    Route::patch('/clinic-profile', [ClinicProfileController::class, 'update'])->name('clinic-profile.update');
    
    // Contact Information and Address consolidated into Clinic Profile above
    
    // Clinic Gallery
    Route::get('/clinic-gallery', [ClinicGalleryController::class, 'edit'])->name('clinic-gallery.edit');
    Route::post('/clinic-gallery', [ClinicGalleryController::class, 'update'])->name('clinic-gallery.update');
    
    // Two-Factor Authentication
    Route::get('/two-factor', [TwoFactorAuthenticationController::class, 'show'])->name('two-factor.show');
    
    // Appearance - using AppearanceTabs component which handles its own state
    Route::get('/appearance', function () {
        $user = auth()->user();
        $clinicRegistration = \App\Models\ClinicRegistration::where('user_id', $user->id)->first();
        
        return inertia('2clinicPages/settings/Appearance', [
            'clinicRegistration' => $clinicRegistration ? [
                'id' => $clinicRegistration->id,
                'clinic_name' => $clinicRegistration->clinic_name,
                'clinic_description' => $clinicRegistration->clinic_description,
                'clinic_photo' => $clinicRegistration->clinic_photo ? asset('storage/' . $clinicRegistration->clinic_photo) : null,
                'rating' => $clinicRegistration->rating ?? 0,
                'total_reviews' => $clinicRegistration->total_reviews ?? 0,
                'region' => $clinicRegistration->region,
                'province' => $clinicRegistration->province,
                'city' => $clinicRegistration->city,
            ] : null,
        ]);
    })->name('appearance.edit');
    
    Route::post('/appearance/photo', [ClinicProfileController::class, 'updatePhoto'])->name('appearance.photo');
    
    // Password
    Route::get('/password', [PasswordController::class, 'edit'])->name('password.edit');
    Route::put('/password', [PasswordController::class, 'update'])->name('password.update');
    
    // Current Plan
    Route::get('/current-plan', [CurrentPlanController::class, 'show'])->name('current-plan.show');
    
    // Payment Methods
    Route::get('/payment-methods', [PaymentMethodsController::class, 'index'])->name('payment-methods.index');
    
    // Billing History
    Route::get('/billing-history', [BillingHistoryController::class, 'index'])->name('billing-history.index');
    
    // Subscription & Payments
    Route::get('/subscription', [SubscriptionSettingsController::class, 'edit'])->name('subscription.edit');
    Route::post('/subscription/payment-method', [SubscriptionSettingsController::class, 'addPaymentMethod'])->name('subscription.payment-method.add');
    Route::delete('/subscription/payment-method/{payment_method_id}', [SubscriptionSettingsController::class, 'removePaymentMethod'])->name('subscription.payment-method.remove');
    Route::post('/subscription/payment-method/default', [SubscriptionSettingsController::class, 'setDefaultPaymentMethod'])->name('subscription.payment-method.default');
});
