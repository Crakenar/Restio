<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Users, Building2, CreditCard, TrendingUp, AlertCircle, Calendar, BarChart, LogOut } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';

interface Stats {
    total_users: number;
    total_companies: number;
    active_subscriptions: number;
    total_vacation_requests: number;
    pending_vacation_requests: number;
    users_today: number;
    companies_today: number;
    users_this_week: number;
    companies_this_week: number;
}

interface Activity {
    id: number;
    event: string;
    description: string;
    user_name: string | null;
    company_name: string | null;
    created_at: string;
    created_at_formatted: string;
}

interface SubscriptionMetrics {
    breakdown: Record<string, { count: number; percentage: number }>;
    total_active: number;
    free_tier: number;
    paid_tiers: number;
}

interface RevenueData {
    mrr: number;
    arr: number;
    subscription_trend: Array<{ date: string; count: number }>;
}

interface Props {
    stats: Stats;
    recent_activity: Activity[];
    subscription_metrics: SubscriptionMetrics;
    revenue_data: RevenueData;
}

const props = defineProps<Props>();

const logout = () => {
    if (confirm('Are you sure you want to logout?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = route('admin.logout');

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (csrfToken) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = '_token';
            input.value = csrfToken;
            form.appendChild(input);
        }

        document.body.appendChild(form);
        form.submit();
    }
};
</script>

