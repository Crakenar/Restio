<?php

namespace App\Models;

use App\Policies\CompanyPolicy;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[UsePolicy(CompanyPolicy::class)]
class Company extends Model
{
    use HasFactory;

    protected $table = 'companies';

    protected $fillable = [
        'name',
        'slug',
        'timezone',
        'stripe_customer_id',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function company_settings(): HasOne
    {
        return $this->hasOne(CompanySetting::class);
    }

    public function vacation_requests(): HasMany
    {
        return $this->hasMany(VacationRequest::class);
    }

    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }

    public function teams(): HasMany
    {
        return $this->hasMany(Team::class);
    }

    public function subscriptions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(CompanySubscription::class);
    }

    public function currentSubscription(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(CompanySubscription::class)->latestOfMany();
    }

    /**
     * Get the current active subscription
     */
    public function getActiveSubscriptionAttribute()
    {
        return $this->subscriptions()
            ->where('status', \App\Enum\SubscriptionStatus::ACTIVE)
            ->with('subscription')
            ->latest()
            ->first();
    }

    /**
     * Get the current user limit based on subscription
     */
    public function getUserLimitAttribute(): int
    {
        $activeSubscription = $this->active_subscription;

        if (!$activeSubscription) {
            // Default to free plan limit if no subscription
            return 6; // 5 employees + 1 owner
        }

        return $activeSubscription->subscription->max_users ?? 6;
    }

    /**
     * Get current number of users (including owner)
     */
    public function getCurrentUserCountAttribute(): int
    {
        return $this->users()->count();
    }

    /**
     * Get remaining user slots
     */
    public function getRemainingUserSlotsAttribute(): int
    {
        return max(0, $this->user_limit - $this->current_user_count);
    }

    /**
     * Check if company can add more users
     */
    public function canAddUsers(int $count = 1): bool
    {
        return ($this->current_user_count + $count) <= $this->user_limit;
    }

    /**
     * Check if company is at or near user limit
     */
    public function isNearUserLimit(int $threshold = 80): bool
    {
        $usagePercentage = ($this->current_user_count / $this->user_limit) * 100;
        return $usagePercentage >= $threshold;
    }

    /**
     * Check if company has reached user limit
     */
    public function hasReachedUserLimit(): bool
    {
        return $this->current_user_count >= $this->user_limit;
    }
}
