<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateCompanySettingsRequest;
use App\Models\Company;
use App\Models\CompanySetting;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CompanySettingsController extends Controller
{
    public function __construct(protected AuditLogger $auditLogger) {}

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

        // Capture original values for audit log
        $originalCompany = $company->replicate();
        $originalSettings = $company->company_settings?->replicate();

        // Update company information
        $company->update([
            'name' => $validated['name'],
            'timezone' => $validated['timezone'],
        ]);

        // Update or create company settings
        $settings = $company->company_settings()->updateOrCreate(
            ['company_id' => $company->id],
            [
                'annual_days' => $validated['annual_days'],
                'approval_required' => $validated['approval_required'],
            ]
        );

        // Log company settings changes
        $this->auditLogger->settingsChanged($settings, [
            'company_name_changed' => $originalCompany->name !== $company->name,
            'old_company_name' => $originalCompany->name,
            'new_company_name' => $company->name,
            'old_timezone' => $originalCompany->timezone,
            'new_timezone' => $company->timezone,
            'old_annual_days' => $originalSettings?->annual_days,
            'new_annual_days' => $settings->annual_days,
            'old_approval_required' => $originalSettings?->approval_required,
            'new_approval_required' => $settings->approval_required,
        ]);

        return back()->with('success', 'Company settings updated successfully.');
    }
}
