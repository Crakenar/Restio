<?php

namespace App\Http\Controllers;

use App\Models\VacationRequest;
use Inertia\Inertia;
use Inertia\Response;

class CalendarController extends Controller
{
    public function index(): Response
    {
        $user = auth()->user();
        $companyId = $user->company_id;

        // Fetch vacation requests scoped to company - only user's own requests for employee role
        $requests = VacationRequest::query()
            ->with(['user.team'])
            ->where('company_id', $companyId)
            ->where('user_id', $user->id) // Only show user's own requests
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
                    'department' => $request->user->team?->name,
                    'days' => $request->days,
                    'reason' => $request->reason,
                ];
            });

        return Inertia::render('VacationCalendarPage', [
            'requests' => $requests,
            'userName' => $user->name,
            'userRole' => $user->role,
        ]);
    }
}
