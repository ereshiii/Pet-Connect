<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Hash;

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
     * Get the users who have favorited this clinic.
     */
    public function favoritedBy(): HasMany
    {
        return $this->hasMany(UserClinicFavorite::class);
    }

    /**
     * Get the users who have favorited this clinic through the pivot.
     */
    public function favoriteUsers(): HasMany
    {
        return $this->hasManyThrough(
            User::class,
            UserClinicFavorite::class,
            'clinic_registration_id',
            'id',
            'id',
            'user_id'
        );
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

        // Create the clinic record first
        $this->createClinicRecord();
        
        // Create individual clinic service records
        $this->createClinicServices();
        
        // Create staff records from veterinarians data
        $this->createClinicStaff();
        
        // Create operating hours records
        $this->createOperatingHours();
    }

    /**
     * Create the main clinic record.
     */
    protected function createClinicRecord(): void
    {
        // Check if clinic already exists
        if ($this->clinic) {
            return;
        }

        Clinic::create([
            'registration_id' => $this->id,
            'name' => $this->clinic_name,
            'license_number' => $this->license_number ?? 'LIC-' . $this->id,
            'type' => 'general', // Default type
            'description' => $this->clinic_description,
            'services' => $this->services,
            'specialties' => [], // Can be populated later
            'phone' => $this->phone,
            'email' => $this->email,
            'website' => $this->website,
            'social_media' => null,
            'status' => 'active',
        ]);
    }

    /**
     * Create individual ClinicService records from the services array.
     */
    protected function createClinicServices(): void
    {
        if (empty($this->services)) {
            return;
        }

        $clinic = $this->clinic;
        if (!$clinic) {
            return;
        }

        // Clear existing services
        ClinicService::where('clinic_id', $clinic->id)->delete();

        // Create new services
        foreach ($this->services as $serviceData) {
            ClinicService::create([
                'clinic_id' => $clinic->id,
                'name' => $serviceData['name'] ?? '',
                'description' => $serviceData['description'] ?? '',
                'category' => $serviceData['category'] ?? 'other',
                'base_price' => $serviceData['base_price'] ?? null,
                'duration_minutes' => $serviceData['duration_minutes'] ?? 30,
                'is_active' => true, // Start all services as active
                'requires_appointment' => $serviceData['requires_appointment'] ?? true,
                'is_emergency_service' => $serviceData['is_emergency_service'] ?? false,
            ]);
        }
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

    /**
     * Create staff records from veterinarians data.
     */
    protected function createClinicStaff(): void
    {
        if (empty($this->veterinarians)) {
            return;
        }

        $clinic = $this->clinic;
        if (!$clinic) {
            return;
        }

        // Clear existing staff records (except owner)
        ClinicStaff::where('clinic_id', $clinic->id)
            ->where('role', '!=', 'owner')
            ->delete();

        // Create staff from veterinarians data
        foreach ($this->veterinarians as $vetData) {
            if (empty($vetData['name'])) continue;

            // Create staff record directly without user account
            ClinicStaff::create([
                'clinic_id' => $clinic->id,
                'name' => $vetData['name'],
                'email' => $vetData['email'] ?? null,
                'phone' => $vetData['phone'] ?? null,
                'role' => 'veterinarian',
                'license_number' => $vetData['license_number'] ?? null,
                'specializations' => !empty($vetData['specialization']) ? [$vetData['specialization']] : [],
                'start_date' => now(),
                'is_active' => true,
            ]);
        }

        // Add the clinic owner as a staff member if not already exists
        $ownerExists = ClinicStaff::where('clinic_id', $clinic->id)
            ->where('user_id', $this->user_id)
            ->exists();

        if (!$ownerExists && $this->user) {
            ClinicStaff::create([
                'clinic_id' => $clinic->id,
                'user_id' => $this->user_id, // Keep user relationship for owner
                'name' => $this->user->name,
                'email' => $this->user->email,
                'phone' => $this->user->phone,
                'role' => 'owner',
                'license_number' => null,
                'specializations' => ['General Management'],
                'start_date' => $this->created_at ?? now(),
                'is_active' => true,
            ]);
        }
    }

    /**
     * Create operating hours records.
     */
    protected function createOperatingHours(): void
    {
        if (empty($this->operating_hours)) {
            return;
        }

        $clinic = $this->clinic;
        if (!$clinic) {
            return;
        }

        // Clear existing operating hours
        ClinicOperatingHour::where('clinic_id', $clinic->id)->delete();

        // Create operating hours from registration data
        foreach ($this->operating_hours as $hourData) {
            if (empty($hourData['day'])) continue;

            $day = strtolower($hourData['day']);
            $isClosed = $hourData['is_closed'] ?? false;

            ClinicOperatingHour::create([
                'clinic_id' => $clinic->id,
                'day_of_week' => $day,
                'is_closed' => $isClosed,
                'opening_time' => !$isClosed && !empty($hourData['opening_time']) ? $hourData['opening_time'] : null,
                'closing_time' => !$isClosed && !empty($hourData['closing_time']) ? $hourData['closing_time'] : null,
                'break_start_time' => !$isClosed && !empty($hourData['break_start']) ? $hourData['break_start'] : null,
                'break_end_time' => !$isClosed && !empty($hourData['break_end']) ? $hourData['break_end'] : null,
            ]);
        }
    }
}
