<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { UserRole } from '@/enums';
import { cn } from '@/lib/utils';
import VacationRequestForm from '@/components/VacationRequestForm.vue';
import { router } from '@inertiajs/vue3';
import {
    addMonths,
    addWeeks,
    addYears,
    eachDayOfInterval,
    eachMonthOfInterval,
    endOfMonth,
    endOfWeek,
    endOfYear,
    format,
    isBefore,
    isSameDay,
    isSameMonth,
    isWeekend,
    startOfDay,
    startOfMonth,
    startOfWeek,
    startOfYear,
    subMonths,
    subWeeks,
    subYears,
} from 'date-fns';
import { ChevronLeft, ChevronRight } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface VacationRequest {
    id: string;
    startDate: Date;
    endDate: Date;
    type: 'vacation' | 'sick' | 'personal' | 'unpaid' | 'wfh';
    status: 'pending' | 'approved' | 'rejected';
    reason?: string;
    document?: File;
}

type RequestType = 'vacation' | 'sick' | 'personal' | 'unpaid' | 'wfh';

const props = defineProps<{
    existingRequests: VacationRequest[];
    userRole: UserRole;
}>();

const currentMonth = ref(new Date());
const currentWeek = ref(new Date());
const currentYear = ref(new Date());
const viewMode = ref<'weekly' | 'monthly' | 'yearly'>('monthly');
const selectedDates = ref<Date[]>([]);
const isDialogOpen = ref(false);

const typeColors: Record<string, string> = {
    vacation: 'bg-blue-500',
    sick: 'bg-red-500',
    personal: 'bg-green-500',
    unpaid: 'bg-gray-500',
    wfh: 'bg-purple-500',
};

const typeLabels: Record<string, string> = {
    vacation: 'Paid Leave',
    sick: 'Sick Leave',
    personal: 'Personal Day',
    unpaid: 'Unpaid Leave',
    wfh: 'Work From Home',
};

const weekDays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
const weekDaysShort = ['S', 'M', 'T', 'W', 'T', 'F', 'S'];

const currentLabel = computed(() => {
    if (viewMode.value === 'weekly') {
        const weekStart = startOfWeek(currentWeek.value);
        const weekEnd = endOfWeek(currentWeek.value);
        return `${format(weekStart, 'MMM d')} - ${format(weekEnd, 'MMM d, yyyy')}`;
    } else if (viewMode.value === 'monthly') {
        return format(currentMonth.value, 'MMMM yyyy');
    } else {
        return format(currentYear.value, 'yyyy');
    }
});

const handleDateClick = (date: Date) => {
    if (isBefore(date, startOfDay(new Date()))) return;
    if (isWeekend(date)) return; // Don't allow selecting weekends

    const isSelected = selectedDates.value.some((d) => isSameDay(d, date));

    if (isSelected) {
        selectedDates.value = selectedDates.value.filter(
            (d) => !isSameDay(d, date),
        );
    } else {
        selectedDates.value = [...selectedDates.value, date].sort(
            (a, b) => a.getTime() - b.getTime(),
        );
    }
};

const isDateSelected = (date: Date) => {
    return selectedDates.value.some((d) => isSameDay(d, date));
};

const hasRequest = (date: Date) => {
    return props.existingRequests.find(
        (req) => date >= req.startDate && date <= req.endDate,
    );
};

const handleRequestSuccess = () => {
    selectedDates.value = [];
    isDialogOpen.value = false;
    router.reload();
};

const handleRequestCancel = () => {
    selectedDates.value = [];
    isDialogOpen.value = false;
};

const handlePrevious = () => {
    if (viewMode.value === 'weekly') {
        currentWeek.value = subWeeks(currentWeek.value, 1);
    } else if (viewMode.value === 'monthly') {
        currentMonth.value = subMonths(currentMonth.value, 1);
    } else {
        currentYear.value = subYears(currentYear.value, 1);
    }
};

