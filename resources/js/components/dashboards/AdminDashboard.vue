<script setup lang="ts">
import AdminDashboard from '@/components/AdminDashboard.vue';
import RequestsTable from '@/components/RequestsTable.vue';
import TeamCalendar from '@/components/TeamCalendar.vue';
import { Badge } from '@/components/ui/badge';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import type { Employee, VacationRequest } from '@/types/vacation';
import {
    AlertTriangle,
    BarChart3,
    Calendar,
    CheckCircle2,
    Clock,
    TrendingUp,
    Users,
    XCircle,
} from 'lucide-vue-next';
import { computed } from 'vue';

interface Props {
    requests: VacationRequest[];
    employees: Employee[];
}

const props = defineProps<Props>();

const emit = defineEmits<{
    selectEmployee: [id: string];
    approve: [id: string];
    reject: [id: string];
}>();

// Analytics computations
const totalRequests = computed(() => props.requests.length);
const pendingRequests = computed(() =>
    props.requests.filter((r) => r.status === 'pending'),
);
const approvedRequests = computed(() =>
    props.requests.filter((r) => r.status === 'approved'),
);
const rejectedRequests = computed(() =>
    props.requests.filter((r) => r.status === 'rejected'),
);

const approvalRate = computed(() => {
    const total = approvedRequests.value.length + rejectedRequests.value.length;
    return total > 0
        ? Math.round((approvedRequests.value.length / total) * 100)
        : 0;
});

const totalEmployees = computed(() => props.employees.length);

const totalDaysUsed = computed(() => {
    return props.employees.reduce((sum, emp) => sum + emp.usedDays, 0);
});

const totalDaysAvailable = computed(() => {
    return props.employees.reduce((sum, emp) => sum + emp.totalDays, 0);
});

const utilizationRate = computed(() => {
    return totalDaysAvailable.value > 0
        ? Math.round((totalDaysUsed.value / totalDaysAvailable.value) * 100)
        : 0;
});

// Department breakdown
const departmentStats = computed(() => {
    const stats = new Map<
        string,
        { count: number; pending: number; approved: number }
    >();

    props.requests.forEach((req) => {
        const dept = req.department || 'Unknown';
        if (!stats.has(dept)) {
            stats.set(dept, { count: 0, pending: 0, approved: 0 });
        }
        const deptStat = stats.get(dept)!;
        deptStat.count++;
        if (req.status === 'pending') deptStat.pending++;
        if (req.status === 'approved') deptStat.approved++;
    });

    return Array.from(stats.entries())
        .map(([name, data]) => ({ name, ...data }))
        .sort((a, b) => b.count - a.count);
});

// Type breakdown
const typeStats = computed(() => {
    const stats = new Map<string, number>();

    props.requests.forEach((req) => {
        const count = stats.get(req.type) || 0;
        stats.set(req.type, count + 1);
    });

    return Array.from(stats.entries())
        .map(([type, count]) => ({
            type,
            count,
            percentage: Math.round((count / totalRequests.value) * 100),
        }))
        .sort((a, b) => b.count - a.count);
});

const todayDate = new Date();
const currentAbsences = computed(
    () =>
        approvedRequests.value.filter(
            (r) => r.startDate <= todayDate && r.endDate >= todayDate,
        ).length,
);

const getTypeColor = (type: string) => {
    switch (type) {
        case 'vacation':
            return 'from-blue-500 to-cyan-600';
        case 'sick':
            return 'from-purple-500 to-pink-600';
        case 'wfh':
            return 'from-teal-500 to-emerald-600';
        case 'personal':
            return 'from-orange-500 to-amber-600';
        default:
            return 'from-gray-500 to-slate-600';
    }
};
</script>

