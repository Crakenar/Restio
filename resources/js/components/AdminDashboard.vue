<script setup lang="ts">
import { Users, Calendar, FileText, TrendingUp, User, ArrowUpDown, ArrowUp, ArrowDown } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface Employee {
    id: string;
    name: string;
    email: string;
    department: string;
    totalDays: number;
    usedDays: number;
    pendingRequests: number;
}

interface VacationRequest {
    id: string;
    startDate: Date;
    endDate: Date;
    type: 'vacation' | 'sick' | 'personal' | 'unpaid' | 'wfh';
    status: 'pending' | 'approved' | 'rejected';
    reason?: string;
    employeeName?: string;
    document?: File;
}

const props = defineProps<{
    employees: Employee[];
    requests: VacationRequest[];
}>();

const emit = defineEmits<{
    selectEmployee: [employeeId: string];
}>();

const hoveredRow = ref<string | null>(null);
const sortColumn = ref<'usedDays' | 'daysRemaining' | null>(null);
const sortDirection = ref<'asc' | 'desc'>('asc');

const getInitials = (name: string) => {
    return name
        .split(' ')
        .map((n) => n[0])
        .join('')
        .toUpperCase()
        .substring(0, 2);
};

const totalPending = computed(() => props.requests.filter((r) => r.status === 'pending').length);
const totalApproved = computed(() => props.requests.filter((r) => r.status === 'approved').length);
const avgDaysUsed = computed(() => {
    if (props.employees.length === 0) return 0;
    return props.employees.reduce((acc, emp) => acc + emp.usedDays, 0) / props.employees.length;
});

const getDaysRemainingColor = (remaining: number): string => {
    if (remaining < 5) return 'from-red-500 to-rose-600';
    if (remaining < 10) return 'from-amber-500 to-orange-600';
    return 'from-emerald-500 to-teal-600';
};

const sortedEmployees = computed(() => {
    const employees = [...props.employees];

    if (!sortColumn.value) return employees;

    return employees.sort((a, b) => {
        let valueA: number;
        let valueB: number;

        if (sortColumn.value === 'usedDays') {
            valueA = a.usedDays;
            valueB = b.usedDays;
        } else {
            // daysRemaining
            valueA = a.totalDays - a.usedDays;
            valueB = b.totalDays - b.usedDays;
        }

        if (sortDirection.value === 'asc') {
            return valueA - valueB;
        } else {
            return valueB - valueA;
        }
    });
});

const handleSort = (column: 'usedDays' | 'daysRemaining') => {
    if (sortColumn.value === column) {
        // Toggle direction
        sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
    } else {
        // New column, default to ascending
        sortColumn.value = column;
        sortDirection.value = 'asc';
    }
};

const getSortIcon = (column: 'usedDays' | 'daysRemaining') => {
    if (sortColumn.value !== column) return ArrowUpDown;
    return sortDirection.value === 'asc' ? ArrowUp : ArrowDown;
};
</script>

