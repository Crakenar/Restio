<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { UserRole, VacationRequestStatus, VacationRequestType } from '@/enums';
import { format } from 'date-fns';
import { Check, X, User, Calendar, Clock, FileText } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface VacationRequest {
    id: string;
    startDate: Date;
    endDate: Date;
    type: VacationRequestType;
    status: VacationRequestStatus;
    reason?: string;
    rejectionReason?: string;
    employeeName?: string;
    document?: File;
}

const props = defineProps<{
    requests: VacationRequest[];
    userRole: UserRole;
}>();

const emit = defineEmits<{
    approve: [id: string, employeeName: string];
    reject: [id: string, employeeName: string];
}>();

const hoveredRow = ref<string | null>(null);

const statusConfig: Record<string, { gradient: string; icon: string; textColor: string }> = {
    pending: {
        gradient: 'from-amber-500 to-orange-600',
        icon: '⏳',
        textColor: 'text-amber-600 dark:text-amber-400',
    },
    approved: {
        gradient: 'from-emerald-500 to-teal-600',
        icon: '✓',
        textColor: 'text-emerald-600 dark:text-emerald-400',
    },
    rejected: {
        gradient: 'from-rose-500 to-red-600',
        icon: '✗',
        textColor: 'text-rose-600 dark:text-rose-400',
    },
};

const typeLabels: Record<string, string> = {
    vacation: 'Paid Leave',
    sick: 'Sick Leave',
    personal: 'Personal Day',
    unpaid: 'Unpaid Leave',
    wfh: 'Work From Home',
};

const canApprove = computed(
    () =>
        props.userRole === UserRole.MANAGER ||
        props.userRole === UserRole.ADMIN ||
        props.userRole === UserRole.OWNER,
);

const showEmployeeColumn = computed(
    () =>
        props.userRole === UserRole.MANAGER ||
        props.userRole === UserRole.ADMIN ||
        props.userRole === UserRole.OWNER,
);

const getDayCount = (startDate: Date, endDate: Date) => {
    return (
        Math.ceil(
            (endDate.getTime() - startDate.getTime()) / (1000 * 60 * 60 * 24),
        ) + 1
    );
};

const capitalizeFirst = (str: string) => {
    return str.charAt(0).toUpperCase() + str.slice(1);
};

const truncateText = (text: string | undefined, maxLength: number = 30): string => {
    if (!text) return 'No reason provided';
    return text.length > maxLength ? text.substring(0, maxLength) + '...' : text;
};

// Consistent grid configuration for alignment
const gridClass = computed(() => {
    if (canApprove.value && showEmployeeColumn.value) {
        return 'grid-cols-[minmax(160px,1.2fr)_minmax(120px,1fr)_minmax(120px,1fr)_minmax(120px,1fr)_80px_minmax(180px,1.5fr)_minmax(120px,1fr)_140px]';
    } else if (canApprove.value) {
        return 'grid-cols-[minmax(120px,1fr)_minmax(120px,1fr)_minmax(120px,1fr)_80px_minmax(180px,1.5fr)_minmax(120px,1fr)_140px]';
    } else if (showEmployeeColumn.value) {
        return 'grid-cols-[minmax(160px,1.2fr)_minmax(120px,1fr)_minmax(120px,1fr)_minmax(120px,1fr)_80px_minmax(180px,1.5fr)_minmax(120px,1fr)]';
    } else {
        return 'grid-cols-[minmax(120px,1fr)_minmax(120px,1fr)_minmax(120px,1fr)_80px_minmax(180px,1.5fr)_minmax(120px,1fr)]';
    }
});
</script>

