<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentMethod extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'provider_id',
        'last_four',
        'brand',
        'exp_month',
        'exp_year',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    /**
     * Get the user that owns the payment method.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the display name for the payment method type.
     */
    public function getTypeLabel(): string
    {
        return match($this->type) {
            'card' => 'Credit/Debit Card',
            'gcash' => 'GCash',
            'grab_pay' => 'GrabPay',
            'paymaya' => 'Maya',
            default => ucfirst($this->type),
        };
    }

    /**
     * Get masked card number for display.
     */
    public function getMaskedNumber(): string
    {
        if ($this->type === 'card' && $this->last_four) {
            return '•••• •••• •••• ' . $this->last_four;
        }
        return '';
    }
}

