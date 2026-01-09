<script setup lang="ts">
import { ref, computed } from 'vue'
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import RequestsTable from '@/components/RequestsTable.vue'
import type { BreadcrumbItem } from '@/types'

interface VacationRequest {
    id: string
    startDate: Date
    endDate: Date
    type: 'vacation' | 'sick' | 'personal' | 'unpaid' | 'wfh'
    status: 'pending' | 'approved' | 'rejected'
    reason?: string
    employeeName?: string
    department?: string
}

// Fake user role - change this to test different views
const userRole = ref<'employee' | 'manager' | 'admin'>('manager')

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
])

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Requests', href: '/requests' },
]

// Filter requests based on role
const visibleRequests = computed(() => {
    return userRole.value === 'employee'
        ? requests.value.filter((r) => r.employeeName === 'John Doe')
        : requests.value
})

const handleApprove = (id: string) => {
    requests.value = requests.value.map((req) =>
        req.id === id ? { ...req, status: 'approved' as const } : req
    )
}

const handleReject = (id: string) => {
    requests.value = requests.value.map((req) =>
        req.id === id ? { ...req, status: 'rejected' as const } : req
    )
}
</script>

<template>
    <Head title="Requests" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <div class="mb-2">
                <h1 class="text-2xl font-semibold">Requests</h1>
                <p class="mt-1 text-sm text-muted-foreground">
                    Manage all time off requests
                </p>
            </div>

            <RequestsTable
                :requests="visibleRequests"
                :user-role="userRole"
                @approve="handleApprove"
                @reject="handleReject"
            />
        </div>
    </AppLayout>
</template>
