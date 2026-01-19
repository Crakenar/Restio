<?php

namespace App\Http\Controllers;

use App\Enum\UserRole;
use App\Enum\VacationRequestStatus;
use App\Models\CompanySetting;
use App\Models\User;
use App\Models\VacationRequest;
use App\Services\VacationBalanceService;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $user = auth()->user();
        $companyId = $user->company_id;
        $balanceService = app(VacationBalanceService::class);

        // Get current user's balance summary
        $userBalance = $balanceService->getBalanceSummary($user);

        // Fetch requests scoped to company
        $requests = VacationRequest::query()
            ->with(['user'])
            ->where('company_id', $companyId)
            ->whereHas('user', function ($query) use ($companyId) {
                $query->where('company_id', $companyId);
            })
            ->latest()
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

        // Fetch company settings for annual days
        $settings = CompanySetting::query()->where('company_id', $companyId)->first();
        $annualDays = $settings ? $settings->annual_days : 25;

        // Fetch employees for admin dashboard
        $employees = [];
        if ($user->role === UserRole::ADMIN->value || $user->role === UserRole::OWNER->value) {
            $employees = User::query()
                ->where('company_id', $companyId)
                ->withCount([
                    'vacation_requests as pending_requests_count' => function ($query) {
                        $query->where('status', VacationRequestStatus::PENDING);
                    },
                ])
                ->get()
                ->map(function ($employee) use ($balanceService) {
                    $balance = $balanceService->getBalanceSummary($employee);

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

        // Count notifications (pending requests for the user)
        $notificationCount = VacationRequest::query()
            ->where('company_id', $companyId)
            ->where('user_id', $user->id)
            ->where('status', VacationRequestStatus::PENDING)
            ->count();

        return Inertia::render('Dashboard', [
            'requests' => $requests,
            'employees' => $employees,
            'userRole' => $user->role,
            'userName' => $user->name,
            'totalDaysAllowed' => $annualDays,
            'notificationCount' => $notificationCount,
            'userBalance' => $userBalance,
        ]);
    }
}
