<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClinicRegistration extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'clinic_name',
        'clinic_description',
        'website',
        'email',
        'phone',
        'country',
        'region',
        'province',
        'city',
        'barangay',
        'street_address',
        'postal_code',
        'latitude',
        'longitude',
        'rating',
        'total_reviews',
        'is_featured',
        'is_open_24_7',
        'operating_hours',
        'is_24_hours',
        'services',
        'other_services',
        'veterinarians',
        'certification_proofs',
        'additional_info',
        'status',
        'rejection_reason',
        'submitted_at',
        'approved_at',
        'approved_by',
    ];

    protected $casts = [
        'operating_hours' => 'array',
        'services' => 'array',
        'veterinarians' => 'array',
        'certification_proofs' => 'array',
        'is_24_hours' => 'boolean',
        'is_featured' => 'boolean',
        'is_open_24_7' => 'boolean',
        'rating' => 'decimal:2',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    /**
     * Get the user that owns the clinic registration.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the admin who approved the registration.
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the organized clinic created from this registration.
     */
    public function clinic(): HasOne
    {
        return $this->hasOne(Clinic::class, 'registration_id');
    }

    /**
     * Get the services offered by this clinic.
     */
    public function clinicServices()
    {
        return $this->hasMany(ClinicService::class, 'clinic_id');
    }

    /**
     * Get the appointments for this clinic.
     */
    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'clinic_id');
    }

    /**
     * Get the invoices for this clinic.
     */
    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'clinic_id');
    }

    /**
     * Get the payments for this clinic.
     */
    public function payments()
    {
        return $this->hasMany(Payment::class, 'clinic_id');
    }

    /**
     * Check if the registration is complete.
     */
    public function isComplete(): bool
    {
        return !empty($this->clinic_name) && 
               !empty($this->email) && 
               !empty($this->phone) && 
               !empty($this->region) && 
               !empty($this->province) && 
               !empty($this->city) && 
               !empty($this->barangay) && 
               !empty($this->street_address) && 
               !empty($this->latitude) && 
               !empty($this->longitude) && 
               !empty($this->operating_hours) && 
               !empty($this->services) && 
               !empty($this->veterinarians);
               // Note: certification_proofs is optional for now, can be added later by admin
    }

    /**
     * Mark as submitted for review.
     */
    public function markAsSubmitted(): void
    {
        $this->update([
            'status' => 'pending',
            'submitted_at' => now(),
        ]);
    }

    /**
     * Approve the registration.
     */
    public function approve(User $approvedBy): void
    {
        $this->update([
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => $approvedBy->id,
            'rejection_reason' => null,
        ]);
    }

    /**
     * Reject the registration.
     */
    public function reject(string $reason): void
    {
        $this->update([
            'status' => 'rejected',
            'rejection_reason' => $reason,
            'approved_at' => null,
            'approved_by' => null,
        ]);
    }

    /**
     * Suspend the registration.
     */
    public function suspend(string $reason): void
    {
        $this->update([
            'status' => 'suspended',
            'rejection_reason' => $reason, // Using rejection_reason field for suspension reason too
        ]);
    }

    /**
     * Get the rating as a float.
     */
    public function getRatingAttribute($value): float
    {
        return (float) $value ?? 0.0;
    }

    /**
     * Get the star rating display.
     */
    public function getStarsAttribute(): string
    {
        $rating = (float) $this->attributes['rating'] ?? 0.0;
        $fullStars = floor($rating);
        $halfStar = ($rating - $fullStars) >= 0.5 ? 1 : 0;
        $emptyStars = 5 - $fullStars - $halfStar;
        
        return str_repeat('★', $fullStars) . 
               str_repeat('☆', $halfStar) . 
               str_repeat('☆', $emptyStars);
    }

    /**
     * Get formatted address.
     */
    public function getFullAddressAttribute(): string
    {
        return collect([
            $this->street_address,
            $this->barangay,
            $this->city,
            $this->province
        ])->filter()->implode(', ');
    }

    /**
     * Check if clinic is currently open (simplified logic).
     */
    public function getIsOpenAttribute(): bool
    {
        if ($this->is_open_24_7) {
            return true;
        }

        // Simplified logic - in real app you'd check actual operating hours
        $currentHour = now()->hour;
        return $currentHour >= 8 && $currentHour < 18; // Assume 8 AM - 6 PM
    }

    /**
     * Get status color class.
     */
    public function getStatusColorAttribute(): string
    {
        return $this->is_open ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400';
    }

    /**
     * Get display status.
     */
    public function getDisplayStatusAttribute(): string
    {
        if ($this->is_open_24_7) {
            return 'Open 24/7';
        }
        
        return $this->is_open ? 'Open Now' : 'Closed';
    }
}
