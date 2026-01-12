<?php

namespace App\Actions;

use App\Enum\UserRole;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ImportEmployeesFromCsv
{
    /**
     * Import employees from a CSV file.
     *
     * @return array{success: int, failed: int, errors: array}
     */
    public function execute(UploadedFile $file, int $companyId): array
    {
        $successCount = 0;
        $failedCount = 0;
        $errors = [];

        // Read CSV file
        $csvData = array_map('str_getcsv', file($file->getRealPath()));
        $header = array_shift($csvData);

        // Expected headers
        $expectedHeaders = ['name', 'email', 'password', 'role'];
        $header = array_map('strtolower', array_map('trim', $header));

        if ($header !== $expectedHeaders) {
            throw ValidationException::withMessages([
                'file' => ['CSV file must have headers: name, email, password, role'],
            ]);
        }

        foreach ($csvData as $index => $row) {
            $lineNumber = $index + 2; // +2 because index starts at 0 and we removed header

            // Skip empty rows
            if (empty(array_filter($row))) {
                continue;
            }

            $employeeData = [
                'name' => trim($row[0] ?? ''),
                'email' => trim($row[1] ?? ''),
                'password' => trim($row[2] ?? ''),
                'role' => trim($row[3] ?? ''),
            ];

            // Validate row data
            $validator = Validator::make($employeeData, [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
                'password' => ['required', 'string', 'min:8'],
                'role' => ['required', 'string', Rule::in([UserRole::EMPLOYEE->value, UserRole::MANAGER->value, UserRole::ADMIN->value])],
            ]);

            if ($validator->fails()) {
                $failedCount++;
                $errors[] = [
                    'line' => $lineNumber,
                    'data' => $employeeData,
                    'errors' => $validator->errors()->all(),
                ];

                continue;
            }

            try {
                User::create([
                    'name' => $employeeData['name'],
                    'email' => $employeeData['email'],
                    'password' => Hash::make($employeeData['password']),
                    'role' => $employeeData['role'],
                    'company_id' => $companyId,
                ]);

                $successCount++;
            } catch (\Exception $e) {
                $failedCount++;
                $errors[] = [
                    'line' => $lineNumber,
                    'data' => $employeeData,
                    'errors' => ['Failed to create employee: '.$e->getMessage()],
                ];
            }
        }

        return [
            'success' => $successCount,
            'failed' => $failedCount,
            'errors' => $errors,
        ];
    }
}
