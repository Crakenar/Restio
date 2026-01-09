<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';
import { cn } from '@/lib/utils';
import {
    addMonths,
    eachDayOfInterval,
    endOfMonth,
    format,
    isSameDay,
    isSameMonth,
    isWeekend,
    startOfMonth,
    subMonths,
} from 'date-fns';
import { AlertCircle, ChevronLeft, ChevronRight } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface VacationRequest {
    id: string;
    startDate: Date;
    endDate: Date;
    type: 'vacation' | 'sick' | 'personal' | 'unpaid' | 'wfh';
    status: 'pending' | 'approved' | 'rejected';
    employeeName?: string;
    department?: string;
}

const props = defineProps<{
    requests: VacationRequest[];
}>();

const currentMonth = ref(new Date());

const filters = ref({
    vacation: true,
    sick: true,
    personal: true,
    unpaid: true,
    wfh: true,
});

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

const calendarDays = computed(() => {
    const monthStart = startOfMonth(currentMonth.value);
    const monthEnd = endOfMonth(currentMonth.value);
    const daysInMonth = eachDayOfInterval({ start: monthStart, end: monthEnd });
    const firstDayOfWeek = monthStart.getDay();
    return Array(firstDayOfWeek).fill(null).concat(daysInMonth);
});

const toggleFilter = (type: keyof typeof filters.value) => {
    filters.value[type] = !filters.value[type];
};

const getRequestsForDay = (date: Date) => {
    return props.requests.filter((req) => {
        if (!filters.value[req.type]) return false;
        if (req.status !== 'approved') return false;
        return date >= req.startDate && date <= req.endDate;
    });
};

const getTeamCoverage = (date: Date) => {
    const totalEmployees = 15; // Mock total
    const absences = getRequestsForDay(date);
    const presentCount = totalEmployees - absences.length;
    const coveragePercent = (presentCount / totalEmployees) * 100;

    return {
        absences: absences.length,
        present: presentCount,
        coveragePercent,
        isLowCoverage: coveragePercent < 60,
    };
};

const goToPreviousMonth = () => {
    currentMonth.value = subMonths(currentMonth.value, 1);
};

const goToNextMonth = () => {
    currentMonth.value = addMonths(currentMonth.value, 1);
};
</script>

<template>
    <Card class="flex-1">
        <CardHeader>
            <div class="flex items-center justify-between">
                <CardTitle>Team Coverage Calendar</CardTitle>
                <div class="flex items-center gap-2">
                    <Button
                        variant="outline"
                        size="icon"
                        @click="goToPreviousMonth"
                    >
                        <ChevronLeft class="h-4 w-4" />
                    </Button>
                    <div class="min-w-[150px] text-center font-semibold">
                        {{ format(currentMonth, 'MMMM yyyy') }}
                    </div>
                    <Button
                        variant="outline"
                        size="icon"
                        @click="goToNextMonth"
                    >
                        <ChevronRight class="h-4 w-4" />
                    </Button>
                </div>
            </div>
        </CardHeader>
        <CardContent class="space-y-6">
            <!-- Filters -->
            <div class="flex flex-wrap gap-6 rounded-lg bg-muted/30 p-4">
                <div class="text-sm font-semibold">Filters:</div>
                <div
                    v-for="(label, type) in typeLabels"
                    :key="type"
                    class="flex items-center space-x-2"
                >
                    <Checkbox
                        :id="`filter-${type}`"
                        :checked="filters[type as keyof typeof filters]"
                        @update:checked="
                            toggleFilter(type as keyof typeof filters)
                        "
                    />
                    <Label
                        :for="`filter-${type}`"
                        class="flex cursor-pointer items-center gap-2 text-sm font-normal"
                    >
                        <div :class="cn('h-3 w-3 rounded', typeColors[type])" />
                        {{ label }}
                    </Label>
                </div>
            </div>

            <!-- Calendar Grid -->
            <div>
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
                    <template v-for="(day, index) in calendarDays" :key="index">
                        <div v-if="!day" class="aspect-square" />
                        <div
                            v-else
                            :class="
                                cn(
                                    'relative aspect-square rounded-lg border p-2 transition-all',
                                    !isSameMonth(day, currentMonth) &&
                                        'opacity-50',
                                    isSameDay(day, new Date()) &&
                                        'border-2 border-blue-500',
                                    getTeamCoverage(day).isLowCoverage &&
                                        isSameMonth(day, currentMonth) &&
                                        'bg-red-50 dark:bg-red-950/20',
                                    isWeekend(day) && 'bg-muted/50',
                                )
                            "
                        >
                            <div class="flex h-full flex-col">
                                <div
                                    class="mb-1 flex items-start justify-between"
                                >
                                    <span
                                        :class="
                                            cn(
                                                'text-sm font-medium',
                                                isSameDay(day, new Date()) &&
                                                    'font-bold text-blue-600',
                                            )
                                        "
                                    >
                                        {{ format(day, 'd') }}
                                    </span>
                                    <AlertCircle
                                        v-if="
                                            getTeamCoverage(day)
                                                .isLowCoverage &&
                                            isSameMonth(day, currentMonth)
                                        "
                                        class="h-3 w-3 text-red-600"
                                    />
                                </div>

                                <div
                                    v-if="getRequestsForDay(day).length > 0"
                                    class="flex-1 space-y-1 overflow-hidden"
                                >
                                    <div
                                        v-for="(req, idx) in getRequestsForDay(
                                            day,
                                        ).slice(0, 2)"
                                        :key="idx"
                                        :class="
                                            cn(
                                                'truncate rounded px-1 py-0.5 text-[10px] text-white',
                                                typeColors[req.type],
                                            )
                                        "
                                        :title="`${req.employeeName} - ${typeLabels[req.type]}`"
                                    >
                                        {{ req.employeeName?.split(' ')[0] }}
                                    </div>
                                    <div
                                        v-if="getRequestsForDay(day).length > 2"
                                        class="text-[10px] text-muted-foreground"
                                    >
                                        +{{ getRequestsForDay(day).length - 2 }}
                                        more
                                    </div>
                                </div>

                                <div
                                    v-if="isSameMonth(day, currentMonth)"
                                    class="mt-auto pt-1"
                                >
                                    <div
                                        class="text-center text-[10px] text-muted-foreground"
                                    >
                                        {{ getTeamCoverage(day).present }}/15
                                        <p class="invisible md:visible">
                                            Team member
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Legend -->
            <div class="space-y-3">
                <div class="flex flex-wrap gap-4 text-sm">
                    <div
                        v-for="(label, type) in typeLabels"
                        :key="type"
                        class="flex items-center gap-2"
                    >
                        <div :class="cn('h-4 w-4 rounded', typeColors[type])" />
                        <span>{{ label }}</span>
                    </div>
                </div>
                <div class="flex items-center gap-2 text-sm">
                    <AlertCircle class="h-4 w-4 text-red-600" />
                    <span class="text-muted-foreground">
                        Low coverage (less than 60% present)
                    </span>
                </div>
            </div>
        </CardContent>
    </Card>
</template>
