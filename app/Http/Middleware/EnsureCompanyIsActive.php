<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCompanyIsActive
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->company) {
            return $next($request);
        }

        $hasActiveSubscription = $user->company->subscriptions()
            ->where('status', \App\Enum\SubscriptionStatus::ACTIVE)
            ->where(function ($query) {
                $query->whereNull('ends_at')
                    ->orWhere('ends_at', '>', now());
            })
            ->exists();

        // If user has no active subscription and is not accessing onboarding, subscription, or logout
        if (! $hasActiveSubscription && ! $request->routeIs('onboarding*') && ! $request->routeIs('subscription.*') && ! $request->is('logout')) {
            return redirect()->route('onboarding');
        }

        // If user has active subscription but trying to access onboarding, redirect to dashboard
        if ($hasActiveSubscription && $request->routeIs('onboarding*')) {
            return redirect()->route('dashboard');
        }

        return $next($request);
    }
}
