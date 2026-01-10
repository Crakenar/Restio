<script setup lang="ts">
import AdminDashboard from '@/components/dashboards/AdminDashboard.vue';
import EmployeeDashboard from '@/components/dashboards/EmployeeDashboard.vue';
import ManagerDashboard from '@/components/dashboards/ManagerDashboard.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import type { Employee, VacationRequest } from '@/types/vacation';
import { Head } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

// TODO: Replace with actual user data from auth
const userRole = ref<'employee' | 'manager' | 'admin'>('employee');

// ============================================
// FAKE DATA - Replace with real API calls later
// ============================================
const requests = ref<VacationRequest[]>([
    {
        id: '1',
        startDate: new Date(2026, 0, 20),
        endDate: new Date(2026, 0, 24),
        type: 'vacation',
        status: 'approved',
        employeeName: 'John Doe',
        department: 'Engineering',
    },
    {
        id: '2',
        startDate: new Date(2026, 1, 5),
        endDate: new Date(2026, 1, 7),
        type: 'sick',
        status: 'pending',
        employeeName: 'Jane Smith',
        department: 'Marketing',
    },
    {
        id: '3',
        startDate: new Date(2026, 1, 14),
        endDate: new Date(2026, 1, 14),
        type: 'personal',
        status: 'approved',
        employeeName: 'John Doe',
        department: 'Engineering',
    },
    {
        id: '4',
        startDate: new Date(2026, 0, 15),
        endDate: new Date(2026, 0, 15),
        type: 'wfh',
        status: 'approved',
        employeeName: 'Bob Johnson',
        department: 'Sales',
    },
    {
        id: '5',
        startDate: new Date(2026, 1, 10),
        endDate: new Date(2026, 1, 12),
        type: 'vacation',
        status: 'approved',
        employeeName: 'Alice Brown',
        department: 'Engineering',
    },
    {
        id: '6',
        startDate: new Date(2026, 1, 20),
        endDate: new Date(2026, 1, 22),
        type: 'vacation',
        status: 'pending',
        employeeName: 'Charlie Wilson',
        department: 'HR',
    },
    {
        id: '7',
        startDate: new Date(2026, 0, 25),
        endDate: new Date(2026, 0, 25),
        type: 'sick',
        status: 'rejected',
        employeeName: 'Diana Prince',
        department: 'Engineering',
    },
]);

const employees = ref<Employee[]>([
    {
        id: '1',
        name: 'John Doe',
        email: 'john.doe@company.com',
        department: 'Engineering',
        totalDays: 25,
        usedDays: 8,
        pendingRequests: 0,
    },
    {
        id: '2',
        name: 'Jane Smith',
        email: 'jane.smith@company.com',
        department: 'Marketing',
        totalDays: 25,
        usedDays: 5,
        pendingRequests: 1,
    },
    {
        id: '3',
        name: 'Bob Johnson',
        email: 'bob.johnson@company.com',
        department: 'Sales',
        totalDays: 20,
        usedDays: 3,
        pendingRequests: 0,
    },
    {
        id: '4',
        name: 'Alice Brown',
        email: 'alice.brown@company.com',
        department: 'Engineering',
        totalDays: 25,
        usedDays: 6,
        pendingRequests: 0,
    },
    {
        id: '5',
        name: 'Charlie Wilson',
        email: 'charlie.wilson@company.com',
        department: 'HR',
        totalDays: 25,
        usedDays: 10,
        pendingRequests: 0,
    },
    {
        id: '6',
        name: 'Diana Prince',
        email: 'diana.prince@company.com',
        department: 'Engineering',
        totalDays: 25,
        usedDays: 4,
        pendingRequests: 0,
    },
    {
        id: '7',
        name: 'Evan Davis',
        email: 'evan.davis@company.com',
        department: 'Marketing',
        totalDays: 25,
        usedDays: 7,
        pendingRequests: 0,
    },
    {
        id: '8',
        name: 'Fiona Green',
        email: 'fiona.green@company.com',
        department: 'Sales',
        totalDays: 20,
        usedDays: 2,
        pendingRequests: 0,
    },
]);
// ============================================

