<?php

namespace App\Http\Controllers;

use App\Enum\UserRole;
use App\Enum\VacationRequestStatus;
use App\Enum\VacationRequestType;
use Inertia\Inertia;
use Inertia\Response;

class RequestsController extends Controller
{
    public function index(): Response
    {
        // TODO: Replace with real database queries
        $requests = $this->getFakeRequests();

        // Get current user role (will be replaced with auth()->user()->role)
        $userRole = UserRole::MANAGER->value; // TODO: Replace with auth()->user()->role

        return Inertia::render('Requests', [
            'requests' => $requests,
            'userRole' => $userRole,
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
                'type' => VacationRequestType::VACATION->value,
                'status' => VacationRequestStatus::APPROVED->value,
                'employeeName' => 'John Doe',
                'department' => 'Engineering',
            ],
            [
                'id' => '2',
                'startDate' => '2026-02-05T00:00:00.000Z',
                'endDate' => '2026-02-07T00:00:00.000Z',
                'type' => VacationRequestType::SICK->value,
                'status' => VacationRequestStatus::PENDING->value,
                'employeeName' => 'Jane Smith',
                'department' => 'Marketing',
            ],
            [
                'id' => '3',
                'startDate' => '2026-02-14T00:00:00.000Z',
                'endDate' => '2026-02-14T00:00:00.000Z',
                'type' => VacationRequestType::PERSONAL->value,
                'status' => VacationRequestStatus::APPROVED->value,
                'employeeName' => 'John Doe',
                'department' => 'Engineering',
            ],
            [
                'id' => '4',
                'startDate' => '2026-01-15T00:00:00.000Z',
                'endDate' => '2026-01-15T00:00:00.000Z',
                'type' => VacationRequestType::WFH->value,
                'status' => VacationRequestStatus::APPROVED->value,
                'employeeName' => 'Bob Johnson',
                'department' => 'Sales',
            ],
            [
                'id' => '5',
                'startDate' => '2026-02-10T00:00:00.000Z',
                'endDate' => '2026-02-12T00:00:00.000Z',
                'type' => VacationRequestType::VACATION->value,
                'status' => VacationRequestStatus::PENDING->value,
                'employeeName' => 'Alice Brown',
                'department' => 'Engineering',
            ],
            [
                'id' => '6',
                'startDate' => '2026-03-01T00:00:00.000Z',
                'endDate' => '2026-03-05T00:00:00.000Z',
                'type' => VacationRequestType::VACATION->value,
                'status' => VacationRequestStatus::REJECTED->value,
                'employeeName' => 'Charlie Wilson',
                'department' => 'HR',
            ],
        ];
    }
}
