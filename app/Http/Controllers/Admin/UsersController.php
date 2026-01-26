<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UsersController extends Controller
{
    /**
     * Display all users.
     */
    public function index(Request $request): Response
    {
        $search = $request->get('search');
        $role = $request->get('role');
        $perPage = $request->get('per_page', 50);

        $query = User::with('company');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhereHas('company', fn ($q) => $q->where('name', 'like', "%{$search}%"));
            });
        }

        if ($role) {
            $query->where('role', $role);
        }

        $users = $query->latest()
            ->paginate($perPage)
            ->through(fn ($user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role->value,
                'company_id' => $user->company_id,
                'company_name' => $user->company?->name,
                'email_verified_at' => $user->email_verified_at?->format('Y-m-d H:i:s'),
                'created_at' => $user->created_at->format('Y-m-d H:i:s'),
                'created_at_human' => $user->created_at->diffForHumans(),
            ]);

        return Inertia::render('admin/Users', [
            'users' => $users,
            'search' => $search,
            'role' => $role,
        ]);
    }

    /**
     * Show user details.
     */
    public function show(User $user): Response
    {
        $user->load(['company', 'vacationRequests']);

        return Inertia::render('admin/UserDetails', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role->value,
                'company_id' => $user->company_id,
                'company' => $user->company ? [
                    'id' => $user->company->id,
                    'name' => $user->company->name,
                ] : null,
                'email_verified_at' => $user->email_verified_at?->format('Y-m-d H:i:s'),
                'created_at' => $user->created_at->format('Y-m-d H:i:s'),
                'vacation_requests_count' => $user->vacationRequests->count(),
            ],
        ]);
    }
}
