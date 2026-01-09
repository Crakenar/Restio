<script setup lang="ts">
import { ref, computed } from 'vue'
import {
    format,
    startOfMonth,
    endOfMonth,
    eachDayOfInterval,
    isSameMonth,
    isSameDay,
    addMonths,
    subMonths,
    isWeekend,
    isBefore,
    startOfDay,
    startOfWeek,
    endOfWeek,
    addWeeks,
    subWeeks,
    startOfYear,
    endOfYear,
    eachMonthOfInterval,
    addYears,
    subYears,
} from 'date-fns'
import { ChevronLeft, ChevronRight } from 'lucide-vue-next'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog'
import { Label } from '@/components/ui/label'
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select'
import { Textarea } from '@/components/ui/textarea'
import { cn } from '@/lib/utils'

interface VacationRequest {
    id: string
    startDate: Date
    endDate: Date
    type: 'vacation' | 'sick' | 'personal' | 'unpaid' | 'wfh'
    status: 'pending' | 'approved' | 'rejected'
    reason?: string
    document?: File
}

type RequestType = 'vacation' | 'sick' | 'personal' | 'unpaid' | 'wfh'

const props = defineProps<{
    existingRequests: VacationRequest[]
    userRole: 'employee' | 'manager' | 'admin'
}>()

const emit = defineEmits<{
    createRequest: [request: Omit<VacationRequest, 'id' | 'status'>]
}>()

const currentMonth = ref(new Date())
const currentWeek = ref(new Date())
const currentYear = ref(new Date())
const viewMode = ref<'weekly' | 'monthly' | 'yearly'>('monthly')
const selectedDates = ref<Date[]>([])
const isDialogOpen = ref(false)
const requestType = ref<RequestType>('vacation')
const reason = ref('')
const document = ref<File | null>(null)

const typeColors: Record<string, string> = {
    vacation: 'bg-blue-500',
    sick: 'bg-red-500',
    personal: 'bg-green-500',
    unpaid: 'bg-gray-500',
    wfh: 'bg-purple-500',
}

const typeLabels: Record<string, string> = {
    vacation: 'Paid Leave',
    sick: 'Sick Leave',
    personal: 'Personal Day',
    unpaid: 'Unpaid Leave',
    wfh: 'Work From Home',
}

const weekDays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']
const weekDaysShort = ['S', 'M', 'T', 'W', 'T', 'F', 'S']

const currentLabel = computed(() => {
    if (viewMode.value === 'weekly') {
        const weekStart = startOfWeek(currentWeek.value)
        const weekEnd = endOfWeek(currentWeek.value)
        return `${format(weekStart, 'MMM d')} - ${format(weekEnd, 'MMM d, yyyy')}`
    } else if (viewMode.value === 'monthly') {
        return format(currentMonth.value, 'MMMM yyyy')
    } else {
        return format(currentYear.value, 'yyyy')
    }
})

const handleDateClick = (date: Date) => {
    if (isBefore(date, startOfDay(new Date()))) return

    const isSelected = selectedDates.value.some((d) => isSameDay(d, date))

    if (isSelected) {
        selectedDates.value = selectedDates.value.filter((d) => !isSameDay(d, date))
    } else {
        selectedDates.value = [...selectedDates.value, date].sort(
            (a, b) => a.getTime() - b.getTime()
        )
    }
}

const isDateSelected = (date: Date) => {
    return selectedDates.value.some((d) => isSameDay(d, date))
}

const hasRequest = (date: Date) => {
    return props.existingRequests.find((req) => date >= req.startDate && date <= req.endDate)
}

const handleSubmitRequest = () => {
    if (selectedDates.value.length === 0) return

    const sortedDates = [...selectedDates.value].sort((a, b) => a.getTime() - b.getTime())

    emit('createRequest', {
        startDate: sortedDates[0],
        endDate: sortedDates[sortedDates.length - 1],
        type: requestType.value,
        reason: reason.value || undefined,
        document: document.value || undefined,
    })

    selectedDates.value = []
    reason.value = ''
    document.value = null
    isDialogOpen.value = false
}

