<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'middle_name',
        'phone',
        'date_of_birth',
        'gender',
        'occupation',
        'bio',
        'profile_image',
        'preferred_language',
        'timezone',
        'emergency_contact_name',
        'emergency_contact_phone',
        'emergency_contact_relationship',
        'preferences',
        'address',
        'city',
        'province',
        'region',
        'barangay',
        'zip_code',
        'latitude',
        'longitude',
        'profile_completed_at',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'preferences' => 'array',
        'profile_completed_at' => 'datetime',
    ];

    /**
     * Get the profile image URL.
     */
    public function getProfileImageUrlAttribute(): ?string
    {
        return $this->profile_image ? asset('storage/' . $this->profile_image) : null;
    }

    /**
     * Get the user that owns the profile.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user's full name.
     */
    public function getFullNameAttribute(): string
    {
        $parts = array_filter([$this->first_name, $this->middle_name, $this->last_name]);
        return implode(' ', $parts) ?: 'Unknown User';
    }

    /**
     * Get the user's initials.
     */
    public function getInitialsAttribute(): string
    {
        $initials = '';
        if ($this->first_name) {
            $initials .= strtoupper(substr($this->first_name, 0, 1));
        }
        if ($this->last_name) {
            $initials .= strtoupper(substr($this->last_name, 0, 1));
        }
        return $initials ?: 'U';
    }

    /**
     * Get the user's age.
     */
    public function getAgeAttribute(): ?int
    {
        return $this->date_of_birth ? $this->date_of_birth->age : null;
    }

    /**
     * Get profile completion percentage.
     */
    public function getCompletionPercentageAttribute(): int
    {
        $fields = [
            'first_name',
            'last_name',
            'phone',
            'date_of_birth',
            'gender',
            'occupation',
            'bio'
        ];

        $completedFields = 0;
        foreach ($fields as $field) {
            if (!empty($this->$field)) {
                $completedFields++;
            }
        }

        return (int) round(($completedFields / count($fields)) * 100);
    }

    /**
     * Check if emergency contact information is complete.
     */
    public function hasEmergencyContact(): bool
    {
        return !empty($this->emergency_contact_name) && !empty($this->emergency_contact_phone);
    }

    /**
     * Get formatted phone number.
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
}