<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClinicAddress extends Model
{
    use HasFactory;

    protected $fillable = [
        'clinic_id',
        'type',
        'address_line_1',
        'address_line_2',
        'city',
        'state',
        'postal_code',
        'country',
        'latitude',
        'longitude',
        'is_primary',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'is_primary' => 'boolean',
    ];

    /**
     * Get the clinic registration that owns this address.
     * Note: This table is deprecated and will be removed.
     * Address data is stored directly in clinic_registrations.
     */
    public function clinic(): BelongsTo
    {
        return $this->belongsTo(ClinicRegistration::class, 'clinic_id');
    }

    /**
     * Get the full address string.
     */
    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->address_line_1,
            $this->address_line_2,
            $this->city,
            $this->state,
            $this->postal_code,
        ]);
        
        return implode(', ', $parts);
    }

    /**
     * Scope to get primary addresses.
     */
    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    /**
     * Scope to get active addresses.
     */
    public function scopeActive($query)
    {
        return $query; // Remove this scope since is_active doesn't exist
    }
}
