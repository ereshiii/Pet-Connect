<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClinicReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'clinic_registration_id',   // References clinic_registrations.id
        'user_id',
        'appointment_id',           // References appointments.id (nullable)
        'rating',
        'comment',
        'response',
        'response_date',
        'responded_by',
        'is_verified',
        'is_featured',
        'helpful_votes',
    ];

    protected $casts = [
        'rating' => 'integer',
        'response_date' => 'datetime',
        'is_verified' => 'boolean',
        'is_featured' => 'boolean',
        'helpful_votes' => 'array',
    ];

    /**
     * Get the clinic registration that this review belongs to.
     * Note: clinic_registration_id is the primary field for clinic association
     */
    public function clinicRegistration(): BelongsTo
    {
        return $this->belongsTo(ClinicRegistration::class);
    }

    /**
     * Get the appointment associated with this review.
     * Note: appointment_id is optional - reviews can be left without an appointment
     */
    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    /**
     * Get the user who wrote this review.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user who responded to this review.
     */
    public function responder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responded_by');
    }

    /**
     * Get the star display for this rating.
     */
    public function getStarsAttribute(): string
    {
        $fullStars = $this->rating;
        $emptyStars = 5 - $fullStars;
        
        return str_repeat('★', $fullStars) . str_repeat('☆', $emptyStars);
    }

    /**
     * Get formatted date for display.
     */
    public function getFormattedDateAttribute(): string
    {
        return $this->created_at->format('M j, Y');
    }

    /**
     * Get user's initials for avatar.
     */
    public function getUserInitialsAttribute(): string
    {
        return $this->user ? $this->user->getInitials() : 'U';
    }

    /**
     * Check if this review has a response.
     */
    public function hasResponse(): bool
    {
        return !empty($this->response);
    }

    /**
     * Get helpful votes count.
     */
    public function getHelpfulVotesCountAttribute(): int
    {
        return count($this->helpful_votes ?? []);
    }

    /**
     * Check if a user found this review helpful.
     */
    public function isHelpfulTo(User $user): bool
    {
        return in_array($user->id, $this->helpful_votes ?? []);
    }

    /**
     * Scope to get verified reviews.
     */
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    /**
     * Scope to get featured reviews.
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope to get reviews by rating.
     */
    public function scopeRating($query, int $rating)
    {
        return $query->where('rating', $rating);
    }

    /**
     * Scope to get recent reviews.
     */
    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Scope to order by helpful votes.
     */
    public function scopeOrderByHelpful($query)
    {
        return $query->orderByRaw('JSON_LENGTH(helpful_votes) DESC');
    }
}