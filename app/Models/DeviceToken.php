<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeviceToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'token',
        'device_type',
        'device_name',
        'browser',
        'platform',
        'capabilities',
        'is_active',
        'last_used_at',
        'notification_preferences',
    ];

    protected $casts = [
        'capabilities' => 'array',
        'notification_preferences' => 'array',
        'is_active' => 'boolean',
        'last_used_at' => 'datetime',
    ];

    /**
     * Get the user that owns the device token.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for active tokens.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for specific device type.
     */
    public function scopeDeviceType($query, string $type)
    {
        return $query->where('device_type', $type);
    }

    /**
     * Scope for recently used tokens.
     */
    public function scopeRecentlyUsed($query, int $days = 30)
    {
        return $query->where('last_used_at', '>=', now()->subDays($days));
    }

    /**
     * Update last used timestamp.
     */
    public function markAsUsed(): void
    {
        $this->update(['last_used_at' => now()]);
    }

    /**
     * Check if device supports a specific capability.
     */
    public function hasCapability(string $capability): bool
    {
        return in_array($capability, $this->capabilities ?? []);
    }

    /**
     * Get notification preference for a specific channel.
     */
    public function getNotificationPreference(string $channel): bool
    {
        $preferences = $this->notification_preferences ?? [];
        return $preferences[$channel] ?? true; // Default to enabled
    }

    /**
     * Set notification preference for a specific channel.
     */
    public function setNotificationPreference(string $channel, bool $enabled): void
    {
        $preferences = $this->notification_preferences ?? [];
        $preferences[$channel] = $enabled;
        $this->update(['notification_preferences' => $preferences]);
    }

    /**
     * Deactivate the token.
     */
    public function deactivate(): void
    {
        $this->update(['is_active' => false]);
    }

    /**
     * Get device display name.
     */
    public function getDisplayNameAttribute(): string
    {
        if ($this->device_name) {
            return $this->device_name;
        }

        if ($this->browser && $this->platform) {
            return "{$this->browser} on {$this->platform}";
        }

        return ucfirst($this->device_type) . ' Device';
    }
}