<script setup lang="ts">
import { computed, ref, watch, onMounted } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Alert, AlertDescription } from '@/components/ui/alert';
import ScrollableModalContent from '@/components/ui/ScrollableModalContent.vue';
import {
    Calendar,
    Plane,
    Heart,
    User,
    DollarSign,
    Home,
    AlertCircle,
    Sparkles
} from 'lucide-vue-next';
import { VacationRequestType } from '@/enums/VacationRequestType';

interface Props {
    selectedDates?: Date[];
}

const props = withDefaults(defineProps<Props>(), {
    selectedDates: () => [],
});

const emit = defineEmits<{
    success: [];
    cancel: [];
}>();

const form = useForm({
    start_date: '',
    end_date: '',
    type: VacationRequestType.VACATION,
    reason: '',
});

const selectedType = ref<VacationRequestType>(VacationRequestType.VACATION);

// Helper to check if date is weekend
const isWeekend = (date: Date): boolean => {
    const day = date.getDay();
    return day === 0 || day === 6; // Sunday = 0, Saturday = 6
};

// Helper to split dates into continuous ranges
const splitIntoContinuousRanges = (dates: Date[]): Date[][] => {
    if (dates.length === 0) return [];

    const sorted = [...dates].sort((a, b) => a.getTime() - b.getTime());
    const ranges: Date[][] = [];
    let currentRange: Date[] = [sorted[0]];

    for (let i = 1; i < sorted.length; i++) {
        const prevDate = sorted[i - 1];
        const currDate = sorted[i];
        const diffDays = Math.floor((currDate.getTime() - prevDate.getTime()) / (1000 * 60 * 60 * 24));

        // If dates are consecutive (including weekends in between)
        if (diffDays <= 1) {
            currentRange.push(currDate);
        } else {
            ranges.push(currentRange);
            currentRange = [currDate];
        }
    }
    ranges.push(currentRange);

    return ranges;
};

// Calculate days from selected dates (excluding weekends)
const calculatedDays = computed(() => {
    if (props.selectedDates && props.selectedDates.length > 0) {
        // Count only selected days that are not weekends
        return props.selectedDates.filter(date => !isWeekend(date)).length;
    }

    if (!form.start_date || !form.end_date) return 0;

    const start = new Date(form.start_date);
    const end = new Date(form.end_date);
    let count = 0;

    for (let d = new Date(start); d <= end; d.setDate(d.getDate() + 1)) {
        if (!isWeekend(d)) {
            count++;
        }
    }

    return count;
});

// Get continuous date ranges from selected dates
const dateRanges = computed(() => {
    if (props.selectedDates && props.selectedDates.length > 0) {
        return splitIntoContinuousRanges(props.selectedDates);
    }
    return [];
});

// Pre-fill form with selected dates from calendar
onMounted(() => {
    if (props.selectedDates && props.selectedDates.length > 0) {
        const sortedDates = [...props.selectedDates].sort((a, b) => a.getTime() - b.getTime());
        form.start_date = sortedDates[0].toISOString().split('T')[0];
        form.end_date = sortedDates[sortedDates.length - 1].toISOString().split('T')[0];
    }
});

// Update form type when selection changes
watch(selectedType, (newType) => {
    form.type = newType;
});

const requestTypes = [
    {
        value: VacationRequestType.VACATION,
        label: 'Vacation',
        icon: Plane,
        gradient: 'from-orange-500 to-rose-500',
        description: 'Paid time off',
    },
    {
        value: VacationRequestType.SICK,
        label: 'Sick Leave',
        icon: Heart,
        gradient: 'from-red-500 to-pink-500',
        description: 'Medical leave',
    },
    {
        value: VacationRequestType.PERSONAL,
        label: 'Personal',
        icon: User,
        gradient: 'from-purple-500 to-indigo-500',
        description: 'Personal day',
    },
    {
        value: VacationRequestType.UNPAID,
        label: 'Unpaid',
        icon: DollarSign,
        gradient: 'from-slate-500 to-gray-600',
        description: 'Unpaid leave',
    },
    {
        value: VacationRequestType.WFH,
        label: 'Work From Home',
        icon: Home,
        gradient: 'from-blue-500 to-cyan-500',
        description: 'Remote work',
    },
];

