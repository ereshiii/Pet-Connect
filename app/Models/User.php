<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Cashier\Billable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable, Billable, HasApiTokens;

    /**
     * The relationships that should always be loaded.
     *
     * @var array
     */
    protected $with = ['profile'];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'email',
        'password',
        'account_type',
        'is_admin',
        'is_verified',
        'verification_token',
        'last_login_at',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
        'remember_token',
        // 'name' is a virtual attribute handled by accessor/mutator
        'banned_at',
        'ban_reason',
        'ban_duration',
        'ban_expires_at',
        'ban_notes',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
            'clinic_registration_data' => 'array',
            'banned_at' => 'datetime',
            'ban_expires_at' => 'datetime',
            'last_login_at' => 'datetime',
        ];
    }

    /**
     * Get the clinic registration for this user.
     */
    public function clinicRegistration(): HasOne
    {
        return $this->hasOne(ClinicRegistration::class);
    }

    /**
     * Get the user's profile.
     */
    public function profile(): HasOne
    {
        return $this->hasOne(UserProfile::class);
    }

    /**
     * Get the user's addresses.
     */
    public function addresses(): HasMany
    {
        return $this->hasMany(UserAddress::class);
    }

    /**
     * Get the user's primary address.
     */
    public function primaryAddress(): HasOne
    {
        return $this->hasOne(UserAddress::class)->where('is_primary', true);
    }

    /**
     * Get the user's emergency contacts.
     */
    public function emergencyContacts(): HasMany
    {
        return $this->hasMany(UserEmergencyContact::class);
    }

    /**
     * Get the user's primary emergency contact.
     */
    public function primaryEmergencyContact(): HasOne
    {
        return $this->hasOne(UserEmergencyContact::class)->where('is_primary', true);
    }

    /**
     * Check if user has a primary emergency contact.
     */
    public function hasPrimaryEmergencyContact(): bool
    {
        return $this->emergencyContacts()->where('is_primary', true)->exists();
    }

    /**
     * Get the user's pets.
     */
    public function pets(): HasMany
    {
        return $this->hasMany(Pet::class, 'owner_id');
    }

    /**
     * Get the user's appointments as a pet owner.
     */
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'owner_id');
    }

    /**
     * Get the user's favorited clinics.
     */
    public function favoriteClinics(): HasMany
    {
        return $this->hasMany(UserClinicFavorite::class);
    }

    /**
     * Get the favorited clinic registrations through the pivot.
     */
    public function favoritedClinics(): HasMany
    {
        return $this->hasManyThrough(
            ClinicRegistration::class,
            UserClinicFavorite::class,
            'user_id',
            'id',
            'id',
            'clinic_registration_id'
        );
    }

    /**
     * Get the user's notifications.
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Get the user's device tokens for push notifications.
     */
    public function deviceTokens(): HasMany
    {
        return $this->hasMany(DeviceToken::class);
    }

    /**
     * Get the user's active device tokens.
     */
    public function activeDeviceTokens(): HasMany
    {
        return $this->hasMany(DeviceToken::class)->where('is_active', true);
    }

    /**
     * Get the user's push notifications.
     */
    public function pushNotifications(): HasMany
    {
        return $this->hasMany(PushNotification::class);
    }

    /**
     * Get the user's invoices (as pet owner).
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'owner_id');
    }

    /**
     * Get the user's payment methods.
     */
    public function paymentMethods(): HasMany
    {
        return $this->hasMany(PaymentMethod::class);
    }

    /**
     * Get the user's default payment method.
     */
    public function defaultPaymentMethod(): HasOne
    {
        return $this->hasOne(PaymentMethod::class)->where('is_default', true);
    }

    /**
     * Get the user's full name from their profile.
     *
     * @return string
     */
    public function getNameAttribute(): string
    {
        if ($this->profile) {
            $firstName = $this->profile->first_name ?? '';
            $lastName = $this->profile->last_name ?? '';
            return trim($firstName . ' ' . $lastName) ?: 'Unknown User';
        }
        
        return 'Unknown User';
    }

    /**
     * Set the user's name by updating their profile.
     *
     * @param string $value
     * @return void
     */
    public function setNameAttribute(string $value): void
    {
        // Only proceed if the user has been saved and has an ID
        if (!$this->exists || !$this->id) {
            return;
        }

        $nameParts = explode(' ', trim($value), 2);
        $firstName = $nameParts[0] ?? '';
        $lastName = $nameParts[1] ?? '';

        // Create or update the user profile
        $this->profile()->updateOrCreate(
            ['user_id' => $this->id],
            [
                'first_name' => $firstName,
                'last_name' => $lastName,
            ]
        );
    }

    /**
     * Get the user's phone number from their profile.
     *
     * @return string|null
     */
    public function getPhoneAttribute(): ?string
    {
        return $this->profile?->phone;
    }

    /**
     * Check if the user is an admin.
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->is_admin === true || $this->account_type === 'admin';
    }

    /**
     * Check if the user is a clinic account.
     *
     * @return bool
     */
    public function isClinic(): bool
    {
        return $this->account_type === 'clinic';
    }

    /**
     * Check if the user is a regular user account.
     *
     * @return bool
     */
    public function isUser(): bool
    {
        return $this->account_type === 'user';
    }

    /**
     * Check if clinic registration is unregistered.
     *
     * @return bool
     */
    public function isClinicUnregistered(): bool
    {
        if (!$this->isClinic()) {
            return false;
        }
        
        $registration = $this->clinicRegistration;
        return !$registration || $registration->status === 'incomplete';
    }

    /**
     * Check if clinic registration is incomplete.
     *
     * @return bool
     */
    public function isClinicRegistrationIncomplete(): bool
    {
        if (!$this->isClinic()) {
            return false;
        }
        
        $registration = $this->clinicRegistration;
        return $registration && $registration->status === 'incomplete';
    }

    /**
     * Check if clinic registration is pending approval.
     *
     * @return bool
     */
    public function isClinicRegistrationPending(): bool
    {
        if (!$this->isClinic()) {
            return false;
        }
        
        $registration = $this->clinicRegistration;
        return $registration && $registration->status === 'pending';
    }

    /**
     * Check if clinic registration is approved.
     *
     * @return bool
     */
    public function isClinicRegistrationApproved(): bool
    {
        if (!$this->isClinic()) {
            return false;
        }
        
        $registration = $this->clinicRegistration;
        return $registration && $registration->status === 'approved';
    }

    /**
     * Check if clinic registration was rejected.
     *
     * @return bool
     */
    public function isClinicRegistrationRejected(): bool
    {
        if (!$this->isClinic()) {
            return false;
        }
        
        $registration = $this->clinicRegistration;
        return $registration && $registration->status === 'rejected';
    }

    /**
     * Check if clinic can access dashboard (approved).
     *
     * @return bool
     */
    public function canAccessClinicDashboard(): bool
    {
        // Admin users can always access clinic dashboard
        if ($this->isAdmin()) {
            return true;
        }
        
        return $this->isClinic() && $this->isClinicRegistrationApproved();
    }

    /**
     * Get the user's initials for avatar display.
     *
     * @return string
     */
    public function getInitials(): string
    {
        // Try to get initials from profile first
        if ($this->profile && ($this->profile->first_name || $this->profile->last_name)) {
            return $this->profile->initials;
        }

        // Fallback to name field
        $nameParts = explode(' ', $this->name);
        $initials = '';
        
        foreach ($nameParts as $part) {
            $initials .= strtoupper(substr($part, 0, 1));
            if (strlen($initials) >= 2) break;
        }
        
        return $initials ?: 'U';
    }

    /**
     * Get the user's full name.
     *
     * @return string
     */
    public function getFullName(): string
    {
        if ($this->profile && $this->profile->full_name !== 'Unknown User') {
            return $this->profile->full_name;
        }

        return $this->name ?: 'Unknown User';
    }

    /**
     * Get the user's formatted address.
     *
     * @return string|null
     */
    public function getFormattedAddress(): ?string
    {
        if ($this->primaryAddress) {
            return $this->primaryAddress->full_address;
        }

        return null;
    }

    /**
     * Get the user's full address.
     *
     * @return string|null
     */
    public function getFullAddress(): ?string
    {
        return $this->getFormattedAddress();
    }

    /**
     * Check if the user has a complete address.
     *
     * @return bool
     */
    public function hasCompleteAddress(): bool
    {
        if ($this->primaryAddress) {
            return !empty($this->primaryAddress->address_line_1) && 
                   !empty($this->primaryAddress->city) && 
                   !empty($this->primaryAddress->state) && 
                   !empty($this->primaryAddress->postal_code);
        }

        return false;
    }

    /**
     * Check if the user has emergency contact information.
     *
     * @return bool
     */
    public function hasEmergencyContact(): bool
    {
        if ($this->primaryEmergencyContact) {
            return !empty($this->primaryEmergencyContact->name) && !empty($this->primaryEmergencyContact->phone);
        }

        return false;
    }

    /**
     * Get the user's membership duration in years.
     *
     * @return int
     */
    public function getMembershipYears(): int
    {
        return (int) $this->created_at->diffInYears(now());
    }

    /**
     * Calculate the profile completion percentage.
     *
     * @return int
     */
    public function getProfileCompletionPercentage(): int
    {
        // Use organized structure if available
        if ($this->profile) {
            $profileCompletion = $this->profile->completion_percentage;
            $addressCompletion = $this->hasCompleteAddress() ? 100 : 0;
            $emergencyContactCompletion = $this->hasEmergencyContact() ? 100 : 0;
            
            // Weight: 60% profile, 20% address, 20% emergency contact
            return (int) round(($profileCompletion * 0.6) + ($addressCompletion * 0.2) + ($emergencyContactCompletion * 0.2));
        }

        // Use organized structure calculation
        $totalFields = 0;
        $completedFields = 0;

        // Core user fields
        $coreFields = ['name', 'email'];
        foreach ($coreFields as $field) {
            $totalFields++;
            if (!empty($this->$field)) {
                $completedFields++;
            }
        }

        // Profile fields
        if ($this->profile) {
            $profileFields = ['phone', 'date_of_birth', 'gender', 'bio'];
            foreach ($profileFields as $field) {
                $totalFields++;
                if (!empty($this->profile->$field)) {
                    $completedFields++;
                }
            }
        } else {
            $totalFields += 4; // Count missing profile fields
        }

        // Address fields
        if ($this->primaryAddress) {
            $addressFields = ['address_line_1', 'city', 'state', 'postal_code'];
            foreach ($addressFields as $field) {
                $totalFields++;
                if (!empty($this->primaryAddress->$field)) {
                    $completedFields++;
                }
            }
        } else {
            $totalFields += 4; // Count missing address fields
        }

        // Emergency contact fields
        if ($this->primaryEmergencyContact) {
            $emergencyFields = ['name', 'phone'];
            foreach ($emergencyFields as $field) {
                $totalFields++;
                if (!empty($this->primaryEmergencyContact->$field)) {
                    $completedFields++;
                }
            }
        } else {
            $totalFields += 2; // Count missing emergency contact fields
        }

        return $totalFields > 0 ? (int) round(($completedFields / $totalFields) * 100) : 0;
    }

    /**
     * Get user's phone number from profile.
     *
     * @return string|null
     */
    public function getPhone(): ?string
    {
        return $this->profile?->phone;
    }

    /**
     * Get user's date of birth from profile.
     *
     * @return \Carbon\Carbon|null
     */
    public function getDateOfBirth(): ?\Carbon\Carbon
    {
        return $this->profile?->date_of_birth;
    }

    /**
     * Get user's gender from profile.
     *
     * @return string|null
     */
    public function getGender(): ?string
    {
        return $this->profile?->gender;
    }

    /**
     * Get the user's bio from profile.
     *
     * @return string|null
     */
    public function getBio(): ?string
    {
        return $this->profile?->bio;
    }

    /**
     * Get user's address line 1 from primary address.
     *
     * @return string|null
     */
    public function getAddressLine1Attribute(): ?string
    {
        return $this->primaryAddress?->address_line_1;
    }

    /**
     * Get user's address line 2 from primary address.
     *
     * @return string|null
     */
    public function getAddressLine2Attribute(): ?string
    {
        return $this->primaryAddress?->address_line_2;
    }

    /**
     * Get user's city from primary address.
     *
     * @return string|null
     */
    public function getCityAttribute(): ?string
    {
        return $this->primaryAddress?->city;
    }

    /**
     * Get user's state from primary address.
     *
     * @return string|null
     */
    public function getStateAttribute(): ?string
    {
        return $this->primaryAddress?->state;
    }

    /**
     * Get user's postal code from primary address.
     *
     * @return string|null
     */
    public function getPostalCodeAttribute(): ?string
    {
        return $this->primaryAddress?->postal_code;
    }

    /**
     * Get user's country from primary address.
     *
     * @return string|null
     */
    public function getCountryAttribute(): ?string
    {
        return $this->primaryAddress?->country;
    }

    /**
     * Get user's emergency contact name from primary emergency contact.
     *
     * @return string|null
     */
    public function getEmergencyContactNameAttribute(): ?string
    {
        return $this->primaryEmergencyContact?->name;
    }

    /**
     * Get user's emergency contact relationship from primary emergency contact.
     *
     * @return string|null
     */
    public function getEmergencyContactRelationshipAttribute(): ?string
    {
        return $this->primaryEmergencyContact?->relationship;
    }

    /**
     * Get user's emergency contact phone from primary emergency contact.
     *
     * @return string|null
     */
    public function getEmergencyContactPhoneAttribute(): ?string
    {
        return $this->primaryEmergencyContact?->phone;
    }

    /**
     * Get user's date of birth from profile.
     *
     * @return \Carbon\Carbon|null
     */
    public function getDateOfBirthAttribute(): ?\Carbon\Carbon
    {
        return $this->profile?->date_of_birth;
    }

    /**
     * Get user's gender from profile.
     *
     * @return string|null
     */
    public function getGenderAttribute(): ?string
    {
        return $this->profile?->gender;
    }

    /**
     * Get user's bio from profile.
     *
     * @return string|null
     */
    public function getBioAttribute(): ?string
    {
        return $this->profile?->bio;
    }

    /**
     * Check if user has completed their profile setup.
     *
     * @return bool
     */
    public function hasCompletedProfile(): bool
    {
        // Admins and clinics don't need profile completion
        if ($this->isAdmin() || $this->isClinic()) {
            return true;
        }

        // Check if profile_completed_at is set
        if ($this->profile?->profile_completed_at) {
            return true;
        }

        // Check required fields
        $hasRequiredFields = $this->profile?->phone 
            && $this->profile?->address 
            && $this->profile?->city 
            && $this->profile?->province;

        // Check if user has at least one pet
        $hasPet = $this->pets()->exists();

        return $hasRequiredFields && $hasPet;
    }
}
