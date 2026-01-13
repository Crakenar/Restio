<?php

namespace App\Http\Responses;

use App\Enum\SubscriptionStatus;
use Illuminate\Http\JsonResponse;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request)
    {
        $user = $request->user();

        if ($user && $user->company) {
            $hasActiveSubscription = $user->company->subscriptions()
                ->where('status', SubscriptionStatus::ACTIVE)
                ->where(function ($query) {
                    $query->whereNull('ends_at')
                        ->orWhere('ends_at', '>', now());
                })
                ->exists();

            if (! $hasActiveSubscription) {
                return $request->wantsJson()
                    ? new JsonResponse('', 200)
                    : redirect()->route('onboarding');
            }
        }

        return $request->wantsJson()
            ? new JsonResponse('', 204)
            : redirect()->intended(config('fortify.home'));
    }
}
