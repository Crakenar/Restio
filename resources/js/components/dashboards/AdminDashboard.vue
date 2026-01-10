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
        <!-- Header -->
        <div
            class="relative overflow-hidden rounded-2xl border border-blue-800/30 bg-gradient-to-br from-slate-900 via-blue-900 to-indigo-900 p-8 text-white dark:from-slate-950 dark:via-blue-950 dark:to-indigo-950"
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
                        <p class="text-sm text-blue-200">
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
            <Card class="border-t-4 border-t-blue-500 md:col-span-2">
                <CardContent class="p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p
                                class="mb-1 text-sm font-medium text-muted-foreground"
                            >
                                Total Employees
                            </p>
                            <p
                                class="text-4xl font-bold text-blue-700 dark:text-blue-400"
                            >
                                {{ totalEmployees }}
                            </p>
                            <p class="mt-1 text-xs text-muted-foreground">
                                Active workforce
                            </p>
                        </div>
                        <Users
                            class="h-10 w-10 text-blue-600 opacity-50 dark:text-blue-400"
                        />
                    </div>
                </CardContent>
            </Card>

            <Card class="border-t-4 border-t-emerald-500 md:col-span-2">
                <CardContent class="p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p
                                class="mb-1 text-sm font-medium text-muted-foreground"
                            >
                                Total Requests
                            </p>
                            <p
                                class="text-4xl font-bold text-emerald-700 dark:text-emerald-400"
                            >
                                {{ totalRequests }}
                            </p>
                            <p class="mt-1 text-xs text-muted-foreground">
                                All time submissions
                            </p>
                        </div>
                        <Calendar
                            class="h-10 w-10 text-emerald-600 opacity-50 dark:text-emerald-400"
                        />
                    </div>
                </CardContent>
            </Card>

            <Card class="border-t-4 border-t-violet-500 md:col-span-2">
                <CardContent class="p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p
                                class="mb-1 text-sm font-medium text-muted-foreground"
                            >
                                Approval Rate
                            </p>
                            <p
                                class="text-4xl font-bold text-violet-700 dark:text-violet-400"
                            >
                                {{ approvalRate }}%
                            </p>
                            <p class="mt-1 text-xs text-muted-foreground">
                                Request success rate
                            </p>
                        </div>
                        <TrendingUp
                            class="h-10 w-10 text-violet-600 opacity-50 dark:text-violet-400"
                        />
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Status Overview -->
        <div class="grid gap-4 md:grid-cols-4">
            <Card class="relative overflow-hidden">
                <CardContent class="p-6">
                    <div class="mb-2 flex items-center justify-between">
                        <p class="text-sm font-medium text-muted-foreground">
                            Pending
                        </p>
                        <Clock class="h-5 w-5 text-amber-500" />
                    </div>
                    <p
                        class="text-3xl font-bold text-amber-600 dark:text-amber-400"
                    >
                        {{ pendingRequests.length }}
                    </p>
                    <div class="mt-3 h-1 overflow-hidden rounded-full bg-muted">
                        <div
                            class="h-full rounded-full bg-amber-500"
                            :style="{
                                width: `${(pendingRequests.length / totalRequests) * 100}%`,
                            }"
                        />
                    </div>
                </CardContent>
            </Card>

            <Card class="relative overflow-hidden">
                <CardContent class="p-6">
                    <div class="mb-2 flex items-center justify-between">
                        <p class="text-sm font-medium text-muted-foreground">
                            Approved
                        </p>
                        <CheckCircle2 class="h-5 w-5 text-emerald-500" />
                    </div>
                    <p
                        class="text-3xl font-bold text-emerald-600 dark:text-emerald-400"
                    >
                        {{ approvedRequests.length }}
                    </p>
                    <div class="mt-3 h-1 overflow-hidden rounded-full bg-muted">
                        <div
                            class="h-full rounded-full bg-emerald-500"
                            :style="{
                                width: `${(approvedRequests.length / totalRequests) * 100}%`,
                            }"
                        />
                    </div>
                </CardContent>
            </Card>

            <Card class="relative overflow-hidden">
                <CardContent class="p-6">
                    <div class="mb-2 flex items-center justify-between">
                        <p class="text-sm font-medium text-muted-foreground">
                            Rejected
                        </p>
                        <XCircle class="h-5 w-5 text-red-500" />
                    </div>
                    <p
                        class="text-3xl font-bold text-red-600 dark:text-red-400"
                    >
                        {{ rejectedRequests.length }}
                    </p>
                    <div class="mt-3 h-1 overflow-hidden rounded-full bg-muted">
                        <div
                            class="h-full rounded-full bg-red-500"
                            :style="{
                                width: `${(rejectedRequests.length / totalRequests) * 100}%`,
                            }"
                        />
                    </div>
                </CardContent>
            </Card>

            <Card class="relative overflow-hidden">
                <CardContent class="p-6">
                    <div class="mb-2 flex items-center justify-between">
                        <p class="text-sm font-medium text-muted-foreground">
                            Out Today
                        </p>
                        <AlertTriangle class="h-5 w-5 text-blue-500" />
                    </div>
                    <p
                        class="text-3xl font-bold text-blue-600 dark:text-blue-400"
                    >
                        {{ currentAbsences }}
                    </p>
                    <p class="mt-2 text-xs text-muted-foreground">
                        Currently absent
                    </p>
                </CardContent>
            </Card>
        </div>

        <!-- Analytics Grid -->
        <div class="grid gap-4 md:grid-cols-2">
            <!-- Department Breakdown -->
            <Card>
                <CardHeader>
                    <CardTitle>Department Breakdown</CardTitle>
                    <CardDescription>Requests by department</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="space-y-3">
                        <div
                            v-for="(dept, index) in departmentStats"
                            :key="dept.name"
                            class="flex animate-in items-center justify-between rounded-lg bg-muted/50 p-3 fade-in slide-in-from-left-4"
                            :style="{ animationDelay: `${index * 50}ms` }"
                        >
                            <div class="flex-1">
                                <p class="font-medium">{{ dept.name }}</p>
                                <div class="mt-1 flex items-center gap-2">
                                    <span class="text-xs text-muted-foreground"
                                        >{{ dept.count }} requests</span
                                    >
                                    <Badge
                                        v-if="dept.pending > 0"
                                        variant="outline"
                                        class="text-xs"
                                    >
                                        {{ dept.pending }} pending
                                    </Badge>
                                </div>
                            </div>
                            <div class="text-right">
                                <p
                                    class="text-lg font-bold text-blue-600 dark:text-blue-400"
                                >
                                    {{ dept.count }}
                                </p>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Request Types -->
            <Card>
                <CardHeader>
                    <CardTitle>Request Types</CardTitle>
                    <CardDescription
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
                                <span class="text-sm font-medium capitalize">{{
                                    stat.type
                                }}</span>
                                <span class="text-sm text-muted-foreground"
                                    >{{ stat.count }} ({{
                                        stat.percentage
                                    }}%)</span
                                >
                            </div>
                            <div
                                class="h-3 overflow-hidden rounded-full bg-muted"
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
        <Card class="border-l-4 border-l-indigo-500">
            <CardHeader>
                <CardTitle>Company-wide Utilization</CardTitle>
                <CardDescription
                    >Vacation day usage across all employees</CardDescription
                >
            </CardHeader>
            <CardContent>
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-muted-foreground">
                                Total Days Used
                            </p>
                            <p
                                class="text-2xl font-bold text-indigo-700 dark:text-indigo-400"
                            >
                                {{ totalDaysUsed }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-muted-foreground">
                                Total Days Available
                            </p>
                            <p
                                class="text-2xl font-bold text-indigo-700 dark:text-indigo-400"
                            >
                                {{ totalDaysAvailable }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-muted-foreground">
                                Utilization Rate
                            </p>
                            <p
                                class="text-2xl font-bold text-indigo-700 dark:text-indigo-400"
                            >
                                {{ utilizationRate }}%
                            </p>
                        </div>
                    </div>
                    <div class="h-4 overflow-hidden rounded-full bg-muted">
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
            <TabsList class="grid w-full grid-cols-3">
                <TabsTrigger value="calendar">Coverage Calendar</TabsTrigger>
                <TabsTrigger value="employees">Employee Management</TabsTrigger>
                <TabsTrigger value="requests">All Requests</TabsTrigger>
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
