<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClinicStaff extends Model
{
    use HasFactory;

    protected $fillable = [
        'clinic_id',
        'user_id',
        'name',
        'email',
        'phone',
        'role',
        'license_number',
        'specializations',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'specializations' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * Get the user associated with this staff member.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the clinic registration that this staff member belongs to.
     * Note: clinic_id references clinic_registrations.id (not clinics.id)
     */
    public function clinic(): BelongsTo
    {
        return $this->belongsTo(ClinicRegistration::class, 'clinic_id');
    }

    /**
     * Alias for clinic() for better semantics.
     * Returns the clinic registration.
     */
    public function clinicRegistration(): BelongsTo
    {
        return $this->clinic();
    }

    /**
     * Get appointments assigned to this staff member.
     */
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'clinic_staff_id');
    }

    /**
     * Get the staff member's full title.
     */
    public function getFullTitleAttribute(): string
    {
        $title = match($this->role) {
            'owner' => 'Owner',
            'veterinarian' => 'Dr.',
            'assistant' => 'Assistant',
            'receptionist' => 'Receptionist',
            'admin' => 'Admin',
            default => ''
        };

        return trim($title . ' ' . $this->name);
    }

    /**
     * Get the role display name.
     */
    public function getRoleDisplayAttribute(): string
    {
        return match($this->role) {
            'owner' => 'Clinic Owner',
            'veterinarian' => 'Veterinarian',
            'assistant' => 'Veterinary Assistant',
            'receptionist' => 'Receptionist',
            'admin' => 'Administrator',
            default => ucfirst(str_replace('_', ' ', $this->role))
        };
    }

    /**
     * Get specializations as a formatted string.
     */
    public function getSpecializationsStringAttribute(): string
    {
        if (!$this->specializations || empty($this->specializations)) {
            return 'General Practice';
        }

        return implode(', ', $this->specializations);
    }

    /**
     * Get years of service at this clinic.
     */
    public function getYearsOfServiceAttribute(): int
    {
        $endDate = $this->end_date ?? now();
        return $this->start_date->diffInYears($endDate);
    }

    /**
     * Scope to get active staff members.
     */
    public function scopeActive($query)
    {
        return $query->whereNull('end_date')->orWhere('end_date', '>=', now());
    }

    /**
     * Scope to get staff by role.
     */
    public function scopeByRole($query, string $role)
    {
        return $query->where('role', $role);
    }

    /**
     * Scope to get veterinarians.
     */
    public function scopeVeterinarians($query)
    {
        return $query->where('role', 'veterinarian');
    }

    /**
     * Scope to get assistants.
     */
    public function scopeAssistants($query)
    {
        return $query->where('role', 'assistant');
    }
}
