<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'type',
        'description',
        'group',
        'is_public',
    ];

    protected $casts = [
        'is_public' => 'boolean',
    ];

    /**
     * Get the setting value cast to the appropriate type.
     */
    public function getCastValueAttribute()
    {
        return match($this->type) {
            'boolean' => filter_var($this->value, FILTER_VALIDATE_BOOLEAN),
            'int', 'integer' => (int) $this->value,
            'float', 'decimal' => (float) $this->value,
            'json', 'array' => json_decode($this->value, true),
            default => $this->value
        };
    }

    /**
     * Alias for cast_value - used by tests
     */
    public function getTypedValueAttribute()
    {
        return $this->getCastValueAttribute();
    }

    /**
     * Get validation rules as array
     */
    public function getValidationRulesArrayAttribute(): ?array
    {
        if (!$this->validation_rules) {
            return null;
        }

        return explode('|', $this->validation_rules);
    }

    /**
     * Set the setting value.
     */
    public function setCastValue($value): void
    {
        $this->value = match($this->type) {
            'boolean' => $value ? 'true' : 'false',
            'json', 'array' => json_encode($value),
            default => (string) $value
        };
        $this->save();
    }

    /**
     * Get a setting value by key.
     */
    public static function get(string $key, $default = null)
    {
        $setting = static::where('key', $key)->first();
        
        if (!$setting) {
            return $default;
        }

        return $setting->cast_value;
    }

    /**
     * Alias for get() method - used by tests
     */
    public static function getValue(string $key, $default = null)
    {
        return static::get($key, $default);
    }

    /**
     * Set a setting value by key.
     */
    public static function set(string $key, $value, string $type = 'string'): void
    {
        $setting = static::firstOrNew(['key' => $key]);
        $setting->type = $type;
        $setting->setCastValue($value);
    }

    /**
     * Alias for set() method - used by tests
     */
    public static function setValue(string $key, $value, string $type = 'string'): void
    {
        static::set($key, $value, $type);
    }

    /**
     * Get all settings as config array
     */
    public static function getAllAsConfig(): array
    {
        return static::all()
            ->pluck('cast_value', 'key')
            ->toArray();
    }

    /**
     * Bulk update settings
     */
    public static function bulkUpdate(array $settings): void
    {
        foreach ($settings as $key => $value) {
            $setting = static::where('key', $key)->first();
            if ($setting) {
                $setting->setCastValue($value);
            }
        }
    }

    /**
     * Check if a setting exists.
     */
    public static function has(string $key): bool
    {
        return static::where('key', $key)->exists();
    }

    /**
     * Get all settings in a group.
     */
    public static function getGroup(string $group): array
    {
        return static::where('group', $group)
            ->get()
            ->pluck('cast_value', 'key')
            ->toArray();
    }

    /**
     * Get all public settings.
     */
    public static function getPublic(): array
    {
        return static::where('is_public', true)
            ->get()
            ->pluck('cast_value', 'key')
            ->toArray();
    }

    /**
     * Scope to get settings by group.
     */
    public function scopeInGroup($query, string $group)
    {
        return $query->where('group', $group);
    }

    /**
     * Scope to get public settings.
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }
}