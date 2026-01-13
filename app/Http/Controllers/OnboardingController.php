<?php

namespace App\Http\Controllers;

use App\Enum\SubscriptionStatus;
use App\Models\Subscription;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OnboardingController extends Controller
{
    public function __construct(protected PaymentService $paymentService) {}

    public function show()
    {
        return Inertia::render('Onboarding', [
            'plans' => Subscription::all(),
            'fake_mode' => $this->paymentService->isFakeMode(),
        ]);
    }

    public function createCheckoutSession(Request $request)
    {
        $request->validate([
            'plan_id' => 'required|exists:subscriptions,id',
        ]);

        $subscription = Subscription::findOrFail($request->plan_id);

        $successUrl = route('onboarding.complete', ['plan_id' => $subscription->id]);
        $cancelUrl = route('onboarding');

        $session = $this->paymentService->createCheckoutSession(
            $subscription,
            $successUrl,
            $cancelUrl
        );

        return response()->json([
            'session_id' => $session['id'],
            'checkout_url' => $session['url'],
            'fake' => $session['fake'] ?? false,
        ]);
    }

    public function complete(Request $request)
    {
        $request->validate([
            'plan_id' => 'required|exists:subscriptions,id',
            'session_id' => 'required|string',
        ]);

        // Verify the payment session
        if (! $this->paymentService->verifyCheckoutSession($request->session_id)) {
            return redirect()->route('onboarding')->with('error', 'Payment verification failed. Please try again.');
        }

        $user = auth()->user();
        if ($user && $user->company) {
            $subscription = Subscription::find($request->plan_id);

            // Check if subscription already exists (prevent duplicate creation)
            $existingSubscription = $user->company->subscriptions()
                ->where('subscription_id', $subscription->id)
                ->where('status', SubscriptionStatus::ACTIVE)
                ->first();

            if (! $existingSubscription) {
                // Create subscription record
                $user->company->subscriptions()->create([
                    'subscription_id' => $subscription->id,
                    'status' => SubscriptionStatus::ACTIVE,
                    'starts_at' => now(),
                    'ends_at' => $subscription->interval === \App\Enum\SubscriptionInterval::ONE_TIME
                        ? null
                        : now()->add(1, $subscription->interval->value),
                ]);
            }
        }

        return to_route('dashboard');
    }
}
