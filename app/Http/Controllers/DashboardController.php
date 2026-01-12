<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        // TODO: Replace with real database queries
        // Fetch vacation requests
        $requests = $this->getFakeRequests();

        // Fetch employees (for admin view)
        $employees = $this->getFakeEmployees();

        // Get current user role (will be replaced with auth()->user()->role)
        $userRole = 'employee'; // TODO: Replace with auth()->user()->role

        return Inertia::render('Dashboard', [
            'requests' => $requests,
            'employees' => $employees,
            'userRole' => $userRole,
            'userName' => 'John Doe', // TODO: Replace with auth()->user()->name
            'totalDaysAllowed' => 25, // TODO: Fetch from user's company settings
        ]);
    }

    /**
     * Temporary method to return fake request data
     * TODO: Replace with VacationRequest::query()->with('user')->get()
     */
    private function getFakeRequests(): array
    {
        return [
            [
                'id' => '1',
                'startDate' => '2026-01-20T00:00:00.000Z',
                'endDate' => '2026-01-24T00:00:00.000Z',
                'type' => 'vacation',
                'status' => 'approved',
                'employeeName' => 'John Doe',
                'department' => 'Engineering',
            ],
            [
                'id' => '2',
                'startDate' => '2026-02-05T00:00:00.000Z',
                'endDate' => '2026-02-07T00:00:00.000Z',
                'type' => 'sick',
                'status' => 'pending',
                'employeeName' => 'Jane Smith',
                'department' => 'Marketing',
            ],
            [
                'id' => '3',
                'startDate' => '2026-02-14T00:00:00.000Z',
                'endDate' => '2026-02-14T00:00:00.000Z',
                'type' => 'personal',
                'status' => 'approved',
                'employeeName' => 'John Doe',
                'department' => 'Engineering',
            ],
            [
                'id' => '4',
                'startDate' => '2026-01-15T00:00:00.000Z',
                'endDate' => '2026-01-15T00:00:00.000Z',
                'type' => 'wfh',
                'status' => 'approved',
                'employeeName' => 'Bob Johnson',
                'department' => 'Sales',
            ],
            [
                'id' => '5',
                'startDate' => '2026-02-10T00:00:00.000Z',
                'endDate' => '2026-02-12T00:00:00.000Z',
                'type' => 'vacation',
                'status' => 'approved',
                'employeeName' => 'Alice Brown',
                'department' => 'Engineering',
            ],
            [
                'id' => '6',
                'startDate' => '2026-02-20T00:00:00.000Z',
                'endDate' => '2026-02-22T00:00:00.000Z',
                'type' => 'vacation',
                'status' => 'pending',
                'employeeName' => 'Charlie Wilson',
                'department' => 'HR',
            ],
            [
                'id' => '7',
                'startDate' => '2026-01-25T00:00:00.000Z',
                'endDate' => '2026-01-25T00:00:00.000Z',
                'type' => 'sick',
                'status' => 'rejected',
                'employeeName' => 'Diana Prince',
                'department' => 'Engineering',
            ],
        ];
    }

    /**
     * Temporary method to return fake employee data
     * TODO: Replace with User::query()->with('vacation_requests')->get()
     */
    private function getFakeEmployees(): array
    {
        return [
            [
                'id' => '1',
                'name' => 'John Doe',
                'email' => 'john.doe@company.com',
                'department' => 'Engineering',
                'totalDays' => 25,
                'usedDays' => 8,
                'pendingRequests' => 0,
            ],
            [
                'id' => '2',
                'name' => 'Jane Smith',
                'email' => 'jane.smith@company.com',
                'department' => 'Marketing',
                'totalDays' => 25,
                'usedDays' => 5,
                'pendingRequests' => 1,
            ],
            [
                'id' => '3',
                'name' => 'Bob Johnson',
                'email' => 'bob.johnson@company.com',
                'department' => 'Sales',
                'totalDays' => 20,
                'usedDays' => 3,
                'pendingRequests' => 0,
            ],
            [
                'id' => '4',
                'name' => 'Alice Brown',
                'email' => 'alice.brown@company.com',
                'department' => 'Engineering',
                'totalDays' => 25,
                'usedDays' => 6,
                'pendingRequests' => 0,
            ],
            [
                'id' => '5',
                'name' => 'Charlie Wilson',
                'email' => 'charlie.wilson@company.com',
                'department' => 'HR',
                'totalDays' => 25,
                'usedDays' => 10,
                'pendingRequests' => 0,
            ],
            [
                'id' => '6',
                'name' => 'Diana Prince',
                'email' => 'diana.prince@company.com',
                'department' => 'Engineering',
                'totalDays' => 25,
                'usedDays' => 4,
                'pendingRequests' => 0,
            ],
            [
                'id' => '7',
                'name' => 'Evan Davis',
                'email' => 'evan.davis@company.com',
                'department' => 'Marketing',
                'totalDays' => 25,
                'usedDays' => 7,
                'pendingRequests' => 0,
            ],
            [
                'id' => '8',
                'name' => 'Fiona Green',
                'email' => 'fiona.green@company.com',
                'department' => 'Sales',
                'totalDays' => 20,
                'usedDays' => 2,
                'pendingRequests' => 0,
            ],
        ];
    }
}
