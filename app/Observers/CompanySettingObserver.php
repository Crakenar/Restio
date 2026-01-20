<?php

namespace App\Observers;

use App\Models\CompanySetting;
use Illuminate\Support\Facades\Cache;

class CompanySettingObserver
{
    /**
     * Handle the CompanySetting "created" event.
     */
    public function created(CompanySetting $companySetting): void
    {
        $this->clearCompanyCache($companySetting);
    }

    /**
     * Handle the CompanySetting "updated" event.
     */
    public function updated(CompanySetting $companySetting): void
    {
        $this->clearCompanyCache($companySetting);
    }

    /**
     * Handle the CompanySetting "deleted" event.
     */
    public function deleted(CompanySetting $companySetting): void
    {
        $this->clearCompanyCache($companySetting);
    }

    /**
     * Handle the CompanySetting "restored" event.
     */
    public function restored(CompanySetting $companySetting): void
    {
        $this->clearCompanyCache($companySetting);
    }

    /**
     * Handle the CompanySetting "force deleted" event.
     */
    public function forceDeleted(CompanySetting $companySetting): void
    {
        $this->clearCompanyCache($companySetting);
    }

    /**
     * Clear all cache related to a company when settings change.
     */
    protected function clearCompanyCache(CompanySetting $companySetting): void
    {
        $companyId = $companySetting->company_id;

        // Clear company-specific caches
        Cache::forget("company.{$companyId}.annual_days");

        // Clear dashboard caches for all users in this company
        // In production, you might want to use Cache::tags() if your cache driver supports it
        Cache::flush(); // For simplicity, clear all cache. In production, use more targeted clearing.
    }
}
