<script setup lang="ts">
import { computed, ref } from 'vue';
import type { VacationRequest } from '@/types/vacation';
import {
    CalendarDays,
    Clock,
    Coffee,
    Home,
    Plane,
    TrendingUp,
    Bell,
    Calendar,
    Wrench,
    Newspaper,
    ChevronDown,
    FileText,
    Download,
    Sparkles,
    Plus,
} from 'lucide-vue-next';
import { Card, CardContent } from '@/components/ui/card';
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Link, router, usePage } from '@inertiajs/vue3';
import PremiumSidebar from '@/components/PremiumSidebar.vue';
import VacationRequestForm from '@/components/VacationRequestForm.vue';
import { useI18n } from 'vue-i18n';

const page = usePage();
const { t } = useI18n();

interface Props {
    requests: VacationRequest[];
    totalDaysAllowed: number;
    userName?: string;
    notificationCount?: number;
}

const props = withDefaults(defineProps<Props>(), {
    userName: 'there',
    notificationCount: 0,
});

// Dialog state
const isRequestDialogOpen = ref(false);

const handleRequestSuccess = () => {
    isRequestDialogOpen.value = false;
    // Reload page to show updated data
    router.reload();
};

const handleRequestCancel = () => {
    isRequestDialogOpen.value = false;
};

// Vacation stats computations
const myRequests = computed(() =>
    props.requests.filter((r) => r.employeeName === props.userName),
);

const approvedRequests = computed(() =>
    myRequests.value.filter((r) => r.status === 'approved'),
);

const pendingRequests = computed(() =>
    myRequests.value.filter((r) => r.status === 'pending'),
);

const upcomingRequests = computed(() =>
    approvedRequests.value
        .filter((r) => r.startDate > new Date())
        .sort((a, b) => a.startDate.getTime() - b.startDate.getTime())
        .slice(0, 3),
);

const totalDaysUsed = computed(() => {
    return approvedRequests.value.reduce((acc, req) => {
        const days =
            Math.ceil(
                (req.endDate.getTime() - req.startDate.getTime()) /
                    (1000 * 60 * 60 * 24),
            ) + 1;
        return acc + days;
    }, 0);
});

const daysRemaining = computed(
    () => props.totalDaysAllowed - totalDaysUsed.value,
);

const usagePercentage = computed(() =>
    Math.round((totalDaysUsed.value / props.totalDaysAllowed) * 100),
);

// Feature cards data
const featureCards = computed(() => [
    {
        id: 'plannings',
        title: t('dashboard.employee.plannings'),
        icon: Calendar,
        gradient: 'from-emerald-500 to-teal-500',
        links: [
            { label: t('dashboard.employee.vacationCalendar'), href: '/calendar' },
            { label: t('dashboard.employee.colleaguesSchedule'), href: '/teams' },
        ],
    },
    {
        id: 'absences',
        title: t('dashboard.employee.absences'),
        icon: Clock,
        gradient: 'from-blue-500 to-indigo-500',
        links: [
            { label: t('dashboard.employee.absenceRequest'), href: '/calendar' },
            { label: t('dashboard.employee.requestHistory'), href: '/requests' },
        ],
    },
    {
        id: 'outils',
        title: t('dashboard.employee.tools'),
        icon: Wrench,
        gradient: 'from-pink-500 to-rose-500',
        links: [
            { label: t('dashboard.employee.attendanceBoard'), href: '/teams' },
            { label: t('dashboard.employee.documentManagement'), href: '/documents' },
        ],
    },
    {
        id: 'actualites',
        title: t('dashboard.employee.news'),
        icon: Newspaper,
        gradient: 'from-violet-500 to-purple-500',
        isNews: true,
    },
]);

const getTypeIcon = (type: string) => {
    switch (type) {
        case 'vacation':
            return Plane;
        case 'sick':
            return Coffee;
        case 'wfh':
            return Home;
        default:
            return CalendarDays;
    }
};

const formatDate = (date: Date) => {
    return new Intl.DateTimeFormat('en-US', {
        month: 'short',
        day: 'numeric',
    }).format(date);
};

const formatDateRange = (start: Date, end: Date) => {
    if (start.toDateString() === end.toDateString()) {
        return formatDate(start);
    }
    return `${formatDate(start)} - ${formatDate(end)}`;
};
</script>

