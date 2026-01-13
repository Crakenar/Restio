<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import {
    Building2,
    Calendar,
    Check,
    CreditCard,
    TrendingUp,
    X,
} from 'lucide-vue-next';
import { ref } from 'vue';
import axios from 'axios';

interface Plan {
    id: number;
    name: string;
    slug: string;
    price: string;
    currency: string;
    interval: string;
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
}

interface Props {
    company: Company;
    current_subscription: CurrentSubscription | null;
    subscription_history: HistoryItem[];
    available_plans: Plan[];
    fake_mode: boolean;
}

const props = defineProps<Props>();

const selectedPlan = ref<number | null>(null);
const processing = ref(false);
const showCancelConfirm = ref(false);

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Subscription', href: '/subscription' },
];

const formatPrice = (price: string, currency: string) => {
    return new Intl.NumberFormat('de-DE', {
        style: 'currency',
        currency: currency.toUpperCase(),
    }).format(Number(price));
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

const getFeatures = (interval: string) => {
    switch (interval) {
        case 'month':
            return ['Up to 10 Employees', 'Basic Support', 'All Core Features'];
        case 'year':
            return [
                'Up to 50 Employees',
                'Priority Support',
                'Advanced Analytics',
                '2 Months Free',
            ];
        case 'one_time':
            return [
                'Unlimited Employees',
                'Premium Support',
                'Access to All Future Updates',
                'Never Pay Again',
            ];
        default:
            return [];
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
        alert(message);
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
            },
            onError: () => {
                processing.value = false;
                alert('Failed to cancel subscription. Please try again.');
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
            class="absolute inset-0 -z-10 bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 dark:from-slate-950 dark:via-blue-950 dark:to-indigo-950"
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

                    <!-- Current Subscription -->
                    <div
                        v-if="current_subscription"
                        class="rounded-2xl border border-slate-200 bg-white p-8 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                    >
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-3">
                                    <Building2
                                        class="h-6 w-6 text-blue-500"
                                    />
                                    <h2
                                        class="text-2xl font-bold text-slate-900 dark:text-white"
                                    >
                                        {{ current_subscription.plan.name }}
                                    </h2>
                                    <span
                                        class="rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-wider"
                                        :class="
                                            getStatusColor(
                                                current_subscription.status,
                                            )
                                        "
                                    >
                                        {{ current_subscription.status }}
                                    </span>
                                </div>
                                <div class="mt-4 grid gap-4 md:grid-cols-3">
                                    <div>
                                        <div
                                            class="text-sm text-slate-600 dark:text-slate-400"
                                        >
                                            Price
                                        </div>
                                        <div
                                            class="mt-1 text-xl font-semibold text-slate-900 dark:text-white"
                                        >
                                            {{
                                                formatPrice(
                                                    current_subscription.plan
                                                        .price,
                                                    current_subscription.plan
                                                        .currency,
                                                )
                                            }}
                                            <span class="text-sm font-normal"
                                                >/
                                                {{
                                                    current_subscription.plan
                                                        .interval
                                                }}</span
                                            >
                                        </div>
                                    </div>
                                    <div>
                                        <div
                                            class="text-sm text-slate-600 dark:text-slate-400"
                                        >
                                            Started
                                        </div>
                                        <div
                                            class="mt-1 text-lg font-medium text-slate-900 dark:text-white"
                                        >
                                            {{
                                                formatDate(
                                                    current_subscription.starts_at,
                                                )
                                            }}
                                        </div>
                                    </div>
                                    <div v-if="current_subscription.ends_at">
                                        <div
                                            class="text-sm text-slate-600 dark:text-slate-400"
                                        >
                                            {{
                                                current_subscription.days_remaining &&
                                                current_subscription.days_remaining >
                                                    0
                                                    ? 'Days Remaining'
                                                    : 'Expires'
                                            }}
                                        </div>
                                        <div
                                            class="mt-1 text-lg font-medium"
                                            :class="
                                                current_subscription.days_remaining &&
                                                current_subscription.days_remaining <
                                                    7
                                                    ? 'text-red-600 dark:text-red-400'
                                                    : 'text-slate-900 dark:text-white'
                                            "
                                        >
                                            {{
                                                current_subscription.days_remaining &&
                                                current_subscription.days_remaining >
                                                    0
                                                    ? `${current_subscription.days_remaining} days`
                                                    : formatDate(
                                                          current_subscription.ends_at,
                                                      )
                                            }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <button
                                v-if="current_subscription.status === 'active'"
                                @click="showCancelConfirm = true"
                                class="rounded-lg border border-red-200 bg-red-50 px-4 py-2 text-sm font-medium text-red-700 transition-colors hover:bg-red-100 dark:border-red-800 dark:bg-red-900/30 dark:text-red-400 dark:hover:bg-red-900/50"
                            >
                                Cancel Subscription
                            </button>
                        </div>
                    </div>

                    <!-- Available Plans -->
                    <div>
                        <h2
                            class="mb-6 text-2xl font-bold text-slate-900 dark:text-white"
                        >
                            Change Plan
                        </h2>
                        <div class="grid gap-6 md:grid-cols-3">
                            <div
                                v-for="plan in available_plans"
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
                                        current_subscription?.plan.id ===
                                            plan.id &&
                                            'ring-2 ring-green-500/50',
                                    ]"
                                >
                                    <div
                                        v-if="
                                            current_subscription?.plan.id ===
                                            plan.id
                                        "
                                        class="absolute -top-3 left-1/2 -translate-x-1/2 rounded-full bg-gradient-to-r from-green-500 to-emerald-500 px-4 py-1 text-xs font-bold tracking-wider uppercase text-white shadow-lg"
                                    >
                                        Current Plan
                                    </div>

                                    <h3
                                        class="mb-2 text-xl font-semibold text-slate-900 dark:text-white"
                                    >
                                        {{ plan.name }}
                                    </h3>
                                    <div class="mb-6">
                                        <span
                                            class="text-3xl font-bold text-slate-900 dark:text-white"
                                            >{{
                                                formatPrice(
                                                    plan.price,
                                                    plan.currency,
                                                )
                                            }}</span
                                        >
                                        <span
                                            class="ml-1 text-sm text-slate-600 dark:text-slate-400"
                                            >/ {{ plan.interval }}</span
                                        >
                                    </div>

                                    <ul class="mb-6 flex-1 space-y-3">
                                        <li
                                            v-for="feature in getFeatures(
                                                plan.interval,
                                            )"
                                            :key="feature"
                                            class="flex items-start gap-2 text-sm text-slate-600 dark:text-slate-400"
                                        >
                                            <Check
                                                class="mt-0.5 h-4 w-4 flex-shrink-0 text-blue-500"
                                            />
                                            {{ feature }}
                                        </li>
                                    </ul>

                                    <div
                                        class="flex h-10 w-full items-center justify-center rounded-lg font-semibold transition-all duration-300"
                                        :class="[
                                            selectedPlan === plan.id
                                                ? 'bg-blue-500 text-white shadow-lg shadow-blue-500/30'
                                                : 'bg-slate-100 text-slate-600 group-hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:group-hover:bg-slate-700',
                                        ]"
                                    >
                                        {{
                                            current_subscription?.plan.id ===
                                            plan.id
                                                ? 'Current Plan'
                                                : selectedPlan === plan.id
                                                  ? 'Selected'
                                                  : 'Select Plan'
                                        }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Subscription History -->
                    <div>
                        <h2
                            class="mb-6 text-2xl font-bold text-slate-900 dark:text-white"
                        >
                            Subscription History
                        </h2>
                        <div
                            class="overflow-hidden rounded-2xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900"
                        >
                            <table class="w-full">
                                <thead
                                    class="border-b border-slate-200 bg-slate-50 dark:border-slate-800 dark:bg-slate-800/50"
                                >
                                    <tr>
                                        <th
                                            class="px-6 py-4 text-left text-sm font-semibold text-slate-900 dark:text-white"
                                        >
                                            Plan
                                        </th>
                                        <th
                                            class="px-6 py-4 text-left text-sm font-semibold text-slate-900 dark:text-white"
                                        >
                                            Status
                                        </th>
                                        <th
                                            class="px-6 py-4 text-left text-sm font-semibold text-slate-900 dark:text-white"
                                        >
                                            Started
                                        </th>
                                        <th
                                            class="px-6 py-4 text-left text-sm font-semibold text-slate-900 dark:text-white"
                                        >
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
                                        <td
                                            class="px-6 py-4 text-sm font-medium text-slate-900 dark:text-white"
                                        >
                                            {{ item.plan_name }}
                                        </td>
                                        <td class="px-6 py-4">
                                            <span
                                                class="inline-flex rounded-full px-2 py-1 text-xs font-semibold uppercase tracking-wider"
                                                :class="
                                                    getStatusColor(item.status)
                                                "
                                            >
                                                {{ item.status }}
                                            </span>
                                        </td>
                                        <td
                                            class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400"
                                        >
                                            {{ item.starts_at }}
                                        </td>
                                        <td
                                            class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400"
                                        >
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
            class="fixed right-0 bottom-0 left-0 z-50 transform border-t border-slate-200 bg-white/80 p-6 backdrop-blur-xl transition-all duration-500 dark:border-slate-800 dark:bg-slate-900/80"
            :class="
                selectedPlan &&
                selectedPlan !== current_subscription?.plan.id
                    ? 'translate-y-0'
                    : 'translate-y-full'
            "
        >
            <div class="mx-auto flex max-w-xl items-center justify-between gap-6">
                <div class="flex items-center gap-4">
                    <div
                        class="rounded-xl bg-blue-500/20 p-3 text-blue-500"
                    >
                        <TrendingUp class="h-6 w-6" />
                    </div>
                    <div>
                        <div
                            class="font-medium text-slate-900 dark:text-white"
                        >
                            Change to a new plan
                        </div>
                        <div class="text-sm text-slate-600 dark:text-slate-400">
                            Secure payment via Stripe
                        </div>
                    </div>
                </div>

                <button
                    @click="handleChangePlan"
                    :disabled="processing"
                    class="flex items-center gap-2 rounded-xl bg-gradient-to-r from-blue-500 to-indigo-600 px-8 py-3 font-bold text-white shadow-lg shadow-blue-500/20 transition-all duration-300 hover:from-blue-600 hover:to-indigo-700 disabled:cursor-not-allowed disabled:opacity-50"
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
                class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-8 shadow-2xl dark:border-slate-800 dark:bg-slate-900"
            >
                <div
                    class="mb-6 flex h-12 w-12 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30"
                >
                    <X class="h-6 w-6 text-red-600 dark:text-red-400" />
                </div>
                <h3
                    class="mb-3 text-xl font-bold text-slate-900 dark:text-white"
                >
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