const handleNext = () => {
    if (viewMode.value === 'weekly') {
        currentWeek.value = addWeeks(currentWeek.value, 1);
    } else if (viewMode.value === 'monthly') {
        currentMonth.value = addMonths(currentMonth.value, 1);
    } else {
        currentYear.value = addYears(currentYear.value, 1);
    }
};

// Weekly view data
const weeklyDays = computed(() => {
    const weekStart = startOfWeek(currentWeek.value);
    const weekEnd = endOfWeek(currentWeek.value);
    return eachDayOfInterval({ start: weekStart, end: weekEnd });
});

// Monthly view data
const monthlyCalendarDays = computed(() => {
    const monthStart = startOfMonth(currentMonth.value);
    const monthEnd = endOfMonth(currentMonth.value);
    const daysInMonth = eachDayOfInterval({ start: monthStart, end: monthEnd });
    const firstDayOfWeek = monthStart.getDay();
    return Array(firstDayOfWeek).fill(null).concat(daysInMonth);
});

// Yearly view data
const yearlyMonths = computed(() => {
    const yearStart = startOfYear(currentYear.value);
    const yearEnd = endOfYear(currentYear.value);
    return eachMonthOfInterval({ start: yearStart, end: yearEnd });
});

const getMonthCalendarDays = (month: Date) => {
    const monthStart = startOfMonth(month);
    const monthEnd = endOfMonth(month);
    const daysInMonth = eachDayOfInterval({ start: monthStart, end: monthEnd });
    const firstDayOfWeek = monthStart.getDay();
    return Array(firstDayOfWeek).fill(null).concat(daysInMonth);
};

const getTypeAbbreviation = (type: string) => {
    const abbrevs: Record<string, string> = {
        vacation: 'VAC',
        sick: 'SICK',
        personal: 'PER',
        wfh: 'WFH',
        unpaid: 'UNP',
    };
    return abbrevs[type] || type.toUpperCase();
};
</script>

