<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug', 
        'type',
        'description',
        'price',
        'annual_price',
        'stripe_price_id',
        'stripe_annual_price_id',
        'features',
        'limits',
        'is_active',
        'trial_days',
        'transaction_fee_percentage',
        'transaction_fee_fixed',
        'sort_order',
    ];

    protected $casts = [
        'features' => 'array',
        'limits' => 'array',
        'price' => 'decimal:2',
        'annual_price' => 'decimal:2',
        'transaction_fee_percentage' => 'decimal:2',
        'transaction_fee_fixed' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get users subscribed to this plan.
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class, 'stripe_price', 'stripe_price_id');
    }

    /**
     * Scope for active plans.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for user plans.
     */
    public function scopeForUsers($query)
    {
        return $query->where('type', 'user');
    }

    /**
     * Scope for clinic plans.
     */
    public function scopeForClinics($query)
    {
        return $query->where('type', 'clinic');
    }

    /**
     * Get formatted price with currency.
     */
    public function getFormattedPriceAttribute(): string
    {
        return '₱' . number_format($this->price, 2);
    }

    /**
     * Get formatted annual price with currency.
     */
    public function getFormattedAnnualPriceAttribute(): string
    {
        if (!$this->annual_price) {
            return $this->getFormattedPriceAttribute();
        }
        return '₱' . number_format($this->annual_price, 2);
    }

    /**
     * Check if plan has a specific feature.
     */
    public function hasFeature(string $feature): bool
    {
        return in_array($feature, $this->features ?? []);
    }

    /**
     * Get limit value for a specific feature.
     */
    public function getLimit(string $limit): mixed
    {
        return $this->limits[$limit] ?? null;
    }

    /**
     * Check if plan is free.
     */
    public function isFree(): bool
    {
        return $this->price == 0;
    }

    /**
     * Get annual savings amount.
     */
    public function getAnnualSavingsAttribute(): ?float
    {
        if (!$this->annual_price) {
            return null;
        }
        
        $monthlyAnnual = $this->price * 12;
        return $monthlyAnnual - $this->annual_price;
    }

    /**
     * Get annual savings percentage.
     */
    public function getAnnualSavingsPercentageAttribute(): ?int
    {
        if (!$this->annual_savings) {
            return null;
        }
        
        $monthlyAnnual = $this->price * 12;
        return round(($this->annual_savings / $monthlyAnnual) * 100);
    }
}