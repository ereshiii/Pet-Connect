<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PetBreed extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'species',
        'description',
        'characteristics',
    ];

    protected $casts = [
        'characteristics' => 'array',
    ];

    /**
     * Get pets of this breed.
     */
    public function pets(): HasMany
    {
        return $this->hasMany(Pet::class, 'breed_id');
    }

    /**
     * Get the size category from characteristics.
     */
    public function getSizeCategoryAttribute(): ?string
    {
        return $this->characteristics['size_category'] ?? null;
    }

    /**
     * Get the size category display name.
     */
    public function getSizeCategoryDisplayAttribute(): string
    {
        $sizeCategory = $this->size_category;
        return match($sizeCategory) {
            'toy' => 'Toy',
            'small' => 'Small',
            'medium' => 'Medium',
            'large' => 'Large',
            'giant' => 'Giant',
            default => ucfirst($sizeCategory ?? 'Unknown')
        };
    }

    /**
     * Get the temperament from characteristics.
     */
    public function getTemperamentAttribute(): ?string
    {
        return $this->characteristics['temperament'] ?? null;
    }

    /**
     * Get the life expectancy range as a string.
     */
    public function getLifeExpectancyRangeAttribute(): string
    {
        $min = $this->characteristics['life_expectancy_min'] ?? null;
        $max = $this->characteristics['life_expectancy_max'] ?? null;
        
        if (!$min || !$max) {
            return 'Unknown';
        }

        if ($min === $max) {
            return $min . ' years';
        }

        return $min . '-' . $max . ' years';
    }

    /**
     * Get the weight range as a string.
     */
    public function getWeightRangeAttribute(): string
    {
        $min = $this->characteristics['weight_range_min'] ?? null;
        $max = $this->characteristics['weight_range_max'] ?? null;
        
        if (!$min || !$max) {
            return 'Unknown';
        }

        if ($min === $max) {
            return $min . ' kg';
        }

        return $min . '-' . $max . ' kg';
    }

    /**
     * Get the height range as a string.
     */
    public function getHeightRangeAttribute(): string
    {
        $min = $this->characteristics['height_range_min'] ?? null;
        $max = $this->characteristics['height_range_max'] ?? null;
        
        if (!$min || !$max) {
            return 'Unknown';
        }

        if ($min === $max) {
            return $min . ' cm';
        }

        return $min . '-' . $max . ' cm';
    }

    /**
     * Get common health issues from characteristics.
     */
    public function getCommonHealthIssuesAttribute(): array
    {
        return $this->characteristics['common_health_issues'] ?? [];
    }

    /**
     * Get grooming requirements from characteristics.
     */
    public function getGroomingRequirementsAttribute(): ?string
    {
        return $this->characteristics['grooming_requirements'] ?? null;
    }

    /**
     * Get exercise requirements from characteristics.
     */
    public function getExerciseRequirementsAttribute(): ?string
    {
        return $this->characteristics['exercise_requirements'] ?? null;
    }

    /**
     * Scope to get breeds by species.
     */
    public function scopeOfSpecies($query, string $species)
    {
        return $query->where('species', $species);
    }

    /**
     * Scope to get breeds by size category.
     */
    public function scopeBySizeCategory($query, string $sizeCategory)
    {
        return $query->whereJsonContains('characteristics->size_category', $sizeCategory);
    }
}
