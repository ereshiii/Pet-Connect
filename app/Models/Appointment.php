<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'appointment_number',
        'pet_id',
        'owner_id',
        'clinic_id',
        'clinic_staff_id',
        'service_id',
        'scheduled_at',
        'duration_minutes',
        'type',
        'priority',
        'status',
        'dispute_window_ends_at',
        'is_disputed',
        'dispute_reason',
        'disputed_at',
        'is_priority',
        'priority_reason',
        'reason',
        'notes',
        'special_instructions',
        'estimated_cost',
        'actual_cost',
        'checked_in_at',
        'checked_out_at',
        'created_by',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'checked_in_at' => 'datetime',
        'checked_out_at' => 'datetime',
        'dispute_window_ends_at' => 'datetime',
        'disputed_at' => 'datetime',
        'is_disputed' => 'boolean',
        'is_priority' => 'boolean',
        'estimated_cost' => 'decimal:2',
        'actual_cost' => 'decimal:2',
        'duration_minutes' => 'integer',
    ];

    /**
     * Prepare a date for array / JSON serialization.
     *
     * @param  \DateTimeInterface  $date
     * @return string
     */
    protected function serializeDate(\DateTimeInterface $date): string
    {
        return $date->format('Y-m-d H:i:s');
    }

    /**
     * Get the pet for this appointment.
     */
    public function pet(): BelongsTo
    {
        return $this->belongsTo(Pet::class, 'pet_id');
    }

    /**
     * Get the owner for this appointment.
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Get the clinic registration for this appointment.
     * Note: appointments.clinic_id directly references clinic_registrations.id
     */
    public function clinicRegistration(): BelongsTo
    {
        return $this->belongsTo(ClinicRegistration::class, 'clinic_id');
    }

    /**
     * Alias for clinicRegistration() to maintain backward compatibility.
     * Note: This now returns the ClinicRegistration, not a Clinic model.
     */
    public function clinic(): BelongsTo
    {
        return $this->clinicRegistration();
    }

    /**
     * Get the user (owner) for this appointment.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Get notifications for this appointment.
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class)->whereJsonContains('data->appointment_id', $this->id);
    }

    /**
     * Get the veterinarian (clinic staff) for this appointment.
     */
    public function veterinarian(): BelongsTo
    {
        return $this->belongsTo(ClinicStaff::class, 'clinic_staff_id');
    }

    /**
     * Alias for veterinarian - get the assigned clinic staff member.
     */
    public function clinicStaff(): BelongsTo
    {
        return $this->belongsTo(ClinicStaff::class, 'clinic_staff_id');
    }

    /**
     * Get the service for this appointment.
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(ClinicService::class, 'service_id');
    }

    /**
     * Get the user who created this appointment.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the invoice for this appointment.
     */
    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }

    /**
     * Get the medical record for this appointment.
     */
    public function medicalRecord(): HasOne
    {
        return $this->hasOne(PetMedicalRecord::class);
    }

    /**
     * Get the appointment's status display name.
     */
    public function getStatusDisplayAttribute(): string
    {
        return match($this->status) {
            'scheduled' => 'Scheduled',
            'confirmed' => 'Confirmed',
            'in_progress' => 'In Progress',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            'no_show' => 'No Show',
            default => ucfirst($this->status)
        };
    }

    /**
     * Check if the appointment is upcoming.
     */
    public function isUpcoming(): bool
    {
        return $this->scheduled_at->isFuture() && in_array($this->status, ['scheduled', 'confirmed']);
    }

    /**
     * Check if the appointment is today.
     */
    public function isToday(): bool
    {
        return $this->scheduled_at->isToday();
    }

    /**
     * Scope to get upcoming appointments.
     */
    public function scopeUpcoming($query)
    {
        return $query->where('scheduled_at', '>=', now())
                    ->whereIn('status', ['scheduled', 'confirmed']);
    }

    /**
     * Scope to get today's appointments.
     */
    public function scopeToday($query)
    {
        return $query->whereDate('scheduled_at', today());
    }

    /**
     * Get the review for this appointment.
     */
    public function review(): HasOne
    {
        return $this->hasOne(ClinicReview::class);
    }

    /**
     * Check if appointment is within dispute window.
     */
    public function isWithinDisputeWindow(): bool
    {
        if (!$this->dispute_window_ends_at) {
            return false;
        }

        return now()->isBefore($this->dispute_window_ends_at);
    }

    /**
     * Check if appointment can be disputed by owner.
     */
    public function canBeDisputed(): bool
    {
        return $this->status === 'completed' 
            && !$this->is_disputed 
            && $this->isWithinDisputeWindow();
    }

    /**
     * Get hours remaining in dispute window.
     */
    public function getDisputeWindowHoursRemaining(): ?int
    {
        if (!$this->isWithinDisputeWindow()) {
            return null;
        }

        return (int) now()->diffInHours($this->dispute_window_ends_at);
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($appointment) {
            if (!$appointment->appointment_number) {
                $appointment->appointment_number = 'APT-' . now()->format('Ymd') . '-' . str_pad(static::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT);
            }
        });
    }
}
