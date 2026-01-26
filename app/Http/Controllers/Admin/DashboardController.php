<?php

namespace App\Http\Controllers\Admin;

use App\Enum\SubscriptionStatus;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Company;
use App\Models\CompanySubscription;
use App\Models\Subscription;
use App\Models\User;
use App\Models\VacationRequest;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Show the admin dashboard.
     */
    public function index(): Response
    {
        $stats = $this->getSystemStats();
        $recentActivity = $this->getRecentActivity();
        $subscriptionMetrics = $this->getSubscriptionMetrics();
        $revenueData = $this->getRevenueData();

        return Inertia::render('admin/Dashboard', [
            'stats' => $stats,
            'recent_activity' => $recentActivity,
            'subscription_metrics' => $subscriptionMetrics,
            'revenue_data' => $revenueData,
        ]);
    }

    /**
     * Get overall system statistics.
     */
    private function getSystemStats(): array
    {
        return [
            'total_users' => User::count(),
            'total_companies' => Company::count(),
            'active_subscriptions' => CompanySubscription::where('status', SubscriptionStatus::ACTIVE)->count(),
            'total_vacation_requests' => VacationRequest::count(),
            'pending_vacation_requests' => VacationRequest::where('status', 'pending')->count(),
            'users_today' => User::whereDate('created_at', today())->count(),
            'companies_today' => Company::whereDate('created_at', today())->count(),
            'users_this_week' => User::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'companies_this_week' => Company::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
        ];
    }

    /**
     * Get recent activity from audit logs.
     */
    private function getRecentActivity(): array
    {
        return AuditLog::with(['user', 'company'])
            ->latest()
            ->limit(20)
            ->get()
            ->map(fn ($log) => [
                'id' => $log->id,
                'event' => $log->event,
                'description' => $log->description,
                'user_name' => $log->user?->name,
                'company_name' => $log->company?->name,
                'created_at' => $log->created_at->diffForHumans(),
                'created_at_formatted' => $log->created_at->format('M d, Y H:i:s'),
            ])
            ->toArray();
    }

    /**
     * Get subscription metrics breakdown.
     */
    private function getSubscriptionMetrics(): array
    {
        $subscriptions = CompanySubscription::where('status', SubscriptionStatus::ACTIVE)
            ->with('subscription')
            ->get();

        $breakdown = $subscriptions->groupBy('subscription.name')
            ->map(fn ($group) => [
                'count' => $group->count(),
                'percentage' => round(($group->count() / $subscriptions->count()) * 100, 1),
            ]);

        return [
            'breakdown' => $breakdown,
            'total_active' => $subscriptions->count(),
            'free_tier' => $subscriptions->where('subscription.slug', 'free')->count(),
            'paid_tiers' => $subscriptions->whereNotIn('subscription.slug', ['free'])->count(),
        ];
    }

    /**
     * Get revenue data and projections.
     */
    private function getRevenueData(): array
    {
        $activeSubscriptions = CompanySubscription::where('status', SubscriptionStatus::ACTIVE)
            ->with('subscription')
            ->get();

        $mrr = $activeSubscriptions->sum(function ($subscription) {
            $plan = $subscription->subscription;
            if ($plan->interval->value === 'month') {
                return $plan->price;
            } elseif ($plan->interval->value === 'year') {
                return $plan->price / 12;
            }

            return 0;
        });

        $arr = $mrr * 12;

        // Get subscription trend (last 30 days)
        $subscriptionTrend = CompanySubscription::where('status', SubscriptionStatus::ACTIVE)
            ->whereBetween('created_at', [now()->subDays(30), now()])
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(fn ($item) => [
                'date' => $item->date,
                'count' => $item->count,
            ]);

        return [
            'mrr' => round($mrr, 2),
            'arr' => round($arr, 2),
            'subscription_trend' => $subscriptionTrend,
        ];
    }
}