<template>
    <Head title="Admin Dashboard" />

    <div class="min-h-screen bg-gradient-to-br from-slate-950 via-slate-900 to-slate-950">
        <!-- Background Pattern -->
        <div class="pointer-events-none fixed inset-0 overflow-hidden opacity-10">
            <div class="absolute -top-1/2 -left-1/2 h-full w-full animate-pulse rounded-full bg-gradient-to-br from-blue-500 via-purple-500 to-pink-500 blur-3xl" style="animation-duration: 15s" />
            <div class="absolute -bottom-1/2 -right-1/2 h-full w-full animate-pulse rounded-full bg-gradient-to-tr from-cyan-500 via-blue-500 to-indigo-500 blur-3xl" style="animation-duration: 20s" />
        </div>

        <!-- Header -->
        <div class="relative border-b border-white/10 bg-slate-900/50 backdrop-blur-xl">
            <div class="mx-auto max-w-7xl px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-blue-500 to-purple-600 shadow-lg shadow-blue-500/50">
                            <BarChart class="h-6 w-6 text-white" />
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold text-white">Admin Dashboard</h1>
                            <p class="text-sm text-slate-400">System Overview & Analytics</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <Link :href="route('admin.users.index')" class="text-sm text-slate-300 hover:text-white">Users</Link>
                        <Link :href="route('admin.companies.index')" class="text-sm text-slate-300 hover:text-white">Companies</Link>
                        <Link :href="route('admin.logs.index')" class="text-sm text-slate-300 hover:text-white">Logs</Link>
                        <a :href="'/admin/horizon'" target="_blank" class="text-sm text-slate-300 hover:text-white">Horizon</a>

                        <Button
                            @click="logout"
                            variant="outline"
                            size="sm"
                            class="border-slate-700 text-slate-300 hover:bg-slate-800 hover:text-white"
                        >
                            <LogOut class="mr-2 h-4 w-4" />
                            Logout
                        </Button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="relative mx-auto max-w-7xl space-y-6 px-6 py-8">
            <!-- Key Stats Grid -->
            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-4">
                <!-- Total Users -->
                <div class="rounded-2xl border border-white/10 bg-slate-900/50 p-6 backdrop-blur-xl">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-slate-400">Total Users</p>
                            <p class="mt-2 text-3xl font-bold text-white">{{ stats.total_users.toLocaleString() }}</p>
                            <p class="mt-2 text-xs text-emerald-400">
                                +{{ stats.users_today }} today
                            </p>
                        </div>
                        <div class="flex h-14 w-14 items-center justify-center rounded-xl bg-blue-500/20 text-blue-400">
                            <Users class="h-7 w-7" />
                        </div>
                    </div>
                </div>

                <!-- Total Companies -->
                <div class="rounded-2xl border border-white/10 bg-slate-900/50 p-6 backdrop-blur-xl">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-slate-400">Total Companies</p>
                            <p class="mt-2 text-3xl font-bold text-white">{{ stats.total_companies.toLocaleString() }}</p>
                            <p class="mt-2 text-xs text-emerald-400">
                                +{{ stats.companies_today }} today
                            </p>
                        </div>
                        <div class="flex h-14 w-14 items-center justify-center rounded-xl bg-purple-500/20 text-purple-400">
                            <Building2 class="h-7 w-7" />
                        </div>
                    </div>
                </div>

                <!-- Active Subscriptions -->
                <div class="rounded-2xl border border-white/10 bg-slate-900/50 p-6 backdrop-blur-xl">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-slate-400">Active Subscriptions</p>
                            <p class="mt-2 text-3xl font-bold text-white">{{ stats.active_subscriptions.toLocaleString() }}</p>
                            <p class="mt-2 text-xs text-slate-400">
                                {{ subscription_metrics.paid_tiers }} paid
                            </p>
                        </div>
                        <div class="flex h-14 w-14 items-center justify-center rounded-xl bg-emerald-500/20 text-emerald-400">
                            <CreditCard class="h-7 w-7" />
                        </div>
                    </div>
                </div>

                <!-- MRR -->
                <div class="rounded-2xl border border-white/10 bg-slate-900/50 p-6 backdrop-blur-xl">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-slate-400">Monthly Recurring Revenue</p>
                            <p class="mt-2 text-3xl font-bold text-white">€{{ revenue_data.mrr.toLocaleString() }}</p>
                            <p class="mt-2 text-xs text-slate-400">
                                ARR: €{{ revenue_data.arr.toLocaleString() }}
                            </p>
                        </div>
                        <div class="flex h-14 w-14 items-center justify-center rounded-xl bg-amber-500/20 text-amber-400">
                            <TrendingUp class="h-7 w-7" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Secondary Stats -->
            <div class="grid gap-6 md:grid-cols-3">
                <!-- Vacation Requests -->
                <div class="rounded-2xl border border-white/10 bg-slate-900/50 p-6 backdrop-blur-xl">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-cyan-500/20 text-cyan-400">
                            <Calendar class="h-5 w-5" />
                        </div>
                        <div>
                            <p class="text-sm font-medium text-slate-400">Vacation Requests</p>
                            <p class="text-lg font-bold text-white">{{ stats.total_vacation_requests.toLocaleString() }}</p>
                        </div>
                    </div>
                </div>

                <!-- Pending Requests -->
                <div class="rounded-2xl border border-white/10 bg-slate-900/50 p-6 backdrop-blur-xl">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-orange-500/20 text-orange-400">
                            <AlertCircle class="h-5 w-5" />
                        </div>
                        <div>
                            <p class="text-sm font-medium text-slate-400">Pending Requests</p>
                            <p class="text-lg font-bold text-white">{{ stats.pending_vacation_requests.toLocaleString() }}</p>
                        </div>
                    </div>
                </div>

                <!-- This Week -->
                <div class="rounded-2xl border border-white/10 bg-slate-900/50 p-6 backdrop-blur-xl">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-pink-500/20 text-pink-400">
                            <TrendingUp class="h-5 w-5" />
                        </div>
                        <div>
                            <p class="text-sm font-medium text-slate-400">New This Week</p>
                            <p class="text-lg font-bold text-white">
                                {{ stats.users_this_week }} users, {{ stats.companies_this_week }} companies
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <!-- Subscription Breakdown -->
                <div class="rounded-2xl border border-white/10 bg-slate-900/50 p-6 backdrop-blur-xl">
                    <h2 class="mb-4 text-lg font-bold text-white">Subscription Breakdown</h2>
                    <div class="space-y-3">
                        <div v-for="(data, planName) in subscription_metrics.breakdown" :key="planName" class="flex items-center justify-between rounded-lg border border-white/5 bg-slate-800/50 p-3">
                            <div>
                                <p class="font-medium text-white">{{ planName }}</p>
                                <p class="text-sm text-slate-400">{{ data.count }} companies</p>
                            </div>
                            <div class="text-right">
                                <p class="font-bold text-blue-400">{{ data.percentage }}%</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="rounded-2xl border border-white/10 bg-slate-900/50 p-6 backdrop-blur-xl">
                    <h2 class="mb-4 text-lg font-bold text-white">Recent Activity</h2>
                    <div class="space-y-2 max-h-96 overflow-y-auto">
                        <div v-for="activity in recent_activity" :key="activity.id" class="rounded-lg border border-white/5 bg-slate-800/50 p-3">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-white">{{ activity.event }}</p>
                                    <p class="text-xs text-slate-400">{{ activity.description }}</p>
                                    <div class="mt-1 flex items-center gap-2 text-xs text-slate-500">
                                        <span v-if="activity.user_name">{{ activity.user_name }}</span>
                                        <span v-if="activity.company_name">• {{ activity.company_name }}</span>
                                    </div>
                                </div>
                                <p class="text-xs text-slate-500">{{ activity.created_at }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
