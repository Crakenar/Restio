<script setup lang="ts">
import AdminDashboard from '@/components/AdminDashboard.vue';
import TeamCalendar from '@/components/TeamCalendar.vue';
import PremiumSidebar from '@/components/PremiumSidebar.vue';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { UserRole, VacationRequestStatus, VacationRequestType } from '@/enums';
import { Head, usePage } from '@inertiajs/vue3';

const page = usePage();
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
    totalEmployeesExcludingOwners: number;
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

const handleSelectEmployee = (id: string) => {
    console.log('Selected employee:', id);
};
</script>

<template>
    <Head title="Team" />

    <div class="flex min-h-screen bg-gradient-to-br from-slate-50 via-orange-50 to-rose-50 dark:from-slate-950 dark:via-orange-950 dark:to-rose-950">
        <!-- Sidebar -->
        <PremiumSidebar :notifications="$page.props.notifications || []" />

        <!-- Main content area -->
        <div class="ml-72 flex-1 p-4 transition-all duration-500 sm:p-6 lg:p-8">
            <!-- Animated gradient orbs -->
            <div class="pointer-events-none fixed inset-0 overflow-hidden">
                <div
                    class="absolute -top-1/2 -right-1/2 h-full w-full animate-pulse rounded-full bg-gradient-to-br from-orange-500/10 via-amber-500/10 to-yellow-500/10 blur-3xl dark:from-orange-500/20 dark:via-amber-500/20 dark:to-yellow-500/20"
                    style="animation-duration: 8s"
                />
                <div
                    class="absolute -bottom-1/2 -left-1/2 h-full w-full animate-pulse rounded-full bg-gradient-to-tr from-rose-500/10 via-pink-500/10 to-red-500/10 blur-3xl dark:from-rose-500/20 dark:via-pink-500/20 dark:to-red-500/20"
                    style="animation-duration: 10s; animation-delay: 1s"
                />
            </div>

            <!-- Content -->
            <div class="relative mx-auto max-w-7xl space-y-6">
            <!-- Enhanced Header -->
            <div class="flex items-center gap-4">
                <div
                    class="flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-orange-400 to-rose-500 shadow-2xl shadow-orange-500/30"
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
                    class="flex flex-1 flex-col space-y-6"
                >
                    <!-- Enhanced Tab List -->
                    <div class="relative">
                        <!-- Gradient background glow -->
                        <div class="pointer-events-none absolute inset-0 -z-10 rounded-3xl bg-gradient-to-r from-orange-500/20 via-amber-500/20 to-rose-500/20 blur-2xl" />

                        <TabsList class="relative inline-flex gap-2 rounded-2xl border border-white/40 bg-white/80 p-2 shadow-2xl backdrop-blur-2xl dark:border-white/20 dark:bg-slate-900/60">
                            <TabsTrigger
                                value="calendar"
                                class="group relative overflow-hidden rounded-xl px-6 py-3 font-semibold text-slate-600 transition-all duration-300 hover:text-slate-900 data-[state=active]:text-white dark:text-slate-300 dark:hover:text-white dark:data-[state=active]:text-white"
                            >
                                <!-- Active gradient background -->
                                <div class="absolute inset-0 bg-gradient-to-r from-orange-500 to-rose-500 opacity-0 shadow-lg transition-all duration-300 group-data-[state=active]:opacity-100 group-data-[state=active]:shadow-orange-500/50" />

                                <!-- Content -->
                                <span class="relative z-10">Coverage Calendar</span>
                            </TabsTrigger>

                            <TabsTrigger
                                value="employees"
                                class="group relative overflow-hidden rounded-xl px-6 py-3 font-semibold text-slate-600 transition-all duration-300 hover:text-orange-700 data-[state=active]:text-white dark:text-slate-300 dark:hover:text-orange-400 dark:data-[state=active]:text-white"
                            >
                                <!-- Active gradient background -->
                                <div class="absolute inset-0 bg-gradient-to-r from-amber-500 to-orange-600 opacity-0 shadow-lg transition-all duration-300 group-data-[state=active]:opacity-100 group-data-[state=active]:shadow-amber-500/50" />

                                <!-- Content -->
                                <span class="relative z-10">Employees</span>
                            </TabsTrigger>
                        </TabsList>
                    </div>

                    <TabsContent
                        value="calendar"
                        class="relative flex-1 overflow-hidden rounded-3xl border border-white/40 bg-white/70 shadow-2xl backdrop-blur-2xl dark:border-white/20 dark:bg-slate-900/40"
                    >
                        <!-- Animated gradient overlay -->
                        <div class="pointer-events-none absolute inset-0 overflow-hidden opacity-30">
                            <div
                                class="absolute -top-1/4 -right-1/4 h-1/2 w-1/2 animate-pulse rounded-full bg-gradient-to-br from-orange-500/20 via-amber-500/20 to-rose-500/20 blur-3xl"
                                style="animation-duration: 8s"
                            />
                        </div>

                        <div class="relative p-8">
                            <TeamCalendar
                                :requests="requests"
                                :total-employees="totalEmployeesExcludingOwners"
                            />
                        </div>
                    </TabsContent>

                    <TabsContent
                        value="employees"
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
                            <AdminDashboard
                                :employees="employees"
                                :requests="requests"
                                @select-employee="handleSelectEmployee"
                            />
                        </div>
                    </TabsContent>
                </Tabs>
            </template>

            <!-- Manager View -->
            <template v-else-if="userRole === UserRole.MANAGER">
                <div
                    class="relative flex-1 overflow-hidden rounded-3xl border border-white/40 bg-white/70 shadow-2xl backdrop-blur-2xl dark:border-white/20 dark:bg-slate-900/40"
                >
                    <!-- Animated gradient overlay -->
                    <div class="pointer-events-none absolute inset-0 overflow-hidden opacity-30">
                        <div
                            class="absolute -top-1/4 -right-1/4 h-1/2 w-1/2 animate-pulse rounded-full bg-gradient-to-br from-orange-500/20 via-amber-500/20 to-rose-500/20 blur-3xl"
                            style="animation-duration: 8s"
                        />
                    </div>

                    <div class="relative p-8">
                        <TeamCalendar
                            :requests="requests"
                            :total-employees="totalEmployeesExcludingOwners"
                        />
                    </div>
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
        </div>
    </div>
</template>