<template>
    <div
        class="animate-in space-y-6 duration-700 fade-in slide-in-from-bottom-4"
    >
        <div
            class="relative overflow-hidden rounded-2xl border border-slate-200/50 bg-white/60 p-8 text-slate-900 backdrop-blur-xl dark:border-white/20 dark:bg-white/10 dark:text-white"
        >
            <div class="relative z-10">
                <div class="mb-3 flex items-center gap-3">
                    <div
                        class="flex h-12 w-12 items-center justify-center rounded-xl bg-white/10 backdrop-blur-sm"
                    >
                        <BarChart3 class="h-6 w-6" />
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold">Admin Dashboard</h2>
                        <p class="text-sm text-slate-600 dark:text-white/70">
                            Company-wide vacation analytics and management
                        </p>
                    </div>
                </div>
            </div>
            <div
                class="absolute top-0 right-0 h-96 w-96 rounded-full bg-gradient-to-br from-blue-500/10 to-indigo-500/10 blur-3xl"
            />
        </div>

        <!-- Key Metrics -->
        <div class="grid gap-4 md:grid-cols-6">
            <Card
                class="border border-slate-200/50 bg-white/40 backdrop-blur-sm transition-colors hover:bg-white/60 md:col-span-2 dark:border-white/10 dark:bg-white/5 dark:hover:bg-white/10"
            >
                <CardContent class="p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p
                                class="mb-1 text-sm font-medium text-slate-600 dark:text-white/60"
                            >
                                Total Employees
                            </p>
                            <p
                                class="text-4xl font-bold text-slate-900 dark:text-white"
                            >
                                {{ totalEmployees }}
                            </p>
                            <p
                                class="mt-1 text-xs text-slate-500 dark:text-white/40"
                            >
                                Active workforce
                            </p>
                        </div>
                        <Users class="h-10 w-10 text-blue-400 opacity-50" />
                    </div>
                </CardContent>
            </Card>

            <Card
                class="border border-white/10 bg-white/5 backdrop-blur-sm transition-colors hover:bg-white/10 md:col-span-2"
            >
                <CardContent class="p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="mb-1 text-sm font-medium text-white/60">
                                Total Requests
                            </p>
                            <p class="text-4xl font-bold text-white">
                                {{ totalRequests }}
                            </p>
                            <p class="mt-1 text-xs text-white/40">
                                All time submissions
                            </p>
                        </div>
                        <Calendar
                            class="h-10 w-10 text-emerald-400 opacity-50"
                        />
                    </div>
                </CardContent>
            </Card>

            <Card
                class="border border-slate-200/50 bg-white/40 backdrop-blur-sm transition-colors hover:bg-white/60 md:col-span-2 dark:border-white/10 dark:bg-white/5 dark:hover:bg-white/10"
            >
                <CardContent class="p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="mb-1 text-sm font-medium text-white/60">
                                Approval Rate
                            </p>
                            <p class="text-4xl font-bold text-white">
                                {{ approvalRate }}%
                            </p>
                            <p class="mt-1 text-xs text-white/40">
                                Request success rate
                            </p>
                        </div>
                        <TrendingUp
                            class="h-10 w-10 text-violet-400 opacity-50"
                        />
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Status Overview -->
        <div class="grid gap-4 md:grid-cols-4">
            <Card
                class="relative overflow-hidden border border-slate-200/50 bg-white/40 backdrop-blur-sm dark:border-white/10 dark:bg-white/5"
            >
                <CardContent class="p-6">
                    <div class="mb-2 flex items-center justify-between">
                        <p
                            class="text-sm font-medium text-slate-600 dark:text-white/60"
                        >
                            Pending
                        </p>
                        <Clock class="h-5 w-5 text-amber-500" />
                    </div>
                    <p
                        class="text-3xl font-bold text-slate-900 dark:text-white"
                    >
                        {{ pendingRequests.length }}
                    </p>
                    <div
                        class="mt-3 h-1 overflow-hidden rounded-full bg-slate-200/50 dark:bg-white/10"
                    >
                        <div
                            class="h-full rounded-full bg-amber-500"
                            :style="{
                                width: `${(pendingRequests.length / totalRequests) * 100}%`,
                            }"
                        />
                    </div>
                </CardContent>
            </Card>

            <Card
                class="relative overflow-hidden border border-white/10 bg-white/5 backdrop-blur-sm"
            >
                <CardContent class="p-6">
                    <div class="mb-2 flex items-center justify-between">
                        <p class="text-sm font-medium text-white/60">
                            Approved
                        </p>
                        <CheckCircle2 class="h-5 w-5 text-emerald-500" />
                    </div>
                    <p class="text-3xl font-bold text-white">
                        {{ approvedRequests.length }}
                    </p>
                    <div
                        class="mt-3 h-1 overflow-hidden rounded-full bg-white/10"
                    >
                        <div
                            class="h-full rounded-full bg-emerald-500"
                            :style="{
                                width: `${(approvedRequests.length / totalRequests) * 100}%`,
                            }"
                        />
                    </div>
                </CardContent>
            </Card>

            <Card
                class="relative overflow-hidden border border-white/10 bg-white/5 backdrop-blur-sm"
            >
                <CardContent class="p-6">
                    <div class="mb-2 flex items-center justify-between">
                        <p class="text-sm font-medium text-white/60">
                            Rejected
                        </p>
                        <XCircle class="h-5 w-5 text-red-500" />
                    </div>
                    <p class="text-3xl font-bold text-white">
                        {{ rejectedRequests.length }}
                    </p>
                    <div
                        class="mt-3 h-1 overflow-hidden rounded-full bg-white/10"
                    >
                        <div
                            class="h-full rounded-full bg-red-500"
                            :style="{
                                width: `${(rejectedRequests.length / totalRequests) * 100}%`,
                            }"
                        />
                    </div>
                </CardContent>
            </Card>

            <Card
                class="relative overflow-hidden border border-white/10 bg-white/5 backdrop-blur-sm"
            >
                <CardContent class="p-6">
                    <div class="mb-2 flex items-center justify-between">
                        <p class="text-sm font-medium text-white/60">
                            Out Today
                        </p>
                        <AlertTriangle class="h-5 w-5 text-blue-500" />
                    </div>
                    <p class="text-3xl font-bold text-white">
                        {{ currentAbsences }}
                    </p>
                    <p class="mt-2 text-xs text-white/40">Currently absent</p>
                </CardContent>
            </Card>
        </div>

        <!-- Analytics Grid -->
        <div class="grid gap-4 md:grid-cols-2">
            <!-- Department Breakdown -->
            <Card
                class="border border-slate-200/50 bg-white/40 backdrop-blur-sm dark:border-white/10 dark:bg-white/5"
            >
                <CardHeader>
                    <CardTitle class="text-slate-900 dark:text-white"
                        >Department Breakdown</CardTitle
                    >
                    <CardDescription class="text-slate-500 dark:text-white/60"
                        >Requests by department</CardDescription
                    >
                </CardHeader>
                <CardContent>
                    <div class="space-y-3">
                        <div
                            v-for="(dept, index) in departmentStats"
                            :key="dept.name"
                            class="flex animate-in items-center justify-between rounded-lg bg-white/40 p-3 transition-colors fade-in slide-in-from-left-4 hover:bg-white/60 dark:bg-white/5 dark:hover:bg-white/10"
                            :style="{ animationDelay: `${index * 50}ms` }"
                        >
                            <div class="flex-1">
                                <p
                                    class="font-medium text-slate-900 dark:text-white"
                                >
                                    {{ dept.name }}
                                </p>
                                <div class="mt-1 flex items-center gap-2">
                                    <span
                                        class="text-xs text-slate-500 dark:text-white/50"
                                        >{{ dept.count }} requests</span
                                    >
                                    <Badge
                                        v-if="dept.pending > 0"
                                        variant="outline"
                                        class="border-amber-500/50 text-xs text-amber-500"
                                    >
                                        {{ dept.pending }} pending
                                    </Badge>
                                </div>
                            </div>
                            <div class="text-right">
                                <p
                                    class="text-lg font-bold text-slate-900 dark:text-white"
                                >
                                    {{ dept.count }}
                                </p>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Request Types -->
            <Card
                class="border border-slate-200/50 bg-white/40 backdrop-blur-sm dark:border-white/10 dark:bg-white/5"
            >
                <CardHeader>
                    <CardTitle class="text-slate-900 dark:text-white"
                        >Request Types</CardTitle
                    >
                    <CardDescription class="text-slate-500 dark:text-white/60"
                        >Distribution by leave type</CardDescription
                    >
                </CardHeader>
                <CardContent>
                    <div class="space-y-4">
                        <div
                            v-for="(stat, index) in typeStats"
                            :key="stat.type"
                            class="animate-in fade-in slide-in-from-right-4"
                            :style="{ animationDelay: `${index * 50}ms` }"
                        >
                            <div class="mb-2 flex items-center justify-between">
                                <span
                                    class="text-sm font-medium text-slate-900 capitalize dark:text-white"
                                    >{{ stat.type }}</span
                                >
                                <span
                                    class="text-sm text-slate-500 dark:text-white/50"
                                    >{{ stat.count }} ({{
                                        stat.percentage
                                    }}%)</span
                                >
                            </div>
                            <div
                                class="h-3 overflow-hidden rounded-full bg-slate-200/50 dark:bg-white/10"
                            >
                                <div
                                    class="h-full rounded-full bg-gradient-to-r transition-all duration-700"
                                    :class="getTypeColor(stat.type)"
                                    :style="{ width: `${stat.percentage}%` }"
                                />
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Utilization Stats -->
        <Card
            class="border border-slate-200/50 bg-white/40 backdrop-blur-sm dark:border-white/10 dark:bg-white/5"
        >
            <CardHeader>
                <CardTitle class="text-slate-900 dark:text-white"
                    >Company-wide Utilization</CardTitle
                >
                <CardDescription class="text-slate-500 dark:text-white/60"
                    >Vacation day usage across all employees</CardDescription
                >
            </CardHeader>
            <CardContent>
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p
                                class="text-sm text-slate-500 dark:text-white/50"
                            >
                                Total Days Used
                            </p>
                            <p
                                class="text-2xl font-bold text-slate-900 dark:text-white"
                            >
                                {{ totalDaysUsed }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-white/50">
                                Total Days Available
                            </p>
                            <p class="text-2xl font-bold text-white">
                                {{ totalDaysAvailable }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-white/50">
                                Utilization Rate
                            </p>
                            <p class="text-2xl font-bold text-white">
                                {{ utilizationRate }}%
                            </p>
                        </div>
                    </div>
                    <div class="h-4 overflow-hidden rounded-full bg-white/10">
                        <div
                            class="h-full rounded-full bg-gradient-to-r from-indigo-500 to-purple-600 transition-all duration-1000"
                            :style="{ width: `${utilizationRate}%` }"
                        />
                    </div>
                </div>
            </CardContent>
        </Card>

        <!-- Detailed Views -->
        <Tabs default-value="calendar" class="space-y-4">
            <TabsList
                class="grid w-full grid-cols-3 border border-slate-200/50 bg-white/40 shadow-lg backdrop-blur-xl dark:border-white/20 dark:bg-white/10"
            >
                <TabsTrigger
                    value="calendar"
                    class="text-slate-600 transition-all hover:bg-white/20 hover:text-slate-900 data-[state=active]:bg-white/60 data-[state=active]:text-slate-900 dark:text-white/70 dark:hover:bg-white/5 dark:hover:text-white dark:data-[state=active]:bg-white/20 dark:data-[state=active]:text-white"
                    >Coverage Calendar</TabsTrigger
                >
                <TabsTrigger
                    value="employees"
                    class="text-slate-600 transition-all hover:bg-white/20 hover:text-slate-900 data-[state=active]:bg-white/60 data-[state=active]:text-slate-900 dark:text-white/70 dark:hover:bg-white/5 dark:hover:text-white dark:data-[state=active]:bg-white/20 dark:data-[state=active]:text-white"
                    >Employee Management</TabsTrigger
                >
                <TabsTrigger
                    value="requests"
                    class="text-slate-600 transition-all hover:bg-white/20 hover:text-slate-900 data-[state=active]:bg-white/60 data-[state=active]:text-slate-900 dark:text-white/70 dark:hover:bg-white/5 dark:hover:text-white dark:data-[state=active]:bg-white/20 dark:data-[state=active]:text-white"
                    >All Requests</TabsTrigger
                >
            </TabsList>

            <TabsContent value="calendar">
                <TeamCalendar :requests="requests" />
            </TabsContent>

            <TabsContent value="employees">
                <AdminDashboard
                    :employees="employees"
                    :requests="requests"
                    @select-employee="emit('selectEmployee', $event)"
                />
            </TabsContent>

            <TabsContent value="requests">
                <RequestsTable
                    :requests="requests"
                    user-role="admin"
                    @approve="emit('approve', $event)"
                    @reject="emit('reject', $event)"
                />
            </TabsContent>
        </Tabs>
    </div>
</template>
