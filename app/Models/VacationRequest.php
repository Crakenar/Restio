<?php

namespace App\Models;

use App\Enum\VacationRequestStatus;
use App\Enum\VacationRequestType;
use App\Models\Concerns\Auditable;
use App\Policies\VacationRequestPolicy;
use App\Services\VacationBalanceService;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[UsePolicy(VacationRequestPolicy::class)]
class VacationRequest extends Model
{
    use Auditable;
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

    protected $appends = ['days'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Calculate the number of business days for this request.
     */
    protected function days(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (! $this->start_date || ! $this->end_date) {
                    return 0;
                }

                $balanceService = app(VacationBalanceService::class);

                return $balanceService->calculateBusinessDays(
                    $this->start_date,
                    $this->end_date
                );
            }
        );
    }
}
