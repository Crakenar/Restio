<?php

namespace App\Policies;

use App\Enum\UserRole;
use App\Models\Admin;
use App\Models\Team;
use App\Models\User;

class TeamPolicy
{
    /**
     * Determine whether the user can view any models.
     * Admins and owners can view all teams in their company.
     */
    public function viewAny(User|Admin $user): bool
    {
        // System admins can view all teams
        if ($user instanceof Admin) {
            return true;
        }

        $role = UserRole::from($user->role);

        return $role->isOwnerOrAdmin();
    }

    /**
     * Determine whether the user can view the model.
     * Admins and owners can view any team in their company.
     * Managers can view their own team.
     */
    public function view(User|Admin $user, Team $team): bool
    {
        // System admins can view any team
        if ($user instanceof Admin) {
            return true;
        }

        // Must be same company
        if ($user->company_id !== $team->company_id) {
            return false;
        }

        $role = UserRole::from($user->role);

        // Owner or admin can view any team in their company
        if ($role->isOwnerOrAdmin()) {
            return true;
        }

        // Managers can view their own team
        if ($role === UserRole::MANAGER && $user->team_id === $team->id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     * Only admins and owners can create teams.
     */
    public function create(User|Admin $user): bool
    {
        // System admins can create teams
        if ($user instanceof Admin) {
            return true;
        }

        $role = UserRole::from($user->role);

        return $role->isOwnerOrAdmin();
    }

    /**
     * Determine whether the user can update the model.
     * Only admins and owners can update teams.
     */
    public function update(User|Admin $user, Team $team): bool
    {
        // System admins can update any team
        if ($user instanceof Admin) {
            return true;
        }

        // Must be same company
        if ($user->company_id !== $team->company_id) {
            return false;
        }

        $role = UserRole::from($user->role);

        return $role->isOwnerOrAdmin();
    }

    /**
     * Determine whether the user can delete the model.
     * Only admins and owners can delete teams.
     */
    public function delete(User|Admin $user, Team $team): bool
    {
        // System admins can delete any team
        if ($user instanceof Admin) {
            return true;
        }

        // Must be same company
        if ($user->company_id !== $team->company_id) {
            return false;
        }

        $role = UserRole::from($user->role);

        return $role->isOwnerOrAdmin();
    }

    /**
     * Determine whether the user can assign users to the team.
     * Only admins and owners can assign users to teams.
     */
    public function assignUsers(User|Admin $user, Team $team): bool
    {
        // System admins can assign users to any team
        if ($user instanceof Admin) {
            return true;
        }

        // Must be same company
        if ($user->company_id !== $team->company_id) {
            return false;
        }

        $role = UserRole::from($user->role);

        return $role->isOwnerOrAdmin();
    }

    /**
     * Determine whether the user can remove users from the team.
     * Only admins and owners can remove users from teams.
     */
    public function removeUser(User|Admin $user, Team $team): bool
    {
        // System admins can remove users from any team
        if ($user instanceof Admin) {
            return true;
        }

        // Must be same company
        if ($user->company_id !== $team->company_id) {
            return false;
        }

        $role = UserRole::from($user->role);

        return $role->isOwnerOrAdmin();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User|Admin $user, Team $team): bool
    {
        // Only system admins can restore teams
        return $user instanceof Admin;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User|Admin $user, Team $team): bool
    {
        // Only system admins can force delete teams
        return $user instanceof Admin;
    }
}
