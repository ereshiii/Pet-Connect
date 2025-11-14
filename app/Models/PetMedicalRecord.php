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
        'record_type',
        'title',
        'description',
        'date',
        'cost',
        'medication',
        'instructions',
        'follow_up_date',
        'attachments',
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
     * Get the veterinarian associated with this record.
     */
    public function veterinarian(): BelongsTo
    {
        return $this->belongsTo(User::class, 'veterinarian_id');
    }

    /**
     * Get the clinic associated with this record.
     */
    public function clinic(): BelongsTo
    {
        return $this->belongsTo(ClinicRegistration::class, 'clinic_id');
    }

    /**
     * Get the record type display name.
     */
    public function getRecordTypeDisplayAttribute(): string
    {
        return match($this->record_type) {
            'vaccination' => 'Vaccination',
            'treatment' => 'Treatment',
            'surgery' => 'Surgery',
            'checkup' => 'Checkup',
            'emergency' => 'Emergency',
            'other' => 'Other',
            default => ucfirst($this->record_type)
        };
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
     * Scope to get records by type.
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('record_type', $type);
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
