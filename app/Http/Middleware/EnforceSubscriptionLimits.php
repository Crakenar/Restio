<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnforceSubscriptionLimits
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || !$user->company) {
            return $next($request);
        }

        $company = $user->company;

        // Only check on user creation/invite routes
        if ($this->isUserCreationRoute($request)) {
            if ($company->hasReachedUserLimit()) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'User limit reached. Please upgrade your subscription to add more users.',
                        'current_users' => $company->current_user_count,
                        'user_limit' => $company->user_limit,
                        'upgrade_url' => route('subscription.index'),
                    ], 403);
                }

                return redirect()
                    ->back()
                    ->with('error', 'User limit reached. Please upgrade your subscription to add more users.')
                    ->with('upgrade_required', true);
            }
        }

        return $next($request);
    }

    /**
     * Check if the current route is for user creation
     */
    protected function isUserCreationRoute(Request $request): bool
    {
        $routeName = $request->route()?->getName();
        $method = $request->method();

        // Check if it's a POST/PUT to employee/user creation routes
        if (in_array($method, ['POST', 'PUT'])) {
            $creationRoutes = [
                'employees.store',
                'employees.create',
                'users.store',
                'users.create',
                'team.members.store',
                'invitations.send',
            ];

            if (in_array($routeName, $creationRoutes)) {
                return true;
            }

            // Also check URL patterns
            if (str_contains($request->url(), '/employees') && $method === 'POST') {
                return true;
            }
        }

        return false;
    }
}
