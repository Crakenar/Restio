<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class LocaleController extends Controller
{
    /**
     * Update the user's locale preference.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'locale' => 'required|in:en,fr',
        ]);

        $locale = $validated['locale'];

        // Update session
        $request->session()->put('locale', $locale);

        // Update user preference if authenticated
        if ($request->user()) {
            $request->user()->update(['locale' => $locale]);
        }

        // Set app locale
        app()->setLocale($locale);

        return Redirect::back()->with('success', 'Language updated successfully');
    }
}
