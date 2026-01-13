<script setup lang="ts">
import AdminDashboard from '@/components/AdminDashboard.vue';
import TeamCalendar from '@/components/TeamCalendar.vue';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { UserRole, VacationRequestStatus, VacationRequestType } from '@/enums';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { Users } from 'lucide-vue-next';
import { ref } from 'vue';

interface VacationRequest {
    id: string;
    startDate: Date;
    endDate: Date;
    type: VacationRequestType;
    status: VacationRequestStatus;
    reason?: string;
    employeeName?: string;
    department?: string;
}

interface Employee {
    id: string;
    name: string;
    email: string;
    department: string;
    totalDays: number;
    usedDays: number;
    pendingRequests: number;
}

// Define props received from Inertia controller
interface Props {
    requests: VacationRequest[];
    employees: Employee[];
    userRole: UserRole;
}

const props = defineProps<Props>();

// Convert props to refs for reactivity (dates need to be converted)
const requests = ref<VacationRequest[]>(
    props.requests.map((req) => ({
        ...req,
        startDate: new Date(req.startDate),
        endDate: new Date(req.endDate),
    })),
);

const employees = ref<Employee[]>(props.employees);

// Fake user role - change this to test different views
const userRole = ref<UserRole>(props.userRole);

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Team', href: '/team' },
];

const handleSelectEmployee = (id: string) => {
    console.log('Selected employee:', id);
};
</script>

<template>
    <Head title="Team" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <!-- Gradient background - adapts to theme -->
        <div
            class="absolute inset-0 -z-10 bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 dark:from-slate-950 dark:via-blue-950 dark:to-indigo-950"
        />

        <!-- Animated gradient orbs -->
        <div class="pointer-events-none absolute inset-0 -z-10 overflow-hidden">
            <div
                class="absolute -top-1/2 -right-1/2 h-full w-full animate-pulse rounded-full bg-gradient-to-br from-blue-500/10 via-indigo-500/10 to-purple-500/10 blur-3xl dark:from-blue-500/20 dark:via-indigo-500/20 dark:to-purple-500/20"
                style="animation-duration: 8s"
            />
            <div
                class="absolute -bottom-1/2 -left-1/2 h-full w-full animate-pulse rounded-full bg-gradient-to-tr from-teal-500/10 via-emerald-500/10 to-green-500/10 blur-3xl dark:from-teal-500/20 dark:via-emerald-500/20 dark:to-green-500/20"
                style="animation-duration: 10s; animation-delay: 1s"
            />
        </div>

        <!-- Content -->
        <div
            class="relative flex h-full flex-1 flex-col gap-6 overflow-hidden p-6"
        >
            <!-- Enhanced Header -->
            <div class="flex items-center gap-4">
                <div
                    class="flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 shadow-2xl shadow-blue-500/30"
                >
                    <Users class="h-8 w-8 text-white" />
                </div>
                <div>
                    <h1
                        class="text-4xl font-bold tracking-tight text-slate-900 dark:text-white"
                    >
                        Team
                    </h1>
                    <p class="mt-1.5 text-sm text-slate-600 dark:text-white/70">
                        View and manage team absences and coverage
                    </p>
                </div>
            </div>

            <!-- Admin View -->
            <template v-if="userRole === UserRole.ADMIN">
                <Tabs
                    default-value="calendar"
                    class="flex flex-1 flex-col space-y-4"
                >
                    <TabsList
                        class="w-fit border border-slate-200 bg-white/90 shadow-lg backdrop-blur-xl dark:border-white/20 dark:bg-white/10"
                    >
                        <TabsTrigger
                            value="calendar"
                            class="data-[state=active]:bg-slate-100 data-[state=active]:text-slate-900 dark:data-[state=active]:bg-white/20 dark:data-[state=active]:text-white"
                            >Coverage Calendar</TabsTrigger
                        >
                        <TabsTrigger
                            value="employees"
                            class="data-[state=active]:bg-slate-100 data-[state=active]:text-slate-900 dark:data-[state=active]:bg-white/20 dark:data-[state=active]:text-white"
                            >Employees</TabsTrigger
                        >
                    </TabsList>

                    <TabsContent
                        value="calendar"
                        class="flex-1 overflow-auto rounded-3xl border border-slate-200 bg-white/80 p-6 shadow-2xl backdrop-blur-xl dark:border-white/20 dark:bg-white/10"
                    >
                        <TeamCalendar
                            :requests="requests"
                            :total-employees="employees.length"
                        />
                    </TabsContent>

                    <TabsContent
                        value="employees"
                        class="flex-1 overflow-auto rounded-3xl border border-slate-200 bg-white/80 shadow-2xl backdrop-blur-xl dark:border-white/20 dark:bg-white/10"
                    >
                        <AdminDashboard
                            :employees="employees"
                            :requests="requests"
                            @select-employee="handleSelectEmployee"
                        />
                    </TabsContent>
                </Tabs>
            </template>

            <!-- Manager View -->
            <template v-else-if="userRole === UserRole.MANAGER">
                <div
                    class="flex-1 overflow-auto rounded-3xl border border-slate-200 bg-white/80 p-6 shadow-2xl backdrop-blur-xl dark:border-white/20 dark:bg-white/10"
                >
                    <TeamCalendar
                        :requests="requests"
                        :total-employees="employees.length"
                    />
                </div>
            </template>

            <!-- Employee View (no access) -->
            <template v-else>
                <div
                    class="flex flex-1 items-center justify-center rounded-3xl border border-slate-200 bg-white/80 p-12 text-center shadow-2xl backdrop-blur-xl dark:border-white/20 dark:bg-white/10"
                >
                    <div>
                        <div
                            class="mx-auto mb-6 flex h-24 w-24 items-center justify-center rounded-3xl bg-slate-100 dark:bg-white/10"
                        >
                            <Users
                                class="h-12 w-12 text-slate-400 dark:text-white/50"
                            />
                        </div>
                        <h3
                            class="mb-2 text-xl font-semibold text-slate-900 dark:text-white"
                        >
                            Team View Restricted
                        </h3>
                        <p class="text-sm text-slate-600 dark:text-white/70">
                            Team view is available for managers and admin users
                            only
                        </p>
                    </div>
                </div>
            </template>
        </div>
    </AppLayout>
</template>
