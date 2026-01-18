<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Define global rate limiter per IP
        RateLimiter::for('global', function (Request $request) {
            return Limit::perMinute(60)->by($request->ip())->response(function (Request $request, array $headers) {
                return response()->json([
                    'message' => 'Too many requests. Please slow down.',
                ], 429, $headers);
            });
        });

        // Define API rate limiter for authenticated users and guests
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // Stricter rate limiting for sensitive actions (approve/reject requests)
        RateLimiter::for('sensitive', function (Request $request) {
            return Limit::perMinute(20)->by($request->user()?->id ?: $request->ip())->response(function (Request $request, array $headers) {
                return response()->json([
                    'message' => 'Too many approval/rejection attempts. Please slow down.',
                ], 429, $headers);
            });
        });
    }
}
