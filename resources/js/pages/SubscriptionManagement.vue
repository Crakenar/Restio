<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { useToast } from '@/composables/useToast';
import {
    Building2,
    Check,
    CreditCard,
    TrendingUp,
    Users,
    X,
    AlertCircle,
    Sparkles,
    Zap,
    Crown,
    Info,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import axios from 'axios';

interface Plan {
    id: number;
    name: string;
    slug: string;
    price: string;
    currency: string;
    interval: string;
    max_users: number;
    description: string;
    features: string[];
    is_popular: boolean;
    sort_order: number;
    formatted_price: string;
}

interface CurrentSubscription {
    id: number;
    plan: Plan;
    status: string;
    starts_at: string;
    ends_at: string | null;
    days_remaining: number | null;
}

interface HistoryItem {
    id: number;
    plan_name: string;
    status: string;
    starts_at: string;
    ends_at: string | null;
    created_at: string;
}

interface Company {
    id: number;
    name: string;
    current_user_count: number;
    user_limit: number;
    remaining_slots: number;
    is_near_limit: boolean;
    has_reached_limit: boolean;
}

interface Props {
    company: Company;
    current_subscription: CurrentSubscription | null;
    subscription_history: HistoryItem[];
    available_plans: Plan[];
    fake_mode: boolean;
}

const props = defineProps<Props>();

const toast = useToast();
const selectedPlan = ref<number | null>(null);
const processing = ref(false);
const showCancelConfirm = ref(false);

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Subscription', href: '/subscription' },
];

// Group plans by tier (monthly, yearly, lifetime)
const planGroups = computed(() => {
    const free = props.available_plans.find(p => p.slug === 'free');
    const starter = props.available_plans.filter(p => p.slug.includes('starter'));
    const professional = props.available_plans.filter(p => p.slug.includes('professional'));
    const enterprise = props.available_plans.filter(p => p.slug.includes('enterprise'));
    const lifetime = props.available_plans.find(p => p.slug === 'lifetime');

    return { free, starter, professional, enterprise, lifetime };
});

// Get recommended plan based on current user count
const recommendedPlan = computed(() => {
    const userCount = props.company.current_user_count;

    // If at or near limit, recommend next tier
    if (userCount >= props.company.user_limit * 0.8) {
        const currentLimit = props.company.user_limit;
        return props.available_plans.find(p => p.max_users > currentLimit && p.interval === 'month');
    }

    // Otherwise recommend based on user count
    if (userCount <= 6) return props.available_plans.find(p => p.slug === 'free');
    if (userCount <= 21) return props.available_plans.find(p => p.slug === 'starter-monthly');
    if (userCount <= 51) return props.available_plans.find(p => p.slug === 'professional-monthly');
    return props.available_plans.find(p => p.slug === 'enterprise-monthly');
});

const usagePercentage = computed(() => {
    return Math.round((props.company.current_user_count / props.company.user_limit) * 100);
});

