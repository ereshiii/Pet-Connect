<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PetHealthCondition extends Model
{
    use HasFactory;

    protected $fillable = [
        'pet_id',
        'type',
        'name',
        'description',
        'severity',
        'diagnosed_date',
        'treatment',
        'is_active',
    ];

    protected $casts = [
        'diagnosed_date' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * Get the pet that owns this health condition.
     */
    public function pet(): BelongsTo
    {
        return $this->belongsTo(Pet::class);
    }

    /**
     * Get the severity display name.
     */
    public function getSeverityDisplayAttribute(): string
    {
        return match($this->severity) {
            'mild' => 'Mild',
            'moderate' => 'Moderate',
            'severe' => 'Severe',
            default => ucfirst($this->severity)
        };
    }

    /**
     * Get the condition type display name.
     */
    public function getTypeDisplayAttribute(): string
    {
        return match($this->type) {
            'allergy' => 'Allergy',
            'condition' => 'Medical Condition',
            'medication' => 'Medication',
            'dietary' => 'Dietary Restriction',
            default => ucfirst($this->type)
        };
    }

    /**
     * Get the status of this condition.
     */
    public function getStatusAttribute(): string
    {
        if (!$this->is_active) {
            return 'resolved';
        }

        if ($this->severity === 'severe') {
            return 'needs_attention';
        }

        return 'monitoring';
    }

    /**
     * Get days since diagnosis.
     */
    public function getDaysSinceDiagnosisAttribute(): int
    {
        return $this->diagnosed_date ? $this->diagnosed_date->diffInDays(now()) : 0;
    }

    /**
     * Scope to get active conditions.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get resolved conditions.
     */
    public function scopeResolved($query)
    {
        return $query->where('is_active', false);
    }

    /**
     * Scope to get conditions by type.
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to get conditions by severity.
     */
    public function scopeBySeverity($query, string $severity)
    {
        return $query->where('severity', $severity);
    }
}