<template>
    <div class="flex min-h-screen bg-gradient-to-br from-slate-50 via-orange-50 to-rose-50 dark:from-slate-950 dark:via-orange-950 dark:to-rose-950">
        <!-- Sidebar -->
        <PremiumSidebar :notifications="page.props.notifications || []" />

        <!-- Main content area -->
        <div class="ml-72 flex-1 p-4 transition-all duration-500 sm:p-6 lg:p-8">
            <!-- Background decorative elements -->
            <div class="pointer-events-none fixed inset-0 overflow-hidden">
                <div
                    class="absolute -top-1/4 -right-1/4 h-[600px] w-[600px] animate-pulse rounded-full bg-gradient-to-br from-orange-500/10 via-amber-500/10 to-yellow-500/10 blur-3xl dark:from-orange-500/20 dark:via-amber-500/15 dark:to-yellow-500/10"
                    style="animation-duration: 15s"
                />
                <div
                    class="absolute -bottom-1/4 -left-1/4 h-[600px] w-[600px] animate-pulse rounded-full bg-gradient-to-tr from-rose-500/10 via-pink-500/10 to-red-500/10 blur-3xl dark:from-rose-500/20 dark:via-pink-500/15 dark:to-red-500/10"
                    style="animation-duration: 20s; animation-delay: 2s"
                />
            </div>

            <!-- Main content -->
            <div class="relative mx-auto max-w-7xl space-y-6">
            <!-- Top Section: Welcome + Notifications -->
            <div class="grid gap-6 lg:grid-cols-3">
                <!-- Welcome Card -->
                <div
                    data-tour="welcome"
                    class="group relative overflow-hidden rounded-3xl border border-white/60 bg-white/70 p-8 shadow-xl shadow-orange-500/5 backdrop-blur-xl transition-all duration-500 hover:scale-[1.01] hover:border-white/80 hover:bg-white/80 hover:shadow-2xl hover:shadow-orange-500/10 dark:border-white/10 dark:bg-slate-900/70 dark:hover:border-white/20 dark:hover:bg-slate-900/80 lg:col-span-2"
                    style="animation: slideInLeft 0.8s cubic-bezier(0.16, 1, 0.3, 1)"
                >
                    <div
                        class="absolute top-0 right-0 h-full w-1/2 bg-gradient-to-l from-orange-500/5 via-transparent to-transparent opacity-0 transition-opacity duration-500 group-hover:opacity-100 dark:from-orange-400/10"
                    />

                    <div class="relative flex items-center gap-4">
                        <div
                            class="flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-orange-500 to-rose-500 shadow-lg transition-transform duration-500 group-hover:scale-110 group-hover:rotate-3"
                        >
                            <Sparkles class="h-8 w-8 text-white" />
                        </div>
                        <div class="flex-1">
                            <h1
                                class="mb-1 bg-gradient-to-r from-slate-800 to-slate-600 bg-clip-text text-3xl font-bold tracking-tight text-transparent dark:from-slate-100 dark:to-slate-300"
                            >
                                {{ t('dashboard.welcomeMessage', { name: userName }) }}
                            </h1>
                            <p class="text-slate-600 dark:text-slate-300">
                                {{ t('dashboard.subtitle') }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Notifications Card -->
                <div
                    class="group relative overflow-hidden rounded-3xl border border-white/60 bg-white/70 p-8 shadow-xl shadow-rose-500/5 backdrop-blur-xl transition-all duration-500 hover:scale-[1.01] hover:border-white/80 hover:bg-white/80 hover:shadow-2xl hover:shadow-rose-500/10 dark:border-white/10 dark:bg-slate-900/70 dark:hover:border-white/20 dark:hover:bg-slate-900/80"
                    style="
                        animation: slideInRight 0.8s cubic-bezier(0.16, 1, 0.3, 1);
                        animation-delay: 0.1s;
                        animation-fill-mode: both;
                    "
                >
                    <div
                        class="absolute top-0 left-0 h-full w-1/2 bg-gradient-to-r from-rose-500/5 via-transparent to-transparent opacity-0 transition-opacity duration-500 group-hover:opacity-100 dark:from-rose-400/10"
                    />

                    <div class="relative">
                        <div class="mb-3 flex items-center justify-between">
                            <h2 class="text-xl font-bold text-slate-800 dark:text-slate-100">
                                {{ t('nav.notifications', 'Notifications') }}
                            </h2>
                            <div class="relative">
                                <div
                                    v-if="notificationCount > 0"
                                    class="absolute -top-1 -right-1 flex h-5 w-5 items-center justify-center rounded-full bg-gradient-to-br from-orange-500 to-rose-500 text-xs font-bold text-white shadow-lg animate-pulse"
                                >
                                    {{ notificationCount }}
                                </div>
                                <div
                                    class="rounded-full bg-gradient-to-br from-orange-500 to-rose-500 p-2.5 shadow-lg"
                                >
                                    <Bell class="h-5 w-5 text-white" />
                                </div>
                            </div>
                        </div>
                        <p class="text-sm text-slate-600 dark:text-slate-300">
                            <span
                                class="font-bold bg-gradient-to-r from-orange-600 to-rose-600 bg-clip-text text-transparent dark:from-orange-400 dark:to-rose-400"
                            >
                                {{ notificationCount }}
                            </span>
                            {{ t('common.notifications', notificationCount) }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Stats Cards -->
            <div
                data-tour="stats"
                class="grid gap-4 md:grid-cols-3"
                style="
                    animation: slideInUp 0.8s cubic-bezier(0.16, 1, 0.3, 1);
                    animation-delay: 0.2s;
                    animation-fill-mode: both;
                "
            >
                <!-- Days Remaining -->
                <Card
                    class="group relative overflow-hidden border border-white/60 bg-white/70 backdrop-blur-xl transition-all duration-500 hover:border-white/80 hover:bg-white/80 hover:shadow-2xl hover:shadow-emerald-500/10 dark:border-white/10 dark:bg-slate-900/70 dark:hover:border-white/20 dark:hover:bg-slate-900/80"
                >
                    <div class="absolute inset-0 bg-gradient-to-br from-emerald-500/5 to-teal-500/5 opacity-0 transition-opacity duration-500 group-hover:opacity-100" />
                    <CardContent class="relative p-6">
                        <div class="mb-3 flex items-center gap-3">
                            <div
                                class="rounded-xl bg-gradient-to-br from-emerald-500 to-teal-500 p-2.5 shadow-lg transition-transform duration-500 group-hover:scale-110 group-hover:rotate-6"
                            >
                                <CalendarDays class="h-5 w-5 text-white" />
                            </div>
                            <p class="text-sm font-semibold text-slate-600 dark:text-slate-300">
                                {{ t('dashboard.stats.remaining') }}
                            </p>
                        </div>
                        <p class="mb-1 text-5xl font-bold text-slate-900 dark:text-white">
                            {{ daysRemaining }}
                        </p>
                        <p class="mb-3 text-xs text-slate-500 dark:text-slate-400">
                            {{ t('dashboard.recentRequests.days', { count: totalDaysAllowed }) }}
                        </p>
                        <!-- Progress bar -->
                        <div class="h-2.5 overflow-hidden rounded-full bg-slate-200 dark:bg-slate-800">
                            <div
                                class="h-full rounded-full bg-gradient-to-r from-emerald-500 to-teal-500 shadow-lg transition-all duration-1000 ease-out"
                                :style="{ width: `${usagePercentage}%` }"
                            />
                        </div>
                    </CardContent>
                </Card>

                <!-- Days Used -->
                <Card
                    class="group relative overflow-hidden border border-white/60 bg-white/70 backdrop-blur-xl transition-all duration-500 hover:border-white/80 hover:bg-white/80 hover:shadow-2xl hover:shadow-blue-500/10 dark:border-white/10 dark:bg-slate-900/70 dark:hover:border-white/20 dark:hover:bg-slate-900/80"
                >
                    <div class="absolute inset-0 bg-gradient-to-br from-blue-500/5 to-indigo-500/5 opacity-0 transition-opacity duration-500 group-hover:opacity-100" />
                    <CardContent class="relative p-6">
                        <div class="mb-3 flex items-center gap-3">
                            <div
                                class="rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 p-2.5 shadow-lg transition-transform duration-500 group-hover:scale-110 group-hover:rotate-6"
                            >
                                <TrendingUp class="h-5 w-5 text-white" />
                            </div>
                            <p class="text-sm font-semibold text-slate-600 dark:text-slate-300">
                                {{ t('dashboard.stats.used') }}
                            </p>
                        </div>
                        <p class="mb-1 text-5xl font-bold text-slate-900 dark:text-white">
                            {{ totalDaysUsed }}
                        </p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            {{ usagePercentage }}% {{ t('dashboard.stats.used').toLowerCase() }}
                        </p>
                    </CardContent>
                </Card>

                <!-- Pending Approval -->
                <Card
                    class="group relative overflow-hidden border border-white/60 bg-white/70 backdrop-blur-xl transition-all duration-500 hover:border-white/80 hover:bg-white/80 hover:shadow-2xl hover:shadow-amber-500/10 dark:border-white/10 dark:bg-slate-900/70 dark:hover:border-white/20 dark:hover:bg-slate-900/80"
                >
                    <div class="absolute inset-0 bg-gradient-to-br from-amber-500/5 to-orange-500/5 opacity-0 transition-opacity duration-500 group-hover:opacity-100" />
                    <CardContent class="relative p-6">
                        <div class="mb-3 flex items-center gap-3">
                            <div
                                class="rounded-xl bg-gradient-to-br from-amber-500 to-orange-500 p-2.5 shadow-lg transition-transform duration-500 group-hover:scale-110 group-hover:rotate-6"
                            >
                                <Clock class="h-5 w-5 text-white" />
                            </div>
                            <p class="text-sm font-semibold text-slate-600 dark:text-slate-300">
                                {{ t('dashboard.stats.pending') }}
                            </p>
                        </div>
                        <p class="mb-1 text-5xl font-bold text-slate-900 dark:text-white">
                            {{ pendingRequests.length }}
                        </p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            {{ t('requests.pending').toLowerCase() }}
                        </p>
                    </CardContent>
                </Card>
            </div>

            <!-- New Request Button -->
            <div
                class="flex justify-center"
                style="
                    animation: slideInUp 0.8s cubic-bezier(0.16, 1, 0.3, 1);
                    animation-delay: 0.25s;
                    animation-fill-mode: both;
                "
            >
                <Button
                    data-tour="request-button"
                    @click="isRequestDialogOpen = true"
                    class="group relative overflow-hidden bg-gradient-to-r from-orange-500 to-rose-500 px-8 py-6 text-lg font-bold text-white shadow-2xl shadow-orange-500/30 transition-all duration-300 hover:scale-105 hover:shadow-3xl hover:shadow-orange-500/40"
                >
                    <span class="absolute inset-0 bg-gradient-to-r from-orange-600 to-rose-600 opacity-0 transition-opacity duration-300 group-hover:opacity-100" />
                    <span class="relative flex items-center gap-3">
                        <Plus class="h-6 w-6 transition-transform duration-300 group-hover:rotate-90" />
                        {{ t('dashboard.employee.requestTimeOff') }}
                    </span>
                </Button>
            </div>

            <!-- Support Bar -->
            <div
                class="group relative overflow-hidden rounded-3xl border border-white/60 bg-gradient-to-r from-orange-500/5 via-amber-500/5 to-rose-500/5 p-5 backdrop-blur-xl transition-all duration-500 hover:border-white/80 hover:from-orange-500/10 hover:via-amber-500/10 hover:to-rose-500/10 hover:shadow-xl dark:border-white/10 dark:from-orange-500/10 dark:via-amber-500/10 dark:to-rose-500/10 dark:hover:border-white/20"
                style="
                    animation: slideInUp 0.8s cubic-bezier(0.16, 1, 0.3, 1);
                    animation-delay: 0.3s;
                    animation-fill-mode: both;
                "
            >
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div
                            class="rounded-2xl bg-gradient-to-br from-orange-500 to-rose-500 p-3 shadow-lg transition-transform duration-500 group-hover:scale-110 group-hover:rotate-3"
                        >
                            <FileText class="h-6 w-6 text-white" />
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100">
                                {{ t('dashboard.employee.supportUser') }}
                            </h3>
                            <p class="text-sm text-slate-600 dark:text-slate-300">
                                {{ t('dashboard.employee.documentationResources') }}
                            </p>
                        </div>
                    </div>
                    <button
                        class="group/btn flex items-center gap-2 rounded-2xl bg-gradient-to-r from-orange-500 to-rose-500 px-6 py-3 font-semibold text-white shadow-lg shadow-orange-500/25 transition-all duration-300 hover:scale-105 hover:shadow-xl hover:shadow-orange-500/40"
                    >
                        <Download class="h-5 w-5 transition-transform duration-300 group-hover/btn:translate-y-0.5" />
                        <span>{{ t('dashboard.employee.download') }}</span>
                    </button>
                </div>
            </div>

            <!-- Feature Cards Grid -->
            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-4">
                <div
                    v-for="(card, index) in featureCards"
                    :key="card.id"
                    class="group relative overflow-hidden rounded-3xl border border-white/60 bg-white/70 shadow-xl backdrop-blur-xl transition-all duration-500 hover:border-white/80 hover:bg-white/80 hover:shadow-2xl dark:border-white/10 dark:bg-slate-900/70 dark:hover:border-white/20 dark:hover:bg-slate-900/80"
                    :style="{
                        animation: `slideInUp 0.8s cubic-bezier(0.16, 1, 0.3, 1)`,
                        animationDelay: `${0.4 + index * 0.1}s`,
                        animationFillMode: 'both',
                    }"
                >
                    <!-- Top gradient accent -->
                    <div
                        class="h-2 bg-gradient-to-r transition-all duration-500 group-hover:h-3"
                        :class="card.gradient"
                    />

                    <div class="p-6">
                        <!-- Card header -->
                        <div class="mb-6 flex items-center gap-4">
                            <div
                                class="rounded-2xl bg-gradient-to-br p-3 shadow-lg transition-all duration-500 group-hover:scale-110 group-hover:rotate-6"
                                :class="card.gradient"
                            >
                                <component :is="card.icon" class="h-7 w-7 text-white" />
                            </div>
                            <h3 class="text-2xl font-bold text-slate-800 dark:text-slate-100">
                                {{ card.title }}
                            </h3>
                        </div>

                        <!-- Links or News content -->
                        <div v-if="!card.isNews" class="space-y-2">
                            <Link
                                v-for="link in card.links"
                                :key="link.label"
                                :href="link.href"
                                class="group/link flex items-center justify-between rounded-xl px-4 py-3 text-slate-700 transition-all duration-300 hover:bg-gradient-to-r hover:text-white hover:shadow-md dark:text-slate-300"
                                :class="`hover:${card.gradient}`"
                            >
                                <span class="text-sm font-medium">
                                    {{ link.label }}
                                </span>
                                <ChevronDown
                                    class="h-4 w-4 -rotate-90 opacity-0 transition-all duration-300 group-hover/link:opacity-100"
                                />
                            </Link>
                        </div>

                        <!-- News section -->
                        <div v-else class="space-y-4">
                            <div
                                class="rounded-2xl border border-slate-200/50 bg-gradient-to-br from-slate-50 to-white p-4 dark:border-slate-700/50 dark:from-slate-800/50 dark:to-slate-900/50"
                            >
                                <h4 class="mb-2 font-bold text-slate-800 dark:text-slate-100">
                                    {{ t('dashboard.employee.userNews') }}
                                </h4>
                                <p class="mb-1 text-sm font-semibold text-slate-700 dark:text-slate-200">
                                    {{ t('dashboard.employee.titlePlaceholder') }}
                                </p>
                                <p class="text-sm leading-relaxed text-slate-600 dark:text-slate-400">
                                    {{ t('dashboard.employee.newsPlaceholder') }}
                                </p>
                            </div>
                            <button
                                class="flex w-full items-center justify-center gap-2 rounded-xl bg-slate-100 py-3 text-sm font-semibold text-slate-700 transition-all duration-300 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700"
                            >
                                <ChevronDown class="h-4 w-4" />
                                <span>{{ t('dashboard.employee.seeMore') }}</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Upcoming Time Off -->
            <Card
                v-if="upcomingRequests.length > 0"
                data-tour="upcoming"
                class="border border-white/60 bg-white/70 backdrop-blur-xl dark:border-white/10 dark:bg-slate-900/70"
                style="
                    animation: slideInUp 0.8s cubic-bezier(0.16, 1, 0.3, 1);
                    animation-delay: 0.8s;
                    animation-fill-mode: both;
                "
            >
                <CardContent class="p-6">
                    <div class="mb-6 flex items-center gap-3">
                        <div
                            class="rounded-2xl bg-gradient-to-br from-violet-500 to-purple-600 p-3 shadow-lg"
                        >
                            <Plane class="h-6 w-6 text-white" />
                        </div>
                        <h3 class="text-2xl font-bold text-slate-800 dark:text-slate-100">
                            {{ t('dashboard.employee.upcomingTimeOff') }}
                        </h3>
                    </div>

                    <div class="space-y-3">
                        <div
                            v-for="(request, index) in upcomingRequests"
                            :key="request.id"
                            class="group/item flex items-center gap-4 rounded-2xl border border-slate-200/50 bg-gradient-to-r from-slate-50 to-white p-4 transition-all duration-300 hover:scale-[1.01] hover:border-slate-200 hover:shadow-md dark:border-slate-700/50 dark:from-slate-800/50 dark:to-slate-900/50 dark:hover:border-slate-700"
                            :style="{ animationDelay: `${index * 100}ms` }"
                        >
                            <div
                                class="flex h-12 w-12 items-center justify-center rounded-xl border-2 border-violet-200 bg-white shadow-sm transition-transform duration-300 group-hover/item:scale-110 group-hover/item:rotate-6 dark:border-violet-800 dark:bg-slate-900"
                            >
                                <component
                                    :is="getTypeIcon(request.type)"
                                    class="h-6 w-6 text-violet-600 dark:text-violet-400"
                                />
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-bold capitalize text-slate-900 dark:text-white">
                                    {{ request.type }} {{ t('dashboard.employee.leave') }}
                                </p>
                                <p class="text-xs text-slate-600 dark:text-slate-400">
                                    {{ formatDateRange(request.startDate, request.endDate) }}
                                </p>
                            </div>
                            <div class="text-right">
                                <p
                                    class="text-lg font-bold bg-gradient-to-r from-violet-600 to-purple-600 bg-clip-text text-transparent dark:from-violet-400 dark:to-purple-400"
                                >
                                    {{
                                        Math.ceil(
                                            (request.endDate.getTime() -
                                                request.startDate.getTime()) /
                                                (1000 * 60 * 60 * 24),
                                        ) + 1
                                    }}
                                    {{
                                        Math.ceil(
                                            (request.endDate.getTime() -
                                                request.startDate.getTime()) /
                                                (1000 * 60 * 60 * 24),
                                        ) + 1 === 1
                                            ? t('dashboard.recentRequests.day')
                                            : t('dashboard.recentRequests.days')
                                    }}
                                </p>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>
            </div>
        </div>

        <!-- Vacation Request Dialog -->
        <Dialog v-model:open="isRequestDialogOpen">
            <DialogContent class="max-w-2xl border-white/20 bg-white/90 backdrop-blur-xl dark:border-white/10 dark:bg-slate-900/90">
                <DialogHeader>
                    <DialogTitle class="sr-only">{{ t('dashboard.employee.requestTimeOff') }}</DialogTitle>
                </DialogHeader>
                <VacationRequestForm
                    @success="handleRequestSuccess"
                    @cancel="handleRequestCancel"
                />
            </DialogContent>
        </Dialog>
    </div>
</template>

<style scoped>
@keyframes slideInLeft {
    from {
        opacity: 0;
        transform: translateX(-30px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

@keyframes slideInRight {
    from {
        opacity: 0;
        transform: translateX(30px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
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

/* Custom scrollbar */
::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}

::-webkit-scrollbar-track {
    background: rgba(241, 245, 249, 0.5);
    border-radius: 10px;
}

.dark ::-webkit-scrollbar-track {
    background: rgba(15, 23, 42, 0.5);
}

::-webkit-scrollbar-thumb {
    background: linear-gradient(to bottom, #f97316, #f43f5e);
    border-radius: 10px;
}

::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(to bottom, #ea580c, #e11d48);
}

/* Smooth rendering */
* {
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
}
</style>
