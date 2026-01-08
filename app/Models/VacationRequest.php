<?php

namespace App\Models;

use App\Enum\VacationRequestStatus;
use App\Enum\VacationRequestType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VacationRequest extends Model
{
    use HasFactory;

    protected $table = 'vacation_requests';
    protected $fillable = [
        'company_id',
        'user_id',
        'start_date',
        'end_date',
        'type',
        'status',
        'approved_by',
        'approved_date',
        'reason',
        'rejection_reason',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'type' => VacationRequestType::class,
        'status' => VacationRequestStatus::class,
        'approved_date' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
