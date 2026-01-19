<?php

namespace App\Http\Controllers;

use App\Enum\SubscriptionStatus;
use App\Models\Subscription;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SubscriptionManagementController extends Controller
{
    public function __construct(protected PaymentService $paymentService) {}

    public function index()
    {
        $user = auth()->user();

        // Only owners can access subscription management
        if (! $user->role->canManageSubscription()) {
            abort(403, 'Only company owners can manage subscriptions.');
        }

        $company = $user->company;

        // Get current active subscription
        $currentSubscription = $company->subscriptions()
            ->where('status', SubscriptionStatus::ACTIVE)
            ->with('subscription')
            ->latest()
            ->first();

        // Get subscription history
        $subscriptionHistory = $company->subscriptions()
            ->with('subscription')
            ->orderBy('created_at', 'desc')
            ->get();

        // Get available plans
        $availablePlans = Subscription::all();

        return Inertia::render('SubscriptionManagement', [
            'company' => [
                'id' => $company->id,
                'name' => $company->name,
            ],
            'current_subscription' => $currentSubscription ? [
                'id' => $currentSubscription->id,
                'plan' => [
                    'id' => $currentSubscription->subscription->id,
                    'name' => $currentSubscription->subscription->name,
                    'slug' => $currentSubscription->subscription->slug,
                    'price' => $currentSubscription->subscription->price,
                    'currency' => $currentSubscription->subscription->currency,
                    'interval' => $currentSubscription->subscription->interval->value,
                ],
                'status' => $currentSubscription->status->value,
                'starts_at' => $currentSubscription->starts_at->toIso8601String(),
                'ends_at' => $currentSubscription->ends_at?->toIso8601String(),
                'days_remaining' => $currentSubscription->ends_at ? now()->diffInDays($currentSubscription->ends_at, false) : null,
            ] : null,
            'subscription_history' => $subscriptionHistory->map(fn ($sub) => [
                'id' => $sub->id,
                'plan_name' => $sub->subscription->name,
                'status' => $sub->status->value,
                'starts_at' => $sub->starts_at->format('M d, Y'),
                'ends_at' => $sub->ends_at?->format('M d, Y'),
                'created_at' => $sub->created_at->format('M d, Y'),
            ]),
            'available_plans' => $availablePlans,
            'fake_mode' => $this->paymentService->isFakeMode(),
        ]);
    }

    public function changePlan(Request $request)
    {
        $user = auth()->user();

        // Only owners can change subscription
        if (! $user->role->canManageSubscription()) {
            abort(403, 'Only company owners can change subscriptions.');
        }

        $request->validate([
            'plan_id' => 'required|exists:subscriptions,id',
        ]);

        $subscription = Subscription::findOrFail($request->plan_id);
        $company = $user->company;

        // Check if already on this plan
        $currentSubscription = $company->subscriptions()
            ->where('status', SubscriptionStatus::ACTIVE)
            ->with('subscription')
            ->latest()
            ->first();

        if ($currentSubscription && $currentSubscription->subscription_id === $subscription->id) {
            return back()->with('error', 'You are already on this plan.');
        }

        // Create or retrieve Stripe customer for the company
        $customerId = $this->paymentService->createOrRetrieveCustomer($company);

        // Create checkout session for plan change
        $successUrl = route('subscription.upgrade.complete', ['plan_id' => $subscription->id]);
        $cancelUrl = route('subscription.index');

        $session = $this->paymentService->createCheckoutSession(
            $subscription,
            $successUrl,
            $cancelUrl,
            $customerId
        );

        return response()->json([
            'session_id' => $session['id'],
            'checkout_url' => $session['url'],
            'fake' => $session['fake'] ?? false,
        ]);
    }

    public function completeUpgrade(Request $request)
    {
        $user = auth()->user();

        // Only owners can complete upgrade
        if (! $user->role->canManageSubscription()) {
            abort(403, 'Only company owners can upgrade subscriptions.');
        }

        $request->validate([
            'plan_id' => 'required|exists:subscriptions,id',
            'session_id' => 'required|string',
        ]);

        // Verify the payment session
        if (! $this->paymentService->verifyCheckoutSession($request->session_id)) {
            return redirect()->route('subscription.index')->with('error', 'Payment verification failed. Please try again.');
        }

        $subscription = Subscription::find($request->plan_id);
        $company = $user->company;

        // Get checkout session details (Stripe subscription and invoice IDs)
        $sessionDetails = $this->paymentService->getCheckoutSessionDetails($request->session_id);

        // Get invoice URL if available
        $invoiceUrl = null;
        if ($sessionDetails && isset($sessionDetails['invoice_id'])) {
            $invoiceUrl = $this->paymentService->getInvoiceUrl($sessionDetails['invoice_id']);
        }

        // Cancel current active subscription
        $company->subscriptions()
            ->where('status', SubscriptionStatus::ACTIVE)
            ->update(['status' => SubscriptionStatus::CANCELLED]);

        // Create new active subscription
        $company->subscriptions()->create([
            'subscription_id' => $subscription->id,
            'status' => SubscriptionStatus::ACTIVE,
            'starts_at' => now(),
            'ends_at' => $subscription->interval === \App\Enum\SubscriptionInterval::ONE_TIME
                ? null
                : now()->add(1, $subscription->interval->value),
            'stripe_subscription_id' => $sessionDetails['subscription_id'] ?? null,
            'stripe_invoice_id' => $sessionDetails['invoice_id'] ?? null,
            'invoice_url' => $invoiceUrl,
        ]);

        return redirect()->route('subscription.index')->with('success', 'Subscription upgraded successfully!');
    }

    public function cancelSubscription(Request $request)
    {
        $user = auth()->user();

        // Only owners can cancel subscription
        if (! $user->role->canManageSubscription()) {
            abort(403, 'Only company owners can cancel subscriptions.');
        }

        $company = $user->company;

        // Cancel current active subscription
        $updated = $company->subscriptions()
            ->where('status', SubscriptionStatus::ACTIVE)
            ->update(['status' => SubscriptionStatus::CANCELLED]);

        if ($updated) {
            return back()->with('success', 'Subscription cancelled. You will retain access until the end of your billing period.');
        }

        return back()->with('error', 'No active subscription found.');
    }
}