const handlePrevious = () => {
    if (viewMode.value === 'weekly') {
        currentWeek.value = subWeeks(currentWeek.value, 1)
    } else if (viewMode.value === 'monthly') {
        currentMonth.value = subMonths(currentMonth.value, 1)
    } else {
        currentYear.value = subYears(currentYear.value, 1)
    }
}

const handleNext = () => {
    if (viewMode.value === 'weekly') {
        currentWeek.value = addWeeks(currentWeek.value, 1)
    } else if (viewMode.value === 'monthly') {
        currentMonth.value = addMonths(currentMonth.value, 1)
    } else {
        currentYear.value = addYears(currentYear.value, 1)
    }
}

const handleFileChange = (event: Event) => {
    const target = event.target as HTMLInputElement
    document.value = target.files?.[0] || null
}

const removeDocument = () => {
    document.value = null
}

// Weekly view data
const weeklyDays = computed(() => {
    const weekStart = startOfWeek(currentWeek.value)
    const weekEnd = endOfWeek(currentWeek.value)
    return eachDayOfInterval({ start: weekStart, end: weekEnd })
})

// Monthly view data
const monthlyCalendarDays = computed(() => {
    const monthStart = startOfMonth(currentMonth.value)
    const monthEnd = endOfMonth(currentMonth.value)
    const daysInMonth = eachDayOfInterval({ start: monthStart, end: monthEnd })
    const firstDayOfWeek = monthStart.getDay()
    return Array(firstDayOfWeek).fill(null).concat(daysInMonth)
})

// Yearly view data
const yearlyMonths = computed(() => {
    const yearStart = startOfYear(currentYear.value)
    const yearEnd = endOfYear(currentYear.value)
    return eachMonthOfInterval({ start: yearStart, end: yearEnd })
})

const getMonthCalendarDays = (month: Date) => {
    const monthStart = startOfMonth(month)
    const monthEnd = endOfMonth(month)
    const daysInMonth = eachDayOfInterval({ start: monthStart, end: monthEnd })
    const firstDayOfWeek = monthStart.getDay()
    return Array(firstDayOfWeek).fill(null).concat(daysInMonth)
}

const getTypeAbbreviation = (type: string) => {
    const abbrevs: Record<string, string> = {
        vacation: 'VAC',
        sick: 'SICK',
        personal: 'PER',
        wfh: 'WFH',
        unpaid: 'UNP',
    }
    return abbrevs[type] || type.toUpperCase()
}
</script>

