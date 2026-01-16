<script setup lang="ts">
import { Users, Search, Mail, Calendar, User, Shield, Crown, Briefcase } from 'lucide-vue-next';
import { ref, computed } from 'vue';
import { UserRole } from '@/enums/UserRole';

interface Employee {
    id: number;
    name: string;
    email: string;
    role: string;
    created_at: string;
}

interface Props {
    employees: Employee[];
}

const props = defineProps<Props>();

const searchQuery = ref('');
const hoveredRow = ref<number | null>(null);

const filteredEmployees = computed(() => {
    if (!searchQuery.value) return props.employees;

    const query = searchQuery.value.toLowerCase();
    return props.employees.filter(emp =>
        emp.name.toLowerCase().includes(query) ||
        emp.email.toLowerCase().includes(query) ||
        emp.role.toLowerCase().includes(query)
    );
});

const roleConfig: Record<string, { gradient: string; icon: any; textColor: string }> = {
    [UserRole.OWNER]: {
        gradient: 'from-amber-500 to-orange-600',
        icon: Crown,
        textColor: 'text-amber-600 dark:text-amber-400',
    },
    [UserRole.ADMIN]: {
        gradient: 'from-rose-500 to-pink-600',
        icon: Shield,
        textColor: 'text-rose-600 dark:text-rose-400',
    },
    [UserRole.MANAGER]: {
        gradient: 'from-orange-500 to-rose-500',
        icon: Briefcase,
        textColor: 'text-orange-600 dark:text-orange-400',
    },
    [UserRole.EMPLOYEE]: {
        gradient: 'from-slate-500 to-slate-700',
        icon: User,
        textColor: 'text-slate-600 dark:text-slate-400',
    },
};

const getRoleConfig = (role: string) => {
    return roleConfig[role] || roleConfig[UserRole.EMPLOYEE];
};

const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    return new Intl.DateTimeFormat('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    }).format(date);
};

const capitalizeFirst = (str: string) => {
    return str.charAt(0).toUpperCase() + str.slice(1);
};

// Get initials for avatar
const getInitials = (name: string): string => {
    return name
        .split(' ')
        .map(n => n[0])
        .join('')
        .toUpperCase()
        .substring(0, 2);
};

// Stats
const totalEmployees = computed(() => props.employees.length);
const adminCount = computed(() => props.employees.filter(e => e.role === UserRole.ADMIN || e.role === UserRole.OWNER).length);
const managerCount = computed(() => props.employees.filter(e => e.role === UserRole.MANAGER).length);
</script>

