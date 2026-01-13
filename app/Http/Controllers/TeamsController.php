<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class TeamsController extends Controller
{
    public function index(): Response
    {
        $user = auth()->user();
        $companyId = $user->company_id;

        // Fetch requests scoped to company (for team calendar view)
        $requests = \App\Models\VacationRequest::query()
            ->with(['user.department'])
            ->where('company_id', $companyId)
            ->whereHas('user', function ($query) use ($companyId) {
                $query->where('company_id', $companyId);
            })
            // Fetch requests for current month +/- 1 month roughly, or just all future?
            // For now, let's fetch roughly relevant requests (e.g. starting from last month) to avoid loading years of history
            ->where('end_date', '>=', now()->subMonth())
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
        $settings = \App\Models\CompanySetting::where('company_id', $companyId)->first();
        $annualDays = $settings ? $settings->annual_days : 25;

        // Fetch employees
        $employees = \App\Models\User::query()
            ->where('company_id', $companyId)
            ->with(['department', 'vacation_requests'])
            ->get()
            ->map(function ($employee) use ($annualDays) {
                // Calculate used days from approved requests in current year
                $usedDays = $employee->vacation_requests
                    ->where('status', \App\Enum\VacationRequestStatus::APPROVED->value)
                    ->where('type', \App\Enum\VacationRequestType::VACATION->value)
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
                    'pendingRequests' => $employee->vacation_requests->where('status', \App\Enum\VacationRequestStatus::PENDING->value)->count(),
                ];
            });

        return Inertia::render('Team', [
            'requests' => $requests,
            'employees' => $employees,
            'userRole' => $user->role,
        ]);
    }
}
