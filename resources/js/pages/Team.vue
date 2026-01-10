<script setup lang="ts">
import AdminDashboard from '@/components/AdminDashboard.vue';
import TeamCalendar from '@/components/TeamCalendar.vue';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { Users } from 'lucide-vue-next';
import { ref } from 'vue';

interface VacationRequest {
    id: string;
    startDate: Date;
    endDate: Date;
    type: 'vacation' | 'sick' | 'personal' | 'unpaid' | 'wfh';
    status: 'pending' | 'approved' | 'rejected';
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

// Fake user role - change this to test different views
const userRole = ref<'employee' | 'manager' | 'admin'>('admin');

// Fake data
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
        startDate: new Date(2026, 0, 22),
        endDate: new Date(2026, 0, 23),
        type: 'vacation',
        status: 'approved',
        employeeName: 'Diana Prince',
        department: 'Engineering',
    },
    {
        id: '7',
        startDate: new Date(2026, 0, 21),
        endDate: new Date(2026, 0, 21),
        type: 'wfh',
        status: 'approved',
        employeeName: 'Evan Davis',
        department: 'Marketing',
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
        <!-- Bold dark gradient background matching auth pages -->
        <div class="absolute inset-0 -z-10 bg-gradient-to-br from-slate-950 via-blue-950 to-indigo-950" />

        <!-- Animated gradient orbs -->
        <div class="pointer-events-none absolute inset-0 -z-10 overflow-hidden">
            <div
                class="absolute -top-1/2 -right-1/2 h-full w-full animate-pulse rounded-full bg-gradient-to-br from-blue-500/20 via-indigo-500/20 to-purple-500/20 blur-3xl"
                style="animation-duration: 8s"
            />
            <div
                class="absolute -bottom-1/2 -left-1/2 h-full w-full animate-pulse rounded-full bg-gradient-to-tr from-teal-500/20 via-emerald-500/20 to-green-500/20 blur-3xl"
                style="animation-duration: 10s; animation-delay: 1s"
            />
        </div>

        <!-- Content -->
        <div class="relative flex h-full flex-1 flex-col gap-6 overflow-hidden p-6">
            <!-- Enhanced Header -->
            <div class="flex items-center gap-4">
                <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 shadow-2xl shadow-blue-500/30">
                    <Users class="h-8 w-8 text-white" />
                </div>
                <div>
                    <h1 class="text-4xl font-bold tracking-tight text-white">
                        Team
                    </h1>
                    <p class="mt-1.5 text-sm text-white/70">
                        View and manage team absences and coverage
                    </p>
                </div>
            </div>

            <!-- Admin View -->
            <template v-if="userRole === 'admin'">
                <Tabs default-value="calendar" class="flex flex-1 flex-col space-y-4">
                    <TabsList class="w-fit border border-white/20 bg-white/10 shadow-lg backdrop-blur-xl">
                        <TabsTrigger value="calendar" class="data-[state=active]:bg-white/20 data-[state=active]:text-white">Coverage Calendar</TabsTrigger>
                        <TabsTrigger value="employees" class="data-[state=active]:bg-white/20 data-[state=active]:text-white">Employees</TabsTrigger>
                    </TabsList>

                    <TabsContent
                        value="calendar"
                        class="flex-1 overflow-auto rounded-3xl border border-white/20 bg-white/10 p-6 shadow-2xl backdrop-blur-xl"
                    >
                        <TeamCalendar :requests="requests" />
                    </TabsContent>

                    <TabsContent
                        value="employees"
                        class="flex-1 overflow-auto rounded-3xl border border-white/20 bg-white/10 shadow-2xl backdrop-blur-xl"
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
            <template v-else-if="userRole === 'manager'">
                <div class="flex-1 overflow-auto rounded-3xl border border-white/20 bg-white/10 p-6 shadow-2xl backdrop-blur-xl">
                    <TeamCalendar :requests="requests" />
                </div>
            </template>

            <!-- Employee View (no access) -->
            <template v-else>
                <div class="flex flex-1 items-center justify-center rounded-3xl border border-white/20 bg-white/10 p-12 text-center shadow-2xl backdrop-blur-xl">
                    <div>
                        <div class="mx-auto mb-6 flex h-24 w-24 items-center justify-center rounded-3xl bg-white/10">
                            <Users class="h-12 w-12 text-white/50" />
                        </div>
                        <h3 class="mb-2 text-xl font-semibold text-white">
                            Team View Restricted
                        </h3>
                        <p class="text-sm text-white/70">
                            Team view is available for managers and admin users only
                        </p>
                    </div>
                </div>
            </template>
        </div>
    </AppLayout>
</template>
