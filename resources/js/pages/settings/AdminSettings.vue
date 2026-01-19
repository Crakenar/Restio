<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { useToast } from '@/composables/useToast';
import PremiumSidebar from '@/components/PremiumSidebar.vue';
import { Button } from '@/components/ui/button';
import {
    CreditCard,
    Receipt,
    Download,
    ExternalLink,
    CheckCircle,
    XCircle,
    Clock,
    AlertTriangle,
    Calendar,
    Sparkles,
    TrendingUp,
} from 'lucide-vue-next';

interface Subscription {
    plan_name: string;
    status: string;
    price: string;
    currency: string;
    interval: string;
    starts_at: string;
    ends_at: string | null;
    days_remaining: number | null;
}

interface BillingHistoryItem {
    id: string;
    date: string;
    plan_name: string;
    amount: string;
    currency: string;
    status: string;
    invoice_url: string | null;
}

interface Props {
    subscription: Subscription | null;
    billingHistory: BillingHistoryItem[];
    stripePortalUrl: string | null;
}

const props = defineProps<Props>();
const toast = useToast();

const showCancelConfirm = ref(false);
const cancelProcessing = ref(false);

const getStatusConfig = (status: string) => {
    const configs = {
        active: {
            icon: CheckCircle,
            text: 'Active',
            gradient: 'from-emerald-500 to-teal-500',
            bg: 'bg-emerald-50 dark:bg-emerald-500/10',
            border: 'border-emerald-200 dark:border-emerald-500/20',
            textColor: 'text-emerald-700 dark:text-emerald-400',
        },
        cancelled: {
            icon: XCircle,
            text: 'Cancelled',
            gradient: 'from-orange-500 to-amber-500',
            bg: 'bg-orange-50 dark:bg-orange-500/10',
            border: 'border-orange-200 dark:border-orange-500/20',
            textColor: 'text-orange-700 dark:text-orange-400',
        },
        expired: {
            icon: AlertTriangle,
            text: 'Expired',
            gradient: 'from-red-500 to-rose-500',
            bg: 'bg-red-50 dark:bg-red-500/10',
            border: 'border-red-200 dark:border-red-500/20',
            textColor: 'text-red-700 dark:text-red-400',
        },
        pending: {
            icon: Clock,
            text: 'Pending',
            gradient: 'from-blue-500 to-indigo-500',
            bg: 'bg-blue-50 dark:bg-blue-500/10',
            border: 'border-blue-200 dark:border-blue-500/20',
            textColor: 'text-blue-700 dark:text-blue-400',
        },
    };
    return configs[status as keyof typeof configs] || configs.pending;
};

const formatCurrency = (amount: string, currency: string) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: currency.toUpperCase(),
    }).format(Number(amount));
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

const handleManageBilling = () => {
    if (props.stripePortalUrl) {
        window.location.href = props.stripePortalUrl;
    } else {
        toast.error('Stripe portal is not available at this time.');
    }
};

const handleCancelSubscription = () => {
    cancelProcessing.value = true;
    router.post(
        '/subscription/cancel',
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                showCancelConfirm.value = false;
                cancelProcessing.value = false;
                toast.success('Subscription cancelled successfully!');
            },
            onError: () => {
                cancelProcessing.value = false;
                toast.error('Failed to cancel subscription. Please try again.');
            },
        },
    );
};

const daysRemainingColor = computed(() => {
    if (!props.subscription?.days_remaining) return '';
    if (props.subscription.days_remaining < 7) return 'text-red-600 dark:text-red-400';
    if (props.subscription.days_remaining < 30) return 'text-orange-600 dark:text-orange-400';
    return 'text-emerald-600 dark:text-emerald-400';
});
</script>

