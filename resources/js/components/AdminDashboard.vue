<script setup lang="ts">
import { ref, computed } from 'vue'
import { Users, Calendar, FileText, TrendingUp } from 'lucide-vue-next'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Avatar, AvatarFallback } from '@/components/ui/avatar'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table'

interface Employee {
    id: string
    name: string
    email: string
    department: string
    totalDays: number
    usedDays: number
    pendingRequests: number
}

interface VacationRequest {
    id: string
    startDate: Date
    endDate: Date
    type: 'vacation' | 'sick' | 'personal' | 'unpaid' | 'wfh'
    status: 'pending' | 'approved' | 'rejected'
    reason?: string
    employeeName?: string
    document?: File
}

const props = defineProps<{
    employees: Employee[]
    requests: VacationRequest[]
}>()

const emit = defineEmits<{
    selectEmployee: [employeeId: string]
}>()

const selectedEmployee = ref<string | null>(null)

const getInitials = (name: string) => {
    return name
        .split(' ')
        .map((n) => n[0])
        .join('')
        .toUpperCase()
}

const getEmployeeRequests = (employeeName: string) => {
    return props.requests.filter((r) => r.employeeName === employeeName)
}

const totalPending = computed(() => props.requests.filter((r) => r.status === 'pending').length)

const totalApproved = computed(() => props.requests.filter((r) => r.status === 'approved').length)

const avgDaysUsed = computed(() => {
    if (props.employees.length === 0) return 0
    return props.employees.reduce((acc, emp) => acc + emp.usedDays, 0) / props.employees.length
})

const handleSelectEmployee = (employeeId: string) => {
    selectedEmployee.value = employeeId
    emit('selectEmployee', employeeId)
}
</script>

<template>
    <div class="space-y-6">
        <!-- Admin Stats -->
        <div class="grid gap-4 md:grid-cols-4">
            <Card>
                <CardContent class="p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-muted-foreground">Total Employees</p>
                            <p class="mt-1 text-2xl font-semibold">{{ employees.length }}</p>
                        </div>
                        <Users class="h-8 w-8 text-blue-600 opacity-80" />
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardContent class="p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-muted-foreground">Pending Requests</p>
                            <p class="mt-1 text-2xl font-semibold">{{ totalPending }}</p>
                        </div>
                        <FileText class="h-8 w-8 text-yellow-600 opacity-80" />
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardContent class="p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-muted-foreground">Approved This Month</p>
                            <p class="mt-1 text-2xl font-semibold">{{ totalApproved }}</p>
                        </div>
                        <Calendar class="h-8 w-8 text-green-600 opacity-80" />
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardContent class="p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-muted-foreground">Avg Days Used</p>
                            <p class="mt-1 text-2xl font-semibold">{{ avgDaysUsed.toFixed(1) }}</p>
                        </div>
                        <TrendingUp class="h-8 w-8 text-purple-600 opacity-80" />
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Employees Table -->
        <Card>
            <CardHeader>
                <CardTitle>All Employees</CardTitle>
            </CardHeader>
            <CardContent>
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Employee</TableHead>
                            <TableHead>Department</TableHead>
                            <TableHead>Days Used</TableHead>
                            <TableHead>Days Remaining</TableHead>
                            <TableHead>Pending</TableHead>
                            <TableHead class="text-right">Actions</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="employee in employees" :key="employee.id">
                            <TableCell>
                                <div class="flex items-center gap-3">
                                    <Avatar class="h-8 w-8">
                                        <AvatarFallback class="text-xs">
                                            {{ getInitials(employee.name) }}
                                        </AvatarFallback>
                                    </Avatar>
                                    <div>
                                        <p class="font-medium">{{ employee.name }}</p>
                                        <p class="text-xs text-muted-foreground">
                                            {{ employee.email }}
                                        </p>
                                    </div>
                                </div>
                            </TableCell>
                            <TableCell>{{ employee.department }}</TableCell>
                            <TableCell>
                                <div class="flex items-center gap-2">
                                    <div class="h-2 w-24 overflow-hidden rounded-full bg-muted">
                                        <div
                                            class="h-full bg-blue-600"
                                            :style="{
                                                width: `${(employee.usedDays / employee.totalDays) * 100}%`,
                                            }"
                                        />
                                    </div>
                                    <span class="text-sm">
                                        {{ employee.usedDays }}/{{ employee.totalDays }}
                                    </span>
                                </div>
                            </TableCell>
                            <TableCell>
                                <Badge
                                    :variant="
                                        employee.totalDays - employee.usedDays < 5
                                            ? 'destructive'
                                            : 'secondary'
                                    "
                                >
                                    {{ employee.totalDays - employee.usedDays }} days
                                </Badge>
                            </TableCell>
                            <TableCell>
                                <Badge
                                    v-if="employee.pendingRequests > 0"
                                    variant="outline"
                                    class="bg-yellow-500/10 text-yellow-700 dark:text-yellow-400"
                                >
                                    {{ employee.pendingRequests }}
                                </Badge>
                                <span v-else class="text-muted-foreground">-</span>
                            </TableCell>
                            <TableCell class="text-right">
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    @click="handleSelectEmployee(employee.id)"
                                >
                                    View Details
                                </Button>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </CardContent>
        </Card>
    </div>
</template>
