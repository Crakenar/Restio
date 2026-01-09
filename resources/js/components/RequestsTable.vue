<script setup lang="ts">
import { computed } from 'vue'
import { format } from 'date-fns'
import { Check, X } from 'lucide-vue-next'
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'

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
    requests: VacationRequest[]
    userRole: 'employee' | 'manager' | 'admin'
}>()

const emit = defineEmits<{
    approve: [id: string]
    reject: [id: string]
}>()

const statusColors: Record<string, string> = {
    pending: 'bg-yellow-500/10 text-yellow-700 dark:text-yellow-400 border-yellow-500/20',
    approved: 'bg-green-500/10 text-green-700 dark:text-green-400 border-green-500/20',
    rejected: 'bg-red-500/10 text-red-700 dark:text-red-400 border-red-500/20',
}

const typeLabels: Record<string, string> = {
    vacation: 'Paid Leave',
    sick: 'Sick Leave',
    personal: 'Personal Day',
    unpaid: 'Unpaid Leave',
    wfh: 'Work From Home',
}

const canApprove = computed(() => props.userRole === 'manager' || props.userRole === 'admin')

const showEmployeeColumn = computed(
    () => props.userRole === 'manager' || props.userRole === 'admin'
)

const getDayCount = (startDate: Date, endDate: Date) => {
    return Math.ceil((endDate.getTime() - startDate.getTime()) / (1000 * 60 * 60 * 24)) + 1
}

const capitalizeFirst = (str: string) => {
    return str.charAt(0).toUpperCase() + str.slice(1)
}
</script>

<template>
    <div class="rounded-md border">
        <Table>
            <TableHeader>
                <TableRow>
                    <TableHead v-if="showEmployeeColumn">Employee</TableHead>
                    <TableHead>Type</TableHead>
                    <TableHead>Start Date</TableHead>
                    <TableHead>End Date</TableHead>
                    <TableHead>Days</TableHead>
                    <TableHead>Status</TableHead>
                    <TableHead v-if="canApprove" class="text-right">Actions</TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                <TableRow v-if="requests.length === 0">
                    <TableCell
                        :colspan="canApprove ? 8 : 7"
                        class="py-8 text-center text-muted-foreground"
                    >
                        No requests found
                    </TableCell>
                </TableRow>
                <template v-else>
                    <TableRow v-for="request in requests" :key="request.id">
                        <TableCell v-if="showEmployeeColumn" class="font-medium">
                            {{ request.employeeName || 'Unknown' }}
                        </TableCell>
                        <TableCell>{{ typeLabels[request.type] }}</TableCell>
                        <TableCell>{{ format(request.startDate, 'MMM d, yyyy') }}</TableCell>
                        <TableCell>{{ format(request.endDate, 'MMM d, yyyy') }}</TableCell>
                        <TableCell>
                            {{
                                getDayCount(request.startDate, request.endDate) === 1
                                    ? '1 day'
                                    : `${getDayCount(request.startDate, request.endDate)} days`
                            }}
                        </TableCell>
                        <TableCell>
                            <Badge variant="outline" :class="statusColors[request.status]">
                                {{ capitalizeFirst(request.status) }}
                            </Badge>
                        </TableCell>
                        <TableCell v-if="canApprove" class="text-right">
                            <div
                                v-if="request.status === 'pending'"
                                class="flex justify-end gap-2"
                            >
                                <Button
                                    size="sm"
                                    variant="outline"
                                    class="h-8 text-green-600 hover:text-green-700"
                                    @click="emit('approve', request.id)"
                                >
                                    <Check class="h-4 w-4" />
                                </Button>
                                <Button
                                    size="sm"
                                    variant="outline"
                                    class="h-8 text-red-600 hover:text-red-700"
                                    @click="emit('reject', request.id)"
                                >
                                    <X class="h-4 w-4" />
                                </Button>
                            </div>
                        </TableCell>
                    </TableRow>
                </template>
            </TableBody>
        </Table>
    </div>
</template>
