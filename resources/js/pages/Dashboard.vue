<script setup lang="ts">
import { ref, computed } from 'vue'
import { Head } from '@inertiajs/vue3'
import { CalendarDays, Clock, CheckCircle2, User, Users, Building2 } from 'lucide-vue-next'
import AppLayout from '@/layouts/AppLayout.vue'
import VacationStatCard from '@/components/VacationStatCard.vue'
import VacationCalendar from '@/components/VacationCalendar.vue'
import RequestsTable from '@/components/RequestsTable.vue'
import AdminDashboard from '@/components/AdminDashboard.vue'
import TeamCalendar from '@/components/TeamCalendar.vue'
import { Button } from '@/components/ui/button'
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs'
import type { BreadcrumbItem } from '@/types'

// Types
interface VacationRequest {
    id: string
    startDate: Date
    endDate: Date
    type: 'vacation' | 'sick' | 'personal' | 'unpaid' | 'wfh'
    status: 'pending' | 'approved' | 'rejected'
    reason?: string
    employeeName?: string
    document?: File
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

// State
const userRole = ref<'employee' | 'manager' | 'admin'>('employee')
const activeView = ref('dashboard')

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
// ============================================

// Handlers
const handleCreateRequest = (newRequest: Omit<VacationRequest, 'id' | 'status'>) => {
    const request: VacationRequest = {
        ...newRequest,
        id: Date.now().toString(),
        status: 'pending',
        employeeName: userRole.value === 'employee' ? 'John Doe' : newRequest.employeeName,
    }
    requests.value = [...requests.value, request]
}

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

const handleSelectEmployee = (id: string) => {
    console.log('Selected employee:', id)
}

// Computed
const visibleRequests = computed(() => {
    return userRole.value === 'employee'
        ? requests.value.filter((r) => r.employeeName === 'John Doe')
        : requests.value
})

const myApprovedRequests = computed(() => {
    return requests.value.filter((r) => r.employeeName === 'John Doe' && r.status === 'approved')
})

const totalDaysUsed = computed(() => {
    return myApprovedRequests.value.reduce((acc, req) => {
        const days = Math.ceil((req.endDate.getTime() - req.startDate.getTime()) / (1000 * 60 * 60 * 24)) + 1
        return acc + days
    }, 0)
})

const totalDaysAllowed = 25
const daysRemaining = computed(() => totalDaysAllowed - totalDaysUsed.value)
const pendingRequests = computed(() => requests.value.filter((r) => r.status === 'pending'))
const lastRequest = computed(() => requests.value[requests.value.length - 1])

const lastRequestStatus = computed(() => {
    if (!lastRequest.value) return 'No requests'
    return lastRequest.value.status.charAt(0).toUpperCase() + lastRequest.value.status.slice(1)
})

const lastRequestSubtitle = computed(() => {
    if (!lastRequest.value) return 'No requests'
    const type = lastRequest.value.type
    return `${type.charAt(0).toUpperCase() + type.slice(1)} leave`
})

const lastRequestColor = computed(() => {
    if (!lastRequest.value) return 'text-gray-600'
    switch (lastRequest.value.status) {
        case 'approved': return 'text-green-600'
        case 'pending': return 'text-yellow-600'
        case 'rejected': return 'text-red-600'
        default: return 'text-gray-600'
    }
})

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
]
</script>

<template>
    <Head title="Vacation Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col overflow-hidden">
            <!-- Header -->
            <div class="border-b bg-card">
                <div class="px-6 py-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-2xl font-semibold">
                                {{ activeView === 'dashboard' ? 'Dashboard' : '' }}
                                {{ activeView === 'requests' ? 'Requests' : '' }}
                                {{ activeView === 'team' ? 'Team' : '' }}
                            </h1>
                            <p class="mt-1 text-sm text-muted-foreground">
                                {{ activeView === 'dashboard' ? 'Overview of your vacation and time off' : '' }}
                                {{ activeView === 'requests' ? 'Manage all time off requests' : '' }}
                                {{ activeView === 'team' ? 'View and manage team absences' : '' }}
                            </p>
                        </div>
                        <!-- Role Switcher -->
                        <div class="flex gap-2">
                            <Button
                                :variant="userRole === 'employee' ? 'default' : 'outline'"
                                size="sm"
                                @click="userRole = 'employee'"
                            >
                                <User class="mr-2 h-4 w-4" />
                                Employee
                            </Button>
                            <Button
                                :variant="userRole === 'manager' ? 'default' : 'outline'"
                                size="sm"
                                @click="userRole = 'manager'"
                            >
                                <Users class="mr-2 h-4 w-4" />
                                Manager
                            </Button>
                            <Button
                                :variant="userRole === 'admin' ? 'default' : 'outline'"
                                size="sm"
                                @click="userRole = 'admin'"
                            >
                                <Building2 class="mr-2 h-4 w-4" />
                                Admin
                            </Button>
                        </div>
                    </div>

                    <!-- View Tabs -->
                    <div class="mt-4 flex gap-2">
                        <Button
                            :variant="activeView === 'dashboard' ? 'default' : 'ghost'"
                            size="sm"
                            @click="activeView = 'dashboard'"
                        >
                            Dashboard
                        </Button>
                        <Button
                            :variant="activeView === 'requests' ? 'default' : 'ghost'"
                            size="sm"
                            @click="activeView = 'requests'"
                        >
                            Requests
                        </Button>
                        <Button
                            v-if="userRole === 'manager' || userRole === 'admin'"
                            :variant="activeView === 'team' ? 'default' : 'ghost'"
                            size="sm"
                            @click="activeView = 'team'"
                        >
                            Team
                        </Button>
                    </div>
                </div>
            </div>

            <!-- Content Area -->
            <div class="flex-1 overflow-auto p-6">
                <!-- Dashboard View -->
                <div v-if="activeView === 'dashboard'" class="space-y-6">
                    <div class="grid gap-4 md:grid-cols-3">
                        <VacationStatCard
                            title="Last Request"
                            :value="lastRequestStatus"
                            :subtitle="lastRequestSubtitle"
                            :icon="CheckCircle2"
                            :icon-color="lastRequestColor"
                        />
                        <VacationStatCard
                            title="Days Remaining"
                            :value="daysRemaining"
                            :subtitle="`Out of ${totalDaysAllowed} days this year`"
                            :icon="CalendarDays"
                            icon-color="text-blue-600"
                        />
                        <VacationStatCard
                            :title="userRole === 'employee' ? 'Pending Requests' : 'Team Pending'"
                            :value="pendingRequests.length"
                            :subtitle="userRole === 'employee' ? 'Awaiting approval' : 'Requires your review'"
                            :icon="Clock"
                            icon-color="text-purple-600"
                        />
                    </div>

                    <VacationCalendar
                        :existing-requests="visibleRequests"
                        :user-role="userRole"
                        @create-request="handleCreateRequest"
                    />
                </div>

                <!-- Requests View -->
                <div v-if="activeView === 'requests'" class="space-y-4">
                    <RequestsTable
                        :requests="visibleRequests"
                        :user-role="userRole"
                        @approve="handleApprove"
                        @reject="handleReject"
                    />
                </div>

                <!-- Team View -->
                <div v-if="activeView === 'team'" class="space-y-4">
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

                    <template v-else-if="userRole === 'manager'">
                        <TeamCalendar :requests="requests" />
                    </template>

                    <template v-else>
                        <div class="py-12 text-center text-muted-foreground">
                            <Users class="mx-auto mb-4 h-12 w-12 opacity-50" />
                            <p>Team view available for admin users only</p>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