const submitForm = async () => {
    // If we have multiple continuous ranges, create multiple requests
    if (dateRanges.value.length > 1) {
        let allSuccessful = true;

        for (const range of dateRanges.value) {
            const rangeForm = useForm({
                start_date: range[0].toISOString().split('T')[0],
                end_date: range[range.length - 1].toISOString().split('T')[0],
                type: form.type,
                reason: form.reason,
            });

            await new Promise<void>((resolve) => {
                rangeForm.post('/vacation-requests', {
                    onSuccess: () => {
                        resolve();
                    },
                    onError: () => {
                        allSuccessful = false;
                        resolve();
                    },
                    preserveScroll: true,
                });
            });

            if (!allSuccessful) break;
        }

        if (allSuccessful) {
            form.reset();
            emit('success');
        }
    } else {
        // Single continuous range or manual date entry
        form.post('/vacation-requests', {
            onSuccess: () => {
                form.reset();
                emit('success');
            },
        });
    }
};

const handleCancel = () => {
    form.reset();
    emit('cancel');
};
</script>

<template>
    <div class="relative" style="animation: slideInUp 0.5s cubic-bezier(0.16, 1, 0.3, 1)">
        <ScrollableModalContent>
            <!-- Header -->
            <template #header>
                <div class="pb-4 text-center">
                    <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-orange-500 to-rose-500 shadow-lg shadow-orange-500/30"
                         style="animation: scaleIn 0.6s cubic-bezier(0.34, 1.56, 0.64, 1) 0.2s backwards">
                        <Calendar class="h-8 w-8 text-white" />
                    </div>
                    <h2 class="mb-2 text-2xl font-bold text-slate-900 dark:text-white"
                        style="animation: slideInUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) 0.1s backwards">
                        Request Time Off
                    </h2>
                    <p class="text-sm text-slate-600 dark:text-slate-400"
                       style="animation: slideInUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) 0.2s backwards">
                        Submit your vacation request for approval
                    </p>
                </div>
            </template>

            <!-- Scrollable Form Content -->
            <template #default>
                <form @submit.prevent="submitForm" class="space-y-6">
            <!-- Selected Dates Display (from calendar) -->
            <div v-if="dateRanges.length > 0"
                 class="space-y-3"
                 style="animation: slideInUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) 0.3s backwards">
                <Label class="flex items-center gap-2 text-slate-700 dark:text-slate-300">
                    <Calendar class="h-4 w-4 text-orange-500" />
                    Selected Dates
                </Label>
                <div class="space-y-2">
                    <div v-for="(range, index) in dateRanges" :key="index"
                         class="rounded-xl border border-orange-200 bg-gradient-to-r from-orange-50 to-rose-50 p-3 dark:border-orange-500/20 dark:from-orange-500/10 dark:to-rose-500/10">
                        <p class="text-sm font-medium text-slate-700 dark:text-slate-300">
                            {{ range[0].toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) }}
                            <template v-if="range.length > 1">
                                - {{ range[range.length - 1].toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) }}
                            </template>
                            <span class="ml-2 text-xs text-slate-500 dark:text-slate-400">
                                ({{ range.filter(d => !isWeekend(d)).length }} {{ range.filter(d => !isWeekend(d)).length === 1 ? 'day' : 'days' }})
                            </span>
                        </p>
                    </div>
                    <p v-if="dateRanges.length > 1" class="text-xs text-slate-500 dark:text-slate-400">
                        Note: {{ dateRanges.length }} separate requests will be created
                    </p>
                </div>
            </div>

            <!-- Date Range (manual entry) -->
            <div v-else class="grid gap-4 md:grid-cols-2"
                 style="animation: slideInUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) 0.3s backwards">
                <div class="space-y-2">
                    <Label for="start_date" class="flex items-center gap-2 text-slate-700 dark:text-slate-300">
                        <Calendar class="h-4 w-4 text-orange-500" />
                        Start Date
                    </Label>
                    <Input
                        id="start_date"
                        v-model="form.start_date"
                        type="date"
                        required
                        :min="new Date().toISOString().split('T')[0]"
                        class="border-slate-300 bg-white/80 backdrop-blur-sm transition-all duration-300 hover:bg-white focus:border-orange-500 focus:ring-orange-500/20 dark:border-white/20 dark:bg-white/5 dark:hover:bg-white/10"
                    />
                    <Alert v-if="form.errors.start_date" variant="destructive" class="mt-2">
                        <AlertCircle class="h-4 w-4" />
                        <AlertDescription>{{ form.errors.start_date }}</AlertDescription>
                    </Alert>
                </div>

                <div class="space-y-2">
                    <Label for="end_date" class="flex items-center gap-2 text-slate-700 dark:text-slate-300">
                        <Calendar class="h-4 w-4 text-rose-500" />
                        End Date
                    </Label>
                    <Input
                        id="end_date"
                        v-model="form.end_date"
                        type="date"
                        required
                        :min="form.start_date || new Date().toISOString().split('T')[0]"
                        class="border-slate-300 bg-white/80 backdrop-blur-sm transition-all duration-300 hover:bg-white focus:border-rose-500 focus:ring-rose-500/20 dark:border-white/20 dark:bg-white/5 dark:hover:bg-white/10"
                    />
                    <Alert v-if="form.errors.end_date" variant="destructive" class="mt-2">
                        <AlertCircle class="h-4 w-4" />
                        <AlertDescription>{{ form.errors.end_date }}</AlertDescription>
                    </Alert>
                </div>
            </div>

            <!-- Days Counter -->
            <div v-if="calculatedDays > 0"
                 class="group relative overflow-hidden rounded-2xl border border-orange-200 bg-gradient-to-r from-orange-50 to-rose-50 p-4 dark:border-orange-500/20 dark:from-orange-500/10 dark:to-rose-500/10"
                 style="animation: scaleIn 0.4s cubic-bezier(0.34, 1.56, 0.64, 1)">
                <div class="absolute inset-0 bg-gradient-to-r from-orange-500/10 to-rose-500/10 opacity-0 transition-opacity duration-300 group-hover:opacity-100" />
                <div class="relative flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-orange-500 to-rose-500 shadow-lg shadow-orange-500/30 transition-transform duration-300 group-hover:scale-110 group-hover:rotate-3">
                            <Sparkles class="h-6 w-6 text-white" />
                        </div>
                        <div>
                            <p class="text-sm font-medium text-slate-600 dark:text-slate-400">Duration</p>
                            <p class="text-xs text-slate-500 dark:text-slate-500">Total days requested</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-4xl font-bold text-transparent bg-gradient-to-r from-orange-600 to-rose-600 bg-clip-text dark:from-orange-400 dark:to-rose-400">
                            {{ calculatedDays }}
                        </p>
                        <p class="text-xs text-slate-600 dark:text-slate-400">
                            {{ calculatedDays === 1 ? 'day' : 'days' }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Request Type -->
            <div class="space-y-3"
                 style="animation: slideInUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) 0.4s backwards">
                <Label class="text-slate-700 dark:text-slate-300">Request Type</Label>
                <div class="grid gap-3 sm:grid-cols-2">
                    <button
                        v-for="type in requestTypes"
                        :key="type.value"
                        type="button"
                        @click="selectedType = type.value"
                        :class="[
                            'group relative overflow-hidden rounded-xl border p-4 text-left transition-all duration-300',
                            selectedType === type.value
                                ? 'border-transparent bg-gradient-to-r shadow-lg scale-105'
                                : 'border-slate-200 bg-white/60 hover:bg-white hover:border-slate-300 dark:border-white/10 dark:bg-white/5 dark:hover:bg-white/10',
                            selectedType === type.value ? type.gradient : '',
                        ]"
                    >
                        <div class="absolute inset-0 bg-gradient-to-r opacity-0 transition-opacity duration-300 group-hover:opacity-10"
                             :class="type.gradient" />
                        <div class="relative flex items-center gap-3">
                            <div :class="[
                                'flex h-10 w-10 items-center justify-center rounded-lg transition-all duration-300',
                                selectedType === type.value
                                    ? 'bg-white/20 shadow-lg'
                                    : 'bg-slate-100 dark:bg-slate-800'
                            ]">
                                <component
                                    :is="type.icon"
                                    :class="[
                                        'h-5 w-5 transition-transform duration-300 group-hover:scale-110',
                                        selectedType === type.value
                                            ? 'text-white'
                                            : 'text-slate-600 dark:text-slate-400'
                                    ]"
                                />
                            </div>
                            <div class="flex-1">
                                <p :class="[
                                    'font-semibold transition-colors',
                                    selectedType === type.value
                                        ? 'text-white'
                                        : 'text-slate-900 dark:text-white'
                                ]">
                                    {{ type.label }}
                                </p>
                                <p :class="[
                                    'text-xs transition-colors',
                                    selectedType === type.value
                                        ? 'text-white/80'
                                        : 'text-slate-500 dark:text-slate-400'
                                ]">
                                    {{ type.description }}
                                </p>
                            </div>
                            <div v-if="selectedType === type.value"
                                 class="flex h-6 w-6 items-center justify-center rounded-full bg-white/20"
                                 style="animation: scaleIn 0.3s cubic-bezier(0.34, 1.56, 0.64, 1)">
                                <div class="h-2 w-2 rounded-full bg-white" />
                            </div>
                        </div>
                    </button>
                </div>
                <Alert v-if="form.errors.type" variant="destructive" class="mt-2">
                    <AlertCircle class="h-4 w-4" />
                    <AlertDescription>{{ form.errors.type }}</AlertDescription>
                </Alert>
            </div>

            <!-- Reason -->
            <div class="space-y-2"
                 style="animation: slideInUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) 0.5s backwards">
                <Label for="reason" class="text-slate-700 dark:text-slate-300">
                    Reason <span class="text-slate-500">(Optional)</span>
                </Label>
                <Textarea
                    id="reason"
                    v-model="form.reason"
                    placeholder="Add any additional details about your request..."
                    rows="4"
                    maxlength="500"
                    class="resize-none border-slate-300 bg-white/80 backdrop-blur-sm transition-all duration-300 hover:bg-white focus:border-orange-500 focus:ring-orange-500/20 dark:border-white/20 dark:bg-white/5 dark:hover:bg-white/10"
                />
                <div class="flex items-center justify-between text-xs text-slate-500">
                    <span>Provide context for your manager</span>
                    <span>{{ form.reason?.length || 0 }}/500</span>
                </div>
                <Alert v-if="form.errors.reason" variant="destructive" class="mt-2">
                    <AlertCircle class="h-4 w-4" />
                    <AlertDescription>{{ form.errors.reason }}</AlertDescription>
                </Alert>
            </div>
                </form>
            </template>

            <!-- Actions (Fixed at bottom) -->
            <template #footer>
                <div class="flex gap-3" style="animation: slideInUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) 0.6s backwards">
                    <Button
                        type="button"
                        variant="outline"
                        @click="handleCancel"
                        :disabled="form.processing"
                        class="flex-1 border-slate-300 bg-white/60 backdrop-blur-sm hover:bg-white dark:border-white/20 dark:bg-white/5 dark:hover:bg-white/10"
                    >
                        Cancel
                    </Button>
                    <Button
                        @click="submitForm"
                        :disabled="form.processing"
                        class="group relative flex-1 overflow-hidden bg-gradient-to-r from-orange-500 to-rose-500 text-white shadow-lg shadow-orange-500/30 transition-all duration-300 hover:shadow-xl hover:shadow-orange-500/40 disabled:opacity-50"
                    >
                        <span class="absolute inset-0 bg-gradient-to-r from-orange-600 to-rose-600 opacity-0 transition-opacity duration-300 group-hover:opacity-100" />
                        <span class="relative flex items-center justify-center gap-2">
                            <Calendar class="h-4 w-4 transition-transform duration-300 group-hover:rotate-12" />
                            {{ form.processing ? 'Submitting...' : 'Submit Request' }}
                        </span>
                    </Button>
                </div>
            </template>
        </ScrollableModalContent>
    </div>
</template>

<style scoped>
@keyframes slideInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes scaleIn {
    from {
        opacity: 0;
        transform: scale(0.9);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}

/* Custom date input styling */
input[type="date"]::-webkit-calendar-picker-indicator {
    filter: opacity(0.6);
    cursor: pointer;
    transition: filter 0.2s;
}

input[type="date"]::-webkit-calendar-picker-indicator:hover {
    filter: opacity(1);
}

/* Dark mode date picker */
.dark input[type="date"]::-webkit-calendar-picker-indicator {
    filter: invert(1) opacity(0.6);
}

.dark input[type="date"]::-webkit-calendar-picker-indicator:hover {
    filter: invert(1) opacity(1);
}
</style>