<template>
    <Card class="flex-1">
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-4">
            <CardTitle>Vacation Calendar</CardTitle>
            <div class="flex items-center gap-4">
                <!-- View Mode Selector -->
                <div class="flex items-center gap-1 rounded-lg border p-1">
                    <Button
                        :variant="viewMode === 'weekly' ? 'default' : 'ghost'"
                        size="sm"
                        class="h-8"
                        @click="viewMode = 'weekly'"
                    >
                        Week
                    </Button>
                    <Button
                        :variant="viewMode === 'monthly' ? 'default' : 'ghost'"
                        size="sm"
                        class="h-8"
                        @click="viewMode = 'monthly'"
                    >
                        Month
                    </Button>
                    <Button
                        :variant="viewMode === 'yearly' ? 'default' : 'ghost'"
                        size="sm"
                        class="h-8"
                        @click="viewMode = 'yearly'"
                    >
                        Year
                    </Button>
                </div>

                <!-- Navigation -->
                <div class="flex items-center gap-2">
                    <Button variant="outline" size="icon" @click="handlePrevious">
                        <ChevronLeft class="h-4 w-4" />
                    </Button>
                    <div class="min-w-[180px] text-center font-semibold">
                        {{ currentLabel }}
                    </div>
                    <Button variant="outline" size="icon" @click="handleNext">
                        <ChevronRight class="h-4 w-4" />
                    </Button>
                </div>

                <Button v-if="selectedDates.length > 0" @click="isDialogOpen = true">
                    Request {{ selectedDates.length }} day{{ selectedDates.length > 1 ? 's' : '' }}
                </Button>
            </div>
        </CardHeader>
        <CardContent>
            <!-- Weekly View -->
            <div v-if="viewMode === 'weekly'" class="space-y-4">
                <div class="grid grid-cols-7 gap-4">
                    <div v-for="day in weeklyDays" :key="day.toISOString()" class="space-y-2">
                        <div class="text-center">
                            <div class="text-sm font-semibold text-muted-foreground">
                                {{ format(day, 'EEE') }}
                            </div>
                            <div
                                :class="
                                    cn(
                                        'mt-1 text-2xl font-bold',
                                        isSameDay(day, new Date()) && 'text-blue-600'
                                    )
                                "
                            >
                                {{ format(day, 'd') }}
                            </div>
                        </div>
                        <button
                            :disabled="isBefore(day, startOfDay(new Date())) || !!hasRequest(day)"
                            :class="
                                cn(
                                    'h-32 w-full rounded-lg border-2 p-3 transition-all',
                                    isSameDay(day, new Date()) && 'border-blue-500',
                                    isDateSelected(day) &&
                                        'border-blue-500 bg-blue-100 dark:bg-blue-900',
                                    hasRequest(day) &&
                                        `${typeColors[hasRequest(day)!.type]} border-transparent text-white`,
                                    !hasRequest(day) &&
                                        !isDateSelected(day) &&
                                        'border-border hover:bg-accent',
                                    isBefore(day, startOfDay(new Date())) &&
                                        'cursor-not-allowed opacity-40',
                                    isWeekend(day) && !hasRequest(day) && 'bg-muted/50'
                                )
                            "
                            @click="handleDateClick(day)"
                        >
                            <div v-if="hasRequest(day)" class="text-sm font-medium">
                                {{ typeLabels[hasRequest(day)!.type] }}
                            </div>
                            <div
                                v-if="!hasRequest(day) && isDateSelected(day)"
                                class="text-sm font-medium text-blue-700 dark:text-blue-300"
                            >
                                Selected
                            </div>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Monthly View -->
            <div v-if="viewMode === 'monthly'" class="space-y-2">
                <div class="mb-2 grid grid-cols-7 gap-2">
                    <div
                        v-for="day in weekDays"
                        :key="day"
                        class="py-2 text-center text-sm font-semibold text-muted-foreground"
                    >
                        {{ day }}
                    </div>
                </div>
                <div class="grid grid-cols-7 gap-2">
                    <template v-for="(day, index) in monthlyCalendarDays" :key="index">
                        <div v-if="!day" class="aspect-square" />
                        <button
                            v-else
                            :disabled="isBefore(day, startOfDay(new Date())) || !!hasRequest(day)"
                            :class="
                                cn(
                                    'aspect-square rounded-lg border p-2 text-sm transition-all',
                                    !isSameMonth(day, currentMonth) &&
                                        'text-muted-foreground opacity-50',
                                    isSameDay(day, new Date()) && 'border-2 border-blue-500',
                                    isDateSelected(day) &&
                                        'border-blue-500 bg-blue-100 dark:bg-blue-900',
                                    hasRequest(day) &&
                                        `${typeColors[hasRequest(day)!.type]} text-white`,
                                    !hasRequest(day) &&
                                        !isDateSelected(day) &&
                                        'border-border hover:bg-accent',
                                    isBefore(day, startOfDay(new Date())) &&
                                        'cursor-not-allowed opacity-40',
                                    isWeekend(day) && !hasRequest(day) && 'bg-muted/50'
                                )
                            "
                            @click="handleDateClick(day)"
                        >
                            <div class="flex h-full flex-col items-center justify-center">
                                <span>{{ format(day, 'd') }}</span>
                                <span v-if="hasRequest(day)" class="mt-1 text-[10px] opacity-90">
                                    {{ getTypeAbbreviation(hasRequest(day)!.type) }}
                                </span>
                            </div>
                        </button>
                    </template>
                </div>
            </div>

            <!-- Yearly View -->
            <div v-if="viewMode === 'yearly'" class="grid grid-cols-3 gap-6">
                <div
                    v-for="month in yearlyMonths"
                    :key="month.toISOString()"
                    class="rounded-lg border p-3"
                >
                    <h3 class="mb-2 text-center text-sm font-semibold">
                        {{ format(month, 'MMMM') }}
                    </h3>
                    <div class="grid grid-cols-7 gap-1">
                        <div
                            v-for="(dayLetter, i) in weekDaysShort"
                            :key="i"
                            class="text-center text-[10px] font-medium text-muted-foreground"
                        >
                            {{ dayLetter }}
                        </div>
                        <template v-for="(day, index) in getMonthCalendarDays(month)" :key="index">
                            <div v-if="!day" class="aspect-square" />
                            <div
                                v-else
                                :class="
                                    cn(
                                        'flex aspect-square items-center justify-center rounded text-[10px]',
                                        isSameDay(day, new Date()) && 'ring-1 ring-blue-500',
                                        hasRequest(day) &&
                                            `${typeColors[hasRequest(day)!.type]} text-white`,
                                        !hasRequest(day) && 'text-muted-foreground'
                                    )
                                "
                            >
                                {{ format(day, 'd') }}
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Legend -->
            <div class="mt-6 flex flex-wrap gap-4 text-sm">
                <div class="flex items-center gap-2">
                    <div class="h-4 w-4 rounded bg-blue-500" />
                    <span>Paid Leave</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="h-4 w-4 rounded bg-red-500" />
                    <span>Sick Leave</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="h-4 w-4 rounded bg-green-500" />
                    <span>Personal Day</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="h-4 w-4 rounded bg-purple-500" />
                    <span>Work From Home</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="h-4 w-4 rounded bg-gray-500" />
                    <span>Unpaid Leave</span>
                </div>
            </div>
        </CardContent>
    </Card>

    <!-- Request Dialog -->
    <Dialog v-model:open="isDialogOpen">
        <DialogContent>
            <DialogHeader>
                <DialogTitle>Request Time Off</DialogTitle>
                <DialogDescription>
                    <template v-if="selectedDates.length > 0">
                        {{ format(selectedDates[0], 'MMM d, yyyy') }}
                        <template v-if="selectedDates.length > 1">
                            - {{ format(selectedDates[selectedDates.length - 1], 'MMM d, yyyy') }}
                        </template>
                        ({{ selectedDates.length }} day{{ selectedDates.length > 1 ? 's' : '' }})
                    </template>
                </DialogDescription>
            </DialogHeader>
            <div class="space-y-4 py-4">
                <div class="space-y-2">
                    <Label for="type">Request Type</Label>
                    <Select v-model="requestType">
                        <SelectTrigger id="type">
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="(label, value) in typeLabels"
                                :key="value"
                                :value="value"
                            >
                                {{ label }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>
                <div class="space-y-2">
                    <Label for="reason">Reason (Optional)</Label>
                    <Textarea
                        id="reason"
                        v-model="reason"
                        placeholder="Add a note about your request..."
                        :rows="3"
                    />
                </div>
                <div class="space-y-2">
                    <Label for="document">
                        Upload Document
                        <template v-if="requestType === 'sick'">
                            (Required for sick leave)
                        </template>
                    </Label>
                    <div
                        class="rounded-lg border-2 border-dashed p-6 text-center transition-colors hover:border-blue-500"
                    >
                        <input
                            id="document"
                            type="file"
                            accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                            class="hidden"
                            @change="handleFileChange"
                        />
                        <label for="document" class="cursor-pointer">
                            <div class="space-y-2">
                                <div
                                    class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-blue-50 dark:bg-blue-950"
                                >
                                    <svg
                                        class="h-6 w-6 text-blue-600"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"
                                        />
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium">
                                        {{
                                            document
                                                ? document.name
                                                : 'Click to upload or drag and drop'
                                        }}
                                    </p>
                                    <p class="mt-1 text-xs text-muted-foreground">
                                        PDF, DOC, DOCX, JPG, PNG (max 10MB)
                                    </p>
                                </div>
                            </div>
                        </label>
                    </div>
                    <div
                        v-if="document"
                        class="flex items-center justify-between rounded-lg bg-muted p-2"
                    >
                        <span class="truncate text-sm">{{ document.name }}</span>
                        <Button variant="ghost" size="sm" @click="removeDocument">
                            Remove
                        </Button>
                    </div>
                </div>
            </div>
            <DialogFooter>
                <Button variant="outline" @click="isDialogOpen = false">Cancel</Button>
                <Button @click="handleSubmitRequest">Submit Request</Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
