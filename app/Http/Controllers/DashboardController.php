<?php

namespace App\Http\Controllers;

use App\Enum\UserRole;
use App\Enum\VacationRequestStatus;
use App\Enum\VacationRequestType;
use App\Models\CompanySetting;
use App\Models\User;
use App\Models\VacationRequest;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $user = auth()->user();
        $companyId = $user->company_id;

        // Fetch requests scoped to company
        $requests = VacationRequest::query()
            ->with(['user.department'])
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
                    'department' => $request->user->department?->name ?? 'Unassigned',
                ];
            });

        // Fetch company settings for annual days
        $settings = CompanySetting::query()->where('company_id', $companyId)->first();
        $annualDays = $settings ? $settings->annual_days : 25;

        // Fetch employees for admin dashboard
        $employees = [];
        if ($user->role === UserRole::ADMIN->value) {
            $employees = User::query()
                ->where('company_id', $companyId)
                ->with('department')
                ->withCount([
                    'vacation_requests as pending_requests_count' => function ($query) {
                        $query->where('status', VacationRequestStatus::PENDING);
                    },
                ])
                ->get()
                ->map(function ($employee) use ($annualDays) {
                    // Calculate used days from approved requests in current year
                    $usedDays = $employee->vacation_requests
                        ->where('status', VacationRequestStatus::APPROVED)
                        ->where('type', VacationRequestType::VACATION)
                        ->where('start_date', '>=', now()->startOfYear())
                        ->sum(function ($req) {
                            return $req->start_date->diffInDays($req->end_date) + 1;
                        });

                    return [
                        'id' => $employee->id,
                        'name' => $employee->name,
                        'email' => $employee->email,
                        'department' => $employee->department?->name ?? 'Unassigned',
                        'totalDays' => $annualDays,
                        'usedDays' => $usedDays,
                        'pendingRequests' => $employee->pending_requests_count,
                    ];
                });
        }

        return Inertia::render('Dashboard', [
            'requests' => $requests,
            'employees' => $employees,
            'userRole' => $user->role,
            'userName' => $user->name,
            'totalDaysAllowed' => $annualDays,
        ]);
    }
}
