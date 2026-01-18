<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TeamManagementController extends Controller
{
    /**
     * Display a listing of teams.
     */
    public function index(): Response
    {
        $user = auth()->user();
        $companyId = $user->company_id;

        // Authorize viewing teams (only admins and owners)
        $this->authorize('viewAny', Team::class);

        // Fetch teams for the company with user count
        $teams = Team::query()
            ->where('company_id', $companyId)
            ->withCount('users')
            ->with(['users' => function ($query) {
                $query->select('id', 'name', 'email', 'team_id');
            }])
            ->get()
            ->map(function ($team) {
                return [
                    'id' => $team->id,
                    'name' => $team->name,
                    'users_count' => $team->users_count,
                    'users' => $team->users->map(function ($user) {
                        return [
                            'id' => $user->id,
                            'name' => $user->name,
                            'email' => $user->email,
                        ];
                    }),
                    'created_at' => $team->created_at->format('Y-m-d'),
                ];
            });

        // Fetch users who don't have a team yet
        $unassignedUsers = User::query()
            ->where('company_id', $companyId)
            ->whereNull('team_id')
            ->select('id', 'name', 'email')
            ->get();

        return Inertia::render('TeamManagement', [
            'teams' => $teams,
            'unassignedUsers' => $unassignedUsers,
            'userRole' => $user->role,
        ]);
    }

    /**
     * Store a newly created team.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();

        // Authorize team creation (only admins and owners)
        $this->authorize('create', Team::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        Team::create([
            'name' => $validated['name'],
            'company_id' => $user->company_id,
        ]);

        return redirect()->back()->with('success', 'Team created successfully.');
    }

    /**
     * Update the specified team.
     */
    public function update(Request $request, Team $team): RedirectResponse
    {
        // Authorize team update (only admins and owners, same company)
        $this->authorize('update', $team);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $team->update($validated);

        return redirect()->back()->with('success', 'Team updated successfully.');
    }

    /**
     * Remove the specified team.
     */
    public function destroy(Team $team): RedirectResponse
    {
        // Authorize team deletion (only admins and owners, same company)
        $this->authorize('delete', $team);

        // Remove team assignment from users
        $team->users()->update(['team_id' => null]);

        $team->delete();

        return redirect()->back()->with('success', 'Team deleted successfully.');
    }

    /**
     * Assign users to a team.
     */
    public function assignUsers(Request $request, Team $team): RedirectResponse
    {
        // Authorize assigning users to team (only admins and owners, same company)
        $this->authorize('assignUsers', $team);

        $validated = $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        // Update users to assign them to this team
        User::whereIn('id', $validated['user_ids'])
            ->where('company_id', auth()->user()->company_id)
            ->update(['team_id' => $team->id]);

        return redirect()->back()->with('success', 'Users assigned to team successfully.');
    }

    /**
     * Remove a user from a team.
     */
    public function removeUser(Request $request, Team $team, User $user): RedirectResponse
    {
        // Authorize removing users from team (only admins and owners, same company)
        $this->authorize('removeUser', $team);

        // Ensure the user belongs to this team
        if ($user->team_id !== $team->id) {
            abort(403, 'User does not belong to this team.');
        }

        $user->update(['team_id' => null]);

        return redirect()->back()->with('success', 'User removed from team successfully.');
    }
}
