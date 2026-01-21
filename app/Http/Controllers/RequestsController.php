<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class RequestsController extends Controller
{
    public function index(): Response
    {
        $user = auth()->user();

        // Fetch requests scoped to company with pagination
        $requests = \App\Models\VacationRequest::query()
            ->with(['user:id,name']) // Only load necessary columns
            ->where('company_id', $user->company_id)
            ->latest()
            ->paginate(50) // 50 items per page
            ->through(function ($request) {
                return [
                    'id' => $request->id,
                    'startDate' => $request->start_date,
                    'endDate' => $request->end_date,
                    'type' => $request->type,
                    'status' => $request->status,
                    'reason' => $request->reason,
                    'rejectionReason' => $request->rejection_reason,
                    'employeeName' => $request->user->name,
                ];
            });

        return Inertia::render('Requests', [
            'requests' => $requests,
            'userRole' => $user->role,
        ]);
    }
}
