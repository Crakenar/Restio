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
            class="relative overflow-hidden rounded-2xl border border-white/20 bg-white/10 p-8 backdrop-blur-xl transition-all hover:bg-white/15"
        >
            <div class="relative z-10">
                <h2
                    class="mb-2 text-2xl font-semibold text-white"
                >
                    Welcome back, {{ userName }}
                </h2>
                <p class="text-white/70">
                    Plan your time off and track your balance
                </p>
            </div>
            <div
                class="absolute top-0 right-0 h-64 w-64 rounded-full bg-gradient-to-br from-orange-400/20 to-rose-400/20 blur-3xl"
            />
        </div>

        <!-- Stats Grid -->
        <div class="grid gap-4 md:grid-cols-3">
            <!-- Days Remaining -->
            <Card
                class="relative overflow-hidden border border-white/10 bg-white/5 backdrop-blur-sm transition-all duration-300 hover:-translate-y-0.5 hover:bg-white/10 hover:shadow-lg"
            >
                <CardContent class="p-6">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="mb-2 flex items-center gap-2">
                                <CalendarDays
                                    class="h-4 w-4 text-emerald-400"
                                />
                                <p
                                    class="text-sm font-medium text-white/60"
                                >
                                    Days Remaining
                                </p>
                            </div>
                            <p
                                class="mb-1 text-4xl font-bold text-white"
                            >
                                {{ daysRemaining }}
                            </p>
                            <p class="text-xs text-white/40">
                                of {{ totalDaysAllowed }} total days
                            </p>
                            <!-- Progress bar -->
                            <div
                                class="mt-3 h-2 overflow-hidden rounded-full bg-white/10"
                            >
                                <div
                                    class="h-full rounded-full bg-gradient-to-r from-emerald-400 to-teal-400 transition-all duration-700"
                                    :style="{ width: `${usagePercentage}%` }"
                                />
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Days Used -->
            <Card
                class="relative overflow-hidden border border-white/10 bg-white/5 backdrop-blur-sm transition-all duration-300 hover:-translate-y-0.5 hover:bg-white/10 hover:shadow-lg"
            >
                <CardContent class="p-6">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="mb-2 flex items-center gap-2">
                                <TrendingUp
                                    class="h-4 w-4 text-blue-400"
                                />
                                <p
                                    class="text-sm font-medium text-white/60"
                                >
                                    Days Used
                                </p>
                            </div>
                            <p
                                class="mb-1 text-4xl font-bold text-white"
                            >
                                {{ totalDaysUsed }}
                            </p>
                            <p class="text-xs text-white/40">
                                {{ usagePercentage }}% of allowance
                            </p>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Pending Requests -->
            <Card
                class="relative overflow-hidden border border-white/10 bg-white/5 backdrop-blur-sm transition-all duration-300 hover:-translate-y-0.5 hover:bg-white/10 hover:shadow-lg"
            >
                <CardContent class="p-6">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="mb-2 flex items-center gap-2">
                                <Clock
                                    class="h-4 w-4 text-amber-400"
                                />
                                <p
                                    class="text-sm font-medium text-white/60"
                                >
                                    Pending Approval
                                </p>
                            </div>
                            <p
                                class="mb-1 text-4xl font-bold text-white"
                            >
                                {{ pendingRequests.length }}
                            </p>
                            <p class="text-xs text-white/40">
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
            class="border border-white/10 bg-white/5 backdrop-blur-sm"
        >
            <CardHeader>
                <CardTitle class="flex items-center gap-2 text-white">
                    <Plane
                        class="h-5 w-5 text-violet-400"
                    />
                    Upcoming Time Off
                </CardTitle>
            </CardHeader>
            <CardContent>
                <div class="space-y-3">
                    <div
                        v-for="(request, index) in upcomingRequests"
                        :key="request.id"
                        class="flex animate-in items-center gap-4 rounded-lg bg-white/5 p-3 transition-all duration-300 fade-in slide-in-from-left-4 hover:bg-white/10"
                        :style="{ animationDelay: `${index * 100}ms` }"
                    >
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-full border-2 border-white/10 bg-white/5"
                        >
                            <component
                                :is="getTypeIcon(request.type)"
                                class="h-5 w-5 text-violet-400"
                            />
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium capitalize text-white">
                                {{ request.type }} Leave
                            </p>
                            <p class="text-xs text-white/50">
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
                                class="text-sm font-semibold text-violet-300"
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
