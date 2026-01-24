<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $this->getLocale($request);

        app()->setLocale($locale);

        return $next($request);
    }

    /**
     * Get the locale for the request.
     */
    protected function getLocale(Request $request): string
    {
        // 1. Check if user has a preferred locale in session
        if ($request->session()->has('locale')) {
            return $request->session()->get('locale');
        }

        // 2. Check if user model has locale preference (if authenticated)
        if ($request->user() && method_exists($request->user(), 'getPreferredLocale')) {
            return $request->user()->getPreferredLocale();
        }

        // 3. Check query parameter (for locale switcher)
        if ($request->has('locale')) {
            $locale = $request->input('locale');
            if (in_array($locale, config('app.available_locales', ['en', 'fr']))) {
                $request->session()->put('locale', $locale);

                return $locale;
            }
        }

        // 4. Use Accept-Language header
        $acceptLanguage = $request->header('Accept-Language');
        if ($acceptLanguage) {
            $preferredLocale = substr($acceptLanguage, 0, 2);
            if (in_array($preferredLocale, config('app.available_locales', ['en', 'fr']))) {
                return $preferredLocale;
            }
        }

        // 5. Fall back to default locale
        return config('app.locale', 'en');
    }
}
