<?php

namespace App\Policies;

use App\Enum\UserRole;
use App\Models\Company;
use App\Models\User;

class CompanyPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     * Users can only view their own company.
     */
    public function view(User $user, Company $company): bool
    {
        return $user->company_id === $company->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     * Only owners and admins can update company information.
     */
    public function update(User $user, Company $company): bool
    {
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
    public function manageSettings(User $user, Company $company): bool
    {
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
    public function manageSubscription(User $user, Company $company): bool
    {
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
    public function delete(User $user, Company $company): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Company $company): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Company $company): bool
    {
        return false;
    }
}