<template>
    <Card
        class="flex-1 border border-slate-200/50 bg-white/60 backdrop-blur-sm dark:border-white/10 dark:bg-white/5"
    >
        <CardHeader
            class="flex flex-row items-center justify-between space-y-0 pb-4"
        >
            <CardTitle class="text-slate-900 dark:text-white"
                >Vacation Calendar</CardTitle
            >
            <div class="flex items-center gap-4">
                <!-- View Mode Selector -->
                <div
                    class="flex items-center gap-1 rounded-lg border border-slate-200/50 bg-white/40 p-1 dark:border-white/10 dark:bg-white/5"
                >
                    <Button
                        :variant="viewMode === 'weekly' ? 'default' : 'ghost'"
                        size="sm"
                        class="h-8 data-[variant=ghost]:text-slate-600 data-[variant=ghost]:hover:bg-white/60 data-[variant=ghost]:hover:text-slate-900 dark:data-[variant=ghost]:text-white/70 dark:data-[variant=ghost]:hover:bg-white/10 dark:data-[variant=ghost]:hover:text-white"
                        @click="viewMode = 'weekly'"
                    >
                        Week
                    </Button>
                    <Button
                        :variant="viewMode === 'monthly' ? 'default' : 'ghost'"
                        size="sm"
                        class="h-8 data-[variant=ghost]:text-slate-600 data-[variant=ghost]:hover:bg-white/60 data-[variant=ghost]:hover:text-slate-900 dark:data-[variant=ghost]:text-white/70 dark:data-[variant=ghost]:hover:bg-white/10 dark:data-[variant=ghost]:hover:text-white"
                        @click="viewMode = 'monthly'"
                    >
                        Month
                    </Button>
                    <Button
                        :variant="viewMode === 'yearly' ? 'default' : 'ghost'"
                        size="sm"
                        class="h-8 data-[variant=ghost]:text-slate-600 data-[variant=ghost]:hover:bg-white/60 data-[variant=ghost]:hover:text-slate-900 dark:data-[variant=ghost]:text-white/70 dark:data-[variant=ghost]:hover:bg-white/10 dark:data-[variant=ghost]:hover:text-white"
                        @click="viewMode = 'yearly'"
                    >
                        Year
                    </Button>
                </div>

                <!-- Navigation -->
                <div class="flex items-center gap-2">
                    <Button
                        variant="outline"
                        size="icon"
                        @click="handlePrevious"
                        class="border-slate-200/50 bg-transparent text-slate-700 hover:bg-white/60 dark:border-white/10 dark:text-white dark:hover:bg-white/10"
                    >
                        <ChevronLeft class="h-4 w-4" />
                    </Button>
                    <div
                        class="min-w-[180px] text-center font-semibold text-slate-900 dark:text-white"
                    >
                        {{ currentLabel }}
                    </div>
                    <Button
                        variant="outline"
                        size="icon"
                        @click="handleNext"
                        class="border-slate-200/50 bg-transparent text-slate-700 hover:bg-white/60 dark:border-white/10 dark:text-white dark:hover:bg-white/10"
                    >
                        <ChevronRight class="h-4 w-4" />
                    </Button>
                </div>

                <Button
                    v-if="selectedDates.length > 0"
                    @click="isDialogOpen = true"
                    class="bg-gradient-to-r from-orange-500 to-rose-500 text-white hover:from-orange-600 hover:to-rose-600"
                >
                    Request {{ selectedDates.filter(d => !isWeekend(d)).length }} day{{
                        selectedDates.filter(d => !isWeekend(d)).length !== 1 ? 's' : ''
                    }}
                </Button>
            </div>
        </CardHeader>
        <CardContent>
            <!-- Weekly View -->
            <div v-if="viewMode === 'weekly'" class="space-y-4">
                <div class="grid grid-cols-7 gap-4">
                    <div
                        v-for="day in weeklyDays"
                        :key="day.toISOString()"
                        class="space-y-2"
                    >
                        <div class="text-center">
                            <div
                                class="text-sm font-semibold text-slate-500 dark:text-white/50"
                            >
                                {{ format(day, 'EEE') }}
                            </div>
                            <div
                                :class="
                                    cn(
                                        'mt-1 text-2xl font-bold text-slate-900 dark:text-white',
                                        isSameDay(day, new Date()) &&
                                            'text-blue-600 dark:text-blue-400',
                                    )
                                "
                            >
                                {{ format(day, 'd') }}
                            </div>
                        </div>
                        <button
                            :disabled="
                                isBefore(day, startOfDay(new Date())) ||
                                !!hasRequest(day) ||
                                isWeekend(day)
                            "
                            :class="
                                cn(
                                    'h-32 w-full rounded-lg border p-3 transition-all',
                                    isSameDay(day, new Date()) &&
                                        'border-blue-500 dark:border-blue-400',
                                    isDateSelected(day) &&
                                        'border-blue-500 bg-blue-100/50 dark:border-blue-400 dark:bg-blue-500/20',
                                    hasRequest(day) &&
                                        `${typeColors[hasRequest(day)!.type]} border-transparent text-white`,
                                    !hasRequest(day) &&
                                        !isDateSelected(day) &&
                                        !isWeekend(day) &&
                                        'border-slate-200/50 text-slate-900 hover:bg-white/60 dark:border-white/10 dark:text-white dark:hover:bg-white/5',
                                    (isBefore(day, startOfDay(new Date())) || isWeekend(day)) &&
                                        'cursor-not-allowed opacity-40',
                                    isWeekend(day) &&
                                        !hasRequest(day) &&
                                        'bg-slate-100/50 dark:bg-white/5',
                                )
                            "
                            @click="handleDateClick(day)"
                        >
                            <div
                                v-if="hasRequest(day)"
                                class="text-sm font-medium"
                            >
                                {{ typeLabels[hasRequest(day)!.type] }}
                            </div>
                            <div
                                v-if="!hasRequest(day) && isDateSelected(day)"
                                class="text-sm font-medium text-blue-300"
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
                        class="py-2 text-center text-sm font-semibold text-slate-500 dark:text-white/50"
                    >
                        {{ day }}
                    </div>
                </div>
                <div class="grid grid-cols-7 gap-2">
                    <template
                        v-for="(day, index) in monthlyCalendarDays"
                        :key="index"
                    >
                        <div v-if="!day" class="aspect-square" />
                        <button
                            v-else
                            :disabled="
                                isBefore(day, startOfDay(new Date())) ||
                                !!hasRequest(day) ||
                                isWeekend(day)
                            "
                            :class="
                                cn(
                                    'aspect-square rounded-lg border p-2 text-sm transition-all',
                                    !isSameMonth(day, currentMonth) &&
                                        'text-slate-400 opacity-50 dark:text-white/30',
                                    isSameDay(day, new Date()) &&
                                        'border-2 border-blue-500 dark:border-blue-400',
                                    isDateSelected(day) &&
                                        'border-blue-500 bg-blue-100/50 text-slate-900 dark:border-blue-400 dark:bg-blue-500/20 dark:text-white',
                                    hasRequest(day) &&
                                        `${typeColors[hasRequest(day)!.type]} border-transparent text-white`,
                                    !hasRequest(day) &&
                                        !isDateSelected(day) &&
                                        !isWeekend(day) &&
                                        'border-slate-200/50 text-slate-900 hover:bg-white/60 dark:border-white/10 dark:text-white/80 dark:hover:bg-white/5',
                                    (isBefore(day, startOfDay(new Date())) || isWeekend(day)) &&
                                        'cursor-not-allowed opacity-40',
                                    isWeekend(day) &&
                                        !hasRequest(day) &&
                                        'bg-slate-100/50 dark:bg-white/5',
                                )
                            "
                            @click="handleDateClick(day)"
                        >
                            <div
                                class="flex h-full flex-col items-center justify-center"
                            >
                                <span>{{ format(day, 'd') }}</span>
                                <span
                                    v-if="hasRequest(day)"
                                    class="mt-1 text-[10px] opacity-90"
                                >
                                    {{
                                        getTypeAbbreviation(
                                            hasRequest(day)!.type,
                                        )
                                    }}
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
                    class="rounded-lg border border-slate-200/50 p-3 transition-colors hover:bg-white/60 dark:border-white/10 dark:hover:bg-white/5"
                >
                    <h3
                        class="mb-2 text-center text-sm font-semibold text-slate-900 dark:text-white"
                    >
                        {{ format(month, 'MMMM') }}
                    </h3>
                    <div class="grid grid-cols-7 gap-1">
                        <div
                            v-for="(dayLetter, i) in weekDaysShort"
                            :key="i"
                            class="text-center text-[10px] font-medium text-slate-400 dark:text-white/50"
                        >
                            {{ dayLetter }}
                        </div>
                        <template
                            v-for="(day, index) in getMonthCalendarDays(month)"
                            :key="index"
                        >
                            <div v-if="!day" class="aspect-square" />
                            <div
                                v-else
                                :class="
                                    cn(
                                        'flex aspect-square items-center justify-center rounded text-[10px]',
                                        isSameDay(day, new Date()) &&
                                            'font-bold text-blue-600 ring-1 ring-blue-500 dark:text-blue-400 dark:ring-blue-400',
                                        hasRequest(day) &&
                                            `${typeColors[hasRequest(day)!.type]} text-white`,
                                        !hasRequest(day) &&
                                            'text-slate-500 dark:text-white/70',
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
            <div
                class="mt-6 flex flex-wrap gap-4 text-sm text-slate-600 dark:text-white/80"
            >
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
        <DialogContent class="max-w-2xl border-white/20 bg-white/90 backdrop-blur-xl dark:border-white/10 dark:bg-slate-900/90">
            <DialogHeader>
                <DialogTitle class="sr-only">Request Time Off</DialogTitle>
            </DialogHeader>
            <VacationRequestForm
                :selected-dates="selectedDates"
                @success="handleRequestSuccess"
                @cancel="handleRequestCancel"
            />
        </DialogContent>
    </Dialog>
</template>
