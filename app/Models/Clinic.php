<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Clinic extends Model
{
    use HasFactory;

    protected $fillable = [
        'registration_id',
        'name',
        'license_number',
        'type',
        'description',
        'services',
        'specialties',
        'phone',
        'email',
        'website',
        'social_media',
        'status',
        'average_rating',
        'total_reviews',
        'last_review_at',
    ];

    protected $casts = [
        'services' => 'array',
        'specialties' => 'array',
        'social_media' => 'array',
        'average_rating' => 'decimal:2',
        'total_reviews' => 'integer',
        'last_review_at' => 'datetime',
    ];

    /**
     * Get the clinic registration that this clinic was created from.
     */
    public function registration(): BelongsTo
    {
        return $this->belongsTo(ClinicRegistration::class, 'registration_id');
    }

    /**
     * Get the clinic's addresses.
     */
    public function addresses(): HasMany
    {
        return $this->hasMany(ClinicAddress::class);
    }

    /**
     * Get the clinic's primary address.
     */
    public function primaryAddress(): HasOne
    {
        return $this->hasOne(ClinicAddress::class)->where('is_primary', true);
    }

    /**
     * Get the clinic's operating hours.
     */
    public function operatingHours(): HasMany
    {
        return $this->hasMany(ClinicOperatingHour::class);
    }

    /**
     * Get the clinic's staff members.
     */
    public function staff(): HasMany
    {
        return $this->hasMany(ClinicStaff::class);
    }

    /**
     * Get the clinic's services.
     */
    public function clinicServices(): HasMany
    {
        return $this->hasMany(ClinicService::class);
    }

    /**
     * Get the clinic's equipment.
     */
    public function equipment(): HasMany
    {
        return $this->hasMany(ClinicEquipment::class);
    }

    /**
     * Get the clinic's appointments.
     */
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    /**
     * Get the clinic's reviews.
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(ClinicReview::class);
    }

    /**
     * Get the clinic's veterinarians.
     */
    public function veterinarians(): HasMany
    {
        return $this->staff()->where('role', 'veterinarian')->where('is_active', true);
    }

    /**
     * Check if the clinic is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if the clinic is open 24/7.
     */
    public function isOpen24_7(): bool
    {
        $allDaysOpen = $this->operatingHours()
            ->where('is_closed', false)
            ->count() === 7;
            
        return $allDaysOpen && $this->operatingHours()
            ->where('opening_time', '00:00:00')
            ->where('closing_time', '23:59:59')
            ->count() === 7;
    }

    /**
     * Get the clinic's current operating status.
     */
    public function getCurrentOperatingStatus(): array
    {
        $now = now();
        $dayOfWeek = strtolower($now->format('l'));
        
        $todayHours = $this->operatingHours()
            ->where('day_of_week', $dayOfWeek)
            ->first();

        if (!$todayHours || $todayHours->is_closed) {
            return [
                'is_open' => false,
                'status' => 'Closed',
                'message' => 'Closed today'
            ];
        }

        $currentTime = $now->format('H:i:s');
        $isOpen = $currentTime >= $todayHours->opening_time && $currentTime <= $todayHours->closing_time;

        return [
            'is_open' => $isOpen,
            'status' => $isOpen ? 'Open' : 'Closed',
            'message' => $isOpen 
                ? "Open until {$todayHours->closing_time}"
                : "Opens at {$todayHours->opening_time}",
            'hours' => $todayHours
        ];
    }

    /**
     * Get the clinic's type display name.
     */
    public function getTypeDisplayAttribute(): string
    {
        return match($this->type) {
            'general' => 'General Practice',
            'emergency' => 'Emergency Clinic',
            'specialty' => 'Specialty Clinic',
            'mobile' => 'Mobile Veterinary Service',
            default => ucfirst($this->type)
        };
    }

    /**
     * Get the clinic's formatted phone number.
     */
    public function getFormattedPhoneAttribute(): ?string
    {
        if (!$this->phone) {
            return null;
        }

        // Basic Philippine phone number formatting
        $phone = preg_replace('/[^0-9]/', '', $this->phone);
        
        if (strlen($phone) === 11 && substr($phone, 0, 2) === '09') {
            return '+63 ' . substr($phone, 1, 3) . ' ' . substr($phone, 4, 3) . ' ' . substr($phone, 7);
        }
        
        return $this->phone;
    }

    /**
     * Scope to get active clinics.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to get clinics by type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to get emergency clinics.
     */
    public function scopeEmergency($query)
    {
        return $query->where('type', 'emergency');
    }

    /**
     * Get the clinic's average rating.
     */
    public function getAverageRatingAttribute(): float
    {
        return $this->reviews()->avg('rating') ?? 0.0;
    }

    /**
     * Get the total review count.
     */
    public function getTotalReviewsAttribute(): int
    {
        return $this->reviews()->count();
    }

    /**
     * Get star rating display.
     */
    public function getStarsDisplayAttribute(): string
    {
        $rating = $this->average_rating;
        $fullStars = floor($rating);
        $halfStar = ($rating - $fullStars) >= 0.5 ? 1 : 0;
        $emptyStars = 5 - $fullStars - $halfStar;
        
        return str_repeat('★', $fullStars) . 
               str_repeat('⭐', $halfStar) . 
               str_repeat('☆', $emptyStars);
    }

    /**
     * Scope to get clinics open 24/7.
     */
    public function scopeOpen24_7($query)
    {
        return $query->whereHas('operatingHours', function ($q) {
            $q->where('is_closed', false)
              ->where('opening_time', '00:00:00')
              ->where('closing_time', '23:59:59');
        }, '=', 7);
    }
}