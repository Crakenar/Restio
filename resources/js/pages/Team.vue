<script setup lang="ts">
import { ref } from 'vue'
import { Head } from '@inertiajs/vue3'
import { Users } from 'lucide-vue-next'
import AppLayout from '@/layouts/AppLayout.vue'
import TeamCalendar from '@/components/TeamCalendar.vue'
import AdminDashboard from '@/components/AdminDashboard.vue'
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs'
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

interface Employee {
    id: string
    name: string
    email: string
    department: string
    totalDays: number
    usedDays: number
    pendingRequests: number
}

// Fake user role - change this to test different views
const userRole = ref<'employee' | 'manager' | 'admin'>('admin')

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
])

const employees = ref<Employee[]>([
    { id: '1', name: 'John Doe', email: 'john.doe@company.com', department: 'Engineering', totalDays: 25, usedDays: 8, pendingRequests: 0 },
    { id: '2', name: 'Jane Smith', email: 'jane.smith@company.com', department: 'Marketing', totalDays: 25, usedDays: 5, pendingRequests: 1 },
    { id: '3', name: 'Bob Johnson', email: 'bob.johnson@company.com', department: 'Sales', totalDays: 20, usedDays: 3, pendingRequests: 0 },
    { id: '4', name: 'Alice Brown', email: 'alice.brown@company.com', department: 'Engineering', totalDays: 25, usedDays: 6, pendingRequests: 0 },
    { id: '5', name: 'Charlie Wilson', email: 'charlie.wilson@company.com', department: 'HR', totalDays: 25, usedDays: 10, pendingRequests: 0 },
    { id: '6', name: 'Diana Prince', email: 'diana.prince@company.com', department: 'Engineering', totalDays: 25, usedDays: 4, pendingRequests: 0 },
    { id: '7', name: 'Evan Davis', email: 'evan.davis@company.com', department: 'Marketing', totalDays: 25, usedDays: 7, pendingRequests: 0 },
    { id: '8', name: 'Fiona Green', email: 'fiona.green@company.com', department: 'Sales', totalDays: 20, usedDays: 2, pendingRequests: 0 },
])

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Team', href: '/team' },
]

const handleSelectEmployee = (id: string) => {
    console.log('Selected employee:', id)
}
</script>

<template>
    <Head title="Team" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <div class="mb-2">
                <h1 class="text-2xl font-semibold">Team</h1>
                <p class="mt-1 text-sm text-muted-foreground">
                    View and manage team absences
                </p>
            </div>

            <!-- Admin View -->
            <template v-if="userRole === 'admin'">
                <Tabs default-value="calendar" class="space-y-4">
                    <TabsList>
                        <TabsTrigger value="calendar">Coverage Calendar</TabsTrigger>
                        <TabsTrigger value="employees">Employees</TabsTrigger>
                    </TabsList>

                    <TabsContent value="calendar">
                        <TeamCalendar :requests="requests" />
                    </TabsContent>

                    <TabsContent value="employees">
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
                <TeamCalendar :requests="requests" />
            </template>

            <!-- Employee View (no access) -->
            <template v-else>
                <div class="py-12 text-center text-muted-foreground">
                    <Users class="mx-auto mb-4 h-12 w-12 opacity-50" />
                    <p>Team view available for managers and admin users only</p>
                </div>
            </template>
        </div>
    </AppLayout>
</template>
