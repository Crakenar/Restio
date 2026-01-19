<?php

namespace App\Http\Controllers\Settings;

use App\Enum\SubscriptionStatus;
use App\Http\Controllers\Controller;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminSettingsController extends Controller
{
    public function __construct(protected PaymentService $paymentService) {}

    public function index(Request $request): Response
    {
        $user = $request->user();

        // Only owners and admins can access admin settings
        if (! in_array($user->role, ['owner', 'admin'])) {
            abort(403, 'Only company owners and admins can access admin settings.');
        }

        $company = $user->company;

        // Get current active subscription
        $currentSubscription = $company->subscriptions()
            ->where('status', SubscriptionStatus::ACTIVE)
            ->with('subscription')
            ->latest()
            ->first();

        // Get billing history (all subscriptions ordered by date)
        $billingHistory = $company->subscriptions()
            ->with('subscription')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($sub) {
                $invoiceUrl = $sub->invoice_url;

                // If no invoice URL is stored but we have a Stripe invoice ID, fetch it
                if (! $invoiceUrl && $sub->stripe_invoice_id) {
                    $invoiceUrl = $this->paymentService->getInvoiceUrl($sub->stripe_invoice_id);

                    // Store it for future use
                    if ($invoiceUrl) {
                        $sub->update(['invoice_url' => $invoiceUrl]);
                    }
                }

                return [
                    'id' => $sub->id,
                    'date' => $sub->created_at->toIso8601String(),
                    'plan_name' => $sub->subscription->name,
                    'amount' => $sub->subscription->price,
                    'currency' => $sub->subscription->currency,
                    'status' => $sub->status->value === 'active' ? 'paid' : $sub->status->value,
                    'invoice_url' => $invoiceUrl,
                ];
            });

        // Get Stripe portal URL
        $returnUrl = route('admin-settings.index');
        $stripePortalUrl = $this->paymentService->createCustomerPortalUrl($company, $returnUrl);

        return Inertia::render('settings/AdminSettings', [
            'subscription' => $currentSubscription ? [
                'plan_name' => $currentSubscription->subscription->name,
                'status' => $currentSubscription->status->value,
                'price' => $currentSubscription->subscription->price,
                'currency' => $currentSubscription->subscription->currency,
                'interval' => $currentSubscription->subscription->interval->value,
                'starts_at' => $currentSubscription->starts_at->toIso8601String(),
                'ends_at' => $currentSubscription->ends_at?->toIso8601String(),
                'days_remaining' => $currentSubscription->ends_at
                    ? max(0, now()->diffInDays($currentSubscription->ends_at, false))
                    : null,
            ] : null,
            'billingHistory' => $billingHistory,
            'stripePortalUrl' => $stripePortalUrl,
        ]);
    }
}