<template>
    <div class="space-y-6">
        <!-- Stats Cards -->
        <div class="grid gap-4 md:grid-cols-4">
            <!-- Total Employees -->
            <div class="group relative overflow-hidden rounded-2xl border border-white/40 bg-white/70 p-6 shadow-xl backdrop-blur-2xl transition-all duration-300 hover:scale-[1.02] hover:shadow-2xl dark:border-white/20 dark:bg-slate-900/40">
                <div class="absolute inset-0 bg-gradient-to-br from-slate-500/10 to-slate-700/10 opacity-0 transition-opacity duration-300 group-hover:opacity-100" />
                <div class="relative flex items-center justify-between">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                            Total Employees
                        </p>
                        <p class="mt-2 text-4xl font-bold text-slate-900 dark:text-white">
                            {{ employees.length }}
                        </p>
                    </div>
                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-slate-500 to-slate-700 shadow-lg">
                        <Users class="h-7 w-7 text-white" />
                    </div>
                </div>
            </div>

            <!-- Pending Requests -->
            <div class="group relative overflow-hidden rounded-2xl border border-white/40 bg-white/70 p-6 shadow-xl backdrop-blur-2xl transition-all duration-300 hover:scale-[1.02] hover:shadow-2xl dark:border-white/20 dark:bg-slate-900/40">
                <div class="absolute inset-0 bg-gradient-to-br from-amber-500/10 to-orange-600/10 opacity-0 transition-opacity duration-300 group-hover:opacity-100" />
                <div class="relative flex items-center justify-between">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-wider text-amber-500 dark:text-amber-400">
                            Pending
                        </p>
                        <p class="mt-2 text-4xl font-bold text-amber-600 dark:text-amber-400">
                            {{ totalPending }}
                        </p>
                    </div>
                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-amber-500 to-orange-600 shadow-lg">
                        <FileText class="h-7 w-7 text-white" />
                    </div>
                </div>
            </div>

            <!-- Approved This Month -->
            <div class="group relative overflow-hidden rounded-2xl border border-white/40 bg-white/70 p-6 shadow-xl backdrop-blur-2xl transition-all duration-300 hover:scale-[1.02] hover:shadow-2xl dark:border-white/20 dark:bg-slate-900/40">
                <div class="absolute inset-0 bg-gradient-to-br from-emerald-500/10 to-teal-600/10 opacity-0 transition-opacity duration-300 group-hover:opacity-100" />
                <div class="relative flex items-center justify-between">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-wider text-emerald-500 dark:text-emerald-400">
                            Approved
                        </p>
                        <p class="mt-2 text-4xl font-bold text-emerald-600 dark:text-emerald-400">
                            {{ totalApproved }}
                        </p>
                    </div>
                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 shadow-lg">
                        <Calendar class="h-7 w-7 text-white" />
                    </div>
                </div>
            </div>

            <!-- Avg Days Used -->
            <div class="group relative overflow-hidden rounded-2xl border border-white/40 bg-white/70 p-6 shadow-xl backdrop-blur-2xl transition-all duration-300 hover:scale-[1.02] hover:shadow-2xl dark:border-white/20 dark:bg-slate-900/40">
                <div class="absolute inset-0 bg-gradient-to-br from-rose-500/10 to-pink-600/10 opacity-0 transition-opacity duration-300 group-hover:opacity-100" />
                <div class="relative flex items-center justify-between">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-wider text-rose-500 dark:text-rose-400">
                            Avg Days Used
                        </p>
                        <p class="mt-2 text-4xl font-bold text-rose-600 dark:text-rose-400">
                            {{ avgDaysUsed.toFixed(1) }}
                        </p>
                    </div>
                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-rose-500 to-pink-600 shadow-lg">
                        <TrendingUp class="h-7 w-7 text-white" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Premium Employee Table -->
        <div class="overflow-hidden">
            <!-- Empty state -->
            <div
                v-if="employees.length === 0"
                class="flex flex-col items-center justify-center py-20"
            >
                <div
                    class="mb-6 flex h-24 w-24 items-center justify-center rounded-3xl bg-gradient-to-br from-slate-100 to-slate-200 shadow-xl dark:from-slate-800 dark:to-slate-900"
                >
                    <Users class="h-12 w-12 text-slate-400 dark:text-slate-500" />
                </div>
                <h3 class="mb-2 text-xl font-bold text-slate-800 dark:text-white">
                    No employees yet
                </h3>
                <p class="text-sm text-slate-600 dark:text-slate-400">
                    Add employees to get started
                </p>
            </div>

            <!-- Employee Cards -->
            <div v-else class="space-y-3">
                <!-- Table Header -->
                <div class="grid grid-cols-[minmax(200px,2fr)_minmax(140px,1fr)_minmax(180px,1.5fr)_minmax(140px,1fr)_minmax(100px,0.8fr)] gap-4 px-6 py-4">
                    <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                        <User class="h-3.5 w-3.5" />
                        Employee
                    </div>
                    <div class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                        Department
                    </div>
                    <div class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                        <button
                            @click="handleSort('usedDays')"
                            class="flex items-center gap-1.5 transition-colors hover:text-orange-500 dark:hover:text-orange-400"
                        >
                            Days Used
                            <component
                                :is="getSortIcon('usedDays')"
                                class="h-3.5 w-3.5"
                                :class="{
                                    'text-orange-500 dark:text-orange-400': sortColumn === 'usedDays',
                                }"
                            />
                        </button>
                    </div>
                    <div class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                        <button
                            @click="handleSort('daysRemaining')"
                            class="flex items-center gap-1.5 transition-colors hover:text-orange-500 dark:hover:text-orange-400"
                        >
                            Days Remaining
                            <component
                                :is="getSortIcon('daysRemaining')"
                                class="h-3.5 w-3.5"
                                :class="{
                                    'text-orange-500 dark:text-orange-400': sortColumn === 'daysRemaining',
                                }"
                            />
                        </button>
                    </div>
                    <div class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                        Pending
                    </div>
                </div>

                <!-- Employee Rows -->
                <div
                    v-for="(employee, index) in sortedEmployees"
                    :key="employee.id"
                    :style="{ animationDelay: `${index * 50}ms` }"
                    class="employee-row group relative overflow-hidden rounded-2xl border border-white/40 bg-white/60 backdrop-blur-xl transition-all duration-300 hover:scale-[1.01] hover:border-white/60 hover:shadow-2xl dark:border-white/20 dark:bg-slate-800/40 dark:hover:border-white/30"
                    @mouseenter="hoveredRow = employee.id"
                    @mouseleave="hoveredRow = null"
                >
                    <!-- Gradient border glow on hover -->
                    <div
                        class="pointer-events-none absolute inset-0 bg-gradient-to-r from-orange-500 to-rose-500 opacity-0 transition-opacity duration-500 group-hover:opacity-100"
                        style="
                            -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
                            -webkit-mask-composite: xor;
                            mask-composite: exclude;
                            padding: 2px;
                        "
                    />

                    <!-- Content -->
                    <div class="grid grid-cols-[minmax(200px,2fr)_minmax(140px,1fr)_minmax(180px,1.5fr)_minmax(140px,1fr)_minmax(100px,0.8fr)] gap-4 px-6 py-5">
                        <!-- Employee Name with Avatar -->
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-orange-500 to-rose-500 shadow-lg transition-all duration-300 group-hover:scale-110 group-hover:rotate-3"
                            >
                                <span class="text-sm font-bold text-white">
                                    {{ getInitials(employee.name) }}
                                </span>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-bold text-slate-900 dark:text-white">
                                    {{ employee.name }}
                                </p>
                                <p class="truncate text-xs text-slate-600 dark:text-slate-400">
                                    {{ employee.email }}
                                </p>
                            </div>
                        </div>

                        <!-- Department -->
                        <div class="flex items-center">
                            <span class="text-sm font-medium text-slate-700 dark:text-slate-300">
                                {{ employee.department }}
                            </span>
                        </div>

                        <!-- Days Used with Progress Bar -->
                        <div class="flex items-center gap-3">
                            <div class="h-2 flex-1 overflow-hidden rounded-full bg-slate-200 dark:bg-slate-700">
                                <div
                                    class="h-full rounded-full bg-gradient-to-r from-orange-500 to-rose-500 transition-all duration-500"
                                    :style="{
                                        width: `${Math.min((employee.usedDays / employee.totalDays) * 100, 100)}%`,
                                    }"
                                />
                            </div>
                            <span class="text-sm font-semibold text-slate-700 dark:text-slate-300">
                                {{ employee.usedDays }}/{{ employee.totalDays }}
                            </span>
                        </div>

                        <!-- Days Remaining Badge -->
                        <div class="flex items-center">
                            <div
                                :class="[
                                    'inline-flex items-center gap-2 rounded-xl px-3 py-1.5 text-xs font-bold shadow-lg backdrop-blur-sm transition-all duration-300 group-hover:scale-105',
                                    'bg-gradient-to-r',
                                    getDaysRemainingColor(employee.totalDays - employee.usedDays),
                                    'text-white',
                                ]"
                            >
                                {{ employee.totalDays - employee.usedDays }} days
                            </div>
                        </div>

                        <!-- Pending Requests -->
                        <div class="flex items-center">
                            <div
                                v-if="employee.pendingRequests > 0"
                                class="flex h-8 w-14 items-center justify-center rounded-lg bg-gradient-to-r from-amber-500 to-orange-600 text-xs font-bold text-white shadow-lg"
                            >
                                {{ employee.pendingRequests }}
                            </div>
                            <span v-else class="text-sm text-slate-400 dark:text-slate-500">
                                —
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Staggered entrance animation */
.employee-row {
    animation: slideInUp 0.5s cubic-bezier(0.34, 1.56, 0.64, 1) both;
}

@keyframes slideInUp {
    from {
        transform: translateY(20px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}
</style>
