<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import PremiumSidebar from '@/components/PremiumSidebar.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Building2, Calendar, Shield, Globe, Sparkles, Save } from 'lucide-vue-next';

interface CompanySettings {
    name: string;
    annual_days: number;
    approval_required: boolean;
    timezone: string;
}

interface Props {
    company: {
        name: string;
    };
    settings: CompanySettings;
}

const props = defineProps<Props>();
const page = usePage();

const form = useForm({
    name: props.company.name,
    annual_days: props.settings.annual_days,
    approval_required: props.settings.approval_required,
    timezone: props.settings.timezone,
});

const isChanged = computed(() => {
    return (
        form.name !== props.company.name ||
        form.annual_days !== props.settings.annual_days ||
        form.approval_required !== props.settings.approval_required ||
        form.timezone !== props.settings.timezone
    );
});

const timezones = [
    { value: 'UTC', label: 'UTC (Coordinated Universal Time)' },
    { value: 'America/New_York', label: 'Eastern Time (US & Canada)' },
    { value: 'America/Chicago', label: 'Central Time (US & Canada)' },
    { value: 'America/Denver', label: 'Mountain Time (US & Canada)' },
    { value: 'America/Los_Angeles', label: 'Pacific Time (US & Canada)' },
    { value: 'America/Anchorage', label: 'Alaska' },
    { value: 'Pacific/Honolulu', label: 'Hawaii' },
    { value: 'Europe/London', label: 'London' },
    { value: 'Europe/Paris', label: 'Paris' },
    { value: 'Europe/Berlin', label: 'Berlin' },
    { value: 'Europe/Rome', label: 'Rome' },
    { value: 'Europe/Madrid', label: 'Madrid' },
    { value: 'Europe/Amsterdam', label: 'Amsterdam' },
    { value: 'Asia/Dubai', label: 'Dubai' },
    { value: 'Asia/Tokyo', label: 'Tokyo' },
    { value: 'Asia/Shanghai', label: 'Shanghai' },
    { value: 'Asia/Singapore', label: 'Singapore' },
    { value: 'Asia/Hong_Kong', label: 'Hong Kong' },
    { value: 'Australia/Sydney', label: 'Sydney' },
    { value: 'Pacific/Auckland', label: 'Auckland' },
];

