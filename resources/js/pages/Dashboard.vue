<script setup lang="ts">
import AdminDashboard from '@/components/dashboards/AdminDashboard.vue';
import HybridDashboard from '@/components/dashboards/HybridDashboard.vue';
import ManagerDashboard from '@/components/dashboards/ManagerDashboard.vue';
import PremiumSidebar from '@/components/PremiumSidebar.vue';
import type { BreadcrumbItem } from '@/types';
import { Employee, UserRole, VacationRequest } from '@/types/vacation';
import { Head } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

// Define props received from Inertia controller
interface Props {
    requests: VacationRequest[];
    employees: Employee[];
    userRole: UserRole;
    userName: string;
    totalDaysAllowed: number;
    notificationCount?: number;
}

const props = withDefaults(defineProps<Props>(), {
    notificationCount: 0,
});

// Convert props to refs for reactivity (dates need to be converted)
const requests = ref<VacationRequest[]>(
    props.requests.map((req) => ({
        ...req,
        startDate: new Date(req.startDate),
        endDate: new Date(req.endDate),
    })),
);

const employees = ref<Employee[]>(props.employees);

// TODO: Remove this in production - role should come from auth
// For now, we allow role switching for development purposes
const userRole = ref<UserRole>(props.userRole);

// Handlers
const handleCreateRequest = (
    newRequest: Omit<VacationRequest, 'id' | 'status'>,
) => {
    const request: VacationRequest = {
        ...newRequest,
        id: Date.now().toString(),
        status: 'pending',
        employeeName:
            userRole.value === UserRole.EMPLOYEE
                ? props.userName
                : newRequest.employeeName,
    };
    requests.value = [...requests.value, request];
};

const handleApprove = (id: string) => {
    requests.value = requests.value.map((req) =>
        req.id === id ? { ...req, status: 'approved' as const } : req,
    );
};

const handleReject = (id: string) => {
    requests.value = requests.value.map((req) =>
        req.id === id ? { ...req, status: 'rejected' as const } : req,
    );
};

const handleSelectEmployee = (id: string) => {
    console.log('Selected employee:', id);
};

// Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
];

// Page title based on role
const pageTitle = computed(() => {
    switch (userRole.value) {
        case UserRole.ADMIN:
            return 'Admin Dashboard';
        case UserRole.MANAGER:
            return 'Manager Dashboard';
        default:
            return 'My Dashboard';
    }
});
</script>

<template>
    <Head :title="pageTitle" />

    <!-- Employee View - uses HybridDashboard with Premium Sidebar -->
    <HybridDashboard
        v-if="userRole === UserRole.EMPLOYEE"
        :requests="requests"
        :total-days-allowed="props.totalDaysAllowed"
        :user-name="props.userName"
        :notification-count="props.notificationCount"
    />

    <!-- Manager and Admin Views - use Premium Sidebar -->
    <div v-else class="flex min-h-screen bg-gradient-to-br from-slate-50 via-orange-50 to-rose-50 dark:from-slate-950 dark:via-orange-950 dark:to-rose-950">
        <!-- Sidebar -->
        <PremiumSidebar />

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
            <div class="relative mx-auto max-w-7xl">
                <!-- Manager View -->
                <ManagerDashboard
                    v-if="userRole === UserRole.MANAGER"
                    :requests="requests"
                    @approve="handleApprove"
                    @reject="handleReject"
                />

                <!-- Admin View -->
                <AdminDashboard
                    v-else-if="userRole === UserRole.ADMIN"
                    :requests="requests"
                    :employees="employees"
                    @select-employee="handleSelectEmployee"
                    @approve="handleApprove"
                    @reject="handleReject"
                />
            </div>

            <!-- Development Only: Role Switcher with theme-aware styling -->
            <!-- TODO: Remove this in production - role should come from auth -->
            <div
                class="fixed right-6 bottom-6 z-50 flex gap-2 rounded-3xl border border-slate-200 bg-white/90 p-2 shadow-2xl backdrop-blur-xl dark:border-white/20 dark:bg-white/10"
            >
                <button
                    v-if="userRole === 'employee'"
                    :class="[
                        'rounded-xl px-4 py-2 text-sm font-semibold transition-all duration-200',
                        userRole === 'employee'
                            ? 'bg-gradient-to-r from-orange-500 to-rose-500 text-white shadow-lg shadow-orange-500/30'
                            : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-white/70 dark:hover:bg-white/10 dark:hover:text-white',
                    ]"
                >
                    Employee
                </button>
                <button
                    v-else-if="userRole === 'manager'"
                    :class="[
                        'rounded-xl px-4 py-2 text-sm font-semibold transition-all duration-200',
                        userRole === 'manager'
                            ? 'bg-gradient-to-r from-orange-500 to-rose-500 text-white shadow-lg shadow-orange-500/30'
                            : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-white/70 dark:hover:bg-white/10 dark:hover:text-white',
                    ]"
                >
                    Manager
                </button>
                <button
                    v-else-if="userRole === 'admin'"
                    :class="[
                        'rounded-xl px-4 py-2 text-sm font-semibold transition-all duration-200',
                        userRole === 'admin'
                            ? 'bg-gradient-to-r from-orange-500 to-rose-500 text-white shadow-lg shadow-orange-500/30'
                            : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-white/70 dark:hover:bg-white/10 dark:hover:text-white',
                    ]"
                >
                    Admin
                </button>
            </div>
        </div>
    </div>
</template>
