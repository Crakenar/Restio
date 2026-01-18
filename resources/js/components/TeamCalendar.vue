<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
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
import {
    AlertCircle,
    Calendar as CalendarIcon,
    ChevronLeft,
    ChevronRight,
    User,
} from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

interface VacationRequest {
    id: string;
    startDate: Date;
    endDate: Date;
    type: 'vacation' | 'sick' | 'personal' | 'unpaid' | 'wfh';
    status: 'pending' | 'approved' | 'rejected';
    employeeName?: string;
    department?: string;
}

interface Team {
    id: number;
    name: string;
}

const props = defineProps<{
    requests: VacationRequest[];
    totalEmployees: number;
    teams: Team[];
}>();

const currentMonth = ref(new Date());
const selectedDay = ref<Date | null>(null);
const isModalOpen = ref(false);

const filters = ref({
    vacation: true,
    sick: true,
    personal: true,
    unpaid: true,
    wfh: true,
});

// Team filters - initially all teams are selected
const teamFilters = ref<Record<number, boolean>>({});

// Initialize team filters when teams are available
const initializeTeamFilters = () => {
    const filters: Record<number, boolean> = {};
    props.teams.forEach((team) => {
        filters[team.id] = true;
    });
    console.log('Initializing team filters:', filters);
    teamFilters.value = filters;
    console.log('teamFilters.value after init:', teamFilters.value);
};

// Watch for teams prop changes and initialize
watch(
    () => props.teams,
    (newTeams) => {
        if (
            newTeams &&
            newTeams.length > 0 &&
            Object.keys(teamFilters.value).length === 0
        ) {
            initializeTeamFilters();
        }
    },
    { immediate: true },
);

const typeColors: Record<string, string> = {
    vacation: 'bg-blue-500',
    sick: 'bg-red-500',
    personal: 'bg-green-500',
    unpaid: 'bg-gray-500',
    wfh: 'bg-purple-500',
};

