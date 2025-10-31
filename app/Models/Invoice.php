<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'clinic_id',
        'appointment_id',
        'patient_id',
        'owner_id',
        'invoice_date',
        'due_date',
        'status',
        'subtotal',
        'tax_rate',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'paid_amount',
        'balance_due',
        'notes',
        'terms',
        'services_summary',
        'sent_at',
        'paid_at',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'balance_due' => 'decimal:2',
        'services_summary' => 'array',
        'sent_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    // Relationships
    public function clinic(): BelongsTo
    {
        return $this->belongsTo(ClinicRegistration::class, 'clinic_id');
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Pet::class, 'patient_id');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    // Accessors & Mutators
    public function getFormattedTotalAttribute(): string
    {
        return '₱' . number_format($this->total_amount, 2);
    }

    public function getFormattedBalanceAttribute(): string
    {
        return '₱' . number_format($this->balance_due, 2);
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->status !== 'paid' && $this->due_date < Carbon::today();
    }

    public function getDaysOverdueAttribute(): int
    {
        if (!$this->is_overdue) {
            return 0;
        }
        return Carbon::today()->diffInDays($this->due_date);
    }

    // Scopes
    public function scopeForClinic($query, $clinicId)
    {
        return $query->where('clinic_id', $clinicId);
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', '!=', 'paid')
                    ->where('due_date', '<', Carbon::today());
    }

    public function scopePending($query)
    {
        return $query->whereIn('status', ['sent', 'overdue']);
    }

    // Methods
    public function generateInvoiceNumber(): string
    {
        $clinic = $this->clinic;
        $year = $this->invoice_date->format('Y');
        $sequence = Invoice::forClinic($this->clinic_id)
            ->whereYear('invoice_date', $year)
            ->count() + 1;
        
        return sprintf('INV-%s-%s-%04d', $clinic->clinic_code ?? 'CLI', $year, $sequence);
    }

    public function calculateTotals(): void
    {
        $this->subtotal = $this->items->sum('line_total');
        $this->tax_amount = ($this->subtotal - $this->discount_amount) * ($this->tax_rate / 100);
        $this->total_amount = $this->subtotal + $this->tax_amount - $this->discount_amount;
        $this->balance_due = $this->total_amount - $this->paid_amount;
    }

    public function markAsPaid(): void
    {
        $this->update([
            'status' => 'paid',
            'paid_amount' => $this->total_amount,
            'balance_due' => 0,
            'paid_at' => now(),
        ]);
    }

    public function updatePaymentStatus(): void
    {
        $totalPaid = $this->payments()->where('status', 'completed')->sum('amount');
        $this->paid_amount = $totalPaid;
        $this->balance_due = $this->total_amount - $totalPaid;

        if ($totalPaid >= $this->total_amount) {
            $this->status = 'paid';
            $this->paid_at = now();
        } elseif ($totalPaid > 0) {
            $this->status = 'partial';
        } elseif ($this->is_overdue) {
            $this->status = 'overdue';
        }

        $this->save();
    }
}
