<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClinicService extends Model
{
    use HasFactory;

    protected $fillable = [
        'clinic_id',
        'name',
        'description',
        'category',
        'duration_minutes',
        'is_active',
        'requires_appointment',
        'is_emergency_service',
    ];

    protected $casts = [
        'duration_minutes' => 'integer',
        'is_active' => 'boolean',
        'requires_appointment' => 'boolean',
        'is_emergency_service' => 'boolean',
    ];

    /**
     * Get the clinic registration that offers this service.
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
     * Get the appointments for this service.
     */
    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'service_id');
    }

    /**
     * Get formatted duration.
     */
    public function getFormattedDurationAttribute(): string
    {
        if (!$this->duration_minutes) {
            return 'Duration varies';
        }

        $hours = intval($this->duration_minutes / 60);
        $minutes = $this->duration_minutes % 60;

        if ($hours > 0) {
            $duration = $hours . ' hour' . ($hours > 1 ? 's' : '');
            if ($minutes > 0) {
                $duration .= ' ' . $minutes . ' min';
            }
        } else {
            $duration = $minutes . ' minutes';
        }

        return $duration;
    }

    /**
     * Get category display name.
     */
    public function getCategoryDisplayAttribute(): string
    {
        return match($this->category) {
            'consultation' => 'Consultation',
            'vaccination' => 'Vaccination',
            'surgery' => 'Surgery',
            'dental' => 'Dental Care',
            'grooming' => 'Grooming',
            'boarding' => 'Boarding',
            'emergency' => 'Emergency Care',
            'diagnostic' => 'Diagnostic Services',
            'other' => 'Other Services',
            default => ucfirst(str_replace('_', ' ', $this->category))
        };
    }

    /**
     * Scope to get active services.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get services by category.
     */
    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope to get emergency services.
     */
    public function scopeEmergency($query)
    {
        return $query->where('is_emergency_service', true);
    }

    /**
     * Scope to get services that require appointments.
     */
    public function scopeRequiresAppointment($query)
    {
        return $query->where('requires_appointment', true);
    }
}