const handleSubmit = () => {
    form.post('/settings/company', {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Company Settings" />

    <div class="flex min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 dark:from-slate-950 dark:via-blue-950 dark:to-indigo-950">
        <!-- Sidebar -->
        <PremiumSidebar :notifications="$page.props.notifications || []" />

        <!-- Main content area -->
        <div class="ml-72 flex-1 p-4 transition-all duration-500 sm:p-6 lg:p-8">
            <!-- Animated gradient orbs -->
            <div class="pointer-events-none fixed inset-0 overflow-hidden">
                <div
                    class="absolute -top-1/2 -right-1/2 h-full w-full animate-pulse rounded-full bg-gradient-to-br from-indigo-500/10 via-purple-500/10 to-pink-500/10 blur-3xl dark:from-indigo-500/20 dark:via-purple-500/20 dark:to-pink-500/20"
                    style="animation-duration: 8s"
                />
                <div
                    class="absolute -bottom-1/2 -left-1/2 h-full w-full animate-pulse rounded-full bg-gradient-to-tr from-blue-500/10 via-cyan-500/10 to-teal-500/10 blur-3xl dark:from-blue-500/20 dark:via-cyan-500/20 dark:to-teal-500/20"
                    style="animation-duration: 10s; animation-delay: 1s"
                />
            </div>

            <!-- Content -->
            <div class="relative mx-auto max-w-4xl space-y-6">
                <!-- Page Header -->
                <div
                    class="group relative overflow-hidden rounded-3xl border border-white/60 bg-white/70 p-8 shadow-xl backdrop-blur-xl transition-all duration-500 hover:border-white/80 hover:bg-white/80 hover:shadow-2xl dark:border-white/10 dark:bg-slate-900/70 dark:hover:border-white/20 dark:hover:bg-slate-900/80"
                    style="animation: slideInDown 0.8s cubic-bezier(0.16, 1, 0.3, 1)"
                >
                    <!-- Gradient accent -->
                    <div
                        class="absolute top-0 right-0 h-full w-1/2 bg-gradient-to-l from-indigo-500/5 via-transparent to-transparent opacity-0 transition-opacity duration-500 group-hover:opacity-100 dark:from-indigo-400/10"
                    />

                    <div class="relative flex items-center gap-4">
                        <div
                            class="flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 shadow-lg transition-transform duration-500 group-hover:scale-110 group-hover:rotate-3"
                        >
                            <Building2 class="h-8 w-8 text-white" />
                        </div>
                        <div class="flex-1">
                            <h1
                                class="mb-1 bg-gradient-to-r from-slate-800 to-slate-600 bg-clip-text text-3xl font-bold tracking-tight text-transparent dark:from-slate-100 dark:to-slate-300"
                            >
                                Company Settings
                            </h1>
                            <p class="text-slate-600 dark:text-slate-300">
                                Configure your organization's preferences and policies
                            </p>
                        </div>
                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-amber-400 to-orange-500 shadow-md opacity-0 transition-all duration-500 group-hover:opacity-100 group-hover:rotate-12"
                        >
                            <Sparkles class="h-6 w-6 text-white" />
                        </div>
                    </div>
                </div>

                <!-- Settings Form -->
                <form
                    @submit.prevent="handleSubmit"
                    class="space-y-6"
                    style="
                        animation: slideInUp 0.8s cubic-bezier(0.16, 1, 0.3, 1);
                        animation-delay: 0.2s;
                        animation-fill-mode: both;
                    "
                >
                    <!-- Company Information Card -->
                    <div
                        class="group relative overflow-hidden rounded-3xl border border-white/60 bg-white/70 backdrop-blur-xl transition-all duration-500 hover:border-white/80 hover:bg-white/80 hover:shadow-xl dark:border-white/10 dark:bg-slate-900/70 dark:hover:border-white/20 dark:hover:bg-slate-900/80"
                    >
                        <!-- Card glow effect -->
                        <div
                            class="absolute inset-0 bg-gradient-to-br from-blue-500/5 via-transparent to-purple-500/5 opacity-0 transition-opacity duration-500 group-hover:opacity-100"
                        />

                        <div class="relative p-8">
                            <div class="mb-6 flex items-center gap-3">
                                <div
                                    class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-blue-500 to-cyan-600 shadow-md"
                                >
                                    <Building2 class="h-5 w-5 text-white" />
                                </div>
                                <div>
                                    <h2
                                        class="text-xl font-bold text-slate-900 dark:text-white"
                                    >
                                        Company Information
                                    </h2>
                                    <p class="text-sm text-slate-600 dark:text-slate-400">
                                        Basic details about your organization
                                    </p>
                                </div>
                            </div>

                            <div class="space-y-4">
                                <div class="grid gap-2">
                                    <Label
                                        for="company-name"
                                        class="text-slate-700 dark:text-slate-300"
                                    >
                                        Company Name
                                    </Label>
                                    <Input
                                        id="company-name"
                                        v-model="form.name"
                                        type="text"
                                        required
                                        placeholder="Acme Corporation"
                                        class="border-slate-200/50 bg-white/60 backdrop-blur-sm transition-all duration-300 focus:border-blue-500 focus:bg-white dark:border-white/10 dark:bg-white/5 dark:focus:border-blue-400 dark:focus:bg-white/10"
                                        :class="{
                                            'border-red-500 dark:border-red-400':
                                                form.errors.name,
                                        }"
                                    />
                                    <p
                                        v-if="form.errors.name"
                                        class="text-sm text-red-600 dark:text-red-400"
                                    >
                                        {{ form.errors.name }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Vacation Policies Card -->
                    <div
                        class="group relative overflow-hidden rounded-3xl border border-white/60 bg-white/70 backdrop-blur-xl transition-all duration-500 hover:border-white/80 hover:bg-white/80 hover:shadow-xl dark:border-white/10 dark:bg-slate-900/70 dark:hover:border-white/20 dark:hover:bg-slate-900/80"
                    >
                        <div
                            class="absolute inset-0 bg-gradient-to-br from-emerald-500/5 via-transparent to-teal-500/5 opacity-0 transition-opacity duration-500 group-hover:opacity-100"
                        />

                        <div class="relative p-8">
                            <div class="mb-6 flex items-center gap-3">
                                <div
                                    class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 shadow-md"
                                >
                                    <Calendar class="h-5 w-5 text-white" />
                                </div>
                                <div>
                                    <h2
                                        class="text-xl font-bold text-slate-900 dark:text-white"
                                    >
                                        Vacation Policies
                                    </h2>
                                    <p class="text-sm text-slate-600 dark:text-slate-400">
                                        Configure time-off allowances and defaults
                                    </p>
                                </div>
                            </div>

                            <div class="space-y-6">
                                <div class="grid gap-2">
                                    <Label
                                        for="annual-days"
                                        class="text-slate-700 dark:text-slate-300"
                                    >
                                        Annual Vacation Days
                                    </Label>
                                    <Input
                                        id="annual-days"
                                        v-model.number="form.annual_days"
                                        type="number"
                                        min="0"
                                        max="365"
                                        required
                                        placeholder="20"
                                        class="border-slate-200/50 bg-white/60 backdrop-blur-sm transition-all duration-300 focus:border-emerald-500 focus:bg-white dark:border-white/10 dark:bg-white/5 dark:focus:border-emerald-400 dark:focus:bg-white/10"
                                        :class="{
                                            'border-red-500 dark:border-red-400':
                                                form.errors.annual_days,
                                        }"
                                    />
                                    <p class="text-sm text-slate-600 dark:text-slate-400">
                                        Default number of paid vacation days per year for
                                        employees
                                    </p>
                                    <p
                                        v-if="form.errors.annual_days"
                                        class="text-sm text-red-600 dark:text-red-400"
                                    >
                                        {{ form.errors.annual_days }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Approval Settings Card -->
                    <div
                        class="group relative overflow-hidden rounded-3xl border border-white/60 bg-white/70 backdrop-blur-xl transition-all duration-500 hover:border-white/80 hover:bg-white/80 hover:shadow-xl dark:border-white/10 dark:bg-slate-900/70 dark:hover:border-white/20 dark:hover:bg-slate-900/80"
                    >
                        <div
                            class="absolute inset-0 bg-gradient-to-br from-violet-500/5 via-transparent to-purple-500/5 opacity-0 transition-opacity duration-500 group-hover:opacity-100"
                        />

                        <div class="relative p-8">
                            <div class="mb-6 flex items-center gap-3">
                                <div
                                    class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-violet-500 to-purple-600 shadow-md"
                                >
                                    <Shield class="h-5 w-5 text-white" />
                                </div>
                                <div>
                                    <h2
                                        class="text-xl font-bold text-slate-900 dark:text-white"
                                    >
                                        Approval Workflow
                                    </h2>
                                    <p class="text-sm text-slate-600 dark:text-slate-400">
                                        Define how vacation requests are handled
                                    </p>
                                </div>
                            </div>

                            <div class="space-y-4">
                                <div
                                    class="flex items-center justify-between rounded-2xl border border-slate-200/50 bg-white/40 p-6 transition-all duration-300 hover:bg-white/60 dark:border-white/10 dark:bg-white/5 dark:hover:bg-white/10"
                                >
                                    <div class="flex-1">
                                        <Label
                                            for="approval-required"
                                            class="text-base font-semibold text-slate-900 dark:text-white"
                                        >
                                            Require Manager Approval
                                        </Label>
                                        <p
                                            class="mt-1 text-sm text-slate-600 dark:text-slate-400"
                                        >
                                            When enabled, all vacation requests must be
                                            approved by a manager before being confirmed
                                        </p>
                                    </div>
                                    <Checkbox
                                        id="approval-required"
                                        :checked="form.approval_required"
                                        @update:checked="form.approval_required = $event"
                                        class="ml-4 h-6 w-6"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Regional Settings Card -->
                    <div
                        class="group relative overflow-hidden rounded-3xl border border-white/60 bg-white/70 backdrop-blur-xl transition-all duration-500 hover:border-white/80 hover:bg-white/80 hover:shadow-xl dark:border-white/10 dark:bg-slate-900/70 dark:hover:border-white/20 dark:hover:bg-slate-900/80"
                    >
                        <div
                            class="absolute inset-0 bg-gradient-to-br from-orange-500/5 via-transparent to-rose-500/5 opacity-0 transition-opacity duration-500 group-hover:opacity-100"
                        />

                        <div class="relative p-8">
                            <div class="mb-6 flex items-center gap-3">
                                <div
                                    class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-orange-500 to-rose-600 shadow-md"
                                >
                                    <Globe class="h-5 w-5 text-white" />
                                </div>
                                <div>
                                    <h2
                                        class="text-xl font-bold text-slate-900 dark:text-white"
                                    >
                                        Regional Settings
                                    </h2>
                                    <p class="text-sm text-slate-600 dark:text-slate-400">
                                        Configure timezone and regional preferences
                                    </p>
                                </div>
                            </div>

                            <div class="space-y-4">
                                <div class="grid gap-2">
                                    <Label
                                        for="timezone"
                                        class="text-slate-700 dark:text-slate-300"
                                    >
                                        Company Timezone
                                    </Label>
                                    <Select v-model="form.timezone">
                                        <SelectTrigger
                                            id="timezone"
                                            class="border-slate-200/50 bg-white/60 backdrop-blur-sm transition-all duration-300 focus:border-orange-500 focus:bg-white dark:border-white/10 dark:bg-white/5 dark:focus:border-orange-400 dark:focus:bg-white/10"
                                        >
                                            <SelectValue placeholder="Select a timezone" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem
                                                v-for="tz in timezones"
                                                :key="tz.value"
                                                :value="tz.value"
                                            >
                                                {{ tz.label }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <p class="text-sm text-slate-600 dark:text-slate-400">
                                        Used for date calculations and scheduling
                                    </p>
                                    <p
                                        v-if="form.errors.timezone"
                                        class="text-sm text-red-600 dark:text-red-400"
                                    >
                                        {{ form.errors.timezone }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Save Button -->
                    <div
                        class="flex items-center justify-end gap-4 rounded-2xl border border-white/60 bg-white/70 p-6 backdrop-blur-xl dark:border-white/10 dark:bg-slate-900/70"
                    >
                        <p
                            v-if="isChanged"
                            class="text-sm text-slate-600 dark:text-slate-400"
                        >
                            You have unsaved changes
                        </p>
                        <Button
                            type="submit"
                            :disabled="!isChanged || form.processing"
                            class="group relative overflow-hidden bg-gradient-to-r from-indigo-500 to-purple-600 px-8 py-6 text-base font-semibold text-white shadow-lg transition-all duration-300 hover:from-indigo-600 hover:to-purple-700 hover:shadow-xl disabled:from-slate-400 disabled:to-slate-500 disabled:opacity-50"
                        >
                            <span class="relative z-10 flex items-center gap-2">
                                <Save class="h-5 w-5" />
                                {{ form.processing ? 'Saving...' : 'Save Changes' }}
                            </span>
                            <div
                                class="absolute inset-0 translate-y-full bg-gradient-to-r from-purple-600 to-pink-600 transition-transform duration-300 group-hover:translate-y-0"
                            />
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<style scoped>
@keyframes slideInDown {
    from {
        opacity: 0;
        transform: translateY(-30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes slideInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>

