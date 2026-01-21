<?php

namespace App\Providers;

use App\Models\CompanySetting;
use App\Observers\CompanySettingObserver;
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
        // Register observers
        CompanySetting::observe(CompanySettingObserver::class);

        // Define global rate limiter per IP
        RateLimiter::for('global', function (Request $request) {
            // Disable rate limiting in testing environment for performance tests
            if (app()->environment('testing')) {
                return Limit::none();
            }

            return Limit::perMinute(60)->by($request->ip())->response(function (Request $request, array $headers) {
                return response()->json([
                    'message' => 'Too many requests. Please slow down.',
                ], 429, $headers);
            });
        });

        // Define API rate limiter for authenticated users and guests
        RateLimiter::for('api', function (Request $request) {
            if (app()->environment('testing')) {
                return Limit::none();
            }

            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // Stricter rate limiting for sensitive actions (approve/reject requests)
        RateLimiter::for('sensitive', function (Request $request) {
            if (app()->environment('testing')) {
                return Limit::none();
            }

            return Limit::perMinute(20)->by($request->user()?->id ?: $request->ip())->response(function (Request $request, array $headers) {
                return response()->json([
                    'message' => 'Too many approval/rejection attempts. Please slow down.',
                ], 429, $headers);
            });
        });

        // Rate limiting for admin actions (user/team/company management)
        RateLimiter::for('admin', function (Request $request) {
            if (app()->environment('testing')) {
                return Limit::none();
            }

            return Limit::perMinute(30)->by($request->user()?->id ?: $request->ip())->response(function (Request $request, array $headers) {
                return response()->json([
                    'message' => 'Too many administrative actions. Please slow down.',
                ], 429, $headers);
            });
        });

        // Rate limiting for subscription/billing actions
        RateLimiter::for('billing', function (Request $request) {
            if (app()->environment('testing')) {
                return Limit::none();
            }

            return Limit::perMinute(10)->by($request->user()?->id ?: $request->ip())->response(function (Request $request, array $headers) {
                return response()->json([
                    'message' => 'Too many billing actions. Please slow down.',
                ], 429, $headers);
            });
        });

        // Rate limiting for bulk import operations
        RateLimiter::for('import', function (Request $request) {
            if (app()->environment('testing')) {
                return Limit::none();
            }

            return Limit::perHour(5)->by($request->user()?->id ?: $request->ip())->response(function (Request $request, array $headers) {
                return response()->json([
                    'message' => 'Too many import operations. Please try again later.',
                ], 429, $headers);
            });
        });
    }
}
