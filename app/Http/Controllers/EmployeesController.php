<?php

namespace App\Http\Controllers;

use App\Actions\ImportEmployeesFromCsv;
use App\Http\Requests\ImportEmployeesRequest;
use App\Http\Requests\StoreEmployeeRequest;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class EmployeesController extends Controller
{
    public function __construct(protected AuditLogger $auditLogger) {}

    /**
     * Display the employees management page.
     */
    public function index(): Response
    {
        $user = auth()->user();

        // Authorize viewing employees (only admins and owners)
        $this->authorize('viewAny', User::class);

        $employees = User::query()
            ->where('company_id', $user->company_id)
            ->orderBy('created_at', 'desc')
            ->paginate(50) // 50 items per page
            ->through(fn ($employee) => [
                'id' => $employee->id,
                'name' => $employee->name,
                'email' => $employee->email,
                'role' => $employee->role,
                'created_at' => $employee->created_at->format('Y-m-d H:i:s'),
            ]);

        return Inertia::render('Employees', [
            'employees' => $employees,
            'subscription_info' => [
                'current_user_count' => $user->company->current_user_count,
                'user_limit' => $user->company->user_limit,
                'remaining_slots' => $user->company->remaining_user_slots,
                'is_near_limit' => $user->company->isNearUserLimit(),
                'has_reached_limit' => $user->company->hasReachedUserLimit(),
                'can_add_users' => $user->company->canAddUsers(),
            ],
        ]);
    }

    /**
     * Store a new employee.
     */
    public function store(StoreEmployeeRequest $request): RedirectResponse
    {
        // Authorize employee creation (only admins and owners)
        $this->authorize('create', User::class);

        $company = auth()->user()->company;

        // Check subscription user limit
        if (!$company->canAddUsers(1)) {
            return redirect()
                ->back()
                ->with('error', "User limit reached ({$company->user_limit} users). Please upgrade your subscription to add more employees.")
                ->with('upgrade_required', true)
                ->with('upgrade_url', route('subscription.index'));
        }

        $employee = User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
            'role' => $request->input('role'),
            'company_id' => auth()->user()->company_id,
        ]);

        // Log the user creation
        $this->auditLogger->created($employee, [
            'name' => $employee->name,
            'email' => $employee->email,
            'role' => $employee->role,
        ]);

        return redirect()->back()->with('success', 'Employee created successfully');
    }

    /**
     * Import employees from CSV.
     */
    public function importCsv(ImportEmployeesRequest $request, ImportEmployeesFromCsv $importAction): RedirectResponse
    {
        // Authorize CSV import (only admins and owners)
        $this->authorize('importCsv', User::class);

        $file = $request->file('file');
        $company = auth()->user()->company;
        $companyId = $company->id;

        // Count rows in CSV to check if we can add that many users
        $csvData = array_map('str_getcsv', file($file->getRealPath()));
        $rowCount = count($csvData) - 1; // Subtract header row

        if (!$company->canAddUsers($rowCount)) {
            $remainingSlots = $company->remaining_user_slots;
            return redirect()
                ->back()
                ->with('error', "Cannot import {$rowCount} employees. You can only add {$remainingSlots} more users. Current limit: {$company->user_limit} users. Please upgrade your subscription.")
                ->with('upgrade_required', true)
                ->with('upgrade_url', route('subscription.index'));
        }

        try {
            $result = $importAction->execute($file, $companyId);

            if ($result['failed'] > 0) {
                return redirect()->back()->with('warning', [
                    'message' => "Import completed with errors. {$result['success']} employees imported, {$result['failed']} failed.",
                    'errors' => $result['errors'],
                ]);
            }

            return redirect()->back()->with('success', "{$result['success']} employees imported successfully");
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->errors());
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to import employees: '.$e->getMessage());
        }
    }
}
