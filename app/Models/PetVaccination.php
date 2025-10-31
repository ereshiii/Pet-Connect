<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class PetVaccination extends Model
{
    use HasFactory;

    protected $fillable = [
        'pet_id',
        'vaccine_name',
        'administered_date',
        'expiry_date',
        'veterinarian_id',
        'clinic_id',
        'notes',
    ];

    protected $casts = [
        'administered_date' => 'date',
        'expiry_date' => 'date',
    ];

    /**
     * Get the pet that owns the vaccination record.
     */
    public function pet(): BelongsTo
    {
        return $this->belongsTo(Pet::class);
    }

    /**
     * Get the veterinarian associated with this vaccination.
     */
    public function veterinarian(): BelongsTo
    {
        return $this->belongsTo(User::class, 'veterinarian_id');
    }

    /**
     * Get the clinic associated with this vaccination.
     */
    public function clinic(): BelongsTo
    {
        return $this->belongsTo(ClinicRegistration::class, 'clinic_id');
    }

    /**
     * Check if the vaccination is expired.
     */
    public function getIsExpiredAttribute(): bool
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    /**
     * Check if the vaccination is due soon (within 30 days).
     */
    public function getIsDueSoonAttribute(): bool
    {
        return $this->expiry_date && $this->expiry_date->isBefore(now()->addDays(30));
    }

    /**
     * Get the status of this vaccination.
     */
    public function getStatusAttribute(): string
    {
        if ($this->is_expired) {
            return 'expired';
        }

        if ($this->is_due_soon) {
            return 'due_soon';
        }

        return 'current';
    }

    /**
     * Get days until expiry.
     */
    public function getDaysUntilExpiryAttribute(): ?int
    {
        if (!$this->expiry_date) {
            return null;
        }

        return now()->diffInDays($this->expiry_date, false);
    }

    /**
     * Scope to get current vaccinations (not expired).
     */
    public function scopeCurrent($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expiry_date')
              ->orWhere('expiry_date', '>=', now());
        });
    }

    /**
     * Scope to get expired vaccinations.
     */
    public function scopeExpired($query)
    {
        return $query->where('expiry_date', '<', now());
    }

    /**
     * Scope to get vaccinations due soon.
     */
    public function scopeDueSoon($query, int $days = 30)
    {
        return $query->where('expiry_date', '<=', now()->addDays($days))
                    ->where('expiry_date', '>=', now());
    }
}