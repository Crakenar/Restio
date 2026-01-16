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

        // Fetch vacation requests scoped to company
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
                ];
            });

        return Inertia::render('VacationCalendarPage', [
            'requests' => $requests,
            'userName' => $user->name,
        ]);
    }
}
