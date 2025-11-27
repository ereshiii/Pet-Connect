<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class ClinicOperatingHour extends Model
{
    use HasFactory;

    protected $fillable = [
        'clinic_id',
        'day_of_week',
        'opening_time',
        'closing_time',
        'is_closed',
        'break_start_time',
        'break_end_time',
        'notes',
    ];

    protected $casts = [
        'is_closed' => 'boolean',
    ];

    /**
     * Get the clinic registration that owns these operating hours.
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
     * Get formatted opening time.
     */
    public function getFormattedOpeningTimeAttribute(): ?string
    {
        return $this->opening_time ? Carbon::parse($this->opening_time)->format('g:i A') : null;
    }

    /**
     * Get formatted closing time.
     */
    public function getFormattedClosingTimeAttribute(): ?string
    {
        return $this->closing_time ? Carbon::parse($this->closing_time)->format('g:i A') : null;
    }

    /**
     * Get formatted hours range.
     */
    public function getFormattedHoursAttribute(): string
    {
        if ($this->is_closed) {
            return 'Closed';
        }

        $opening = $this->formatted_opening_time;
        $closing = $this->formatted_closing_time;

        if ($opening && $closing) {
            return "{$opening} - {$closing}";
        }

        return 'Hours not set';
    }

    /**
     * Check if the clinic is currently open based on this day's hours.
     */
    public function isCurrentlyOpen(): bool
    {
        if ($this->is_closed) {
            return false;
        }

        $now = Carbon::now();
        $currentTime = $now->format('H:i:s');
        
        return $currentTime >= $this->opening_time && $currentTime <= $this->closing_time;
    }

    /**
     * Scope to get hours for a specific day.
     */
    public function scopeForDay($query, string $day)
    {
        return $query->where('day_of_week', $day);
    }

    /**
     * Scope to get open hours only.
     */
    public function scopeOpen($query)
    {
        return $query->where('is_closed', false);
    }
}
