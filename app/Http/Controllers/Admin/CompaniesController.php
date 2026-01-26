<?php

namespace App\Http\Controllers\Admin;

use App\Enum\SubscriptionStatus;
use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CompaniesController extends Controller
{
    /**
     * Display all companies.
     */
    public function index(Request $request): Response
    {
        $search = $request->get('search');
        $perPage = $request->get('per_page', 50);

        $query = Company::withCount('users');

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        $companies = $query->latest()
            ->paginate($perPage)
            ->through(function ($company) {
                $activeSubscription = $company->subscriptions()
                    ->where('status', SubscriptionStatus::ACTIVE)
                    ->with('subscription')
                    ->first();

                return [
                    'id' => $company->id,
                    'name' => $company->name,
                    'users_count' => $company->users_count,
                    'user_limit' => $company->user_limit,
                    'subscription' => $activeSubscription ? [
                        'name' => $activeSubscription->subscription->name,
                        'price' => $activeSubscription->subscription->formatted_price,
                    ] : null,
                    'created_at' => $company->created_at->format('Y-m-d H:i:s'),
                    'created_at_human' => $company->created_at->diffForHumans(),
                ];
            });

        return Inertia::render('admin/Companies', [
            'companies' => $companies,
            'search' => $search,
        ]);
    }

    /**
     * Show company details.
     */
    public function show(Company $company): Response
    {
        $company->load(['users', 'subscriptions.subscription']);

        $activeSubscription = $company->subscriptions()
            ->where('status', SubscriptionStatus::ACTIVE)
            ->with('subscription')
            ->first();

        return Inertia::render('admin/CompanyDetails', [
            'company' => [
                'id' => $company->id,
                'name' => $company->name,
                'users_count' => $company->users->count(),
                'user_limit' => $company->user_limit,
                'current_subscription' => $activeSubscription ? [
                    'id' => $activeSubscription->id,
                    'plan_name' => $activeSubscription->subscription->name,
                    'price' => $activeSubscription->subscription->formatted_price,
                    'status' => $activeSubscription->status->value,
                    'starts_at' => $activeSubscription->starts_at->format('Y-m-d'),
                    'ends_at' => $activeSubscription->ends_at?->format('Y-m-d'),
                ] : null,
                'subscription_history' => $company->subscriptions->map(fn ($sub) => [
                    'id' => $sub->id,
                    'plan_name' => $sub->subscription->name,
                    'status' => $sub->status->value,
                    'starts_at' => $sub->starts_at->format('Y-m-d'),
                    'ends_at' => $sub->ends_at?->format('Y-m-d'),
                    'created_at' => $sub->created_at->format('Y-m-d H:i:s'),
                ]),
                'users' => $company->users->map(fn ($user) => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role->value,
                ]),
                'created_at' => $company->created_at->format('Y-m-d H:i:s'),
            ],
        ]);
    }
}
