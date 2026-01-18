<?php

namespace App\Policies;

use App\Enum\UserRole;
use App\Enum\VacationRequestStatus;
use App\Models\User;
use App\Models\VacationRequest;
use Illuminate\Auth\Access\Response;

class VacationRequestPolicy
{
    /**
     * Determine whether the user can view any models.
     * All authenticated users can view vacation requests (filtered by company in controller).
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     * Users can view their own requests.
     * Managers can view requests from users in their team.
     * Admins and owners can view any request in their company.
     */
    public function view(User $user, VacationRequest $vacationRequest): bool
    {
        // Must be same company
        if ($user->company_id !== $vacationRequest->company_id) {
            return false;
        }

        // Owner or admin can view any request in their company
        $role = UserRole::from($user->role);
        if ($role->isOwnerOrAdmin()) {
            return true;
        }

        // Users can view their own requests
        if ($user->id === $vacationRequest->user_id) {
            return true;
        }

        // Managers can view requests from users in their team
        if ($role === UserRole::MANAGER && $user->team_id !== null) {
            $requestUser = $vacationRequest->user;

            return $requestUser->team_id === $user->team_id;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     * All employees can create vacation requests.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     * Users can only update their own PENDING requests.
     */
    public function update(User $user, VacationRequest $vacationRequest): bool
    {
        // Must be same company
        if ($user->company_id !== $vacationRequest->company_id) {
            return false;
        }

        // Must be the owner of the request
        if ($user->id !== $vacationRequest->user_id) {
            return false;
        }

        // Can only update pending requests
        return $vacationRequest->status === VacationRequestStatus::PENDING;
    }

    /**
     * Determine whether the user can delete the model.
     * Users can only delete their own PENDING requests.
     */
    public function delete(User $user, VacationRequest $vacationRequest): bool
    {
        // Must be same company
        if ($user->company_id !== $vacationRequest->company_id) {
            return false;
        }

        // Must be the owner of the request
        if ($user->id !== $vacationRequest->user_id) {
            return false;
        }

        // Can only delete pending requests
        return $vacationRequest->status === VacationRequestStatus::PENDING;
    }

    /**
     * Determine whether the user can approve the model.
     * Managers can approve requests from users in their team.
     * Admins and owners can approve any request in their company.
     */
    public function approve(User $user, VacationRequest $vacationRequest): Response
    {
        // Must be same company
        if ($user->company_id !== $vacationRequest->company_id) {
            return Response::deny('You cannot approve requests from another company.');
        }

        // Can only approve pending requests
        if ($vacationRequest->status !== VacationRequestStatus::PENDING) {
            return Response::deny('Only pending requests can be approved.');
        }

        // Cannot approve your own request
        if ($user->id === $vacationRequest->user_id) {
            return Response::deny('You cannot approve your own vacation request.');
        }

        $role = UserRole::from($user->role);

        // Owner or admin can approve any request in their company
        if ($role->isOwnerOrAdmin()) {
            return Response::allow();
        }

        // Managers can approve requests from users in their team
        if ($role === UserRole::MANAGER && $user->team_id !== null) {
            $requestUser = $vacationRequest->user;

            if ($requestUser->team_id === $user->team_id) {
                return Response::allow();
            }

            return Response::deny('You can only approve requests from your team members.');
        }

        return Response::deny('You do not have permission to approve vacation requests.');
    }

    /**
     * Determine whether the user can reject the model.
     * Same permissions as approve.
     */
    public function reject(User $user, VacationRequest $vacationRequest): Response
    {
        // Must be same company
        if ($user->company_id !== $vacationRequest->company_id) {
            return Response::deny('You cannot reject requests from another company.');
        }

        // Can only reject pending requests
        if ($vacationRequest->status !== VacationRequestStatus::PENDING) {
            return Response::deny('Only pending requests can be rejected.');
        }

        // Cannot reject your own request
        if ($user->id === $vacationRequest->user_id) {
            return Response::deny('You cannot reject your own vacation request.');
        }

        $role = UserRole::from($user->role);

        // Owner or admin can reject any request in their company
        if ($role->isOwnerOrAdmin()) {
            return Response::allow();
        }

        // Managers can reject requests from users in their team
        if ($role === UserRole::MANAGER && $user->team_id !== null) {
            $requestUser = $vacationRequest->user;

            if ($requestUser->team_id === $user->team_id) {
                return Response::allow();
            }

            return Response::deny('You can only reject requests from your team members.');
        }

        return Response::deny('You do not have permission to reject vacation requests.');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, VacationRequest $vacationRequest): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, VacationRequest $vacationRequest): bool
    {
        return false;
    }
}
