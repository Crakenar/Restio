<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class RequestsController extends Controller
{
    public function index(): Response
    {
        $user = auth()->user();

        // Fetch requests scoped to company
        $requests = \App\Models\VacationRequest::query()
            ->with(['user.department'])
            ->where('company_id', $user->company_id)
            ->whereHas('user', function ($query) use ($user) {
                $query->where('company_id', $user->company_id);
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

        return Inertia::render('Requests', [
            'requests' => $requests,
            'userRole' => $user->role,
        ]);
    }
}