<template>
    <Head title="Admin Settings" />

    <div
        class="flex min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 dark:from-slate-950 dark:via-blue-950 dark:to-indigo-950"
    >
        <!-- Sidebar -->
        <PremiumSidebar :notifications="$page.props.notifications || []" />

        <!-- Main content area -->
        <div class="ml-72 flex-1 p-4 transition-all duration-500 sm:p-6 lg:p-8">
            <!-- Animated gradient orbs -->
            <div class="pointer-events-none fixed inset-0 overflow-hidden">
                <div
                    class="absolute -top-1/2 -right-1/2 h-full w-full animate-pulse rounded-full bg-gradient-to-br from-blue-500/10 via-indigo-500/10 to-violet-500/10 blur-3xl dark:from-blue-500/20 dark:via-indigo-500/20 dark:to-violet-500/20"
                    style="animation-duration: 8s"
                />
                <div
                    class="absolute -bottom-1/2 -left-1/2 h-full w-full animate-pulse rounded-full bg-gradient-to-tr from-indigo-500/10 via-blue-500/10 to-cyan-500/10 blur-3xl dark:from-indigo-500/20 dark:via-blue-500/20 dark:to-cyan-500/20"
                    style="animation-duration: 10s; animation-delay: 1s"
                />
            </div>

            <!-- Content -->
            <div class="relative mx-auto max-w-6xl space-y-8">
                <!-- Page Header -->
                <div
                    class="flex items-center gap-4"
                    style="animation: slideInDown 0.6s cubic-bezier(0.16, 1, 0.3, 1)"
                >
                    <div
                        class="flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 shadow-2xl shadow-blue-500/30"
                    >
                        <CreditCard class="h-8 w-8 text-white" />
                    </div>
                    <div>
                        <h1
                            class="text-4xl font-bold tracking-tight text-slate-900 dark:text-white"
                        >
                            Admin Settings
                        </h1>
                        <p class="mt-1.5 text-sm text-slate-600 dark:text-slate-400">
                            Manage your subscription and billing
                        </p>
                    </div>
                </div>

                <!-- Subscription Section -->
                <div
                    class="space-y-4"
                    style="animation: slideInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) 0.1s backwards"
                >
                    <!-- Section Header -->
                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 shadow-lg shadow-blue-500/30"
                        >
                            <CreditCard class="h-5 w-5 text-white" />
                        </div>
                        <h2 class="text-2xl font-bold text-slate-900 dark:text-white">
                            Subscription Plan
                        </h2>
                    </div>

                    <!-- Subscription Card -->
                    <div
                        v-if="subscription"
                        class="group relative overflow-hidden rounded-3xl border border-white/60 bg-white/70 p-8 backdrop-blur-xl transition-all duration-500 hover:border-white/80 hover:bg-white/80 hover:shadow-2xl dark:border-white/10 dark:bg-slate-900/70 dark:hover:border-white/20 dark:hover:bg-slate-900/80"
                    >
                        <!-- Holographic gradient overlay -->
                        <div
                            class="pointer-events-none absolute inset-0 opacity-0 transition-opacity duration-500 group-hover:opacity-100"
                        >
                            <div
                                class="absolute inset-0 bg-gradient-to-br from-blue-500/5 via-indigo-500/5 to-violet-500/5 dark:from-blue-500/10 dark:via-indigo-500/10 dark:to-violet-500/10"
                            />
                        </div>

                        <div class="relative space-y-6">
                            <!-- Plan Header -->
                            <div class="flex items-start justify-between">
                                <div class="flex-1 space-y-2">
                                    <div class="flex items-center gap-3">
                                        <h3
                                            class="text-3xl font-bold text-slate-900 dark:text-white"
                                        >
                                            {{ subscription.plan_name }}
                                        </h3>
                                        <div
                                            :class="[
                                                'flex items-center gap-2 rounded-full px-4 py-1.5 border backdrop-blur-sm transition-all duration-300',
                                                getStatusConfig(subscription.status).bg,
                                                getStatusConfig(subscription.status).border,
                                            ]"
                                        >
                                            <component
                                                :is="getStatusConfig(subscription.status).icon"
                                                class="h-4 w-4"
                                                :class="
                                                    getStatusConfig(subscription.status).textColor
                                                "
                                            />
                                            <span
                                                class="text-sm font-semibold"
                                                :class="
                                                    getStatusConfig(subscription.status).textColor
                                                "
                                            >
                                                {{ getStatusConfig(subscription.status).text }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="flex items-baseline gap-2">
                                        <span
                                            class="bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-5xl font-bold text-transparent dark:from-blue-400 dark:to-indigo-400"
                                        >
                                            {{
                                                formatCurrency(
                                                    subscription.price,
                                                    subscription.currency,
                                                )
                                            }}
                                        </span>
                                        <span class="text-lg text-slate-600 dark:text-slate-400">
                                            / {{ subscription.interval }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Plan Details Grid -->
                            <div class="grid gap-6 md:grid-cols-3">
                                <!-- Start Date -->
                                <div
                                    class="group/item rounded-2xl border border-slate-200 bg-slate-50/50 p-4 backdrop-blur-sm transition-all duration-300 hover:border-blue-300 hover:bg-blue-50/50 dark:border-white/10 dark:bg-white/5 dark:hover:border-blue-500/30 dark:hover:bg-blue-500/10"
                                >
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 p-2.5 shadow-lg transition-transform duration-300 group-hover/item:scale-110"
                                        >
                                            <Calendar class="h-5 w-5 text-white" />
                                        </div>
                                        <div>
                                            <p
                                                class="text-xs font-medium text-slate-600 dark:text-slate-400"
                                            >
                                                Started
                                            </p>
                                            <p
                                                class="text-sm font-bold text-slate-900 dark:text-white"
                                            >
                                                {{ formatDate(subscription.starts_at) }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- End Date / Renewal -->
                                <div
                                    v-if="subscription.ends_at"
                                    class="group/item rounded-2xl border border-slate-200 bg-slate-50/50 p-4 backdrop-blur-sm transition-all duration-300 hover:border-indigo-300 hover:bg-indigo-50/50 dark:border-white/10 dark:bg-white/5 dark:hover:border-indigo-500/30 dark:hover:bg-indigo-500/10"
                                >
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="rounded-xl bg-gradient-to-br from-indigo-500 to-violet-600 p-2.5 shadow-lg transition-transform duration-300 group-hover/item:scale-110"
                                        >
                                            <Calendar class="h-5 w-5 text-white" />
                                        </div>
                                        <div>
                                            <p
                                                class="text-xs font-medium text-slate-600 dark:text-slate-400"
                                            >
                                                {{
                                                    subscription.status === 'cancelled'
                                                        ? 'Expires'
                                                        : 'Renews'
                                                }}
                                            </p>
                                            <p
                                                class="text-sm font-bold text-slate-900 dark:text-white"
                                            >
                                                {{ formatDate(subscription.ends_at) }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Days Remaining -->
                                <div
                                    v-if="
                                        subscription.days_remaining !== null &&
                                        subscription.days_remaining > 0
                                    "
                                    class="group/item rounded-2xl border border-slate-200 bg-slate-50/50 p-4 backdrop-blur-sm transition-all duration-300 hover:border-emerald-300 hover:bg-emerald-50/50 dark:border-white/10 dark:bg-white/5 dark:hover:border-emerald-500/30 dark:hover:bg-emerald-500/10"
                                >
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 p-2.5 shadow-lg transition-transform duration-300 group-hover/item:scale-110"
                                        >
                                            <Sparkles class="h-5 w-5 text-white" />
                                        </div>
                                        <div>
                                            <p
                                                class="text-xs font-medium text-slate-600 dark:text-slate-400"
                                            >
                                                Days Remaining
                                            </p>
                                            <p
                                                class="text-sm font-bold"
                                                :class="daysRemainingColor"
                                            >
                                                {{ subscription.days_remaining }} days
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex flex-wrap gap-3 pt-4">
                                <Button
                                    v-if="stripePortalUrl"
                                    @click="handleManageBilling"
                                    class="group/btn flex items-center gap-2 bg-gradient-to-r from-blue-500 to-indigo-600 text-white shadow-lg shadow-blue-500/30 transition-all duration-300 hover:shadow-xl hover:shadow-blue-500/40"
                                >
                                    <TrendingUp
                                        class="h-4 w-4 transition-transform duration-300 group-hover/btn:scale-110"
                                    />
                                    Manage Subscription
                                    <ExternalLink class="h-3.5 w-3.5 opacity-70" />
                                </Button>

                                <Button
                                    v-if="subscription.status === 'active'"
                                    @click="showCancelConfirm = true"
                                    variant="outline"
                                    class="border-red-200 text-red-700 hover:bg-red-50 dark:border-red-500/20 dark:text-red-400 dark:hover:bg-red-500/10"
                                >
                                    <XCircle class="mr-2 h-4 w-4" />
                                    Cancel Subscription
                                </Button>
                            </div>
                        </div>
                    </div>

                    <!-- No Subscription State -->
                    <div
                        v-else
                        class="rounded-3xl border border-white/60 bg-white/70 p-12 text-center backdrop-blur-xl dark:border-white/10 dark:bg-slate-900/70"
                    >
                        <div
                            class="mx-auto mb-4 flex h-20 w-20 items-center justify-center rounded-full bg-gradient-to-br from-slate-200 to-slate-300 dark:from-slate-700 dark:to-slate-800"
                        >
                            <CreditCard class="h-10 w-10 text-slate-500 dark:text-slate-400" />
                        </div>
                        <h3 class="mb-2 text-xl font-semibold text-slate-900 dark:text-white">
                            No Active Subscription
                        </h3>
                        <p class="text-sm text-slate-600 dark:text-slate-400">
                            You don't have an active subscription plan
                        </p>
                    </div>
                </div>

                <!-- Billing History Section -->
                <div
                    class="space-y-4"
                    style="animation: slideInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) 0.2s backwards"
                >
                    <!-- Section Header -->
                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-500 to-violet-600 shadow-lg shadow-indigo-500/30"
                        >
                            <Receipt class="h-5 w-5 text-white" />
                        </div>
                        <h2 class="text-2xl font-bold text-slate-900 dark:text-white">
                            Billing History
                        </h2>
                    </div>

                    <!-- Billing Table -->
                    <div
                        v-if="billingHistory.length > 0"
                        class="overflow-hidden rounded-3xl border border-white/60 bg-white/70 backdrop-blur-xl dark:border-white/10 dark:bg-slate-900/70"
                    >
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead
                                    class="border-b border-slate-200 bg-slate-50/50 dark:border-white/10 dark:bg-white/5"
                                >
                                    <tr>
                                        <th
                                            class="px-6 py-4 text-left text-sm font-semibold text-slate-900 dark:text-white"
                                        >
                                            Date
                                        </th>
                                        <th
                                            class="px-6 py-4 text-left text-sm font-semibold text-slate-900 dark:text-white"
                                        >
                                            Plan
                                        </th>
                                        <th
                                            class="px-6 py-4 text-left text-sm font-semibold text-slate-900 dark:text-white"
                                        >
                                            Amount
                                        </th>
                                        <th
                                            class="px-6 py-4 text-left text-sm font-semibold text-slate-900 dark:text-white"
                                        >
                                            Status
                                        </th>
                                        <th
                                            class="px-6 py-4 text-right text-sm font-semibold text-slate-900 dark:text-white"
                                        >
                                            Invoice
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200 dark:divide-white/10">
                                    <tr
                                        v-for="(item, index) in billingHistory"
                                        :key="item.id"
                                        class="group/row transition-colors duration-200 hover:bg-slate-50/50 dark:hover:bg-white/5"
                                        :style="`animation: fadeIn 0.4s cubic-bezier(0.16, 1, 0.3, 1) ${0.3 + index * 0.05}s backwards`"
                                    >
                                        <td
                                            class="px-6 py-4 text-sm text-slate-700 dark:text-slate-300"
                                        >
                                            {{ formatDate(item.date) }}
                                        </td>
                                        <td
                                            class="px-6 py-4 text-sm font-medium text-slate-900 dark:text-white"
                                        >
                                            {{ item.plan_name }}
                                        </td>
                                        <td
                                            class="px-6 py-4 text-sm font-semibold text-slate-900 dark:text-white"
                                        >
                                            {{ formatCurrency(item.amount, item.currency) }}
                                        </td>
                                        <td class="px-6 py-4">
                                            <div
                                                :class="[
                                                    'inline-flex items-center gap-1.5 rounded-full px-3 py-1 border',
                                                    getStatusConfig(item.status).bg,
                                                    getStatusConfig(item.status).border,
                                                ]"
                                            >
                                                <component
                                                    :is="getStatusConfig(item.status).icon"
                                                    class="h-3.5 w-3.5"
                                                    :class="getStatusConfig(item.status).textColor"
                                                />
                                                <span
                                                    class="text-xs font-semibold capitalize"
                                                    :class="getStatusConfig(item.status).textColor"
                                                >
                                                    {{ item.status }}
                                                </span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <a
                                                v-if="item.invoice_url"
                                                :href="item.invoice_url"
                                                target="_blank"
                                                class="inline-flex items-center gap-2 rounded-lg px-3 py-1.5 text-sm font-medium text-blue-600 transition-all duration-200 hover:bg-blue-50 dark:text-blue-400 dark:hover:bg-blue-500/10"
                                            >
                                                <Download class="h-4 w-4" />
                                                Download
                                            </a>
                                            <span
                                                v-else
                                                class="text-sm text-slate-400 dark:text-slate-600"
                                            >
                                                N/A
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div
                        v-else
                        class="rounded-3xl border border-white/60 bg-white/70 p-12 text-center backdrop-blur-xl dark:border-white/10 dark:bg-slate-900/70"
                    >
                        <div
                            class="mx-auto mb-4 flex h-20 w-20 items-center justify-center rounded-full bg-gradient-to-br from-slate-200 to-slate-300 dark:from-slate-700 dark:to-slate-800"
                        >
                            <Receipt
                                class="h-10 w-10 text-slate-500 dark:text-slate-400"
                            />
                        </div>
                        <h3 class="mb-2 text-xl font-semibold text-slate-900 dark:text-white">
                            No Billing History
                        </h3>
                        <p class="text-sm text-slate-600 dark:text-slate-400">
                            Your billing history will appear here
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Cancel Confirmation Modal -->
    <Transition name="modal">
        <div
            v-if="showCancelConfirm"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-6 backdrop-blur-sm"
            @click.self="showCancelConfirm = false"
        >
            <div
                class="w-full max-w-md overflow-hidden rounded-3xl border border-white/40 bg-white/90 shadow-2xl backdrop-blur-2xl dark:border-white/20 dark:bg-slate-900/90"
                style="animation: scaleIn 0.3s cubic-bezier(0.34, 1.56, 0.64, 1)"
            >
                <!-- Animated gradient overlay -->
                <div class="pointer-events-none absolute inset-0 overflow-hidden opacity-30">
                    <div
                        class="absolute -top-20 -right-20 h-40 w-40 animate-pulse rounded-full bg-gradient-to-br from-red-500 to-rose-500 blur-3xl"
                        style="animation-duration: 4s"
                    />
                </div>

                <div class="relative p-8">
                    <!-- Icon -->
                    <div class="mb-6 flex justify-center">
                        <div
                            class="flex h-20 w-20 items-center justify-center rounded-3xl bg-gradient-to-br from-red-500 to-rose-600 shadow-2xl shadow-red-500/30"
                        >
                            <AlertTriangle class="h-10 w-10 text-white" />
                        </div>
                    </div>

                    <!-- Content -->
                    <h2 class="mb-3 text-center text-2xl font-bold text-slate-900 dark:text-white">
                        Cancel Subscription?
                    </h2>
                    <p class="mb-6 text-center text-sm text-slate-600 dark:text-slate-400">
                        Are you sure you want to cancel your subscription? You will retain access
                        until the end of your billing period.
                    </p>

                    <!-- Actions -->
                    <div class="flex gap-3">
                        <Button
                            @click="showCancelConfirm = false"
                            :disabled="cancelProcessing"
                            variant="outline"
                            class="flex-1"
                        >
                            Keep Subscription
                        </Button>
                        <Button
                            @click="handleCancelSubscription"
                            :disabled="cancelProcessing"
                            class="flex-1 bg-gradient-to-r from-red-500 to-rose-600 text-white shadow-lg shadow-red-500/30 hover:shadow-xl hover:shadow-red-500/40"
                        >
                            {{ cancelProcessing ? 'Cancelling...' : 'Cancel Subscription' }}
                        </Button>
                    </div>
                </div>
            </div>
        </div>
    </Transition>
</template>

<style scoped>
@keyframes slideInDown {
    from {
        opacity: 0;
        transform: translateY(-30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes slideInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes fadeIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}

@keyframes scaleIn {
    from {
        opacity: 0;
        transform: scale(0.9);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}

/* Modal transitions */
.modal-enter-active {
    transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.modal-leave-active {
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

.modal-enter-from {
    opacity: 0;
}

.modal-leave-to {
    opacity: 0;
}

.modal-enter-from > div {
    transform: scale(0.9);
}

.modal-leave-to > div {
    transform: scale(0.95);
}
</style>
