<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'clinic_id', // FK to clinic_registrations.id (transaction table pattern)
        'payment_number',
        'amount',
        'method',
        'status',
        'payment_date',
        'reference_number',
        'processed_by',
        'notes',
        'metadata',
        'refunded_amount',
        'refunded_at',
        'refund_reason',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'refunded_amount' => 'decimal:2',
        'payment_date' => 'date',
        'refunded_at' => 'datetime',
        'metadata' => 'array',
    ];

    // Relationships
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Get the clinic registration that owns this payment.
     * Note: clinic_id references clinic_registrations.id (not clinics.id)
     * This follows the dual-ID pattern where clinic_registrations.id is used for transactions.
     */
    public function clinic(): BelongsTo
    {
        return $this->belongsTo(ClinicRegistration::class, 'clinic_id');
    }

    // Accessors
    public function getFormattedAmountAttribute(): string
    {
        return '₱' . number_format($this->amount, 2);
    }

    public function getMethodDisplayAttribute(): string
    {
        $methods = [
            'cash' => 'Cash',
            'card' => 'Credit/Debit Card',
            'bank_transfer' => 'Bank Transfer',
            'gcash' => 'GCash',
            'paymaya' => 'PayMaya',
            'check' => 'Check',
            'other' => 'Other',
        ];

        return $methods[$this->method] ?? ucfirst($this->method);
    }

    public function getStatusDisplayAttribute(): string
    {
        $statuses = [
            'pending' => 'Pending',
            'completed' => 'Completed',
            'failed' => 'Failed',
            'refunded' => 'Refunded',
            'cancelled' => 'Cancelled',
        ];

        return $statuses[$this->status] ?? ucfirst($this->status);
    }

    // Scopes
    public function scopeForClinic($query, $clinicId)
    {
        return $query->where('clinic_id', $clinicId);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    // Methods
    public function generatePaymentNumber(): string
    {
        $clinic = $this->clinic;
        $year = $this->payment_date->format('Y');
        $sequence = Payment::forClinic($this->clinic_id)
            ->whereYear('payment_date', $year)
            ->count() + 1;
        
        return sprintf('PAY-%s-%s-%04d', $clinic->clinic_code ?? 'CLI', $year, $sequence);
    }

    public function refund(float $amount, string $reason = null): bool
    {
        if ($this->status !== 'completed') {
            return false;
        }

        if ($amount > ($this->amount - $this->refunded_amount)) {
            return false;
        }

        $this->refunded_amount += $amount;
        $this->refunded_at = now();
        $this->refund_reason = $reason;

        if ($this->refunded_amount >= $this->amount) {
            $this->status = 'refunded';
        }

        $this->save();

        // Update the invoice payment status
        $this->invoice->updatePaymentStatus();

        return true;
    }

    // Boot method to auto-generate payment number and update invoice
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($payment) {
            // Set payment_date to now if not provided
            if (!$payment->payment_date) {
                $payment->payment_date = now();
            }
            
            if (!$payment->payment_number) {
                $payment->payment_number = $payment->generatePaymentNumber();
            }
        });

        static::saved(function ($payment) {
            if ($payment->invoice) {
                $payment->invoice->updatePaymentStatus();
            }
        });
    }
}
