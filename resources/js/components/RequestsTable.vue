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
    pending: 'bg-amber-500/10 text-amber-500 border-amber-500/20',
    approved: 'bg-emerald-500/10 text-emerald-500 border-emerald-500/20',
    rejected: 'bg-rose-500/10 text-rose-500 border-rose-500/20',
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
    <div class="rounded-md border border-slate-200/50 dark:border-white/10 bg-white/60 dark:bg-white/5 backdrop-blur-sm">
        <Table>
            <TableHeader>
                <TableRow class="border-slate-200/50 dark:border-white/10 hover:bg-transparent">
                    <TableHead v-if="showEmployeeColumn" class="text-slate-600 dark:text-white/60">Employee</TableHead>
                    <TableHead class="text-slate-600 dark:text-white/60">Type</TableHead>
                    <TableHead class="text-slate-600 dark:text-white/60">Start Date</TableHead>
                    <TableHead class="text-slate-600 dark:text-white/60">End Date</TableHead>
                    <TableHead class="text-slate-600 dark:text-white/60">Days</TableHead>
                    <TableHead class="text-slate-600 dark:text-white/60">Status</TableHead>
                    <TableHead v-if="canApprove" class="text-right text-slate-600 dark:text-white/60">Actions</TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                <TableRow v-if="requests.length === 0" class="border-slate-200/50 dark:border-white/10 hover:bg-transparent">
                    <TableCell
                        :colspan="canApprove ? 8 : 7"
                        class="py-8 text-center text-slate-500 dark:text-white/50"
                    >
                        No requests found
                    </TableCell>
                </TableRow>
                <template v-else>
                    <TableRow v-for="request in requests" :key="request.id" class="border-slate-200/50 dark:border-white/10 hover:bg-white/60 dark:hover:bg-white/5 transition-colors">
                        <TableCell v-if="showEmployeeColumn" class="font-medium text-slate-900 dark:text-white">
                            {{ request.employeeName || 'Unknown' }}
                        </TableCell>
                        <TableCell class="text-slate-700 dark:text-white/80">{{ typeLabels[request.type] }}</TableCell>
                        <TableCell class="text-slate-700 dark:text-white/80">{{ format(request.startDate, 'MMM d, yyyy') }}</TableCell>
                        <TableCell class="text-slate-700 dark:text-white/80">{{ format(request.endDate, 'MMM d, yyyy') }}</TableCell>
                        <TableCell class="text-slate-700 dark:text-white/80">
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
                                    class="h-8 border-emerald-500/30 text-emerald-400 hover:bg-emerald-500/10 hover:text-emerald-300"
                                    @click="emit('approve', request.id)"
                                >
                                    <Check class="h-4 w-4" />
                                </Button>
                                <Button
                                    size="sm"
                                    variant="outline"
                                    class="h-8 border-red-500/30 text-red-400 hover:bg-red-500/10 hover:text-red-300"
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
