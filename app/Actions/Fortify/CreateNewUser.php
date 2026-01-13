<?php

namespace App\Actions\Fortify;

use App\Enum\UserRole;
use App\Models\Company;
use App\Models\CompanySetting;
use App\Models\Department;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Throwable;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     *
     * @throws Throwable
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'company_name' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class),
            ],
            'password' => $this->passwordRules(),
        ])->validate();

        return DB::transaction(function () use ($input) {
            // Create Company
            $company = Company::query()->create([
                'name' => $input['company_name'],
                'slug' => Str::slug($input['company_name']),
                'timezone' => 'UTC',
            ]);

            // Create Company Settings
            CompanySetting::query()->create([
                'company_id' => $company->id,
                'annual_days' => 25,
                'approval_required' => true,
            ]);

            // Create Default Departments
            $departments = collect(['Engineering', 'Marketing', 'Sales', 'HR', 'Finance'])
                ->map(fn ($name) => Department::query()->create([
                    'company_id' => $company->id,
                    'name' => $name,
                ]));

            // Create Owner User (first user in company)
            return User::create([
                'name' => $input['name'],
                'email' => $input['email'],
                'password' => Hash::make($input['password']),
                'company_id' => $company->id,
                'role' => UserRole::OWNER,
                'department_id' => $departments->first()->id,
            ]);
        });
    }
}