const typeGradients: Record<string, string> = {
    vacation: 'from-blue-500 to-cyan-600',
    sick: 'from-red-500 to-rose-600',
    personal: 'from-green-500 to-emerald-600',
    unpaid: 'from-gray-500 to-slate-600',
    wfh: 'from-purple-500 to-indigo-600',
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

// Computed: Filter requests based on team filters (approved requests only)
const teamFilteredRequests = computed(() => {
    return props.requests.filter((req) => {
        // Only approved requests
        if (req.status !== 'approved') return false;

        // Only include if department matches a selected team

        const matchingTeam = props.teams.find((t) => t.name === req.department);

        // If no matching team found, exclude the request
        if (!matchingTeam) return false;
        // console.log(matchingTeam, req);

        // Only include if the team is selected (teamFilters[teamId] === true)
        if (teamFilters.value[matchingTeam.id] === true) {
            return true;
        }
        return teamFilters.value[matchingTeam.id] === true;
    });
});

// Get ALL approved requests for a day (for counting, respects team filter but ignores type filters)
const getAllApprovedRequestsForDay = (date: Date) => {
    return teamFilteredRequests.value.filter((req) => {
        return date >= req.startDate && date <= req.endDate;
    });
};

// Get filtered requests for a day (for display, respects both type and team filters)
const getRequestsForDay = (date: Date) => {
    return teamFilteredRequests.value.filter((req) => {
        if (!filters.value[req.type]) return false;
        return date >= req.startDate && date <= req.endDate;
    });
};

const getTeamCoverage = (date: Date) => {
    const totalEmployees = props.totalEmployees;
    // Count ALL approved absences, regardless of filters
    const absences = getAllApprovedRequestsForDay(date);
    const availableCount = totalEmployees - absences.length;
    const coveragePercent =
        totalEmployees > 0 ? (availableCount / totalEmployees) * 100 : 0;

    return {
        absences: absences.length,
        available: availableCount,
        coveragePercent,
        isLowCoverage: coveragePercent < 60,
    };
};

const openDayModal = (date: Date) => {
    selectedDay.value = date;
    isModalOpen.value = true;
};

const selectedDayRequests = computed(() => {
    if (!selectedDay.value) return [];
    return getAllApprovedRequestsForDay(selectedDay.value);
});

const selectedDayCoverage = computed(() => {
    if (!selectedDay.value) return null;
    return getTeamCoverage(selectedDay.value);
});

const goToPreviousMonth = () => {
    currentMonth.value = subMonths(currentMonth.value, 1);
};

const goToNextMonth = () => {
    currentMonth.value = addMonths(currentMonth.value, 1);
};
</script>

<template>
    <Card
        class="flex-1 border border-slate-200/50 bg-white/60 backdrop-blur-sm dark:border-white/10 dark:bg-white/5"
    >
        <CardHeader>
            <div class="flex items-center justify-between">
                <CardTitle class="text-slate-900 dark:text-white"
                    >Team Availability Calendar</CardTitle
                >
                <div class="flex items-center gap-2">
                    <Button
                        variant="outline"
                        size="icon"
                        @click="goToPreviousMonth"
                        class="border-slate-200/50 bg-transparent text-slate-700 hover:bg-white/60 dark:border-white/10 dark:text-white dark:hover:bg-white/10"
                    >
                        <ChevronLeft class="h-4 w-4" />
                    </Button>
                    <div
                        class="min-w-[150px] text-center font-semibold text-slate-900 dark:text-white"
                    >
                        {{ format(currentMonth, 'MMMM yyyy') }}
                    </div>
                    <Button
                        variant="outline"
                        size="icon"
                        @click="goToNextMonth"
                        class="border-slate-200/50 bg-transparent text-slate-700 hover:bg-white/60 dark:border-white/10 dark:text-white dark:hover:bg-white/10"
                    >
                        <ChevronRight class="h-4 w-4" />
                    </Button>
                </div>
            </div>
        </CardHeader>
        <CardContent class="space-y-6">
            <!-- Filters -->
            <div class="space-y-4">
                <!-- Team Filters -->
                <div
                    class="flex flex-wrap gap-6 rounded-lg border border-slate-200/50 bg-white/40 p-4 dark:border-white/10 dark:bg-white/5"
                >
                    <div
                        class="text-sm font-semibold text-slate-900 dark:text-white"
                    >
                        Teams: ({{ teams.length }} total)
                    </div>
                    <div
                        v-for="team in teams"
                        :key="team.id"
                        class="flex items-center space-x-2"
                    >
                        <Checkbox
                            :id="`filter-team-${team.id}`"
                            :checked="teamFilters[team.id]"
                            @update:modelValue="
                                (value: boolean | 'indeterminate') => {
                                    if (typeof value === 'boolean')
                                        teamFilters[team.id] = value;
                                }
                            "
                            class="border-slate-300 data-[state=checked]:border-orange-500 data-[state=checked]:bg-gradient-to-r data-[state=checked]:from-orange-500 data-[state=checked]:to-rose-500 dark:border-white/50"
                        />
                        <Label
                            :for="`filter-team-${team.id}`"
                            class="cursor-pointer text-sm font-normal text-slate-700 dark:text-white/80"
                        >
                            {{ team.name }}
                        </Label>
                    </div>
                </div>

                <!-- Type Filters -->
                <div
                    class="flex flex-wrap gap-6 rounded-lg border border-slate-200/50 bg-white/40 p-4 dark:border-white/10 dark:bg-white/5"
                >
                    <!--                    <div-->
                    <!--                        class="text-sm font-semibold text-slate-900 dark:text-white"-->
                    <!--                    >-->
                    <!--                        Request Types:-->
                    <!--                    </div>-->
                    <!--                    <div-->
                    <!--                        v-for="(label, type) in typeLabels"-->
                    <!--                        :key="type"-->
                    <!--                        class="flex items-center space-x-2"-->
                    <!--                    >-->
                    <!--                        <Checkbox-->
                    <!--                            :id="`filter-${type}`"-->
                    <!--                            :checked="filters[type as keyof typeof filters]"-->
                    <!--                            @update:checked="-->
                    <!--                                toggleFilter(type as keyof typeof filters)-->
                    <!--                            "-->
                    <!--                            class="border-slate-300 data-[state=checked]:border-blue-500 data-[state=checked]:bg-blue-500 dark:border-white/50"-->
                    <!--                        />-->
                    <!--                        <Label-->
                    <!--                            :for="`filter-${type}`"-->
                    <!--                            class="flex cursor-pointer items-center gap-2 text-sm font-normal text-slate-700 dark:text-white/80"-->
                    <!--                        >-->
                    <!--                            <div-->
                    <!--                                :class="cn('h-3 w-3 rounded', typeColors[type])"-->
                    <!--                            />-->
                    <!--                            {{ label }}-->
                    <!--                        </Label>-->
                    <!--                    </div>-->
                </div>

                <!-- Calendar Grid -->
                <div>
                    <div class="mb-2 grid grid-cols-7 gap-2">
                        <div
                            v-for="day in weekDays"
                            :key="day"
                            class="py-2 text-center text-sm font-semibold text-slate-500 dark:text-white/60"
                        >
                            {{ day }}
                        </div>
                    </div>
                    <div class="grid grid-cols-7 gap-2">
                        <template
                            v-for="(day, index) in calendarDays"
                            :key="index"
                        >
                            <div v-if="!day" class="aspect-square" />
                            <button
                                v-else
                                @click="openDayModal(day)"
                                :class="
                                    cn(
                                        'relative aspect-square cursor-pointer rounded-lg border border-slate-200/50 p-2 transition-all hover:scale-105 hover:shadow-lg dark:border-white/10',
                                        !isSameMonth(day, currentMonth) &&
                                            'opacity-30',
                                        isSameDay(day, new Date()) &&
                                            'border-2 border-blue-500 bg-blue-50 dark:border-blue-400 dark:bg-blue-500/10',
                                        getTeamCoverage(day).isLowCoverage &&
                                            isSameMonth(day, currentMonth) &&
                                            'border-red-200 bg-red-50 dark:border-red-500/30 dark:bg-red-500/10',
                                        isWeekend(day) &&
                                            'bg-slate-50/50 dark:bg-white/5',
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
                                                    'text-sm font-medium text-slate-700 dark:text-white/80',
                                                    isSameDay(
                                                        day,
                                                        new Date(),
                                                    ) &&
                                                        'font-bold text-blue-600 dark:text-blue-400',
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
                                            class="h-3 w-3 text-red-600 dark:text-red-400"
                                        />
                                    </div>

                                    <div
                                        v-if="getRequestsForDay(day).length > 0"
                                        class="flex-1 space-y-1 overflow-hidden"
                                    >
                                        <div
                                            v-for="(
                                                req, idx
                                            ) in getRequestsForDay(day).slice(
                                                0,
                                                2,
                                            )"
                                            :key="idx"
                                            :class="
                                                cn(
                                                    'truncate rounded px-1 py-0.5 text-[10px] text-white',
                                                    typeColors[req.type],
                                                )
                                            "
                                            :title="`${req.employeeName} - ${typeLabels[req.type]}`"
                                        >
                                            {{
                                                req.employeeName?.split(' ')[0]
                                            }}
                                        </div>
                                        <div
                                            v-if="
                                                getRequestsForDay(day).length >
                                                2
                                            "
                                            class="text-[10px] text-slate-500 dark:text-white/50"
                                        >
                                            +{{
                                                getRequestsForDay(day).length -
                                                2
                                            }}
                                            more
                                        </div>
                                    </div>

                                    <div
                                        v-if="isSameMonth(day, currentMonth)"
                                        class="mt-auto pt-1"
                                    >
                                        <div
                                            class="text-center text-[10px] text-slate-400 dark:text-white/40"
                                        >
                                            {{
                                                getTeamCoverage(day).available
                                            }}/{{ totalEmployees }}
                                            <p class="invisible md:visible">
                                                available
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </button>
                        </template>
                    </div>
                </div>

                <!-- Legend -->
                <div class="space-y-3">
                    <div
                        class="flex flex-wrap gap-4 text-sm text-slate-600 dark:text-white/80"
                    >
                        <div
                            v-for="(label, type) in typeLabels"
                            :key="type"
                            class="flex items-center gap-2"
                        >
                            <div
                                :class="cn('h-4 w-4 rounded', typeColors[type])"
                            />
                            <span>{{ label }}</span>
                        </div>
                    </div>
                    <div
                        class="flex items-center gap-2 text-sm text-slate-600 dark:text-white/60"
                    >
                        <AlertCircle
                            class="h-4 w-4 text-red-500 dark:text-red-400"
                        />
                        <span> Low coverage (less than 60% available) </span>
                    </div>
                </div>
            </div>
        </CardContent>
    </Card>

    <!-- Day Details Modal -->
    <Dialog v-model:open="isModalOpen">
        <DialogContent
            class="flex max-h-[85vh] max-w-4xl flex-col border-white/20 bg-white/90 backdrop-blur-xl dark:border-white/10 dark:bg-slate-900/90"
        >
            <DialogHeader
                class="shrink-0 border-b border-slate-200/50 pb-4 dark:border-white/10"
            >
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div
                            class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-orange-500 to-rose-500 shadow-lg"
                        >
                            <CalendarIcon class="h-7 w-7 text-white" />
                        </div>
                        <div>
                            <DialogTitle
                                class="text-2xl font-bold text-slate-900 dark:text-white"
                            >
                                {{
                                    selectedDay
                                        ? format(
                                              selectedDay,
                                              'EEEE, MMMM d, yyyy',
                                          )
                                        : ''
                                }}
                            </DialogTitle>
                            <p
                                class="mt-1 text-sm text-slate-600 dark:text-slate-400"
                            >
                                Team availability and vacation requests
                                (excluding owners)
                            </p>
                        </div>
                    </div>
                </div>
            </DialogHeader>

            <!-- Scrollable Content -->
            <div
                class="flex-1 space-y-6 overflow-y-auto py-4 pr-2"
                style="scrollbar-gutter: stable"
                v-if="selectedDay"
            >
                <!-- Coverage Stats -->
                <div class="grid gap-4 md:grid-cols-3">
                    <!-- Total Employees -->
                    <div
                        class="group relative overflow-hidden rounded-2xl border border-white/40 bg-white/70 p-4 shadow-lg backdrop-blur-xl transition-all duration-300 hover:scale-[1.02] dark:border-white/20 dark:bg-slate-800/40"
                    >
                        <div
                            class="absolute inset-0 bg-gradient-to-br from-slate-500/10 to-slate-700/10 opacity-0 transition-opacity duration-300 group-hover:opacity-100"
                        />
                        <div class="relative">
                            <p
                                class="text-xs font-semibold tracking-wider text-slate-500 uppercase dark:text-slate-400"
                            >
                                Total Employees
                            </p>
                            <p
                                class="mt-1 text-3xl font-bold text-slate-900 dark:text-white"
                            >
                                {{ totalEmployees }}
                            </p>
                        </div>
                    </div>

                    <!-- Available -->
                    <div
                        class="group relative overflow-hidden rounded-2xl border border-white/40 bg-white/70 p-4 shadow-lg backdrop-blur-xl transition-all duration-300 hover:scale-[1.02] dark:border-white/20 dark:bg-slate-800/40"
                    >
                        <div
                            class="absolute inset-0 bg-gradient-to-br from-emerald-500/10 to-teal-600/10 opacity-0 transition-opacity duration-300 group-hover:opacity-100"
                        />
                        <div class="relative">
                            <p
                                class="text-xs font-semibold tracking-wider text-emerald-600 uppercase dark:text-emerald-400"
                            >
                                Available
                            </p>
                            <p
                                class="mt-1 text-3xl font-bold text-emerald-700 dark:text-emerald-300"
                            >
                                {{ selectedDayCoverage?.available || 0 }}
                            </p>
                        </div>
                    </div>

                    <!-- Absent -->
                    <div
                        class="group relative overflow-hidden rounded-2xl border border-white/40 bg-white/70 p-4 shadow-lg backdrop-blur-xl transition-all duration-300 hover:scale-[1.02] dark:border-white/20 dark:bg-slate-800/40"
                    >
                        <div
                            class="absolute inset-0 bg-gradient-to-br from-rose-500/10 to-pink-600/10 opacity-0 transition-opacity duration-300 group-hover:opacity-100"
                        />
                        <div class="relative">
                            <p
                                class="text-xs font-semibold tracking-wider text-rose-600 uppercase dark:text-rose-400"
                            >
                                Absent
                            </p>
                            <p
                                class="mt-1 text-3xl font-bold text-rose-700 dark:text-rose-300"
                            >
                                {{ selectedDayCoverage?.absences || 0 }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Low Coverage Warning -->
                <div
                    v-if="selectedDayCoverage?.isLowCoverage"
                    class="flex items-center gap-3 rounded-2xl border border-red-200 bg-gradient-to-r from-red-50 to-rose-50 p-4 dark:border-red-500/20 dark:from-red-500/10 dark:to-rose-500/10"
                >
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-red-500 shadow-lg"
                    >
                        <AlertCircle class="h-5 w-5 text-white" />
                    </div>
                    <div>
                        <p class="font-semibold text-red-900 dark:text-red-300">
                            Low Coverage Alert
                        </p>
                        <p class="text-sm text-red-700 dark:text-red-400">
                            Less than 60% of team members are available on this
                            day
                        </p>
                    </div>
                </div>

                <!-- Vacation Requests List -->
                <div>
                    <h3
                        class="mb-4 flex items-center gap-2 text-lg font-bold text-slate-900 dark:text-white"
                    >
                        <User class="h-5 w-5 text-orange-500" />
                        Vacation Requests
                        <span
                            class="ml-2 flex h-6 min-w-[24px] items-center justify-center rounded-full bg-gradient-to-r from-orange-500 to-rose-500 px-2 text-xs font-bold text-white"
                        >
                            {{ selectedDayRequests.length }}
                        </span>
                    </h3>

                    <!-- Empty State -->
                    <div
                        v-if="selectedDayRequests.length === 0"
                        class="flex flex-col items-center justify-center rounded-2xl border border-slate-200/50 bg-white/60 py-12 backdrop-blur-xl dark:border-white/10 dark:bg-slate-800/40"
                    >
                        <div
                            class="mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-slate-100 to-slate-200 dark:from-slate-800 dark:to-slate-900"
                        >
                            <User
                                class="h-8 w-8 text-slate-400 dark:text-slate-500"
                            />
                        </div>
                        <p
                            class="text-sm font-medium text-slate-600 dark:text-slate-400"
                        >
                            No vacation requests for this day
                        </p>
                    </div>

                    <!-- Requests List -->
                    <div v-else class="space-y-3">
                        <div
                            v-for="(request, index) in selectedDayRequests"
                            :key="request.id"
                            :style="{ animationDelay: `${index * 50}ms` }"
                            class="request-card group relative overflow-hidden rounded-2xl border border-white/40 bg-white/60 backdrop-blur-xl transition-all duration-300 hover:scale-[1.01] hover:shadow-xl dark:border-white/20 dark:bg-slate-800/40"
                        >
                            <!-- Gradient border glow on hover -->
                            <div
                                :class="[
                                    'pointer-events-none absolute inset-0 opacity-0 transition-opacity duration-500 group-hover:opacity-100',
                                    'bg-gradient-to-r',
                                    typeGradients[request.type],
                                ]"
                                style="
                                    -webkit-mask:
                                        linear-gradient(#fff 0 0) content-box,
                                        linear-gradient(#fff 0 0);
                                    -webkit-mask-composite: xor;
                                    mask-composite: exclude;
                                    padding: 2px;
                                "
                            />

                            <!-- Content -->
                            <div class="relative flex items-center gap-4 p-4">
                                <!-- Employee Avatar -->
                                <div
                                    :class="[
                                        'flex h-12 w-12 shrink-0 items-center justify-center rounded-xl shadow-lg transition-all duration-300 group-hover:scale-110 group-hover:rotate-3',
                                        'bg-gradient-to-br',
                                        typeGradients[request.type],
                                    ]"
                                >
                                    <User class="h-6 w-6 text-white" />
                                </div>

                                <!-- Request Details -->
                                <div class="flex-1">
                                    <div class="flex items-center gap-3">
                                        <p
                                            class="text-base font-bold text-slate-900 dark:text-white"
                                        >
                                            {{
                                                request.employeeName ||
                                                'Unknown Employee'
                                            }}
                                        </p>
                                        <div
                                            :class="[
                                                'inline-flex items-center gap-1 rounded-lg px-2 py-1 text-xs font-bold shadow-md',
                                                'bg-gradient-to-r',
                                                typeGradients[request.type],
                                                'text-white',
                                            ]"
                                        >
                                            {{ typeLabels[request.type] }}
                                        </div>
                                    </div>
                                    <div
                                        class="mt-1 flex items-center gap-4 text-sm text-slate-600 dark:text-slate-400"
                                    >
                                        <span class="flex items-center gap-1">
                                            <CalendarIcon class="h-3.5 w-3.5" />
                                            {{
                                                format(
                                                    request.startDate,
                                                    'MMM d',
                                                )
                                            }}
                                            -
                                            {{
                                                format(
                                                    request.endDate,
                                                    'MMM d, yyyy',
                                                )
                                            }}
                                        </span>
                                        <span
                                            v-if="request.department"
                                            class="flex items-center gap-1"
                                        >
                                            {{ request.department }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </DialogContent>
    </Dialog>
</template>

<style scoped>
.request-card {
    animation: slideInUp 0.4s cubic-bezier(0.34, 1.56, 0.64, 1) both;
}

@keyframes slideInUp {
    from {
        transform: translateY(10px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

/* Custom scrollbar */
:deep(.overflow-y-auto) {
    scrollbar-width: thin;
    scrollbar-color: rgb(203 213 225 / 0.5) transparent;
}

:deep(.overflow-y-auto::-webkit-scrollbar) {
    width: 6px;
}

:deep(.overflow-y-auto::-webkit-scrollbar-track) {
    background: transparent;
}

:deep(.overflow-y-auto::-webkit-scrollbar-thumb) {
    background-color: rgb(203 213 225 / 0.5);
    border-radius: 3px;
}

:deep(.overflow-y-auto::-webkit-scrollbar-thumb:hover) {
    background-color: rgb(203 213 225 / 0.8);
}

.dark :deep(.overflow-y-auto) {
    scrollbar-color: rgb(255 255 255 / 0.2) transparent;
}

.dark :deep(.overflow-y-auto::-webkit-scrollbar-thumb) {
    background-color: rgb(255 255 255 / 0.2);
}

.dark :deep(.overflow-y-auto::-webkit-scrollbar-thumb:hover) {
    background-color: rgb(255 255 255 / 0.3);
}
</style>
