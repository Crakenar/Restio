<script setup lang="ts">
import TeamCalendar from '@/components/TeamCalendar.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import type { VacationRequest } from '@/types/vacation';
import {
    AlertCircle,
    Calendar,
    CheckCircle2,
    Users,
    XCircle,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface Props {
    requests: VacationRequest[];
}

const props = defineProps<Props>();

const emit = defineEmits<{
    approve: [id: string];
    reject: [id: string];
}>();

const activeTab = ref('pending');

const pendingRequests = computed(() =>
    props.requests.filter((r) => r.status === 'pending'),
);

const approvedRequests = computed(() =>
    props.requests.filter((r) => r.status === 'approved'),
);

const rejectedRequests = computed(() =>
    props.requests.filter((r) => r.status === 'rejected'),
);

const upcomingAbsences = computed(() =>
    approvedRequests.value
        .filter((r) => r.startDate > new Date())
        .sort((a, b) => a.startDate.getTime() - b.startDate.getTime())
        .slice(0, 5),
);

const todayDate = new Date();
const currentAbsences = computed(() =>
    approvedRequests.value.filter(
        (r) => r.startDate <= todayDate && r.endDate >= todayDate,
    ),
);

const teamMembers = computed(() => {
    const members = new Set(props.requests.map((r) => r.employeeName));
    return members.size;
});

const formatDate = (date: Date) => {
    return new Intl.DateTimeFormat('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    }).format(date);
};

const getStatusColor = (status: string) => {
    switch (status) {
        case 'pending':
            return 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400';
        case 'approved':
            return 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400';
        case 'rejected':
            return 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400';
        default:
            return 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400';
    }
};

const getTypeColor = (type: string) => {
    switch (type) {
        case 'vacation':
            return 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400';
        case 'sick':
            return 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400';
        case 'wfh':
            return 'bg-teal-100 text-teal-800 dark:bg-teal-900/30 dark:text-teal-400';
        default:
            return 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400';
    }
};
</script>

<template>
    <div
        class="animate-in space-y-6 duration-700 fade-in slide-in-from-bottom-4"
    >
        <!-- Header with Action Metrics -->
        <div data-tour="welcome" class="grid gap-4 md:grid-cols-4">
            <Card
                class="relative overflow-hidden border border-slate-200/60 dark:border-white/20 bg-white/60 dark:bg-white/10 text-slate-900 dark:text-white shadow-lg shadow-amber-500/10 backdrop-blur-sm"
            >
                <CardContent class="p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="mb-1 text-sm font-medium text-slate-600 dark:text-white/70">
                                Action Required
                            </p>
                            <p class="text-4xl font-bold">
                                {{ pendingRequests.length }}
                            </p>
                            <p class="mt-1 text-xs text-slate-500 dark:text-white/50">
                                Pending approvals
                            </p>
                        </div>
                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-full bg-white/40 dark:bg-white/10 backdrop-blur-sm"
                        >
                            <AlertCircle class="h-6 w-6 text-amber-500 dark:text-amber-400" />
                        </div>
                    </div>
                </CardContent>
            </Card>

            <Card
                class="border border-slate-200/50 dark:border-white/10 bg-white/40 dark:bg-white/5 backdrop-blur-sm transition-all duration-300 hover:bg-white/60 dark:hover:bg-white/10 hover:shadow-lg"
            >
                <CardContent class="p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p
                                class="mb-1 text-sm font-medium text-slate-600 dark:text-white/60"
                            >
                                Approved
                            </p>
                            <p
                                class="text-3xl font-bold text-slate-900 dark:text-white"
                            >
                                {{ approvedRequests.length }}
                            </p>
                        </div>
                        <CheckCircle2
                            class="h-8 w-8 text-emerald-400 opacity-60"
                        />
                    </div>
                </CardContent>
            </Card>

            <Card
                class="border border-slate-200/50 bg-white/40 backdrop-blur-sm transition-all duration-300 hover:bg-white/60 hover:shadow-lg dark:border-white/10 dark:bg-white/5 dark:hover:bg-white/10"
            >
                <CardContent class="p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p
                                class="mb-1 text-sm font-medium text-slate-600 dark:text-white/60"
                            >
                                Out Today
                            </p>
                            <p
                                class="text-3xl font-bold text-slate-900 dark:text-white"
                            >
                                {{ currentAbsences.length }}
                            </p>
                        </div>
                        <Calendar
                            class="h-8 w-8 text-blue-500 opacity-60 dark:text-blue-400"
                        />
                    </div>
                </CardContent>
            </Card>

            <Card
                class="border border-slate-200/50 bg-white/40 backdrop-blur-sm transition-all duration-300 hover:bg-white/60 hover:shadow-lg dark:border-white/10 dark:bg-white/5 dark:hover:bg-white/10"
            >
                <CardContent class="p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p
                                class="mb-1 text-sm font-medium text-slate-600 dark:text-white/60"
                            >
                                Team Size
                            </p>
                            <p
                                class="text-3xl font-bold text-slate-900 dark:text-white"
                            >
                                {{ teamMembers }}
                            </p>
                        </div>
                        <Users
                            class="h-8 w-8 text-violet-500 opacity-60 dark:text-violet-400"
                        />
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Main Content Area -->
        <Tabs v-model="activeTab" class="space-y-4">
            <TabsList class="grid w-full grid-cols-3 border border-slate-200 bg-white/90 shadow-lg backdrop-blur-xl dark:border-white/20 dark:bg-white/10">
                <TabsTrigger value="pending" class="relative text-slate-600 hover:bg-slate-100 hover:text-slate-900 data-[state=active]:bg-slate-200 data-[state=active]:text-slate-900 dark:text-white/70 dark:hover:bg-white/5 dark:hover:text-white dark:data-[state=active]:bg-white/20 dark:data-[state=active]:text-white">
                    Pending
                    <Badge
                        v-if="pendingRequests.length > 0"
                        class="ml-2 border-0 bg-amber-500 text-white shadow-sm"
                    >
                        {{ pendingRequests.length }}
                    </Badge>
                </TabsTrigger>
                <TabsTrigger value="team" class="text-slate-600 hover:bg-slate-100 hover:text-slate-900 data-[state=active]:bg-slate-200 data-[state=active]:text-slate-900 dark:text-white/70 dark:hover:bg-white/5 dark:hover:text-white dark:data-[state=active]:bg-white/20 dark:data-[state=active]:text-white">Team Calendar</TabsTrigger>
                <TabsTrigger value="upcoming" class="text-slate-600 hover:bg-slate-100 hover:text-slate-900 data-[state=active]:bg-slate-200 data-[state=active]:text-slate-900 dark:text-white/70 dark:hover:bg-white/5 dark:hover:text-white dark:data-[state=active]:bg-white/20 dark:data-[state=active]:text-white">Upcoming</TabsTrigger>
            </TabsList>

            <!-- Pending Requests -->
            <TabsContent value="pending" data-tour="pending-requests" class="space-y-4">
                <Card v-if="pendingRequests.length === 0" class="border border-dashed border-slate-300 dark:border-white/20 bg-white/40 dark:bg-white/5">
                    <CardContent
                        class="flex flex-col items-center justify-center py-12"
                    >
                        <CheckCircle2 class="mb-4 h-12 w-12 text-emerald-500" />
                        <h3 class="mb-2 text-lg font-semibold text-slate-900 dark:text-white">
                            All caught up!
                        </h3>
                        <p class="text-sm text-slate-600 dark:text-white/60">
                            No pending requests to review
                        </p>
                    </CardContent>
                </Card>

                <div v-else class="space-y-3">
                    <Card
                        v-for="(request, index) in pendingRequests"
                        :key="request.id"
                        class="animate-in border border-slate-200/50 dark:border-white/10 bg-white/40 dark:bg-white/5 transition-all duration-300 fade-in slide-in-from-left-4 hover:bg-white/60 dark:hover:bg-white/10 border-l-4 border-l-amber-500"
                        :style="{ animationDelay: `${index * 50}ms` }"
                    >
                        <CardContent class="p-6">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex-1">
                                    <div class="mb-3 flex items-center gap-3">
                                        <div
                                            class="flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-br from-amber-500 to-orange-600 font-semibold text-white shadow-lg shadow-amber-500/20"
                                        >
                                            {{
                                                request.employeeName?.charAt(
                                                    0,
                                                ) || 'U'
                                            }}
                                        </div>
                                        <div>
                                            <h4 class="font-semibold text-slate-900 dark:text-white">
                                                {{ request.employeeName }}
                                            </h4>
                                            <p
                                                class="text-sm text-slate-600 dark:text-white/60"
                                            >
                                                {{ request.department }}
                                            </p>
                                        </div>
                                    </div>
                                    <div
                                        class="flex flex-wrap items-center gap-3"
                                    >
                                        <Badge
                                            :class="getTypeColor(request.type)"
                                            class="capitalize"
                                        >
                                            {{ request.type }}
                                        </Badge>
                                        <span
                                            class="text-sm text-white/50"
                                        >
                                            {{
                                                formatDate(request.startDate)
                                            }}
                                            - {{ formatDate(request.endDate) }}
                                        </span>
                                        <span class="text-sm font-medium text-white/70">
                                            {{
                                                Math.ceil(
                                                    (request.endDate.getTime() -
                                                        request.startDate.getTime()) /
                                                        (1000 * 60 * 60 * 24),
                                                ) + 1
                                            }}
                                            days
                                        </span>
                                    </div>
                                    <p
                                        v-if="request.reason"
                                        class="mt-3 text-sm text-slate-600 dark:text-white/60 italic"
                                    >
                                        "{{ request.reason }}"
                                    </p>
                                </div>
                                <div data-tour="approve-reject" class="flex gap-2">
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        @click="emit('approve', request.id)"
                                        class="border-emerald-500/30 text-emerald-400 hover:bg-emerald-500/10 hover:text-emerald-300"
                                    >
                                        <CheckCircle2 class="mr-1 h-4 w-4" />
                                        Approve
                                    </Button>
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        @click="emit('reject', request.id)"
                                        class="border-red-500/30 text-red-400 hover:bg-red-500/10 hover:text-red-300"
                                    >
                                        <XCircle class="mr-1 h-4 w-4" />
                                        Reject
                                    </Button>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </TabsContent>

            <!-- Team Calendar -->
            <TabsContent value="team" data-tour="team-overview">
                <TeamCalendar :requests="requests" />
            </TabsContent>

            <!-- Upcoming Absences -->
            <TabsContent value="upcoming" class="space-y-4">
                <Card class="border border-slate-200/50 dark:border-white/10 bg-white/40 dark:bg-white/5 backdrop-blur-sm">
                    <CardHeader>
                        <CardTitle class="text-slate-900 dark:text-white">Upcoming Team Absences</CardTitle>
                        <CardDescription class="text-slate-500 dark:text-white/60"
                            >Plan ahead for team coverage</CardDescription
                        >
                    </CardHeader>
                    <CardContent>
                        <div
                            v-if="upcomingAbsences.length === 0"
                            class="flex flex-col items-center justify-center py-8"
                        >
                            <Calendar
                                class="mb-4 h-12 w-12 text-slate-300 dark:text-white/30"
                            />
                            <p class="text-sm text-slate-400 dark:text-white/50">
                                No upcoming absences scheduled
                            </p>
                        </div>
                        <div v-else class="space-y-3">
                            <div
                                v-for="(request, index) in upcomingAbsences"
                                :key="request.id"
                                class="flex animate-in items-center justify-between rounded-lg border border-slate-200/50 dark:border-white/10 bg-white/40 dark:bg-white/5 p-4 transition-all duration-300 fade-in slide-in-from-right-4 hover:bg-white/60 dark:hover:bg-white/10"
                                :style="{ animationDelay: `${index * 50}ms` }"
                            >
                                <div class="flex items-center gap-4">
                                    <div
                                        class="flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-br from-blue-500 to-violet-600 font-semibold text-white shadow-lg shadow-blue-500/20"
                                    >
                                        {{
                                            request.employeeName?.charAt(0) ||
                                            'U'
                                        }}
                                    </div>
                                    <div>
                                        <p class="font-medium text-slate-900 dark:text-white">
                                            {{ request.employeeName }}
                                        </p>
                                        <p
                                            class="text-sm text-slate-500 dark:text-white/60"
                                        >
                                            {{ request.department }}
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-4">
                                    <Badge
                                        :class="getTypeColor(request.type)"
                                        class="capitalize"
                                    >
                                        {{ request.type }}
                                    </Badge>
                                    <div class="text-right">
                                        <p class="text-sm font-medium text-white/80">
                                            {{ formatDate(request.startDate) }}
                                        </p>
                                        <p
                                            class="text-xs text-white/50"
                                        >
                                            {{
                                                Math.ceil(
                                                    (request.endDate.getTime() -
                                                        request.startDate.getTime()) /
                                                        (1000 * 60 * 60 * 24),
                                                ) + 1
                                            }}
                                            days
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </TabsContent>
        </Tabs>
    </div>
</template>
