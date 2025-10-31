<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserEmergencyContact extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'relationship',
        'phone',
        'email',
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    /**
     * Get the user that owns the emergency contact.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the relationship display name.
     */
    public function getRelationshipDisplayAttribute(): string
    {
        return match($this->relationship) {
            'spouse' => 'Spouse',
            'parent' => 'Parent',
            'child' => 'Child',
            'sibling' => 'Sibling',
            'friend' => 'Friend',
            'other' => 'Other',
            default => ucfirst($this->relationship)
        };
    }

    /**
     * Get formatted phone number.
     */
    public function getFormattedPhoneAttribute(): string
    {
        if (!$this->phone) {
            return '';
        }

        // Basic Philippine phone number formatting
        $phone = preg_replace('/[^0-9]/', '', $this->phone);
        
        if (strlen($phone) === 11 && substr($phone, 0, 2) === '09') {
            return '+63 ' . substr($phone, 1, 3) . ' ' . substr($phone, 4, 3) . ' ' . substr($phone, 7);
        }
        
        return $this->phone;
    }

    /**
     * Get the contact's initials.
     */
    public function getInitialsAttribute(): string
    {
        $nameParts = explode(' ', $this->name);
        $initials = '';
        
        foreach ($nameParts as $part) {
            $initials .= strtoupper(substr($part, 0, 1));
            if (strlen($initials) >= 2) break;
        }
        
        return $initials ?: 'EC';
    }

    /**
     * Scope to get primary emergency contacts.
     */
    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    /**
     * Scope to get contacts by relationship.
     */
    public function scopeByRelationship($query, string $relationship)
    {
        return $query->where('relationship', $relationship);
    }

    /**
     * Boot method to ensure only one primary emergency contact per user.
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($contact) {
            if ($contact->is_primary) {
                // Set other emergency contacts of the same user to non-primary
                static::where('user_id', $contact->user_id)
                    ->where('id', '!=', $contact->id)
                    ->update(['is_primary' => false]);
            }
        });
    }
}