// Handlers
const handleCreateRequest = (
    newRequest: Omit<VacationRequest, 'id' | 'status'>,
) => {
    const request: VacationRequest = {
        ...newRequest,
        id: Date.now().toString(),
        status: 'pending',
        employeeName:
            userRole.value === 'employee'
                ? 'John Doe'
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
        case 'admin':
            return 'Admin Dashboard';
        case 'manager':
            return 'Manager Dashboard';
        default:
            return 'My Dashboard';
    }
});
</script>

<template>
    <Head :title="pageTitle" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <!-- Bold dark gradient background matching auth pages -->
        <div class="absolute inset-0 -z-10 bg-gradient-to-br from-slate-950 via-blue-950 to-indigo-950" />

        <!-- Animated gradient orbs -->
        <div class="pointer-events-none absolute inset-0 -z-10 overflow-hidden">
            <div
                class="absolute -top-1/2 -right-1/2 h-full w-full animate-pulse rounded-full bg-gradient-to-br from-orange-500/20 via-amber-500/20 to-yellow-500/20 blur-3xl"
                style="animation-duration: 8s"
            />
            <div
                class="absolute -bottom-1/2 -left-1/2 h-full w-full animate-pulse rounded-full bg-gradient-to-tr from-blue-500/20 via-teal-500/20 to-emerald-500/20 blur-3xl"
                style="animation-duration: 10s; animation-delay: 1s"
            />
        </div>

        <!-- Content -->
        <div class="relative flex h-full flex-1 flex-col overflow-hidden">
            <div class="flex-1 overflow-auto p-6">
                <!-- Employee View -->
                <EmployeeDashboard
                    v-if="userRole === 'employee'"
                    :requests="requests"
                    :total-days-allowed="25"
                    user-name="John Doe"
                    @create-request="handleCreateRequest"
                />

                <!-- Manager View -->
                <ManagerDashboard
                    v-else-if="userRole === 'manager'"
                    :requests="requests"
                    @approve="handleApprove"
                    @reject="handleReject"
                />

                <!-- Admin View -->
                <AdminDashboard
                    v-else-if="userRole === 'admin'"
                    :requests="requests"
                    :employees="employees"
                    @select-employee="handleSelectEmployee"
                    @approve="handleApprove"
                    @reject="handleReject"
                />
            </div>
        </div>

        <!-- Development Only: Role Switcher with glass-morphism -->
        <!-- TODO: Remove this in production - role should come from auth -->
        <div
            class="fixed bottom-6 right-6 z-50 flex gap-2 rounded-3xl border border-white/20 bg-white/10 p-2 shadow-2xl backdrop-blur-xl"
        >
            <button
                @click="userRole = 'employee'"
                :class="[
                    'rounded-xl px-4 py-2 text-sm font-semibold transition-all duration-200',
                    userRole === 'employee'
                        ? 'bg-gradient-to-r from-orange-500 to-rose-500 text-white shadow-lg shadow-orange-500/30'
                        : 'text-white/70 hover:bg-white/10 hover:text-white',
                ]"
            >
                Employee
            </button>
            <button
                @click="userRole = 'manager'"
                :class="[
                    'rounded-xl px-4 py-2 text-sm font-semibold transition-all duration-200',
                    userRole === 'manager'
                        ? 'bg-gradient-to-r from-orange-500 to-rose-500 text-white shadow-lg shadow-orange-500/30'
                        : 'text-white/70 hover:bg-white/10 hover:text-white',
                ]"
            >
                Manager
            </button>
            <button
                @click="userRole = 'admin'"
                :class="[
                    'rounded-xl px-4 py-2 text-sm font-semibold transition-all duration-200',
                    userRole === 'admin'
                        ? 'bg-gradient-to-r from-orange-500 to-rose-500 text-white shadow-lg shadow-orange-500/30'
                        : 'text-white/70 hover:bg-white/10 hover:text-white',
                ]"
            >
                Admin
            </button>
        </div>
    </AppLayout>
</template>
