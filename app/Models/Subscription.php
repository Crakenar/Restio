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
    ];
}
