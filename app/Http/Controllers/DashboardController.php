<?php

namespace App\Http\Controllers;

use App\Enum\UserRole;
use App\Enum\VacationRequestStatus;
use App\Models\CompanySetting;
use App\Models\User;
use App\Models\VacationRequest;
use App\Services\VacationBalanceService;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $user = auth()->user();
        $companyId = $user->company_id;
        $balanceService = app(VacationBalanceService::class);

        // Cache key for this user's dashboard
        $cacheKey = "dashboard.{$user->id}.{$user->role}";

        // Cache dashboard data for 5 minutes
        $dashboardData = Cache::remember($cacheKey, now()->addMinutes(5), function () use ($user, $companyId, $balanceService) {
            // Get current user's balance summary
            $userBalance = $balanceService->getBalanceSummary($user);

            // Fetch recent requests scoped to company (limit to 50 most recent for performance)
            $requests = VacationRequest::query()
                ->with(['user:id,name']) // Only select necessary columns
                ->where('company_id', $companyId)
                ->latest()
                ->limit(50) // Limit dashboard to 50 most recent requests
                ->get()
                ->map(function ($request) {
                    return [
                        'id' => $request->id,
                        'startDate' => $request->start_date,
                        'endDate' => $request->end_date,
                        'type' => $request->type,
                        'status' => $request->status,
                        'employeeName' => $request->user->name,
                        'days' => $request->days,
                    ];
                });

            // Fetch company settings for annual days (with caching)
            $annualDays = Cache::remember("company.{$companyId}.annual_days", now()->addHours(1), function () use ($companyId) {
                $settings = CompanySetting::query()->where('company_id', $companyId)->first();

                return $settings ? $settings->annual_days : 25;
            });

            // Fetch employees for admin dashboard
            $employees = [];
            if ($user->role === UserRole::ADMIN->value || $user->role === UserRole::OWNER->value) {
                $employeeModels = User::query()
                    ->where('company_id', $companyId)
                    ->select(['id', 'name', 'email', 'team_id', 'company_id']) // Only select necessary columns
                    ->with(['team:id,name']) // Eager load team with only necessary columns
                    ->withCount([
                        'vacation_requests as pending_requests_count' => function ($query) {
                            $query->where('status', VacationRequestStatus::PENDING);
                        },
                    ])
                    ->limit(100) // Limit to 100 employees on dashboard for performance
                    ->get();

                // Batch calculate balances to avoid N+1 queries
                $balances = $balanceService->getBatchBalanceSummaries($employeeModels);

                $employees = $employeeModels->map(function ($employee) use ($balances) {
                    $balance = $balances[$employee->id];

                    return [
                        'id' => $employee->id,
                        'name' => $employee->name,
                        'email' => $employee->email,
                        'department' => $employee->team?->name ?? 'Unassigned',
                        'totalDays' => $balance['annual_days'],
                        'usedDays' => $balance['used_days'],
                        'pendingRequests' => $employee->pending_requests_count,
                        'remainingDays' => $balance['remaining_balance'],
                        'availableDays' => $balance['available_balance'],
                        'pendingDays' => $balance['pending_days'],
                    ];
                });
            }

            // Count notifications (pending requests for the user) - cached for 1 minute
            $notificationCount = Cache::remember("notifications.{$user->id}.count", now()->addMinute(), function () use ($companyId, $user) {
                return VacationRequest::query()
                    ->where('company_id', $companyId)
                    ->where('user_id', $user->id)
                    ->where('status', VacationRequestStatus::PENDING)
                    ->count();
            });

            return [
                'requests' => $requests,
                'employees' => $employees,
                'totalDaysAllowed' => $annualDays,
                'notificationCount' => $notificationCount,
                'userBalance' => $userBalance,
            ];
        });

        return Inertia::render('Dashboard', array_merge($dashboardData, [
            'userRole' => $user->role,
            'userName' => $user->name,
        ]));
    }
}
