<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RequestsController extends Controller
{
    public function index(Request $request): Response
    {
        $user = auth()->user();
        $statusFilter = $request->query('status'); // Get status from query params

        // Base query
        $query = \App\Models\VacationRequest::query()
            ->with(['user:id,name']) // Only load necessary columns
            ->where('company_id', $user->company_id);

        // Apply status filter if provided
        if ($statusFilter && in_array($statusFilter, ['pending', 'approved', 'rejected'])) {
            $query->where('status', $statusFilter);
        }

        // Fetch requests with pagination
        $requests = $query
            ->latest()
            ->paginate(50) // 50 items per page
            ->withQueryString() // Preserve query parameters in pagination links
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

        // Get counts for each status (optimized - single query per status)
        $baseCountQuery = \App\Models\VacationRequest::where('company_id', $user->company_id);
        $counts = [
            'all' => (clone $baseCountQuery)->count(),
            'pending' => (clone $baseCountQuery)->where('status', 'pending')->count(),
            'approved' => (clone $baseCountQuery)->where('status', 'approved')->count(),
            'rejected' => (clone $baseCountQuery)->where('status', 'rejected')->count(),
        ];

        return Inertia::render('Requests', [
            'requests' => $requests,
            'counts' => $counts,
            'currentStatus' => $statusFilter ?? 'all',
            'userRole' => $user->role,
        ]);
    }
}
