<?php

namespace App\Models;

use App\Policies\TeamPolicy;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[UsePolicy(TeamPolicy::class)]
class Team extends Model
{
    protected $fillable = [
        'name',
        'company_id',
    ];

    /**
     * Get the company that owns the team.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the users that belong to the team.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
