<?php

namespace App\Models;

use App\Enum\SubscriptionInterval;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'interval' => SubscriptionInterval::class,
        'features' => 'array',
        'is_popular' => 'boolean',
        'max_users' => 'integer',
        'price' => 'decimal:2',
    ];

    /**
     * Get subscriptions ordered by sort order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    /**
     * Get only active (non-free) subscriptions
     */
    public function scopePaid($query)
    {
        return $query->where('price', '>', 0);
    }

    /**
     * Check if this is a free plan
     */
    public function isFree(): bool
    {
        return $this->price == 0;
    }

    /**
     * Check if this is a lifetime plan
     */
    public function isLifetime(): bool
    {
        return $this->interval === SubscriptionInterval::ONE_TIME;
    }

    /**
     * Get formatted price with currency
     */
    public function getFormattedPriceAttribute(): string
    {
        if ($this->isFree()) {
            return 'Free';
        }

        return number_format($this->price, 2, ',', ' ') . ' €';
    }
}
