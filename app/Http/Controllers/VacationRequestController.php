<?php

namespace App\Http\Controllers;

use App\Enum\VacationRequestStatus;
use App\Http\Requests\StoreVacationRequestRequest;
use App\Models\User;
use App\Models\VacationRequest;
use App\Notifications\VacationRequestApproved;
use App\Notifications\VacationRequestRejected;
use App\Notifications\VacationRequestSubmitted;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class VacationRequestController extends Controller
{
    /**
     * Store a newly created vacation request.
     */
    public function store(StoreVacationRequestRequest $request): RedirectResponse
    {
        $user = auth()->user();

        // Authorize the creation
        $this->authorize('create', VacationRequest::class);

        $vacationRequest = VacationRequest::create([
            'user_id' => $user->id,
            'company_id' => $user->company_id,
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
            'type' => $request->input('type'),
            'status' => VacationRequestStatus::PENDING->value,
            'reason' => $request->input('reason'),
        ]);

        // Notify managers, admins, and owners in the same company
        $notifiableUsers = User::where('company_id', $user->company_id)
            ->whereIn('role', ['manager', 'admin', 'owner'])
            ->get();

        Notification::send($notifiableUsers, new VacationRequestSubmitted($vacationRequest));

        return redirect()->back()->with('success', 'Your time off request has been submitted successfully!');
    }

    /**
     * Update an existing vacation request (only if pending).
     */
    public function update(StoreVacationRequestRequest $request, VacationRequest $vacationRequest): RedirectResponse
    {
        // Authorize the update (checks ownership and pending status)
        $this->authorize('update', $vacationRequest);

        $vacationRequest->update([
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
            'type' => $request->input('type'),
            'reason' => $request->input('reason'),
        ]);

        return redirect()->back()->with('success', 'Your time off request has been updated successfully!');
    }

    /**
     * Cancel a vacation request (soft delete / mark as cancelled).
     */
    public function destroy(VacationRequest $vacationRequest): RedirectResponse
    {
        // Authorize the deletion (checks ownership and pending status)
        $this->authorize('delete', $vacationRequest);

        $vacationRequest->delete();

        return redirect()->back()->with('success', 'Your time off request has been cancelled.');
    }

    /**
     * Approve a vacation request (managers/admins only).
     */
    public function approve(VacationRequest $vacationRequest): RedirectResponse
    {
        $user = auth()->user();

        // Authorize the approval (checks role, team membership, company, and pending status)
        $this->authorize('approve', $vacationRequest);

        $vacationRequest->update([
            'status' => VacationRequestStatus::APPROVED->value,
            'approved_by' => $user->id,
            'approved_date' => now(),
        ]);

        // Send notification to employee
        $vacationRequest->user->notify(new VacationRequestApproved($vacationRequest));

        return redirect()->back()->with('success', 'Request approved successfully!');
    }

    /**
     * Reject a vacation request (managers/admins only).
     */
    public function reject(Request $request, VacationRequest $vacationRequest): RedirectResponse
    {
        $user = auth()->user();

        // Authorize the rejection (checks role, team membership, company, and pending status)
        $this->authorize('reject', $vacationRequest);

        $validated = $request->validate([
            'rejection_reason' => 'nullable|string|max:500',
        ]);

        $vacationRequest->update([
            'status' => VacationRequestStatus::REJECTED->value,
            'approved_by' => $user->id,
            'approved_date' => now(),
            'rejection_reason' => $validated['rejection_reason'] ?? null,
        ]);

        // Send notification to employee
        $vacationRequest->user->notify(new VacationRequestRejected($vacationRequest));

        return redirect()->back()->with('success', 'Request rejected.');
    }
}
