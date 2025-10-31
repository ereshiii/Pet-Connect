<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'service_id',
        'item_type',
        'name',
        'description',
        'quantity',
        'unit_price',
        'discount_amount',
        'line_total',
        'metadata',
        'sort_order',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'line_total' => 'decimal:2',
        'metadata' => 'array',
        'sort_order' => 'integer',
    ];

    // Relationships
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(ClinicService::class, 'service_id');
    }

    // Accessors
    public function getFormattedUnitPriceAttribute(): string
    {
        return '₱' . number_format($this->unit_price, 2);
    }

    public function getFormattedLineTotalAttribute(): string
    {
        return '₱' . number_format($this->line_total, 2);
    }

    // Methods
    public function calculateLineTotal(): void
    {
        $this->line_total = ($this->quantity * $this->unit_price) - $this->discount_amount;
    }

    // Boot method to auto-calculate line total
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($item) {
            $item->calculateLineTotal();
        });
    }
}
