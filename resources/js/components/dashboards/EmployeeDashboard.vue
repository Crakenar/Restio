<script setup lang="ts">
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import VacationCalendar from '@/components/VacationCalendar.vue';
import type { VacationRequest } from '@/types/vacation';
import {
    CalendarDays,
    Clock,
    Coffee,
    Home,
    Plane,
    TrendingUp,
} from 'lucide-vue-next';
import { computed } from 'vue';

interface Props {
    requests: VacationRequest[];
    totalDaysAllowed: number;
    userName?: string;
}

const props = withDefaults(defineProps<Props>(), {
    userName: 'there',
});

const emit = defineEmits<{
    createRequest: [request: Omit<VacationRequest, 'id' | 'status'>];
}>();

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
    <div
        class="animate-in space-y-6 duration-700 fade-in slide-in-from-bottom-4"
    >
        <!-- Welcome Header -->
        <div
            class="relative overflow-hidden rounded-2xl border border-orange-200/50 bg-gradient-to-br from-amber-50 via-orange-50 to-rose-50 p-8 dark:border-orange-800/30 dark:from-amber-950/20 dark:via-orange-950/20 dark:to-rose-950/20"
        >
            <div class="relative z-10">
                <h2
                    class="mb-2 text-2xl font-semibold text-amber-950 dark:text-amber-50"
                >
                    Welcome back, {{ userName }}
                </h2>
                <p class="text-amber-800/80 dark:text-amber-200/70">
                    Plan your time off and track your balance
                </p>
            </div>
            <div
                class="absolute top-0 right-0 h-64 w-64 rounded-full bg-gradient-to-br from-orange-400/10 to-rose-400/10 blur-3xl dark:from-orange-400/5 dark:to-rose-400/5"
            />
        </div>

        <!-- Stats Grid -->
        <div class="grid gap-4 md:grid-cols-3">
            <!-- Days Remaining -->
            <Card
                class="relative overflow-hidden border-l-4 border-l-emerald-500 transition-all duration-300 hover:-translate-y-0.5 hover:shadow-lg dark:border-l-emerald-400"
            >
                <CardContent class="p-6">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="mb-2 flex items-center gap-2">
                                <CalendarDays
                                    class="h-4 w-4 text-emerald-600 dark:text-emerald-400"
                                />
                                <p
                                    class="text-sm font-medium text-muted-foreground"
                                >
                                    Days Remaining
                                </p>
                            </div>
                            <p
                                class="mb-1 text-4xl font-bold text-emerald-700 dark:text-emerald-400"
                            >
                                {{ daysRemaining }}
                            </p>
                            <p class="text-xs text-muted-foreground">
                                of {{ totalDaysAllowed }} total days
                            </p>
                            <!-- Progress bar -->
                            <div
                                class="mt-3 h-2 overflow-hidden rounded-full bg-muted"
                            >
                                <div
                                    class="h-full rounded-full bg-gradient-to-r from-emerald-500 to-teal-500 transition-all duration-700"
                                    :style="{ width: `${usagePercentage}%` }"
                                />
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Days Used -->
            <Card
                class="relative overflow-hidden border-l-4 border-l-blue-500 transition-all duration-300 hover:-translate-y-0.5 hover:shadow-lg dark:border-l-blue-400"
            >
                <CardContent class="p-6">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="mb-2 flex items-center gap-2">
                                <TrendingUp
                                    class="h-4 w-4 text-blue-600 dark:text-blue-400"
                                />
                                <p
                                    class="text-sm font-medium text-muted-foreground"
                                >
                                    Days Used
                                </p>
                            </div>
                            <p
                                class="mb-1 text-4xl font-bold text-blue-700 dark:text-blue-400"
                            >
                                {{ totalDaysUsed }}
                            </p>
                            <p class="text-xs text-muted-foreground">
                                {{ usagePercentage }}% of allowance
                            </p>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Pending Requests -->
            <Card
                class="relative overflow-hidden border-l-4 border-l-amber-500 transition-all duration-300 hover:-translate-y-0.5 hover:shadow-lg dark:border-l-amber-400"
            >
                <CardContent class="p-6">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="mb-2 flex items-center gap-2">
                                <Clock
                                    class="h-4 w-4 text-amber-600 dark:text-amber-400"
                                />
                                <p
                                    class="text-sm font-medium text-muted-foreground"
                                >
                                    Pending Approval
                                </p>
                            </div>
                            <p
                                class="mb-1 text-4xl font-bold text-amber-700 dark:text-amber-400"
                            >
                                {{ pendingRequests.length }}
                            </p>
                            <p class="text-xs text-muted-foreground">
                                {{
                                    pendingRequests.length === 1
                                        ? 'request'
                                        : 'requests'
                                }}
                                awaiting review
                            </p>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Upcoming Time Off -->
        <Card
            v-if="upcomingRequests.length > 0"
            class="border-t-4 border-t-violet-500 dark:border-t-violet-400"
        >
            <CardHeader>
                <CardTitle class="flex items-center gap-2">
                    <Plane
                        class="h-5 w-5 text-violet-600 dark:text-violet-400"
                    />
                    Upcoming Time Off
                </CardTitle>
            </CardHeader>
            <CardContent>
                <div class="space-y-3">
                    <div
                        v-for="(request, index) in upcomingRequests"
                        :key="request.id"
                        class="flex animate-in items-center gap-4 rounded-lg bg-muted/50 p-3 transition-all duration-300 fade-in slide-in-from-left-4 hover:bg-muted"
                        :style="{ animationDelay: `${index * 100}ms` }"
                    >
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-full border-2 border-violet-500/20 bg-background"
                        >
                            <component
                                :is="getTypeIcon(request.type)"
                                class="h-5 w-5 text-violet-600 dark:text-violet-400"
                            />
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium capitalize">
                                {{ request.type }} Leave
                            </p>
                            <p class="text-xs text-muted-foreground">
                                {{
                                    formatDateRange(
                                        request.startDate,
                                        request.endDate,
                                    )
                                }}
                            </p>
                        </div>
                        <div class="text-right">
                            <p
                                class="text-sm font-semibold text-violet-700 dark:text-violet-400"
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
                                    ) +
                                        1 ===
                                    1
                                        ? 'day'
                                        : 'days'
                                }}
                            </p>
                        </div>
                    </div>
                </div>
            </CardContent>
        </Card>

        <!-- Calendar -->
        <VacationCalendar
            :existing-requests="myRequests"
            user-role="employee"
            @create-request="emit('createRequest', $event)"
        />
    </div>
</template>
