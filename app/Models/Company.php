<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Company extends Model
{
    use HasFactory;

    protected $table = 'companies';

    protected $fillable = [
        'name',
        'slug',
        'timezone',
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
}
