<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateCompanySettingsRequest;
use App\Models\Company;
use App\Models\CompanySetting;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CompanySettingsController extends Controller
{
    /**
     * Show the company settings page.
     */
    public function edit(): Response
    {
        $user = auth()->user();

        // Only owners and admins can access company settings
        $this->authorize('update', $user->company);

        $company = Company::with('company_settings')->findOrFail($user->company_id);

        // Get or create company settings with defaults
        $settings = $company->company_settings ?? CompanySetting::create([
            'company_id' => $company->id,
            'annual_days' => 20,
            'approval_required' => true,
        ]);

        return Inertia::render('settings/CompanySettings', [
            'company' => [
                'name' => $company->name,
            ],
            'settings' => [
                'annual_days' => $settings->annual_days,
                'approval_required' => (bool) $settings->approval_required,
                'timezone' => $company->timezone ?? 'UTC',
            ],
        ]);
    }

    /**
     * Update the company settings.
     */
    public function update(UpdateCompanySettingsRequest $request): RedirectResponse
    {
        $user = auth()->user();

        // Only owners and admins can update company settings
        $this->authorize('update', $user->company);

        $company = Company::with('company_settings')->findOrFail($user->company_id);
        $validated = $request->validated();

        // Update company information
        $company->update([
            'name' => $validated['name'],
            'timezone' => $validated['timezone'],
        ]);

        // Update or create company settings
        $company->company_settings()->updateOrCreate(
            ['company_id' => $company->id],
            [
                'annual_days' => $validated['annual_days'],
                'approval_required' => $validated['approval_required'],
            ]
        );

        return back()->with('success', 'Company settings updated successfully.');
    }
}
