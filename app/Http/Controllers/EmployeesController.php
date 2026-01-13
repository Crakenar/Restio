<?php

namespace App\Http\Controllers;

use App\Actions\ImportEmployeesFromCsv;
use App\Enum\UserRole;
use App\Http\Requests\ImportEmployeesRequest;
use App\Http\Requests\StoreEmployeeRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class EmployeesController extends Controller
{
    /**
     * Display the employees management page.
     */
    public function index(): Response
    {
        $user = auth()->user();

        // Only admins can access employee management
        abort_if(
            $user->role !== UserRole::ADMIN->value,
            HttpResponse::HTTP_FORBIDDEN,
            'Only administrators can access employee management.'
        );

        $employees = User::query()
            ->where('company_id', $user->company_id)
            ->with('department')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn ($employee) => [
                'id' => $employee->id,
                'name' => $employee->name,
                'email' => $employee->email,
                'role' => $employee->role,
                'department_id' => $employee->department_id,
                'department' => $employee->department?->name ?? 'Unassigned',
                'created_at' => $employee->created_at->format('Y-m-d H:i:s'),
            ]);

        $departments = \App\Models\Department::query()
            ->where('company_id', $user->company_id)
            ->orderBy('name')
            ->get()
            ->map(fn ($dept) => [
                'id' => $dept->id,
                'name' => $dept->name,
            ]);

        return Inertia::render('Employees', [
            'employees' => $employees,
            'departments' => $departments,
        ]);
    }

    /**
     * Store a new employee.
     */
    public function store(StoreEmployeeRequest $request): RedirectResponse
    {
        User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
            'role' => $request->input('role'),
            'company_id' => auth()->user()->company_id,
            'department_id' => $request->input('department_id'),
        ]);

        return redirect()->back()->with('success', 'Employee created successfully');
    }

    /**
     * Import employees from CSV.
     */
    public function importCsv(ImportEmployeesRequest $request, ImportEmployeesFromCsv $importAction): RedirectResponse
    {
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
