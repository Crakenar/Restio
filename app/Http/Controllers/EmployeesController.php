<?php

namespace App\Http\Controllers;

use App\Actions\ImportEmployeesFromCsv;
use App\Http\Requests\ImportEmployeesRequest;
use App\Http\Requests\StoreEmployeeRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class EmployeesController extends Controller
{
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
        ]);
    }

    /**
     * Store a new employee.
     */
    public function store(StoreEmployeeRequest $request): RedirectResponse
    {
        // Authorize employee creation (only admins and owners)
        $this->authorize('create', User::class);

        User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
            'role' => $request->input('role'),
            'company_id' => auth()->user()->company_id,
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
        $companyId = auth()->user()->company_id;

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
