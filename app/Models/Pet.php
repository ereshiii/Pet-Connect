<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Pet extends Model
{
    use HasFactory;

    protected $fillable = [
        'owner_id',
        'name',
        'species',   // dog, cat, bird, etc. (derived from type_id)
        'breed_id',  // Foreign key to pet_breeds table (preferred)
        'breed',     // String breed name (fallback when breed_id not set)
        'type_id',
        'gender',
        'birth_date', // Preferred: calculate age from this
        'weight',
        'size',
        'color',
        'markings',
        'microchip_number',
        'is_neutered',
        'special_needs',
        'notes',
        'profile_image',
        'images',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'weight' => 'decimal:2',
        'is_neutered' => 'boolean',
        'images' => 'array',
    ];

    /**
     * Get the owner of the pet.
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Get the breed of the pet.
     */
    public function breed(): BelongsTo
    {
        return $this->belongsTo(PetBreed::class);
    }

    /**
     * Get the type of the pet.
     */
    public function type(): BelongsTo
    {
        return $this->belongsTo(PetType::class);
    }

    /**
     * Get the pet's medical records.
     */
    public function medicalRecords(): HasMany
    {
        return $this->hasMany(PetMedicalRecord::class);
    }

    /**
     * Get the pet's vaccinations.
     */
    public function vaccinations(): HasMany
    {
        return $this->hasMany(PetVaccination::class);
    }

    /**
     * Get the pet's health conditions.
     */
    public function healthConditions(): HasMany
    {
        return $this->hasMany(PetHealthCondition::class);
    }

    /**
     * Get the pet's appointments.
     */
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    /**
     * Get the pet's invoices (as patient).
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'patient_id');
    }

    /**
     * Get the pet's active health conditions.
     */
    public function activeHealthConditions(): HasMany
    {
        return $this->healthConditions()->where('is_active', true);
    }

    /**
     * Get the pet's current vaccinations (not expired).
     */
    public function currentVaccinations(): HasMany
    {
        return $this->vaccinations()->where('expiry_date', '>=', now())->orWhereNull('expiry_date');
    }

    /**
     * Get the pet's calculated age from birth date.
     */
    public function getCalculatedAgeAttribute(): ?string
    {
        if (!$this->birth_date) {
            return null;
        }

        $age = (int) $this->birth_date->diffInYears(now());
        $months = (int) ($this->birth_date->diffInMonths(now()) % 12);

        if ($age == 0) {
            return $months . ' month' . ($months != 1 ? 's' : '') . ' old';
        }

        $ageStr = $age . ' year' . ($age != 1 ? 's' : '');
        if ($months > 0) {
            $ageStr .= ' and ' . $months . ' month' . ($months != 1 ? 's' : '');
        }
        return $ageStr . ' old';
    }

    /**
     * Get the pet's age in years (decimal).
     */
    public function getAgeInYearsAttribute(): ?float
    {
        if (!$this->birth_date) {
            return null;
        }

        return round($this->birth_date->diffInDays(now()) / 365.25, 1);
    }



    /**
     * Get the pet's size display name.
     */
    public function getSizeDisplayAttribute(): ?string
    {
        return match($this->size) {
            'tiny' => 'Tiny',
            'small' => 'Small',
            'medium' => 'Medium',
            'large' => 'Large',
            'giant' => 'Giant',
            default => $this->size ? ucfirst($this->size) : null
        };
    }

    /**
     * Get the pet's gender display name.
     */
    public function getGenderDisplayAttribute(): string
    {
        return match($this->gender) {
            'male' => 'Male',
            'female' => 'Female',
            'unknown' => 'Unknown',
            default => ucfirst($this->gender)
        };
    }

    /**
     * Check if the pet needs vaccinations soon.
     */
    public function needsVaccinationSoon(): bool
    {
        $thirtyDaysFromNow = now()->addDays(30);
        
        return $this->vaccinations()
            ->where('expiry_date', '<=', $thirtyDaysFromNow)
            ->where('expiry_date', '>=', now())
            ->exists();
    }

    /**
     * Check if the pet has overdue vaccinations.
     */
    public function hasOverdueVaccinations(): bool
    {
        return $this->vaccinations()
            ->where('expiry_date', '<', now())
            ->exists();
    }

    /**
     * Get the pet's health status.
     */
    public function getHealthStatusAttribute(): array
    {
        $status = [
            'overall' => 'healthy',
            'vaccination_status' => 'up_to_date',
            'alerts' => []
        ];

        // Check vaccination status
        if ($this->hasOverdueVaccinations()) {
            $status['vaccination_status'] = 'overdue';
            $status['overall'] = 'needs_attention';
            $status['alerts'][] = 'Overdue vaccinations';
        } elseif ($this->needsVaccinationSoon()) {
            $status['vaccination_status'] = 'due_soon';
            $status['alerts'][] = 'Vaccinations due soon';
        }

        // Check for active health conditions
        if ($this->activeHealthConditions()->where('severity', 'severe')->exists()) {
            $status['overall'] = 'needs_attention';
            $status['alerts'][] = 'Severe health conditions';
        } elseif ($this->activeHealthConditions()->exists()) {
            $status['alerts'][] = 'Has health conditions';
        }

        return $status;
    }

    /**
     * Scope to get active pets.
     */
    public function scopeActive($query)
    {
        return $query;
    }



    /**
     * Scope to get pets needing vaccination.
     */
    public function scopeNeedsVaccination($query)
    {
        return $query->whereHas('vaccinations', function ($q) {
            $q->where('expiry_date', '<=', now()->addDays(30));
        });
    }
}