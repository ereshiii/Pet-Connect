<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PetMedicalRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'pet_id',
        'veterinarian_id',
        'clinic_id',
        'appointment_id',
        'title',
        'description',
        'date',
        'cost',
        'follow_up_date',
        'attachments',
        // Simplified core fields only
        'diagnosis',
        'findings',
        'treatment_given',
        'prescriptions',
    ];

    protected $casts = [
        'date' => 'date',
        'follow_up_date' => 'date',
        'cost' => 'decimal:2',
        'attachments' => 'array',
    ];

    /**
     * Get the pet that owns this medical record.
     */
    public function pet(): BelongsTo
    {
        return $this->belongsTo(Pet::class);
    }

    /**
     * Get the veterinarian (clinic staff) associated with this record.
     * Note: veterinarian_id references users.id (legacy), should be migrated to clinic_staff.id
     */
    public function veterinarian(): BelongsTo
    {
        return $this->belongsTo(ClinicStaff::class, 'veterinarian_id');
    }

    /**
     * Get the clinic registration associated with this record.
     * Note: clinic_id references clinic_registrations.id (not clinics.id)
     */
    public function clinic(): BelongsTo
    {
        return $this->belongsTo(ClinicRegistration::class, 'clinic_id');
    }

    /**
     * Get the clinic registration associated with this record (alias).
     */
    public function clinicRegistration(): BelongsTo
    {
        return $this->belongsTo(ClinicRegistration::class, 'clinic_id');
    }

    /**
     * Get the appointment associated with this medical record.
     */
    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    /**
     * Get days since record date.
     */
    public function getDaysSinceRecordAttribute(): int
    {
        return $this->date->diffInDays(now());
    }

    /**
     * Get days until follow-up.
     */
    public function getDaysUntilFollowUpAttribute(): ?int
    {
        if (!$this->follow_up_date) {
            return null;
        }

        return now()->diffInDays($this->follow_up_date, false);
    }

    /**
     * Check if follow-up is due soon.
     */
    public function getIsFollowUpDueSoonAttribute(): bool
    {
        return $this->follow_up_date && $this->follow_up_date->isBefore(now()->addDays(7));
    }

    /**
     * Check if follow-up is overdue.
     */
    public function getIsFollowUpOverdueAttribute(): bool
    {
        return $this->follow_up_date && $this->follow_up_date->isPast();
    }

    /**
     * Get the cost formatted as currency.
     */
    public function getCostFormattedAttribute(): string
    {
        return $this->cost ? '₱' . number_format($this->cost, 2) : 'N/A';
    }

    /**
     * Scope to get records needing follow-up.
     */
    public function scopeNeedsFollowUp($query)
    {
        return $query->where('follow_up_date', '<=', now()->addDays(7))
                    ->whereNotNull('follow_up_date');
    }

    /**
     * Scope to get records with overdue follow-ups.
     */
    public function scopeOverdueFollowUp($query)
    {
        return $query->where('follow_up_date', '<', now())
                    ->whereNotNull('follow_up_date');
    }

    /**
     * Scope to get recent records.
     */
    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('date', '>=', now()->subDays($days));
    }
}