const formatPrice = (price: string | number, currency: string) => {
    const numPrice = typeof price === 'string' ? parseFloat(price) : price;
    if (numPrice === 0) return 'Free';

    return new Intl.NumberFormat('de-DE', {
        style: 'currency',
        currency: currency.toUpperCase(),
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
    }).format(numPrice);
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

const getStatusColor = (status: string) => {
    switch (status) {
        case 'active':
            return 'text-green-600 bg-green-100 dark:text-green-400 dark:bg-green-900/30';
        case 'cancelled':
            return 'text-red-600 bg-red-100 dark:text-red-400 dark:bg-red-900/30';
        case 'expired':
            return 'text-gray-600 bg-gray-100 dark:text-gray-400 dark:bg-gray-900/30';
        default:
            return 'text-yellow-600 bg-yellow-100 dark:text-yellow-400 dark:bg-yellow-900/30';
    }
};

const getIntervalDisplay = (interval: string) => {
    switch (interval) {
        case 'month': return 'per month';
        case 'year': return 'per year';
        case 'one_time': return 'one-time';
        default: return interval;
    }
};

const handleChangePlan = async () => {
    if (!selectedPlan.value) return;

    processing.value = true;

    try {
        const response = await axios.post('/subscription/change', {
            plan_id: selectedPlan.value,
        });

        const { checkout_url, fake } = response.data;

        if (fake) {
            setTimeout(() => {
                window.location.href = checkout_url;
            }, 1000);
        } else {
            window.location.href = checkout_url;
        }
    } catch (error: any) {
        console.error('Change plan error:', error);
        processing.value = false;
        const message =
            error.response?.data?.message ||
            'Failed to change plan. Please try again.';
        toast.error(message);
    }
};

const handleCancelSubscription = async () => {
    processing.value = true;

    try {
        await router.post('/subscription/cancel', {}, {
            preserveScroll: true,
            onSuccess: () => {
                showCancelConfirm.value = false;
                processing.value = false;
                toast.success('Subscription cancelled successfully!');
            },
            onError: () => {
                processing.value = false;
                toast.error('Failed to cancel subscription. Please try again.');
            },
        });
    } catch (error) {
        console.error('Cancel subscription error:', error);
        processing.value = false;
    }
};
</script>

<template>
    <Head title="Subscription Management" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <!-- Gradient background -->
        <div
            class="absolute inset-0 -z-10 bg-gradient-to-br from-slate-50 via-orange-50 to-rose-50 dark:from-slate-950 dark:via-orange-950 dark:to-rose-950"
        />

        <div class="relative flex h-full flex-1 flex-col overflow-hidden">
            <div class="flex-1 overflow-auto p-6">
                <div class="mx-auto max-w-7xl space-y-8">
                    <!-- Header -->
                    <div class="flex items-center justify-between">
                        <div>
                            <h1
                                class="text-3xl font-bold text-slate-900 dark:text-white"
                            >
                                Subscription Management
                            </h1>
                            <p class="mt-2 text-slate-600 dark:text-slate-400">
                                Manage your {{ company.name }} subscription
                            </p>
                        </div>
                    </div>

                    <!-- User Limit Warning Banner -->
                    <div
                        v-if="company.is_near_limit || company.has_reached_limit"
                        class="rounded-2xl border p-6 backdrop-blur-xl"
                        :class="
                            company.has_reached_limit
                                ? 'border-red-500/30 bg-red-50/50 dark:bg-red-900/20'
                                : 'border-amber-500/30 bg-amber-50/50 dark:bg-amber-900/20'
                        "
                    >
                        <div class="flex items-start gap-4">
                            <div
                                class="flex h-12 w-12 items-center justify-center rounded-xl"
                                :class="
                                    company.has_reached_limit
                                        ? 'bg-red-500'
                                        : 'bg-amber-500'
                                "
                            >
                                <AlertCircle class="h-6 w-6 text-white" />
                            </div>
                            <div class="flex-1">
                                <h3
                                    class="text-lg font-bold"
                                    :class="
                                        company.has_reached_limit
                                            ? 'text-red-900 dark:text-red-100'
                                            : 'text-amber-900 dark:text-amber-100'
                                    "
                                >
                                    {{
                                        company.has_reached_limit
                                            ? 'User Limit Reached!'
                                            : 'Approaching User Limit'
                                    }}
                                </h3>
                                <p
                                    class="mt-1"
                                    :class="
                                        company.has_reached_limit
                                            ? 'text-red-700 dark:text-red-200'
                                            : 'text-amber-700 dark:text-amber-200'
                                    "
                                >
                                    You're using {{ company.current_user_count }} of
                                    {{ company.user_limit }} users ({{ usagePercentage }}%).
                                    {{
                                        company.has_reached_limit
                                            ? 'Upgrade now to add more team members.'
                                            : 'Consider upgrading to avoid hitting your limit.'
                                    }}
                                </p>
                                <div class="mt-3 h-2 overflow-hidden rounded-full bg-white/50">
                                    <div
                                        class="h-full rounded-full transition-all duration-500"
                                        :class="
                                            usagePercentage >= 100
                                                ? 'bg-red-500'
                                                : usagePercentage >= 80
                                                  ? 'bg-amber-500'
                                                  : 'bg-green-500'
                                        "
                                        :style="{ width: `${Math.min(usagePercentage, 100)}%` }"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Current Subscription Overview -->
                    <div
                        v-if="current_subscription"
                        class="rounded-2xl border border-white/60 bg-white/70 p-8 shadow-xl backdrop-blur-xl dark:border-white/10 dark:bg-slate-900/70"
                    >
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-3">
                                    <Building2 class="h-6 w-6 text-orange-500" />
                                    <h2
                                        class="text-2xl font-bold text-slate-900 dark:text-white"
                                    >
                                        {{ current_subscription.plan.name }}
                                    </h2>
                                    <span
                                        class="rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-wider"
                                        :class="getStatusColor(current_subscription.status)"
                                    >
                                        {{ current_subscription.status }}
                                    </span>
                                </div>
                                <p class="mt-2 text-slate-600 dark:text-slate-400">
                                    {{ current_subscription.plan.description }}
                                </p>

                                <div class="mt-6 grid gap-6 md:grid-cols-4">
                                    <div>
                                        <div class="text-sm text-slate-600 dark:text-slate-400">
                                            Price
                                        </div>
                                        <div
                                            class="mt-1 text-2xl font-bold text-slate-900 dark:text-white"
                                        >
                                            {{ formatPrice(current_subscription.plan.price, current_subscription.plan.currency) }}
                                        </div>
                                        <div class="text-sm text-slate-500 dark:text-slate-400">
                                            {{ getIntervalDisplay(current_subscription.plan.interval) }}
                                        </div>
                                    </div>
                                    <div>
                                        <div class="text-sm text-slate-600 dark:text-slate-400">
                                            User Limit
                                        </div>
                                        <div class="mt-1 flex items-baseline gap-2">
                                            <span class="text-2xl font-bold text-slate-900 dark:text-white">
                                                {{ company.current_user_count }}
                                            </span>
                                            <span class="text-lg text-slate-500 dark:text-slate-400">
                                                / {{ current_subscription.plan.max_users }}
                                            </span>
                                        </div>
                                        <div class="text-sm text-slate-500 dark:text-slate-400">
                                            {{ company.remaining_slots }} slots remaining
                                        </div>
                                    </div>
                                    <div>
                                        <div class="text-sm text-slate-600 dark:text-slate-400">
                                            Started
                                        </div>
                                        <div
                                            class="mt-1 text-lg font-medium text-slate-900 dark:text-white"
                                        >
                                            {{ formatDate(current_subscription.starts_at) }}
                                        </div>
                                    </div>
                                    <div v-if="current_subscription.ends_at">
                                        <div class="text-sm text-slate-600 dark:text-slate-400">
                                            {{ current_subscription.days_remaining && current_subscription.days_remaining > 0 ? 'Days Remaining' : 'Expires' }}
                                        </div>
                                        <div
                                            class="mt-1 text-lg font-medium"
                                            :class="
                                                current_subscription.days_remaining &&
                                                current_subscription.days_remaining < 7
                                                    ? 'text-red-600 dark:text-red-400'
                                                    : 'text-slate-900 dark:text-white'
                                            "
                                        >
                                            {{
                                                current_subscription.days_remaining &&
                                                current_subscription.days_remaining > 0
                                                    ? `${current_subscription.days_remaining} days`
                                                    : formatDate(current_subscription.ends_at)
                                            }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <button
                                v-if="current_subscription.status === 'active' && current_subscription.plan.interval !== 'one_time'"
                                @click="showCancelConfirm = true"
                                class="rounded-lg border border-red-200 bg-red-50 px-4 py-2 text-sm font-medium text-red-700 transition-colors hover:bg-red-100 dark:border-red-800 dark:bg-red-900/30 dark:text-red-400 dark:hover:bg-red-900/50"
                            >
                                Cancel Subscription
                            </button>
                        </div>
                    </div>

                    <!-- Recommended Plan Alert -->
                    <div
                        v-if="recommendedPlan && recommendedPlan.id !== current_subscription?.plan.id"
                        class="rounded-2xl border border-blue-500/30 bg-blue-50/50 p-6 backdrop-blur-xl dark:bg-blue-900/20"
                    >
                        <div class="flex items-start gap-4">
                            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-500">
                                <Info class="h-6 w-6 text-white" />
                            </div>
                            <div class="flex-1">
                                <h3 class="text-lg font-bold text-blue-900 dark:text-blue-100">
                                    Recommended Plan for You
                                </h3>
                                <p class="mt-1 text-blue-700 dark:text-blue-200">
                                    Based on your current usage ({{ company.current_user_count }} users), we recommend the <strong>{{ recommendedPlan.name }}</strong> plan for optimal value.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Available Plans -->
                    <div>
                        <h2 class="mb-6 text-2xl font-bold text-slate-900 dark:text-white">
                            Available Plans
                        </h2>

                        <!-- Free & Lifetime Plans -->
                        <div class="mb-8 grid gap-6 md:grid-cols-2">
                            <!-- Free Plan -->
                            <div
                                v-if="planGroups.free"
                                class="group relative cursor-pointer transition-all duration-300"
                                @click="selectedPlan = planGroups.free.id"
                            >
                                <div
                                    class="flex h-full flex-col rounded-2xl border p-6 backdrop-blur-xl transition-all duration-300"
                                    :class="[
                                        selectedPlan === planGroups.free.id
                                            ? 'scale-105 border-orange-500 bg-orange-50 shadow-xl dark:bg-orange-950/30'
                                            : 'border-slate-200 bg-white hover:border-slate-300 dark:border-slate-800 dark:bg-slate-900 dark:hover:border-slate-700',
                                        current_subscription?.plan.id === planGroups.free.id &&
                                            'ring-2 ring-green-500/50',
                                    ]"
                                >
                                    <div
                                        v-if="current_subscription?.plan.id === planGroups.free.id"
                                        class="absolute -top-3 left-1/2 -translate-x-1/2 rounded-full bg-gradient-to-r from-green-500 to-emerald-500 px-4 py-1 text-xs font-bold uppercase tracking-wider text-white shadow-lg"
                                    >
                                        Current Plan
                                    </div>

                                    <div class="flex items-start justify-between">
                                        <div>
                                            <h3 class="text-2xl font-bold text-slate-900 dark:text-white">
                                                {{ planGroups.free.name }}
                                            </h3>
                                            <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
                                                {{ planGroups.free.description }}
                                            </p>
                                        </div>
                                        <Sparkles class="h-8 w-8 text-orange-500" />
                                    </div>

                                    <div class="my-6">
                                        <div class="flex items-baseline gap-2">
                                            <span class="text-4xl font-bold text-slate-900 dark:text-white">
                                                Free
                                            </span>
                                        </div>
                                        <div class="mt-2 flex items-center gap-2 text-sm">
                                            <Users class="h-4 w-4 text-slate-500" />
                                            <span class="text-slate-600 dark:text-slate-400">
                                                Up to {{ planGroups.free.max_users }} users
                                            </span>
                                        </div>
                                    </div>

                                    <ul class="mb-6 flex-1 space-y-3">
                                        <li
                                            v-for="feature in planGroups.free.features"
                                            :key="feature"
                                            class="flex items-start gap-2 text-sm text-slate-600 dark:text-slate-400"
                                        >
                                            <Check class="mt-0.5 h-4 w-4 flex-shrink-0 text-orange-500" />
                                            {{ feature }}
                                        </li>
                                    </ul>

                                    <div
                                        class="flex h-12 w-full items-center justify-center rounded-lg font-semibold transition-all duration-300"
                                        :class="[
                                            selectedPlan === planGroups.free.id
                                                ? 'bg-orange-500 text-white shadow-lg shadow-orange-500/30'
                                                : 'bg-slate-100 text-slate-600 group-hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:group-hover:bg-slate-700',
                                        ]"
                                    >
                                        {{ current_subscription?.plan.id === planGroups.free.id ? 'Current Plan' : selectedPlan === planGroups.free.id ? 'Selected' : 'Select Plan' }}
                                    </div>
                                </div>
                            </div>

                            <!-- Lifetime Plan -->
                            <div
                                v-if="planGroups.lifetime"
                                class="group relative cursor-pointer transition-all duration-300"
                                @click="selectedPlan = planGroups.lifetime.id"
                            >
                                <div
                                    class="flex h-full flex-col rounded-2xl border p-6 backdrop-blur-xl transition-all duration-300"
                                    :class="[
                                        selectedPlan === planGroups.lifetime.id
                                            ? 'scale-105 border-amber-500 bg-amber-50 shadow-xl dark:bg-amber-950/30'
                                            : 'border-amber-300 bg-gradient-to-br from-amber-50 to-orange-50 hover:border-amber-400 dark:border-amber-700 dark:from-amber-900/20 dark:to-orange-900/20 dark:hover:border-amber-600',
                                        current_subscription?.plan.id === planGroups.lifetime.id &&
                                            'ring-2 ring-green-500/50',
                                    ]"
                                >
                                    <div class="absolute -top-3 left-1/2 -translate-x-1/2 rounded-full bg-gradient-to-r from-amber-500 to-orange-500 px-4 py-1 text-xs font-bold uppercase tracking-wider text-white shadow-lg">
                                        🌟 Limited Offer
                                    </div>

                                    <div class="flex items-start justify-between">
                                        <div>
                                            <h3 class="text-2xl font-bold text-slate-900 dark:text-white">
                                                {{ planGroups.lifetime.name }}
                                            </h3>
                                            <p class="mt-1 text-sm text-amber-700 dark:text-amber-300">
                                                {{ planGroups.lifetime.description }}
                                            </p>
                                        </div>
                                        <Crown class="h-8 w-8 text-amber-500" />
                                    </div>

                                    <div class="my-6">
                                        <div class="flex items-baseline gap-2">
                                            <span class="text-4xl font-bold text-slate-900 dark:text-white">
                                                {{ formatPrice(planGroups.lifetime.price, planGroups.lifetime.currency) }}
                                            </span>
                                        </div>
                                        <div class="text-sm text-amber-600 dark:text-amber-400">
                                            One-time payment • Never pay again
                                        </div>
                                        <div class="mt-2 flex items-center gap-2 text-sm">
                                            <Users class="h-4 w-4 text-slate-500" />
                                            <span class="text-slate-600 dark:text-slate-400">
                                                Up to {{ planGroups.lifetime.max_users }} users
                                            </span>
                                        </div>
                                    </div>

                                    <ul class="mb-6 flex-1 space-y-3">
                                        <li
                                            v-for="feature in planGroups.lifetime.features"
                                            :key="feature"
                                            class="flex items-start gap-2 text-sm text-slate-600 dark:text-slate-400"
                                        >
                                            <Check class="mt-0.5 h-4 w-4 flex-shrink-0 text-amber-500" />
                                            {{ feature }}
                                        </li>
                                    </ul>

                                    <div
                                        class="flex h-12 w-full items-center justify-center rounded-lg font-semibold transition-all duration-300"
                                        :class="[
                                            selectedPlan === planGroups.lifetime.id
                                                ? 'bg-gradient-to-r from-amber-500 to-orange-500 text-white shadow-lg shadow-amber-500/30'
                                                : 'bg-amber-100 text-amber-800 group-hover:bg-amber-200 dark:bg-amber-900/50 dark:text-amber-200 dark:group-hover:bg-amber-900/70',
                                        ]"
                                    >
                                        {{ current_subscription?.plan.id === planGroups.lifetime.id ? 'Current Plan' : selectedPlan === planGroups.lifetime.id ? 'Selected' : 'Get Lifetime Access' }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tiered Plans (Starter, Professional, Enterprise) -->
                        <div class="grid gap-6 lg:grid-cols-3">
                            <!-- Starter Plans -->
                            <div class="space-y-4">
                                <h3 class="text-lg font-bold text-slate-900 dark:text-white">
                                    Starter
                                </h3>
                                <div
                                    v-for="plan in planGroups.starter"
                                    :key="plan.id"
                                    class="group relative cursor-pointer transition-all duration-300"
                                    @click="selectedPlan = plan.id"
                                >
                                    <div
                                        class="flex h-full flex-col rounded-2xl border p-6 backdrop-blur-xl transition-all duration-300"
                                        :class="[
                                            selectedPlan === plan.id
                                                ? 'scale-105 border-blue-500 bg-blue-50 shadow-xl dark:bg-blue-950/30'
                                                : 'border-slate-200 bg-white hover:border-slate-300 dark:border-slate-800 dark:bg-slate-900 dark:hover:border-slate-700',
                                            current_subscription?.plan.id === plan.id &&
                                                'ring-2 ring-green-500/50',
                                        ]"
                                    >
                                        <div
                                            v-if="plan.is_popular"
                                            class="absolute -top-3 left-1/2 -translate-x-1/2 rounded-full bg-gradient-to-r from-blue-500 to-indigo-500 px-4 py-1 text-xs font-bold uppercase tracking-wider text-white shadow-lg"
                                        >
                                            ⭐ Popular
                                        </div>

                                        <div
                                            v-if="current_subscription?.plan.id === plan.id"
                                            class="absolute -top-3 right-4 rounded-full bg-gradient-to-r from-green-500 to-emerald-500 px-3 py-1 text-xs font-bold uppercase tracking-wider text-white shadow-lg"
                                        >
                                            Current
                                        </div>

                                        <div>
                                            <div class="text-sm font-semibold text-slate-600 dark:text-slate-400">
                                                {{ plan.interval === 'month' ? 'Monthly' : 'Yearly' }}
                                            </div>
                                            <div class="mt-2 flex items-baseline gap-2">
                                                <span class="text-3xl font-bold text-slate-900 dark:text-white">
                                                    {{ formatPrice(plan.price, plan.currency) }}
                                                </span>
                                            </div>
                                            <div class="text-sm text-slate-500 dark:text-slate-400">
                                                {{ getIntervalDisplay(plan.interval) }}
                                            </div>
                                            <div class="mt-2 flex items-center gap-2 text-sm">
                                                <Users class="h-4 w-4 text-slate-500" />
                                                <span class="text-slate-600 dark:text-slate-400">
                                                    Up to {{ plan.max_users }} users
                                                </span>
                                            </div>
                                        </div>

                                        <ul class="my-4 flex-1 space-y-2">
                                            <li
                                                v-for="(feature, idx) in plan.features.slice(0, 4)"
                                                :key="idx"
                                                class="flex items-start gap-2 text-xs text-slate-600 dark:text-slate-400"
                                            >
                                                <Check class="mt-0.5 h-3 w-3 flex-shrink-0 text-blue-500" />
                                                {{ feature }}
                                            </li>
                                        </ul>

                                        <div
                                            class="flex h-10 w-full items-center justify-center rounded-lg text-sm font-semibold transition-all duration-300"
                                            :class="[
                                                selectedPlan === plan.id
                                                    ? 'bg-blue-500 text-white shadow-lg shadow-blue-500/30'
                                                    : 'bg-slate-100 text-slate-600 group-hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:group-hover:bg-slate-700',
                                            ]"
                                        >
                                            {{ current_subscription?.plan.id === plan.id ? 'Current' : selectedPlan === plan.id ? 'Selected' : 'Select' }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Professional Plans -->
                            <div class="space-y-4">
                                <h3 class="text-lg font-bold text-slate-900 dark:text-white">
                                    Professional
                                </h3>
                                <div
                                    v-for="plan in planGroups.professional"
                                    :key="plan.id"
                                    class="group relative cursor-pointer transition-all duration-300"
                                    @click="selectedPlan = plan.id"
                                >
                                    <div
                                        class="flex h-full flex-col rounded-2xl border p-6 backdrop-blur-xl transition-all duration-300"
                                        :class="[
                                            selectedPlan === plan.id
                                                ? 'scale-105 border-indigo-500 bg-indigo-50 shadow-xl dark:bg-indigo-950/30'
                                                : 'border-slate-200 bg-white hover:border-slate-300 dark:border-slate-800 dark:bg-slate-900 dark:hover:border-slate-700',
                                            current_subscription?.plan.id === plan.id &&
                                                'ring-2 ring-green-500/50',
                                        ]"
                                    >
                                        <div
                                            v-if="plan.is_popular"
                                            class="absolute -top-3 left-1/2 -translate-x-1/2 rounded-full bg-gradient-to-r from-indigo-500 to-purple-500 px-4 py-1 text-xs font-bold uppercase tracking-wider text-white shadow-lg"
                                        >
                                            ⭐ Popular
                                        </div>

                                        <div
                                            v-if="current_subscription?.plan.id === plan.id"
                                            class="absolute -top-3 right-4 rounded-full bg-gradient-to-r from-green-500 to-emerald-500 px-3 py-1 text-xs font-bold uppercase tracking-wider text-white shadow-lg"
                                        >
                                            Current
                                        </div>

                                        <div>
                                            <div class="text-sm font-semibold text-slate-600 dark:text-slate-400">
                                                {{ plan.interval === 'month' ? 'Monthly' : 'Yearly' }}
                                            </div>
                                            <div class="mt-2 flex items-baseline gap-2">
                                                <span class="text-3xl font-bold text-slate-900 dark:text-white">
                                                    {{ formatPrice(plan.price, plan.currency) }}
                                                </span>
                                            </div>
                                            <div class="text-sm text-slate-500 dark:text-slate-400">
                                                {{ getIntervalDisplay(plan.interval) }}
                                            </div>
                                            <div class="mt-2 flex items-center gap-2 text-sm">
                                                <Users class="h-4 w-4 text-slate-500" />
                                                <span class="text-slate-600 dark:text-slate-400">
                                                    Up to {{ plan.max_users }} users
                                                </span>
                                            </div>
                                        </div>

                                        <ul class="my-4 flex-1 space-y-2">
                                            <li
                                                v-for="(feature, idx) in plan.features.slice(0, 4)"
                                                :key="idx"
                                                class="flex items-start gap-2 text-xs text-slate-600 dark:text-slate-400"
                                            >
                                                <Check class="mt-0.5 h-3 w-3 flex-shrink-0 text-indigo-500" />
                                                {{ feature }}
                                            </li>
                                        </ul>

                                        <div
                                            class="flex h-10 w-full items-center justify-center rounded-lg text-sm font-semibold transition-all duration-300"
                                            :class="[
                                                selectedPlan === plan.id
                                                    ? 'bg-indigo-500 text-white shadow-lg shadow-indigo-500/30'
                                                    : 'bg-slate-100 text-slate-600 group-hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:group-hover:bg-slate-700',
                                            ]"
                                        >
                                            {{ current_subscription?.plan.id === plan.id ? 'Current' : selectedPlan === plan.id ? 'Selected' : 'Select' }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Enterprise Plans -->
                            <div class="space-y-4">
                                <h3 class="text-lg font-bold text-slate-900 dark:text-white">
                                    Enterprise
                                </h3>
                                <div
                                    v-for="plan in planGroups.enterprise"
                                    :key="plan.id"
                                    class="group relative cursor-pointer transition-all duration-300"
                                    @click="selectedPlan = plan.id"
                                >
                                    <div
                                        class="flex h-full flex-col rounded-2xl border p-6 backdrop-blur-xl transition-all duration-300"
                                        :class="[
                                            selectedPlan === plan.id
                                                ? 'scale-105 border-purple-500 bg-purple-50 shadow-xl dark:bg-purple-950/30'
                                                : 'border-slate-200 bg-white hover:border-slate-300 dark:border-slate-800 dark:bg-slate-900 dark:hover:border-slate-700',
                                            current_subscription?.plan.id === plan.id &&
                                                'ring-2 ring-green-500/50',
                                        ]"
                                    >
                                        <div
                                            v-if="plan.is_popular"
                                            class="absolute -top-3 left-1/2 -translate-x-1/2 rounded-full bg-gradient-to-r from-purple-500 to-pink-500 px-4 py-1 text-xs font-bold uppercase tracking-wider text-white shadow-lg"
                                        >
                                            ⭐ Popular
                                        </div>

                                        <div
                                            v-if="current_subscription?.plan.id === plan.id"
                                            class="absolute -top-3 right-4 rounded-full bg-gradient-to-r from-green-500 to-emerald-500 px-3 py-1 text-xs font-bold uppercase tracking-wider text-white shadow-lg"
                                        >
                                            Current
                                        </div>

                                        <div>
                                            <div class="text-sm font-semibold text-slate-600 dark:text-slate-400">
                                                {{ plan.interval === 'month' ? 'Monthly' : 'Yearly' }}
                                            </div>
                                            <div class="mt-2 flex items-baseline gap-2">
                                                <span class="text-3xl font-bold text-slate-900 dark:text-white">
                                                    {{ formatPrice(plan.price, plan.currency) }}
                                                </span>
                                            </div>
                                            <div class="text-sm text-slate-500 dark:text-slate-400">
                                                {{ getIntervalDisplay(plan.interval) }}
                                            </div>
                                            <div class="mt-2 flex items-center gap-2 text-sm">
                                                <Users class="h-4 w-4 text-slate-500" />
                                                <span class="text-slate-600 dark:text-slate-400">
                                                    Up to {{ plan.max_users }} users
                                                </span>
                                            </div>
                                        </div>

                                        <ul class="my-4 flex-1 space-y-2">
                                            <li
                                                v-for="(feature, idx) in plan.features.slice(0, 4)"
                                                :key="idx"
                                                class="flex items-start gap-2 text-xs text-slate-600 dark:text-slate-400"
                                            >
                                                <Check class="mt-0.5 h-3 w-3 flex-shrink-0 text-purple-500" />
                                                {{ feature }}
                                            </li>
                                        </ul>

                                        <div
                                            class="flex h-10 w-full items-center justify-center rounded-lg text-sm font-semibold transition-all duration-300"
                                            :class="[
                                                selectedPlan === plan.id
                                                    ? 'bg-purple-500 text-white shadow-lg shadow-purple-500/30'
                                                    : 'bg-slate-100 text-slate-600 group-hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:group-hover:bg-slate-700',
                                            ]"
                                        >
                                            {{ current_subscription?.plan.id === plan.id ? 'Current' : selectedPlan === plan.id ? 'Selected' : 'Select' }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Subscription History -->
                    <div v-if="subscription_history.length > 0">
                        <h2 class="mb-6 text-2xl font-bold text-slate-900 dark:text-white">
                            Subscription History
                        </h2>
                        <div
                            class="overflow-hidden rounded-2xl border border-white/60 bg-white/70 backdrop-blur-xl dark:border-white/10 dark:bg-slate-900/70"
                        >
                            <table class="w-full">
                                <thead class="border-b border-slate-200 bg-slate-50 dark:border-slate-800 dark:bg-slate-800/50">
                                    <tr>
                                        <th class="px-6 py-4 text-left text-sm font-semibold text-slate-900 dark:text-white">
                                            Plan
                                        </th>
                                        <th class="px-6 py-4 text-left text-sm font-semibold text-slate-900 dark:text-white">
                                            Status
                                        </th>
                                        <th class="px-6 py-4 text-left text-sm font-semibold text-slate-900 dark:text-white">
                                            Started
                                        </th>
                                        <th class="px-6 py-4 text-left text-sm font-semibold text-slate-900 dark:text-white">
                                            Ended
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                                    <tr
                                        v-for="item in subscription_history"
                                        :key="item.id"
                                        class="hover:bg-slate-50 dark:hover:bg-slate-800/50"
                                    >
                                        <td class="px-6 py-4 text-sm font-medium text-slate-900 dark:text-white">
                                            {{ item.plan_name }}
                                        </td>
                                        <td class="px-6 py-4">
                                            <span
                                                class="inline-flex rounded-full px-2 py-1 text-xs font-semibold uppercase tracking-wider"
                                                :class="getStatusColor(item.status)"
                                            >
                                                {{ item.status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400">
                                            {{ item.starts_at }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400">
                                            {{ item.ends_at || '-' }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Change Plan Action Bar -->
        <div
            class="fixed right-0 bottom-0 left-0 z-50 transform border-t border-slate-200 bg-white/90 p-6 backdrop-blur-xl transition-all duration-500 dark:border-slate-800 dark:bg-slate-900/90"
            :class="
                selectedPlan && selectedPlan !== current_subscription?.plan.id
                    ? 'translate-y-0'
                    : 'translate-y-full'
            "
        >
            <div class="mx-auto flex max-w-xl items-center justify-between gap-6">
                <div class="flex items-center gap-4">
                    <div class="rounded-xl bg-gradient-to-r from-orange-500 to-rose-500 p-3 text-white">
                        <Zap class="h-6 w-6" />
                    </div>
                    <div>
                        <div class="font-bold text-slate-900 dark:text-white">
                            Ready to upgrade?
                        </div>
                        <div class="text-sm text-slate-600 dark:text-slate-400">
                            Secure payment via Stripe
                        </div>
                    </div>
                </div>

                <button
                    @click="handleChangePlan"
                    :disabled="processing"
                    class="flex items-center gap-2 rounded-xl bg-gradient-to-r from-orange-500 to-rose-500 px-8 py-3 font-bold text-white shadow-lg shadow-orange-500/30 transition-all duration-300 hover:from-orange-600 hover:to-rose-600 disabled:cursor-not-allowed disabled:opacity-50"
                >
                    <template v-if="processing">
                        <div
                            class="h-5 w-5 animate-spin rounded-full border-2 border-white/30 border-t-white"
                        ></div>
                        Processing...
                    </template>
                    <template v-else>
                        Continue to Payment
                        <CreditCard class="h-5 w-5" />
                    </template>
                </button>
            </div>
        </div>

        <!-- Cancel Confirmation Modal -->
        <div
            v-if="showCancelConfirm"
            class="fixed inset-0 z-[100] flex items-center justify-center bg-black/50 p-6 backdrop-blur-sm"
            @click.self="showCancelConfirm = false"
        >
            <div
                class="w-full max-w-md rounded-2xl border border-white/20 bg-white/90 p-8 shadow-2xl backdrop-blur-xl dark:border-white/10 dark:bg-slate-900/90"
            >
                <div class="mb-6 flex h-12 w-12 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30">
                    <X class="h-6 w-6 text-red-600 dark:text-red-400" />
                </div>
                <h3 class="mb-3 text-xl font-bold text-slate-900 dark:text-white">
                    Cancel Subscription
                </h3>
                <p class="mb-6 text-slate-600 dark:text-slate-400">
                    Are you sure you want to cancel your subscription? You will
                    retain access until the end of your billing period.
                </p>
                <div class="flex gap-3">
                    <button
                        @click="showCancelConfirm = false"
                        class="flex-1 rounded-lg border border-slate-200 bg-white px-4 py-2 font-medium text-slate-700 transition-colors hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700"
                    >
                        Keep Subscription
                    </button>
                    <button
                        @click="handleCancelSubscription"
                        :disabled="processing"
                        class="flex-1 rounded-lg bg-red-600 px-4 py-2 font-medium text-white transition-colors hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        {{ processing ? 'Cancelling...' : 'Cancel Subscription' }}
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
