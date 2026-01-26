<?php

namespace App\Filament\Widgets;

use App\Enum\SubscriptionStatus;
use App\Models\Company;
use App\Models\CompanySubscription;
use App\Models\User;
use App\Models\VacationRequest;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 0;

    protected function getStats(): array
    {
        $mrr = $this->calculateMRR();

        return [
            Stat::make('Total Users', User::count())
                ->description(User::whereDate('created_at', today())->count().' new today')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success')
                ->chart([7, 12, 15, 18, 22, 25, 30]),

            Stat::make('Total Companies', Company::count())
                ->description(Company::whereDate('created_at', today())->count().' new today')
                ->descriptionIcon('heroicon-m-building-office-2')
                ->color('info')
                ->chart([3, 5, 7, 9, 11, 13, 15]),

            Stat::make('Active Subscriptions', CompanySubscription::where('status', SubscriptionStatus::ACTIVE)->count())
                ->description('Paid subscriptions')
                ->descriptionIcon('heroicon-m-credit-card')
                ->color('warning'),

            Stat::make('Monthly Recurring Revenue', '€'.number_format($mrr, 2))
                ->description('ARR: €'.number_format($mrr * 12, 2))
                ->descriptionIcon('heroicon-m-currency-euro')
                ->color('success')
                ->chart([100, 150, 200, 250, 300, 350, $mrr]),

            Stat::make('Pending Vacation Requests', VacationRequest::where('status', 'pending')->count())
                ->description('Awaiting approval')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('danger'),
        ];
    }

    protected function calculateMRR(): float
    {
        $activeSubscriptions = CompanySubscription::where('status', SubscriptionStatus::ACTIVE)
            ->with('subscription')
            ->get();

        return $activeSubscriptions->sum(function ($subscription) {
            $plan = $subscription->subscription;
            if ($plan->interval->value === 'month') {
                return $plan->price;
            } elseif ($plan->interval->value === 'year') {
                return $plan->price / 12;
            }

            return 0;
        });
    }
}