<template>
    <div class="space-y-6">
        <!-- Stats Cards at Top -->
        <div class="grid gap-4 md:grid-cols-3">
            <!-- Total Employees -->
            <div class="group relative overflow-hidden rounded-2xl border border-white/40 bg-white/70 p-6 shadow-xl backdrop-blur-2xl transition-all duration-300 hover:scale-[1.02] hover:shadow-2xl dark:border-white/20 dark:bg-slate-900/40">
                <div class="absolute inset-0 bg-gradient-to-br from-slate-500/10 to-slate-700/10 opacity-0 transition-opacity duration-300 group-hover:opacity-100" />
                <div class="relative flex items-center justify-between">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                            Total Employees
                        </p>
                        <p class="mt-2 text-4xl font-bold text-slate-900 dark:text-white">
                            {{ totalEmployees }}
                        </p>
                    </div>
                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-slate-500 to-slate-700 shadow-lg">
                        <Users class="h-7 w-7 text-white" />
                    </div>
                </div>
            </div>

            <!-- Admins/Owners -->
            <div class="group relative overflow-hidden rounded-2xl border border-white/40 bg-white/70 p-6 shadow-xl backdrop-blur-2xl transition-all duration-300 hover:scale-[1.02] hover:shadow-2xl dark:border-white/20 dark:bg-slate-900/40">
                <div class="absolute inset-0 bg-gradient-to-br from-rose-500/10 to-pink-600/10 opacity-0 transition-opacity duration-300 group-hover:opacity-100" />
                <div class="relative flex items-center justify-between">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-wider text-rose-500 dark:text-rose-400">
                            Admins & Owners
                        </p>
                        <p class="mt-2 text-4xl font-bold text-rose-600 dark:text-rose-400">
                            {{ adminCount }}
                        </p>
                    </div>
                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-rose-500 to-pink-600 shadow-lg">
                        <Shield class="h-7 w-7 text-white" />
                    </div>
                </div>
            </div>

            <!-- Managers -->
            <div class="group relative overflow-hidden rounded-2xl border border-white/40 bg-white/70 p-6 shadow-xl backdrop-blur-2xl transition-all duration-300 hover:scale-[1.02] hover:shadow-2xl dark:border-white/20 dark:bg-slate-900/40">
                <div class="absolute inset-0 bg-gradient-to-br from-orange-500/10 to-rose-500/10 opacity-0 transition-opacity duration-300 group-hover:opacity-100" />
                <div class="relative flex items-center justify-between">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-wider text-orange-500 dark:text-orange-400">
                            Managers
                        </p>
                        <p class="mt-2 text-4xl font-bold text-orange-600 dark:text-orange-400">
                            {{ managerCount }}
                        </p>
                    </div>
                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-orange-500 to-rose-500 shadow-lg">
                        <Briefcase class="h-7 w-7 text-white" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Premium Search Bar -->
        <div class="relative">
            <!-- Gradient glow -->
            <div class="pointer-events-none absolute inset-0 -z-10 rounded-2xl bg-gradient-to-r from-orange-500/20 via-amber-500/20 to-rose-500/20 blur-2xl" />

            <div class="relative flex items-center gap-3 rounded-2xl border border-white/40 bg-white/80 px-6 py-4 shadow-xl backdrop-blur-2xl dark:border-white/20 dark:bg-slate-900/60">
                <Search class="h-5 w-5 text-slate-400 dark:text-slate-500" />
                <input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Search employees by name, email, or role..."
                    class="flex-1 bg-transparent text-sm font-medium text-slate-900 placeholder-slate-400 outline-none dark:text-white dark:placeholder-slate-500"
                />
                <div
                    v-if="searchQuery"
                    class="flex items-center gap-2 rounded-xl bg-gradient-to-r from-orange-500 to-rose-500 px-3 py-1.5 text-xs font-bold text-white shadow-lg"
                >
                    {{ filteredEmployees.length }} results
                </div>
            </div>
        </div>

        <!-- Premium Employee Table -->
        <div class="overflow-hidden">
            <!-- Empty state -->
            <div
                v-if="filteredEmployees.length === 0"
                class="flex flex-col items-center justify-center py-20"
            >
                <div
                    class="mb-6 flex h-24 w-24 items-center justify-center rounded-3xl bg-gradient-to-br from-slate-100 to-slate-200 shadow-xl dark:from-slate-800 dark:to-slate-900"
                >
                    <Users class="h-12 w-12 text-slate-400 dark:text-slate-500" />
                </div>
                <h3 class="mb-2 text-xl font-bold text-slate-800 dark:text-white">
                    {{ searchQuery ? 'No employees found' : 'No employees yet' }}
                </h3>
                <p class="text-sm text-slate-600 dark:text-slate-400">
                    {{ searchQuery ? 'Try adjusting your search query' : 'Add employees to get started' }}
                </p>
            </div>

            <!-- Employee Cards -->
            <div v-else class="space-y-3">
                <!-- Table Header -->
                <div class="grid grid-cols-[minmax(200px,2fr)_minmax(220px,2fr)_minmax(140px,1fr)_minmax(140px,1fr)] gap-4 px-6 py-4">
                    <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                        <User class="h-3.5 w-3.5" />
                        Employee
                    </div>
                    <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                        <Mail class="h-3.5 w-3.5" />
                        Email
                    </div>
                    <div class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                        Role
                    </div>
                    <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                        <Calendar class="h-3.5 w-3.5" />
                        Joined
                    </div>
                </div>

                <!-- Employee Rows -->
                <div
                    v-for="(employee, index) in filteredEmployees"
                    :key="employee.id"
                    :style="{ animationDelay: `${index * 50}ms` }"
                    class="employee-row group relative overflow-hidden rounded-2xl border border-white/40 bg-white/60 backdrop-blur-xl transition-all duration-300 hover:scale-[1.01] hover:border-white/60 hover:shadow-2xl dark:border-white/20 dark:bg-slate-800/40 dark:hover:border-white/30"
                    @mouseenter="hoveredRow = employee.id"
                    @mouseleave="hoveredRow = null"
                >
                    <!-- Gradient border glow on hover -->
                    <div
                        :class="[
                            'pointer-events-none absolute inset-0 opacity-0 transition-opacity duration-500 group-hover:opacity-100',
                            'bg-gradient-to-r',
                            getRoleConfig(employee.role).gradient,
                        ]"
                        style="
                            -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
                            -webkit-mask-composite: xor;
                            mask-composite: exclude;
                            padding: 2px;
                        "
                    />

                    <!-- Content -->
                    <div class="grid grid-cols-[minmax(200px,2fr)_minmax(220px,2fr)_minmax(140px,1fr)_minmax(140px,1fr)] gap-4 px-6 py-5">
                        <!-- Employee Name with Avatar -->
                        <div class="flex items-center gap-3">
                            <div
                                :class="[
                                    'flex h-12 w-12 shrink-0 items-center justify-center rounded-xl shadow-lg transition-all duration-300 group-hover:scale-110 group-hover:rotate-3',
                                    'bg-gradient-to-br',
                                    getRoleConfig(employee.role).gradient,
                                ]"
                            >
                                <span class="text-sm font-bold text-white">
                                    {{ getInitials(employee.name) }}
                                </span>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-bold text-slate-900 dark:text-white">
                                    {{ employee.name }}
                                </p>
                            </div>
                        </div>

                        <!-- Email -->
                        <div class="flex items-center">
                            <span class="truncate text-sm text-slate-600 dark:text-slate-400">
                                {{ employee.email }}
                            </span>
                        </div>

                        <!-- Role Badge -->
                        <div class="flex items-center">
                            <div
                                :class="[
                                    'inline-flex items-center gap-2 rounded-xl px-3 py-1.5 text-xs font-bold shadow-lg backdrop-blur-sm transition-all duration-300 group-hover:scale-105',
                                    'bg-gradient-to-r',
                                    getRoleConfig(employee.role).gradient,
                                    'text-white',
                                ]"
                            >
                                <component :is="getRoleConfig(employee.role).icon" class="h-3.5 w-3.5" />
                                {{ capitalizeFirst(employee.role) }}
                            </div>
                        </div>

                        <!-- Joined Date -->
                        <div class="flex items-center">
                            <span class="text-sm text-slate-600 dark:text-slate-400">
                                {{ formatDate(employee.created_at) }}
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
