<?php

namespace App\Services;

use App\Enum\VacationRequestStatus;
use App\Enum\VacationRequestType;
use App\Models\CompanySetting;
use App\Models\User;
use App\Models\VacationRequest;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class VacationBalanceService
{
    /**
     * Calculate the number of business days between two dates (excluding weekends).
     */
    public function calculateBusinessDays(Carbon $startDate, Carbon $endDate): int
    {
        if ($startDate->greaterThan($endDate)) {
            return 0;
        }

        $days = 0;
        $period = CarbonPeriod::create($startDate, $endDate);

        foreach ($period as $date) {
            // Skip weekends (Saturday = 6, Sunday = 0)
            if (! $date->isWeekend()) {
                $days++;
            }
        }

        return $days;
    }

    /**
     * Calculate total days used by a user in a given year.
     * Only counts APPROVED requests of types that affect annual balance.
     */
    public function calculateUsedDays(User $user, ?int $year = null): float
    {
        $year = $year ?? now()->year;

        $requests = VacationRequest::query()
            ->where('user_id', $user->id)
            ->where('status', VacationRequestStatus::APPROVED)
            ->where(function ($query) use ($year) {
                $query->whereYear('start_date', $year)
                    ->orWhereYear('end_date', $year);
            })
            ->get();

        $totalDays = 0;

        foreach ($requests as $request) {
            // Only count request types that affect annual vacation balance
            if ($this->requestTypeAffectsBalance($request->type)) {
                $totalDays += $this->calculateRequestDays($request);
            }
        }

        return $totalDays;
    }

    /**
     * Calculate the number of days for a specific vacation request.
     */
    public function calculateRequestDays(VacationRequest $request): int
    {
        return $this->calculateBusinessDays(
            $request->start_date,
            $request->end_date
        );
    }

    /**
     * Calculate pending days (days in pending requests).
     */
    public function calculatePendingDays(User $user, ?int $year = null): float
    {
        $year = $year ?? now()->year;

        $requests = VacationRequest::query()
            ->where('user_id', $user->id)
            ->where('status', VacationRequestStatus::PENDING)
            ->where(function ($query) use ($year) {
                $query->whereYear('start_date', $year)
                    ->orWhereYear('end_date', $year);
            })
            ->get();

        $totalDays = 0;

        foreach ($requests as $request) {
            if ($this->requestTypeAffectsBalance($request->type)) {
                $totalDays += $this->calculateRequestDays($request);
            }
        }

        return $totalDays;
    }

    /**
     * Get the annual vacation days for a user's company.
     */
    public function getAnnualDays(User $user): int
    {
        $companySetting = CompanySetting::where('company_id', $user->company_id)->first();

        return $companySetting?->annual_days ?? 20; // Default to 20 days
    }

    /**
     * Calculate remaining vacation balance for a user.
     */
    public function getRemainingBalance(User $user, ?int $year = null): float
    {
        $annualDays = $this->getAnnualDays($user);
        $usedDays = $this->calculateUsedDays($user, $year);
        $pendingDays = $this->calculatePendingDays($user, $year);

        // Remaining = Annual - (Used + Pending)
        // We subtract pending because those days are "reserved" even though not approved yet
        return max(0, $annualDays - $usedDays - $pendingDays);
    }

    /**
     * Get available balance (can still be requested).
     */
    public function getAvailableBalance(User $user, ?int $year = null): float
    {
        $annualDays = $this->getAnnualDays($user);
        $usedDays = $this->calculateUsedDays($user, $year);
        $pendingDays = $this->calculatePendingDays($user, $year);

        return max(0, $annualDays - $usedDays - $pendingDays);
    }

    /**
     * Check if a user has sufficient balance for a request.
     */
    public function hasSufficientBalance(
        User $user,
        Carbon $startDate,
        Carbon $endDate,
        VacationRequestType $type,
        ?int $excludeRequestId = null
    ): bool {
        // Request types that don't affect balance are always allowed
        if (! $this->requestTypeAffectsBalance($type)) {
            return true;
        }

        $requestedDays = $this->calculateBusinessDays($startDate, $endDate);
        $year = $startDate->year;

        // Get current used days (excluding the request being updated if applicable)
        $usedDays = $this->calculateUsedDays($user, $year);

        // Get pending days (excluding the request being updated if applicable)
        $pendingDays = $this->calculatePendingDays($user, $year);

        // If updating an existing request, subtract its days from the calculation
        if ($excludeRequestId) {
            $existingRequest = VacationRequest::find($excludeRequestId);
            if ($existingRequest && $this->requestTypeAffectsBalance($existingRequest->type)) {
                $existingDays = $this->calculateRequestDays($existingRequest);

                if ($existingRequest->status === VacationRequestStatus::APPROVED) {
                    $usedDays -= $existingDays;
                } elseif ($existingRequest->status === VacationRequestStatus::PENDING) {
                    $pendingDays -= $existingDays;
                }
            }
        }

        $annualDays = $this->getAnnualDays($user);
        $availableBalance = $annualDays - $usedDays - $pendingDays;

        return $requestedDays <= $availableBalance;
    }

    /**
     * Check if overlapping requests exist for a user.
     */
    public function hasOverlappingRequests(
        User $user,
        Carbon $startDate,
        Carbon $endDate,
        ?int $excludeRequestId = null
    ): bool {
        $query = VacationRequest::query()
            ->where('user_id', $user->id)
            ->whereIn('status', [VacationRequestStatus::PENDING, VacationRequestStatus::APPROVED])
            ->where(function ($query) use ($startDate, $endDate) {
                // Check for any overlap
                $query->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate])
                    ->orWhere(function ($q) use ($startDate, $endDate) {
                        $q->where('start_date', '<=', $startDate)
                            ->where('end_date', '>=', $endDate);
                    });
            });

        if ($excludeRequestId) {
            $query->where('id', '!=', $excludeRequestId);
        }

        return $query->exists();
    }

    /**
     * Determine if a request type affects annual vacation balance.
     * Sick days, WFH, etc. might not count against annual vacation days.
     */
    protected function requestTypeAffectsBalance(VacationRequestType $type): bool
    {
        return match ($type) {
            VacationRequestType::VACATION => true,
            VacationRequestType::PERSONAL => true,
            VacationRequestType::UNPAID => false, // Unpaid doesn't count against balance
            VacationRequestType::SICK => false, // Sick days typically don't count
            VacationRequestType::WFH => false, // Work from home doesn't count
            VacationRequestType::UNKNOWN => true,
        };
    }

    /**
     * Get complete balance summary for a user.
     */
    public function getBalanceSummary(User $user, ?int $year = null): array
    {
        $year = $year ?? now()->year;

        return [
            'annual_days' => $this->getAnnualDays($user),
            'used_days' => $this->calculateUsedDays($user, $year),
            'pending_days' => $this->calculatePendingDays($user, $year),
            'remaining_balance' => $this->getRemainingBalance($user, $year),
            'available_balance' => $this->getAvailableBalance($user, $year),
            'year' => $year,
        ];
    }

    /**
     * Get balance summaries for multiple users efficiently (no N+1 queries).
     */
    public function getBatchBalanceSummaries(iterable $users, ?int $year = null): array
    {
        $year = $year ?? now()->year;
        $userIds = [];
        $companyIds = [];
        $userMap = [];

        foreach ($users as $user) {
            $userIds[] = $user->id;
            $companyIds[] = $user->company_id;
            $userMap[$user->id] = $user;
        }

        if (empty($userIds)) {
            return [];
        }

        // Preload all vacation requests for all users
        $allRequests = VacationRequest::query()
            ->whereIn('user_id', $userIds)
            ->where(function ($query) use ($year) {
                $query->whereYear('start_date', $year)
                    ->orWhereYear('end_date', $year);
            })
            ->get()
            ->groupBy('user_id');

        // Preload company settings
        $companySettings = CompanySetting::whereIn('company_id', array_unique($companyIds))
            ->get()
            ->keyBy('company_id');

        // Calculate balances for each user using preloaded data
        $results = [];
        foreach ($userMap as $userId => $user) {
            $userRequests = $allRequests->get($userId, collect());
            $companySetting = $companySettings->get($user->company_id);
            $annualDays = $companySetting?->annual_days ?? 20;

            $usedDays = 0;
            $pendingDays = 0;

            foreach ($userRequests as $request) {
                if ($this->requestTypeAffectsBalance($request->type)) {
                    $days = $this->calculateRequestDays($request);

                    if ($request->status === VacationRequestStatus::APPROVED) {
                        $usedDays += $days;
                    } elseif ($request->status === VacationRequestStatus::PENDING) {
                        $pendingDays += $days;
                    }
                }
            }

            $remainingBalance = max(0, $annualDays - $usedDays - $pendingDays);
            $availableBalance = max(0, $annualDays - $usedDays - $pendingDays);

            $results[$userId] = [
                'annual_days' => $annualDays,
                'used_days' => $usedDays,
                'pending_days' => $pendingDays,
                'remaining_balance' => $remainingBalance,
                'available_balance' => $availableBalance,
                'year' => $year,
            ];
        }

        return $results;
    }
}
