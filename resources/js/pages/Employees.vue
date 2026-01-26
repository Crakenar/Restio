<script setup lang="ts">
import PremiumSidebar from '@/components/PremiumSidebar.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
const page = usePage();
import { Users, AlertTriangle, ArrowRight, Crown } from 'lucide-vue-next';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import EmployeeForm from '@/components/EmployeeForm.vue';
import CsvUpload from '@/components/CsvUpload.vue';
import EmployeeList from '@/components/EmployeeList.vue';
import { ref, computed } from 'vue';
import { index as subscriptionIndexRoute } from '@/routes/subscription';
import { employees as employeesRoute } from '@/routes';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

interface Employee {
    id: number;
    name: string;
    email: string;
    role: string;
    created_at: string;
}

interface SubscriptionInfo {
    current_user_count: number;
    user_limit: number;
    remaining_slots: number;
    is_near_limit: boolean;
    has_reached_limit: boolean;
    can_add_users: boolean;
}

interface Props {
    employees: {
        data: Employee[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    subscription_info: SubscriptionInfo;
}

const props = defineProps<Props>();

// Extract employee data from pagination
const employees = computed(() => props.employees.data);

const activeTab = ref('list');

// Calculate usage percentage
const usagePercentage = computed(() => {
    return Math.round((props.subscription_info.current_user_count / props.subscription_info.user_limit) * 100);
});

// Determine progress bar color based on usage
const progressBarColor = computed(() => {
    const percentage = usagePercentage.value;
    if (percentage >= 100) return 'bg-red-500';
    if (percentage >= 80) return 'bg-amber-500';
    return 'bg-gradient-to-r from-orange-500 to-rose-500';
});
</script>

<template>
    <Head :title="t('employees.title')" />

    <div class="flex min-h-screen bg-gradient-to-br from-slate-50 via-orange-50 to-rose-50 dark:from-slate-950 dark:via-orange-950 dark:to-rose-950">
        <!-- Sidebar -->
        <PremiumSidebar :notifications="$page.props.notifications || []" />

        <!-- Main content area -->
        <div class="ml-72 flex-1 p-4 transition-all duration-500 sm:p-6 lg:p-8">
            <!-- Animated gradient orbs -->
            <div class="pointer-events-none fixed inset-0 overflow-hidden">
                <div
                    class="absolute -top-1/2 -right-1/2 h-full w-full animate-pulse rounded-full bg-gradient-to-br from-orange-500/10 via-amber-500/10 to-yellow-500/10 dark:from-orange-500/20 dark:via-amber-500/20 dark:to-yellow-500/20 blur-3xl"
                    style="animation-duration: 8s"
                />
                <div
                    class="absolute -bottom-1/2 -left-1/2 h-full w-full animate-pulse rounded-full bg-gradient-to-tr from-rose-500/10 via-pink-500/10 to-red-500/10 dark:from-rose-500/20 dark:via-pink-500/20 dark:to-red-500/20 blur-3xl"
                    style="animation-duration: 10s; animation-delay: 1s"
                />
            </div>

            <!-- Content -->
            <div class="relative mx-auto max-w-7xl space-y-6">
            <!-- Enhanced Header -->
            <div class="flex items-center gap-4">
                <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-orange-400 to-rose-500 shadow-2xl shadow-orange-500/30">
                    <Users class="h-8 w-8 text-white" />
                </div>
                <div>
                    <h1 class="text-4xl font-bold tracking-tight text-slate-900 dark:text-white">
                        {{ t('employees.title') }}
                    </h1>
                    <p class="mt-1.5 text-sm text-slate-600 dark:text-white/70">
                        {{ t('employees.subtitle') }}
                    </p>
                </div>
            </div>

            <!-- User Limit Banner -->
            <div
                v-if="subscription_info.is_near_limit || subscription_info.has_reached_limit"
                class="rounded-2xl border p-6 backdrop-blur-xl transition-all duration-300"
                :class="
                    subscription_info.has_reached_limit
                        ? 'border-red-500/30 bg-red-50/80 dark:bg-red-900/20'
                        : 'border-amber-500/30 bg-amber-50/80 dark:bg-amber-900/20'
                "
            >
                <div class="flex items-start gap-4">
                    <div
                        class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-xl shadow-lg"
                        :class="
                            subscription_info.has_reached_limit
                                ? 'bg-red-500 text-white'
                                : 'bg-amber-500 text-white'
                        "
                    >
                        <AlertTriangle class="h-6 w-6" />
                    </div>

                    <div class="flex-1 space-y-3">
                        <div>
                            <h3
                                class="text-lg font-bold"
                                :class="
                                    subscription_info.has_reached_limit
                                        ? 'text-red-900 dark:text-red-200'
                                        : 'text-amber-900 dark:text-amber-200'
                                "
                            >
                                {{
                                    subscription_info.has_reached_limit
                                        ? 'User Limit Reached'
                                        : 'Approaching User Limit'
                                }}
                            </h3>
                            <p
                                class="mt-1 text-sm"
                                :class="
                                    subscription_info.has_reached_limit
                                        ? 'text-red-700 dark:text-red-300'
                                        : 'text-amber-700 dark:text-amber-300'
                                "
                            >
                                {{ subscription_info.current_user_count }} of {{ subscription_info.user_limit }} users
                                •
                                <span v-if="subscription_info.remaining_slots > 0">
                                    {{ subscription_info.remaining_slots }}
                                    {{ subscription_info.remaining_slots === 1 ? 'slot' : 'slots' }} remaining
                                </span>
                                <span v-else class="font-semibold">
                                    No slots remaining
                                </span>
                            </p>
                        </div>

                        <!-- Progress Bar -->
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between text-xs font-medium">
                                <span
                                    :class="
                                        subscription_info.has_reached_limit
                                            ? 'text-red-700 dark:text-red-300'
                                            : 'text-amber-700 dark:text-amber-300'
                                    "
                                >
                                    Usage
                                </span>
                                <span
                                    class="font-bold"
                                    :class="
                                        subscription_info.has_reached_limit
                                            ? 'text-red-900 dark:text-red-200'
                                            : 'text-amber-900 dark:text-amber-200'
                                    "
                                >
                                    {{ usagePercentage }}%
                                </span>
                            </div>
                            <div class="h-3 overflow-hidden rounded-full bg-white/50 dark:bg-slate-900/50">
                                <div
                                    class="h-full transition-all duration-500 ease-out"
                                    :class="progressBarColor"
                                    :style="{ width: usagePercentage + '%' }"
                                />
                            </div>
                        </div>

                        <!-- Warning Message -->
                        <p
                            class="text-sm font-medium"
                            :class="
                                subscription_info.has_reached_limit
                                    ? 'text-red-800 dark:text-red-200'
                                    : 'text-amber-800 dark:text-amber-200'
                            "
                        >
                            <span v-if="subscription_info.has_reached_limit">
                                You cannot add more employees until you upgrade your subscription plan.
                            </span>
                            <span v-else>
                                You're running low on user slots. Consider upgrading soon to continue growing your team.
                            </span>
                        </p>

                        <!-- Upgrade CTA -->
                        <a
                            :href="subscriptionIndexRoute.url()"
                            class="group inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold text-white shadow-lg transition-all duration-200 hover:shadow-xl"
                            :class="
                                subscription_info.has_reached_limit
                                    ? 'bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800'
                                    : 'bg-gradient-to-r from-amber-600 to-amber-700 hover:from-amber-700 hover:to-amber-800'
                            "
                        >
                            <Crown class="h-4 w-4" />
                            Upgrade Subscription
                            <ArrowRight class="h-4 w-4 transition-transform group-hover:translate-x-1" />
                        </a>
                    </div>
                </div>
            </div>

            <!-- User Count Summary (shown when not near limit) -->
            <div
                v-else
                class="rounded-2xl border border-white/40 bg-white/70 p-4 shadow-lg backdrop-blur-xl dark:border-white/20 dark:bg-slate-900/40"
            >
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-gradient-to-br from-orange-400 to-rose-500 text-white shadow-md">
                            <Users class="h-5 w-5" />
                        </div>
                        <div>
                            <p class="text-sm font-medium text-slate-600 dark:text-slate-400">
                                Team Size
                            </p>
                            <p class="text-lg font-bold text-slate-900 dark:text-white">
                                {{ subscription_info.current_user_count }} / {{ subscription_info.user_limit }} users
                            </p>
                        </div>
                    </div>

                    <!-- Mini Progress Bar -->
                    <div class="flex items-center gap-4">
                        <div class="w-32 space-y-1">
                            <div class="flex items-center justify-between text-xs font-medium text-slate-600 dark:text-slate-400">
                                <span>Usage</span>
                                <span class="font-bold text-slate-900 dark:text-white">{{ usagePercentage }}%</span>
                            </div>
                            <div class="h-2 overflow-hidden rounded-full bg-slate-200 dark:bg-slate-700">
                                <div
                                    class="h-full bg-gradient-to-r from-orange-500 to-rose-500 transition-all duration-500"
                                    :style="{ width: usagePercentage + '%' }"
                                />
                            </div>
                        </div>

                        <a
                            v-if="usagePercentage >= 60"
                            :href="subscriptionIndexRoute.url()"
                            class="group inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-orange-500 to-rose-500 px-3 py-1.5 text-xs font-semibold text-white shadow-md transition-all hover:shadow-lg"
                        >
                            <Crown class="h-3.5 w-3.5" />
                            Upgrade
                            <ArrowRight class="h-3.5 w-3.5 transition-transform group-hover:translate-x-0.5" />
                        </a>
                    </div>
                </div>
            </div>

            <!-- Tabs for different actions -->
            <Tabs v-model="activeTab" class="flex flex-1 flex-col space-y-6">
                <!-- Enhanced Tab List -->
                <div class="relative">
                    <!-- Gradient background glow -->
                    <div class="pointer-events-none absolute inset-0 -z-10 rounded-3xl bg-gradient-to-r from-orange-500/20 via-amber-500/20 to-rose-500/20 blur-2xl" />

                    <TabsList class="relative inline-flex gap-2 rounded-2xl border border-white/40 bg-white/80 p-2 shadow-2xl backdrop-blur-2xl dark:border-white/20 dark:bg-slate-900/60">
                        <TabsTrigger
                            value="list"
                            class="group relative overflow-hidden rounded-xl px-6 py-3 font-semibold text-slate-600 transition-all duration-300 hover:text-slate-900 data-[state=active]:text-white dark:text-slate-300 dark:hover:text-white dark:data-[state=active]:text-white"
                        >
                            <!-- Active gradient background -->
                            <div class="absolute inset-0 bg-gradient-to-r from-orange-500 to-rose-500 opacity-0 shadow-lg transition-all duration-300 group-data-[state=active]:opacity-100 group-data-[state=active]:shadow-orange-500/50" />

                            <!-- Content -->
                            <span class="relative z-10 flex items-center gap-2">
                                <Users class="h-4 w-4" />
                                All Employees
                                <span class="ml-1 flex h-6 min-w-[24px] items-center justify-center rounded-full bg-slate-200 px-2 text-xs font-bold text-slate-700 transition-all group-data-[state=active]:bg-white/20 group-data-[state=active]:text-white dark:bg-slate-700 dark:text-slate-300 dark:group-data-[state=active]:bg-white/20">
                                    {{ employees.length }}
                                </span>
                            </span>
                        </TabsTrigger>

                        <TabsTrigger
                            value="add"
                            class="group relative overflow-hidden rounded-xl px-6 py-3 font-semibold text-slate-600 transition-all duration-300 hover:text-orange-700 data-[state=active]:text-white dark:text-slate-300 dark:hover:text-orange-400 dark:data-[state=active]:text-white"
                        >
                            <!-- Active gradient background -->
                            <div class="absolute inset-0 bg-gradient-to-r from-amber-500 to-orange-600 opacity-0 shadow-lg transition-all duration-300 group-data-[state=active]:opacity-100 group-data-[state=active]:shadow-amber-500/50" />

                            <!-- Content -->
                            <span class="relative z-10">Add Employee</span>
                        </TabsTrigger>

                        <TabsTrigger
                            value="import"
                            class="group relative overflow-hidden rounded-xl px-6 py-3 font-semibold text-slate-600 transition-all duration-300 hover:text-rose-700 data-[state=active]:text-white dark:text-slate-300 dark:hover:text-rose-400 dark:data-[state=active]:text-white"
                        >
                            <!-- Active gradient background -->
                            <div class="absolute inset-0 bg-gradient-to-r from-rose-500 to-pink-600 opacity-0 shadow-lg transition-all duration-300 group-data-[state=active]:opacity-100 group-data-[state=active]:shadow-rose-500/50" />

                            <!-- Content -->
                            <span class="relative z-10">Import CSV</span>
                        </TabsTrigger>
                    </TabsList>
                </div>

                <!-- Employee List Tab -->
                <TabsContent
                    value="list"
                    class="relative flex-1 space-y-4 overflow-hidden rounded-3xl border border-white/40 bg-white/70 shadow-2xl backdrop-blur-2xl dark:border-white/20 dark:bg-slate-900/40"
                >
                    <!-- Animated gradient overlay -->
                    <div class="pointer-events-none absolute inset-0 overflow-hidden opacity-30">
                        <div
                            class="absolute -top-1/4 -right-1/4 h-1/2 w-1/2 animate-pulse rounded-full bg-gradient-to-br from-orange-500/20 via-amber-500/20 to-rose-500/20 blur-3xl"
                            style="animation-duration: 8s"
                        />
                    </div>

                    <div class="relative p-8">
                        <EmployeeList :employees="employees" />
                    </div>

                    <!-- Pagination -->
                    <div
                        v-if="props.employees.last_page > 1"
                        class="relative flex items-center justify-between border-t border-white/20 px-8 py-4"
                    >
                        <p class="text-sm font-medium text-slate-600 dark:text-slate-300">
                            Page {{ props.employees.current_page }} of {{ props.employees.last_page }}
                            <span class="ml-2 text-slate-500 dark:text-slate-400">({{ props.employees.total }} total employees)</span>
                        </p>
                        <div class="flex gap-2">
                            <Link
                                v-if="props.employees.current_page > 1"
                                :href="employeesRoute.url({ query: { page: props.employees.current_page - 1 } })"
                                class="rounded-xl bg-gradient-to-r from-slate-600 to-slate-700 px-4 py-2 text-sm font-semibold text-white transition-all hover:from-slate-700 hover:to-slate-800 hover:shadow-lg"
                            >
                                Previous
                            </Link>
                            <Link
                                v-if="props.employees.current_page < props.employees.last_page"
                                :href="employeesRoute.url({ query: { page: props.employees.current_page + 1 } })"
                                class="rounded-xl bg-gradient-to-r from-orange-500 to-rose-500 px-4 py-2 text-sm font-semibold text-white transition-all hover:from-orange-600 hover:to-rose-600 hover:shadow-lg"
                            >
                                Next
                            </Link>
                        </div>
                    </div>
                </TabsContent>

                <!-- Add Single Employee Tab -->
                <TabsContent
                    value="add"
                    class="relative flex-1 overflow-hidden rounded-3xl border border-white/40 bg-white/70 shadow-2xl backdrop-blur-2xl dark:border-white/20 dark:bg-slate-900/40"
                >
                    <!-- Animated gradient overlay -->
                    <div class="pointer-events-none absolute inset-0 overflow-hidden opacity-30">
                        <div
                            class="absolute -top-1/4 -right-1/4 h-1/2 w-1/2 animate-pulse rounded-full bg-gradient-to-br from-amber-500/20 via-orange-500/20 to-yellow-500/20 blur-3xl"
                            style="animation-duration: 8s"
                        />
                    </div>

                    <div class="relative p-8">
                        <EmployeeForm @success="activeTab = 'list'" />
                    </div>
                </TabsContent>

                <!-- Import CSV Tab -->
                <TabsContent
                    value="import"
                    class="relative flex-1 overflow-hidden rounded-3xl border border-white/40 bg-white/70 shadow-2xl backdrop-blur-2xl dark:border-white/20 dark:bg-slate-900/40"
                >
                    <!-- Animated gradient overlay -->
                    <div class="pointer-events-none absolute inset-0 overflow-hidden opacity-30">
                        <div
                            class="absolute -top-1/4 -right-1/4 h-1/2 w-1/2 animate-pulse rounded-full bg-gradient-to-br from-rose-500/20 via-pink-500/20 to-red-500/20 blur-3xl"
                            style="animation-duration: 8s"
                        />
                    </div>

                    <div class="relative p-8">
                        <CsvUpload @success="activeTab = 'list'" />
                    </div>
                </TabsContent>
            </Tabs>
            </div>
        </div>
    </div>
</template>