<template>
    <div class="overflow-hidden">
        <!-- Empty state -->
        <div
            v-if="requests.length === 0"
            class="flex flex-col items-center justify-center py-20"
        >
            <div
                class="mb-6 flex h-24 w-24 items-center justify-center rounded-3xl bg-gradient-to-br from-slate-100 to-slate-200 shadow-xl dark:from-slate-800 dark:to-slate-900"
            >
                <FileText class="h-12 w-12 text-slate-400 dark:text-slate-500" />
            </div>
            <h3 class="mb-2 text-xl font-bold text-slate-800 dark:text-white">
                No requests found
            </h3>
            <p class="text-sm text-slate-600 dark:text-slate-400">
                There are no time off requests to display.
            </p>
        </div>

        <!-- Premium Table -->
        <div v-else class="space-y-3">
            <!-- Table Header -->
            <div
                class="grid gap-4 px-6 py-4"
                :class="gridClass"
            >
                <div
                    v-if="showEmployeeColumn"
                    class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400"
                >
                    <User class="h-3.5 w-3.5" />
                    Employee
                </div>
                <div class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                    Type
                </div>
                <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                    <Calendar class="h-3.5 w-3.5" />
                    Start Date
                </div>
                <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                    <Calendar class="h-3.5 w-3.5" />
                    End Date
                </div>
                <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                    <Clock class="h-3.5 w-3.5" />
                    Days
                </div>
                <div class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                    Reason
                </div>
                <div class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                    Status
                </div>
                <div
                    v-if="canApprove"
                    class="text-right text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400"
                >
                    Actions
                </div>
            </div>

            <!-- Table Rows -->
            <div
                v-for="(request, index) in requests"
                :key="request.id"
                :style="{ animationDelay: `${index * 50}ms` }"
                class="request-row group relative overflow-hidden rounded-2xl border border-white/40 bg-white/60 backdrop-blur-xl transition-all duration-300 hover:scale-[1.01] hover:border-white/60 hover:shadow-2xl dark:border-white/20 dark:bg-slate-800/40 dark:hover:border-white/30"
                @mouseenter="hoveredRow = request.id"
                @mouseleave="hoveredRow = null"
            >
                <!-- Gradient border glow on hover -->
                <div
                    :class="[
                        'pointer-events-none absolute inset-0 opacity-0 transition-opacity duration-500 group-hover:opacity-100',
                        'bg-gradient-to-r',
                        statusConfig[request.status].gradient,
                    ]"
                    style="
                        -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
                        -webkit-mask-composite: xor;
                        mask-composite: exclude;
                        padding: 2px;
                    "
                />

                <!-- Content -->
                <div
                    class="grid gap-4 px-6 py-5"
                    :class="gridClass"
                >
                    <!-- Employee -->
                    <div
                        v-if="showEmployeeColumn"
                        class="flex items-center gap-3"
                    >
                        <div
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 shadow-lg transition-all duration-300 group-hover:scale-110 group-hover:rotate-3"
                        >
                            <User class="h-5 w-5 text-white" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-semibold text-slate-900 dark:text-white">
                                {{ request.employeeName || 'Unknown' }}
                            </p>
                        </div>
                    </div>

                    <!-- Type -->
                    <div class="flex items-center">
                        <span class="text-sm font-medium text-slate-700 dark:text-slate-300">
                            {{ typeLabels[request.type] }}
                        </span>
                    </div>

                    <!-- Start Date -->
                    <div class="flex items-center">
                        <span class="text-sm text-slate-600 dark:text-slate-400">
                            {{ format(request.startDate, 'MMM d, yyyy') }}
                        </span>
                    </div>

                    <!-- End Date -->
                    <div class="flex items-center">
                        <span class="text-sm text-slate-600 dark:text-slate-400">
                            {{ format(request.endDate, 'MMM d, yyyy') }}
                        </span>
                    </div>

                    <!-- Days -->
                    <div class="flex items-center">
                        <div
                            class="flex h-8 w-14 items-center justify-center rounded-lg bg-gradient-to-r from-slate-100 to-slate-200 text-xs font-bold text-slate-700 shadow-sm dark:from-slate-700 dark:to-slate-800 dark:text-slate-200"
                        >
                            {{ getDayCount(request.startDate, request.endDate) }}
                        </div>
                    </div>

                    <!-- Reason with tooltip -->
                    <div class="group/tooltip relative flex items-center">
                        <span class="truncate text-sm text-slate-600 dark:text-slate-400">
                            {{ truncateText(request.reason) }}
                        </span>

                        <!-- Tooltip on hover -->
                        <div
                            v-if="request.reason && request.reason.length > 30"
                            class="pointer-events-none absolute left-0 top-full z-50 mt-2 hidden w-64 rounded-xl border border-white/40 bg-white/95 p-4 text-sm text-slate-700 shadow-2xl backdrop-blur-xl group-hover/tooltip:block dark:border-white/20 dark:bg-slate-900/95 dark:text-slate-300"
                        >
                            <div class="absolute -top-2 left-4 h-4 w-4 rotate-45 border-l border-t border-white/40 bg-white/95 dark:border-white/20 dark:bg-slate-900/95" />
                            <p class="relative z-10">{{ request.reason }}</p>
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="flex items-center">
                        <div
                            :class="[
                                'inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-bold shadow-lg backdrop-blur-sm transition-all duration-300 group-hover:scale-105',
                                'bg-gradient-to-r',
                                statusConfig[request.status].gradient,
                                'text-white',
                            ]"
                        >
                            <span class="text-base">{{ statusConfig[request.status].icon }}</span>
                            {{ capitalizeFirst(request.status) }}
                        </div>
                    </div>

                    <!-- Actions -->
                    <div v-if="canApprove" class="flex items-center justify-end gap-2">
                        <template v-if="request.status === 'pending'">
                            <button
                                @click="emit('approve', request.id, request.employeeName || 'Unknown')"
                                class="group/btn flex h-10 w-10 items-center justify-center rounded-xl border-2 border-emerald-500/30 bg-emerald-500/10 text-emerald-600 shadow-lg backdrop-blur-sm transition-all duration-300 hover:scale-110 hover:border-emerald-500 hover:bg-emerald-500 hover:text-white hover:shadow-emerald-500/50 dark:text-emerald-400 dark:hover:text-white"
                            >
                                <Check class="h-5 w-5 transition-transform duration-300 group-hover/btn:scale-125" />
                            </button>
                            <button
                                @click="emit('reject', request.id, request.employeeName || 'Unknown')"
                                class="group/btn flex h-10 w-10 items-center justify-center rounded-xl border-2 border-rose-500/30 bg-rose-500/10 text-rose-600 shadow-lg backdrop-blur-sm transition-all duration-300 hover:scale-110 hover:border-rose-500 hover:bg-rose-500 hover:text-white hover:shadow-rose-500/50 dark:text-rose-400 dark:hover:text-white"
                            >
                                <X class="h-5 w-5 transition-transform duration-300 group-hover/btn:scale-125" />
                            </button>
                        </template>
                        <div v-else class="text-sm italic text-slate-400 dark:text-slate-500">
                            {{ request.status === 'approved' ? 'Approved' : 'Rejected' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Staggered entrance animation */
.request-row {
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
