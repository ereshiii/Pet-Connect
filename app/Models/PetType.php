<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PetType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'species',
        'description',
    ];

    /**
     * Get pets of this type.
     */
    public function pets(): HasMany
    {
        return $this->hasMany(Pet::class, 'type_id');
    }

    /**
     * Scope to get types by species.
     */
    public function scopeOfSpecies($query, string $species)
    {
        return $query->where('species', $species);
    }
}