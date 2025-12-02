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
        'clinic_photo',
        'gallery',
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
        'is_emergency_clinic',
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
        'gallery' => 'array',
        'is_24_hours' => 'boolean',
        'is_featured' => 'boolean',
        'is_open_24_7' => 'boolean',
        'is_emergency_clinic' => 'boolean',
        'rating' => 'decimal:2',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    /**
     * Get the clinic photo URL.
     */
    public function getClinicPhotoUrlAttribute(): ?string
    {
        return $this->clinic_photo ? asset('storage/' . $this->clinic_photo) : null;
    }

    /**
     * Get the gallery images URLs.
     */
    public function getGalleryUrlAttribute(): array
    {
        if (!$this->gallery || !is_array($this->gallery)) {
            return [];
        }
        return array_map(fn($image) => asset('storage/' . $image), $this->gallery);
    }

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
     * Get the services offered by this clinic.
     * Note: clinic_services.clinic_id now directly references clinic_registrations.id
     */
    public function clinicServices(): HasMany
    {
        return $this->hasMany(ClinicService::class, 'clinic_id');
    }

    /**
     * Get the staff members for this clinic.
     * Note: clinic_staff.clinic_id now directly references clinic_registrations.id
     */
    public function staff(): HasMany
    {
        return $this->hasMany(ClinicStaff::class, 'clinic_id');
    }

    /**
     * Get the operating hours for this clinic.
     * Note: clinic_operating_hours.clinic_id now directly references clinic_registrations.id
     */
    public function operatingHours(): HasMany
    {
        return $this->hasMany(ClinicOperatingHour::class, 'clinic_id');
    }

    // Note: clinic_addresses and clinic_equipment tables have been removed
    // Address data is stored directly in clinic_registrations table
    // Equipment tracking is not needed for this application

    /**
     * Get the clinic's veterinarians.
     */
    public function veterinarians(): HasMany
    {
        return $this->staff()->where('role', 'veterinarian');
    }

    /**
     * Get the appointments for this clinic registration.
     * Note: appointments.clinic_id references clinic_registrations.id (this model's ID), not clinics.id
     */
    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'clinic_id');
    }

    /**
     * Get the invoices for this clinic registration.
     * Note: invoices.clinic_id directly references clinic_registrations.id (this model's ID)
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'clinic_id');
    }

    /**
     * Get the payments for this clinic registration.
     * Note: payments.clinic_id directly references clinic_registrations.id (this model's ID)
     */
    public function payments(): HasMany
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
     * Get the reviews for this clinic.
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(ClinicReview::class);
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
     * Note: This method is now a no-op since the clinics table has been eliminated.
     * All data now resides in clinic_registrations.
     */
    protected function createClinicRecord(): void
    {
        // No-op: clinics table no longer exists
        // All clinic data is now stored directly in clinic_registrations
        return;
    }

    /**
     * Create individual ClinicService records from the services array.
     * Note: clinic_services.clinic_id now references clinic_registrations.id
     */
    protected function createClinicServices(): void
    {
        if (empty($this->services)) {
            return;
        }

        // Clear existing services (clinic_services.clinic_id references clinic_registrations.id)
        ClinicService::where('clinic_id', $this->id)->delete();

        // Create new services
        foreach ($this->services as $serviceData) {
            ClinicService::create([
                'clinic_id' => $this->id, // References clinic_registrations.id
                'name' => $serviceData['name'] ?? '',
                'description' => $serviceData['description'] ?? '',
                'category' => $serviceData['category'] ?? 'other',
                'duration_minutes' => $serviceData['duration_minutes'] ?? 30,
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
     * Note: clinic_staff.clinic_id now references clinic_registrations.id
     */
    protected function createClinicStaff(): void
    {
        if (empty($this->veterinarians)) {
            return;
        }

        // Clear existing staff records (except owner)
        // clinic_staff.clinic_id references clinic_registrations.id
        ClinicStaff::where('clinic_id', $this->id)
            ->where('role', '!=', 'owner')
            ->delete();

        // Create staff from veterinarians data
        foreach ($this->veterinarians as $vetData) {
            if (empty($vetData['name'])) continue;

            // Get specializations - check both 'specializations' (plural) and 'specialization' (singular)
            $specializations = [];
            if (!empty($vetData['specializations'])) {
                $specializations = is_array($vetData['specializations']) ? $vetData['specializations'] : [$vetData['specializations']];
            } elseif (!empty($vetData['specialization'])) {
                $specializations = [$vetData['specialization']];
            }

            // Create staff record directly without user account
            ClinicStaff::create([
                'clinic_id' => $this->id, // References clinic_registrations.id
                'name' => $vetData['name'],
                'email' => $vetData['email'] ?? null,
                'phone' => $vetData['phone'] ?? null,
                'role' => 'veterinarian',
                'license_number' => $vetData['license_number'] ?? null,
                'specializations' => $specializations,
                'start_date' => now(),
            ]);
        }

        // Add the clinic owner as a staff member if not already exists
        $ownerExists = ClinicStaff::where('clinic_id', $this->id)
            ->where('user_id', $this->user_id)
            ->exists();

        if (!$ownerExists && $this->user) {
            ClinicStaff::create([
                'clinic_id' => $this->id, // References clinic_registrations.id
                'user_id' => $this->user_id, // Keep user relationship for owner
                'name' => $this->user->name,
                'email' => $this->user->email,
                'phone' => $this->user->phone,
                'role' => 'owner',
                // Some deployments enforce NOT NULL on license_number; use empty string when missing
                'license_number' => $this->user->license_number ?? '',
                'specializations' => ['General Management'],
                'start_date' => $this->created_at ?? now(),
            ]);
        }
    }

    /**
     * Create operating hours records.
     * Note: clinic_operating_hours.clinic_id now references clinic_registrations.id
     */
    protected function createOperatingHours(): void
    {
        if (empty($this->operating_hours)) {
            return;
        }

        // Clear existing operating hours (clinic_operating_hours.clinic_id references clinic_registrations.id)
        ClinicOperatingHour::where('clinic_id', $this->id)->delete();

        // Handle both formats: array of objects or keyed object
        $operatingHours = $this->operating_hours;
        
        // If it's a keyed object (e.g., {monday: {open, close}, tuesday: {...}})
        if (isset($operatingHours['monday']) || isset($operatingHours['tuesday'])) {
            foreach ($operatingHours as $day => $hours) {
                if (!is_array($hours)) continue;
                
                $day = strtolower($day);
                $open = $hours['open'] ?? $hours['opening_time'] ?? null;
                $close = $hours['close'] ?? $hours['closing_time'] ?? null;
                $isClosed = empty($open) && empty($close);

                ClinicOperatingHour::create([
                    'clinic_id' => $this->id, // References clinic_registrations.id
                    'day_of_week' => $day,
                    'is_closed' => $isClosed,
                    'opening_time' => !$isClosed && !empty($open) ? $open : null,
                    'closing_time' => !$isClosed && !empty($close) ? $close : null,
                    'break_start_time' => null,
                    'break_end_time' => null,
                ]);
            }
        } else {
            // Handle array format [{day: 'monday', opening_time, closing_time, ...}]
            foreach ($operatingHours as $hourData) {
                if (empty($hourData['day'])) continue;

                $day = strtolower($hourData['day']);
                $isClosed = $hourData['is_closed'] ?? false;

                ClinicOperatingHour::create([
                    'clinic_id' => $this->id, // References clinic_registrations.id
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
}
