<script setup lang="ts">
import RequestsTable from '@/components/RequestsTable.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { FileText } from 'lucide-vue-next';
import { computed, ref } from 'vue';

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

// Fake user role - change this to test different views
const userRole = ref<'employee' | 'manager' | 'admin'>('manager');

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
        status: 'pending',
        employeeName: 'Alice Brown',
        department: 'Engineering',
    },
    {
        id: '6',
        startDate: new Date(2026, 2, 1),
        endDate: new Date(2026, 2, 5),
        type: 'vacation',
        status: 'rejected',
        employeeName: 'Charlie Wilson',
        department: 'HR',
    },
]);

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Requests', href: '/requests' },
];

// Filter requests based on role
const visibleRequests = computed(() => {
    return userRole.value === 'employee'
        ? requests.value.filter((r) => r.employeeName === 'John Doe')
        : requests.value;
});

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
</script>

<template>
    <Head title="Requests" />

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
        <div class="relative flex h-full flex-1 flex-col gap-6 overflow-hidden p-6">
            <!-- Enhanced Header -->
            <div class="flex items-center gap-4">
                <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-orange-400 to-rose-500 shadow-2xl shadow-orange-500/30">
                    <FileText class="h-8 w-8 text-white" />
                </div>
                <div>
                    <h1 class="text-4xl font-bold tracking-tight text-white">
                        Requests
                    </h1>
                    <p class="mt-1.5 text-sm text-white/70">
                        Manage all time off requests across your team
                    </p>
                </div>
            </div>

            <!-- Glass-morphism content card -->
            <div
                class="flex-1 overflow-auto rounded-3xl border border-white/20 bg-white/10 p-6 shadow-2xl backdrop-blur-xl"
            >
                <RequestsTable
                    :requests="visibleRequests"
                    :user-role="userRole"
                    @approve="handleApprove"
                    @reject="handleReject"
                />
            </div>
        </div>
    </AppLayout>
</template>
