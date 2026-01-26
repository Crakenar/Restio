<?php

namespace App\Policies;

use App\Enum\UserRole;
use App\Models\Admin;
use App\Models\Company;
use App\Models\User;

class CompanyPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User|Admin $user): bool
    {
        // Admins can view all companies
        if ($user instanceof Admin) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can view the model.
     * Users can only view their own company.
     */
    public function view(User|Admin $user, Company $company): bool
    {
        // Admins can view any company
        if ($user instanceof Admin) {
            return true;
        }

        return $user->company_id === $company->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User|Admin $user): bool
    {
        // Admins can create companies
        if ($user instanceof Admin) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can update the model.
     * Only owners and admins can update company information.
     */
    public function update(User|Admin $user, Company $company): bool
    {
        // Admins can update any company
        if ($user instanceof Admin) {
            return true;
        }

        // Must be their own company
        if ($user->company_id !== $company->id) {
            return false;
        }

        $role = UserRole::from($user->role);

        return $role->isOwnerOrAdmin();
    }

    /**
     * Determine whether the user can manage company settings.
     * Only owners and admins can manage company settings.
     */
    public function manageSettings(User|Admin $user, Company $company): bool
    {
        // Admins can manage any company settings
        if ($user instanceof Admin) {
            return true;
        }

        // Must be their own company
        if ($user->company_id !== $company->id) {
            return false;
        }

        $role = UserRole::from($user->role);

        return $role->isOwnerOrAdmin();
    }

    /**
     * Determine whether the user can manage subscriptions.
     * Only owners can manage subscriptions.
     */
    public function manageSubscription(User|Admin $user, Company $company): bool
    {
        // Admins can manage any subscriptions
        if ($user instanceof Admin) {
            return true;
        }

        // Must be their own company
        if ($user->company_id !== $company->id) {
            return false;
        }

        $role = UserRole::from($user->role);

        return $role->canManageSubscription();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User|Admin $user, Company $company): bool
    {
        // Only admins can delete companies
        return $user instanceof Admin;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User|Admin $user, Company $company): bool
    {
        // Only admins can restore companies
        return $user instanceof Admin;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User|Admin $user, Company $company): bool
    {
        // Only admins can force delete companies
        return $user instanceof Admin;
    }
}
