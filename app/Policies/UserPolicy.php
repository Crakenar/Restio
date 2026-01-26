<?php

namespace App\Policies;

use App\Enum\UserRole;
use App\Models\Admin;
use App\Models\User;

class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     * Admins and owners can view all users in their company.
     */
    public function viewAny(User|Admin $user): bool
    {
        // System admins can view all users
        if ($user instanceof Admin) {
            return true;
        }

        $role = UserRole::from($user->role);

        return $role->isOwnerOrAdmin();
    }

    /**
     * Determine whether the user can view the model.
     * Admins and owners can view any user in their company.
     * Managers can view users in their team.
     * Users can view themselves.
     */
    public function view(User|Admin $user, User $model): bool
    {
        // System admins can view any user
        if ($user instanceof Admin) {
            return true;
        }

        // Must be same company
        if ($user->company_id !== $model->company_id) {
            return false;
        }

        $role = UserRole::from($user->role);

        // Owner or admin can view any user in their company
        if ($role->isOwnerOrAdmin()) {
            return true;
        }

        // Users can view themselves
        if ($user->id === $model->id) {
            return true;
        }

        // Managers can view users in their team
        if ($role === UserRole::MANAGER && $user->team_id !== null) {
            return $model->team_id === $user->team_id;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     * Only admins and owners can create new users.
     */
    public function create(User|Admin $user): bool
    {
        // System admins can create users
        if ($user instanceof Admin) {
            return true;
        }

        $role = UserRole::from($user->role);

        return $role->isOwnerOrAdmin();
    }

    /**
     * Determine whether the user can update the model.
     * Admins and owners can update any user in their company.
     * Users can update their own profile information.
     */
    public function update(User|Admin $user, User $model): bool
    {
        // System admins can update any user
        if ($user instanceof Admin) {
            return true;
        }

        // Must be same company
        if ($user->company_id !== $model->company_id) {
            return false;
        }

        $role = UserRole::from($user->role);

        // Owner or admin can update any user in their company
        if ($role->isOwnerOrAdmin()) {
            return true;
        }

        // Users can update their own profile
        return $user->id === $model->id;
    }

    /**
     * Determine whether the user can delete the model.
     * Only admins and owners can delete users.
     * Cannot delete yourself.
     */
    public function delete(User|Admin $user, User $model): bool
    {
        // System admins can delete any user
        if ($user instanceof Admin) {
            return true;
        }

        // Must be same company
        if ($user->company_id !== $model->company_id) {
            return false;
        }

        // Cannot delete yourself
        if ($user->id === $model->id) {
            return false;
        }

        $role = UserRole::from($user->role);

        return $role->isOwnerOrAdmin();
    }

    /**
     * Determine whether the user can import users via CSV.
     * Only admins and owners can import users.
     */
    public function importCsv(User|Admin $user): bool
    {
        // System admins can import users
        if ($user instanceof Admin) {
            return true;
        }

        $role = UserRole::from($user->role);

        return $role->isOwnerOrAdmin();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User|Admin $user, User $model): bool
    {
        // Only system admins can restore users
        return $user instanceof Admin;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User|Admin $user, User $model): bool
    {
        // Only system admins can force delete users
        return $user instanceof Admin;
    }
}
