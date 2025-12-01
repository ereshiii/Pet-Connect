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
        // New fields for emergency walk-in and follow-ups
        'appointment_type',
        'confirmation_window_ends_at',
        'confirmed_at',
        'reschedule_reason',
        'cancel_reason',
        'suggested_follow_up_date',
        'parent_appointment_id',
        'is_follow_up',
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
        // New casts
        'confirmation_window_ends_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'suggested_follow_up_date' => 'datetime',
        'is_follow_up' => 'boolean',
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
     * Get the parent appointment (for follow-ups).
     */
    public function parentAppointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class, 'parent_appointment_id');
    }

    /**
     * Get the follow-up appointment (child).
     */
    public function followUpAppointment(): HasOne
    {
        return $this->hasOne(Appointment::class, 'parent_appointment_id');
    }

    /**
     * Scope to get walk-in appointments.
     */
    public function scopeWalkIn($query)
    {
        return $query->where('appointment_type', 'walk-in');
    }

    /**
     * Scope to get scheduled appointments.
     */
    public function scopeScheduled($query)
    {
        return $query->where('appointment_type', 'scheduled');
    }

    /**
     * Check if this is a walk-in appointment.
     */
    public function isWalkIn(): bool
    {
        return $this->appointment_type === 'walk-in';
    }

    /**
     * Check if pet owner can still reschedule/cancel (within 24 hours of confirmation).
     */
    public function canOwnerRescheduleOrCancel(): bool
    {
        if (!$this->confirmed_at || !$this->confirmation_window_ends_at) {
            return false;
        }
        
        return now()->isBefore($this->confirmation_window_ends_at) && 
               in_array($this->status, ['pending', 'confirmed', 'scheduled']);
    }

    /**
     * Check if clinic can reschedule (only pending appointments).
     */
    public function canClinicReschedule(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if clinic can cancel (only pending appointments).
     */
    public function canClinicCancel(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if follow-up can be created (only for completed appointments without existing follow-up).
     */
    public function canCreateFollowUp(): bool
    {
        return $this->status === 'completed' && !$this->followUpAppointment()->exists();
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
