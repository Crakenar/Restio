<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head, useForm, Link, usePage } from '@inertiajs/vue3';
import PremiumSidebar from '@/components/PremiumSidebar.vue';
import DeleteUser from '@/components/DeleteUser.vue';
import ToastContainer from '@/components/ToastContainer.vue';
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
import {
    User,
    Mail,
    Building2,
    Calendar,
    Shield,
    Globe,
    Save,
    CheckCircle2,
    AlertCircle,
} from 'lucide-vue-next';
import { useToast } from '@/composables/useToast';

interface Props {
    mustVerifyEmail: boolean;
    status?: string;
    company?: {
        name: string;
    };
    companySettings?: {
        annual_days: number;
        approval_required: boolean;
        timezone: string;
    };
    userRole: string;
}

const props = defineProps<Props>();
const page = usePage();
const user = page.props.auth.user;
const toast = useToast();

const isOwnerOrAdmin = computed(
    () => props.userRole === 'owner' || props.userRole === 'admin',
);

// Profile form
const profileForm = useForm({
    name: user.name,
    email: user.email,
});

const profileChanged = computed(() => {
    return profileForm.name !== user.name || profileForm.email !== user.email;
});

// Company form (only for owners/admins)
const companyForm = useForm({
    name: props.company?.name || '',
    annual_days: props.companySettings?.annual_days || 20,
    approval_required: props.companySettings?.approval_required || true,
    timezone: props.companySettings?.timezone || 'UTC',
});

const companyChanged = computed(() => {
    if (!props.company || !props.companySettings) return false;
    return (
        companyForm.name !== props.company.name ||
        companyForm.annual_days !== props.companySettings.annual_days ||
        companyForm.approval_required !== props.companySettings.approval_required ||
        companyForm.timezone !== props.companySettings.timezone
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

const handleProfileSubmit = () => {
    profileForm.patch('/settings/profile', {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Profile updated successfully!');
        },
        onError: () => {
            toast.error('Failed to update profile. Please check your inputs.');
        },
    });
};

const handleCompanySubmit = () => {
    companyForm.post('/settings/company', {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Company settings saved successfully!');
        },
        onError: () => {
            toast.error('Failed to save settings. Please check your inputs.');
        },
    });
};
</script>

<template>
    <Head title="Settings" />

    <div
        class="flex min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 dark:from-slate-950 dark:via-blue-950 dark:to-indigo-950"
    >
        <!-- Sidebar -->
        <PremiumSidebar :notifications="$page.props.notifications || []" />

        <!-- Toast Container -->
        <ToastContainer />

        <!-- Main content area -->
        <div class="ml-72 flex-1 p-4 transition-all duration-500 sm:p-6 lg:p-8">
            <!-- Animated gradient orbs -->
            <div class="pointer-events-none fixed inset-0 overflow-hidden">
                <div
                    class="absolute -top-1/2 -right-1/2 h-full w-full animate-pulse rounded-full bg-gradient-to-br from-violet-500/10 via-blue-500/10 to-cyan-500/10 blur-3xl dark:from-violet-500/20 dark:via-blue-500/20 dark:to-cyan-500/20"
                    style="animation-duration: 8s"
                />
                <div
                    class="absolute -bottom-1/2 -left-1/2 h-full w-full animate-pulse rounded-full bg-gradient-to-tr from-indigo-500/10 via-purple-500/10 to-pink-500/10 blur-3xl dark:from-indigo-500/20 dark:via-purple-500/20 dark:to-pink-500/20"
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
                    <div
                        class="absolute top-0 right-0 h-full w-1/2 bg-gradient-to-l from-blue-500/5 via-transparent to-transparent opacity-0 transition-opacity duration-500 group-hover:opacity-100 dark:from-blue-400/10"
                    />

                    <div class="relative flex items-center gap-4">
                        <div
                            class="flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-500 to-cyan-600 shadow-lg transition-transform duration-500 group-hover:scale-110 group-hover:rotate-3"
                        >
                            <User class="h-8 w-8 text-white" />
                        </div>
                        <div class="flex-1">
                            <h1
                                class="mb-1 bg-gradient-to-r from-slate-800 to-slate-600 bg-clip-text text-3xl font-bold tracking-tight text-transparent dark:from-slate-100 dark:to-slate-300"
                            >
                                Settings
                            </h1>
                            <p class="text-slate-600 dark:text-slate-300">
                                Manage your account and preferences
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Personal Settings Section -->
                <div
                    class="space-y-6"
                    style="
                        animation: slideInUp 0.8s cubic-bezier(0.16, 1, 0.3, 1);
                        animation-delay: 0.1s;
                        animation-fill-mode: both;
                    "
                >
                    <!-- Profile Information Card -->
                    <form @submit.prevent="handleProfileSubmit">
                        <div
                            class="group relative overflow-hidden rounded-3xl border border-white/60 bg-white/70 backdrop-blur-xl transition-all duration-500 hover:border-white/80 hover:bg-white/80 hover:shadow-xl dark:border-white/10 dark:bg-slate-900/70 dark:hover:border-white/20 dark:hover:bg-slate-900/80"
                        >
                            <div
                                class="absolute inset-0 bg-gradient-to-br from-blue-500/5 via-transparent to-cyan-500/5 opacity-0 transition-opacity duration-500 group-hover:opacity-100"
                            />

                            <div class="relative p-8">
                                <div class="mb-6 flex items-center gap-3">
                                    <div
                                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-blue-500 to-cyan-600 shadow-md"
                                    >
                                        <User class="h-5 w-5 text-white" />
                                    </div>
                                    <div>
                                        <h2
                                            class="text-xl font-bold text-slate-900 dark:text-white"
                                        >
                                            Personal Information
                                        </h2>
                                        <p
                                            class="text-sm text-slate-600 dark:text-slate-400"
                                        >
                                            Update your profile details
                                        </p>
                                    </div>
                                </div>

                                <div class="space-y-6">
                                    <!-- Name Field -->
                                    <div class="grid gap-2">
                                        <Label
                                            for="name"
                                            class="text-slate-700 dark:text-slate-300"
                                        >
                                            Full Name
                                        </Label>
                                        <Input
                                            id="name"
                                            v-model="profileForm.name"
                                            type="text"
                                            required
                                            placeholder="John Doe"
                                            class="border-slate-200/50 bg-white/60 backdrop-blur-sm transition-all duration-300 focus:border-blue-500 focus:bg-white dark:border-white/10 dark:bg-white/5 dark:focus:border-blue-400 dark:focus:bg-white/10"
                                            :class="{
                                                'border-red-500 dark:border-red-400':
                                                    profileForm.errors.name,
                                            }"
                                        />
                                        <p
                                            v-if="profileForm.errors.name"
                                            class="text-sm text-red-600 dark:text-red-400"
                                        >
                                            {{ profileForm.errors.name }}
                                        </p>
                                    </div>

                                    <!-- Email Field -->
                                    <div class="grid gap-2">
                                        <Label
                                            for="email"
                                            class="text-slate-700 dark:text-slate-300"
                                        >
                                            Email Address
                                        </Label>
                                        <Input
                                            id="email"
                                            v-model="profileForm.email"
                                            type="email"
                                            required
                                            placeholder="john@example.com"
                                            class="border-slate-200/50 bg-white/60 backdrop-blur-sm transition-all duration-300 focus:border-blue-500 focus:bg-white dark:border-white/10 dark:bg-white/5 dark:focus:border-blue-400 dark:focus:bg-white/10"
                                            :class="{
                                                'border-red-500 dark:border-red-400':
                                                    profileForm.errors.email,
                                            }"
                                        />
                                        <p
                                            v-if="profileForm.errors.email"
                                            class="text-sm text-red-600 dark:text-red-400"
                                        >
                                            {{ profileForm.errors.email }}
                                        </p>

                                        <!-- Email Verification Status -->
                                        <div
                                            v-if="mustVerifyEmail && !user.email_verified_at"
                                            class="mt-2 flex items-start gap-2 rounded-xl border border-amber-200 bg-amber-50 p-4 dark:border-amber-500/20 dark:bg-amber-500/10"
                                        >
                                            <AlertCircle
                                                class="mt-0.5 h-4 w-4 shrink-0 text-amber-600 dark:text-amber-400"
                                            />
                                            <div class="flex-1">
                                                <p
                                                    class="text-sm text-amber-800 dark:text-amber-300"
                                                >
                                                    Your email address is unverified.
                                                    <Link
                                                        href="/email/verification-notification"
                                                        method="post"
                                                        as="button"
                                                        class="font-semibold underline underline-offset-2 transition-colors hover:text-amber-900 dark:hover:text-amber-200"
                                                    >
                                                        Click here to resend verification
                                                        email.
                                                    </Link>
                                                </p>
                                                <p
                                                    v-if="
                                                        status === 'verification-link-sent'
                                                    "
                                                    class="mt-2 text-sm font-semibold text-amber-900 dark:text-amber-200"
                                                >
                                                    ✓ Verification email sent!
                                                </p>
                                            </div>
                                        </div>
                                        <div
                                            v-else-if="user.email_verified_at"
                                            class="mt-2 flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-2 dark:border-emerald-500/20 dark:bg-emerald-500/10"
                                        >
                                            <CheckCircle2
                                                class="h-4 w-4 text-emerald-600 dark:text-emerald-400"
                                            />
                                            <p
                                                class="text-sm font-medium text-emerald-800 dark:text-emerald-300"
                                            >
                                                Email verified
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Save Button -->
                                    <div class="flex items-center justify-end gap-4 pt-2">
                                        <Button
                                            type="submit"
                                            :disabled="
                                                !profileChanged || profileForm.processing
                                            "
                                            class="group relative overflow-hidden bg-gradient-to-r from-blue-500 to-cyan-600 px-6 py-2.5 text-sm font-semibold text-white shadow-md transition-all duration-300 hover:from-blue-600 hover:to-cyan-700 hover:shadow-lg disabled:from-slate-400 disabled:to-slate-500 disabled:opacity-50"
                                        >
                                            <span class="relative z-10 flex items-center gap-2">
                                                <Save class="h-4 w-4" />
                                                {{
                                                    profileForm.processing
                                                        ? 'Saving...'
                                                        : 'Save Profile'
                                                }}
                                            </span>
                                        </Button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>

                    <!-- Delete Account Section -->
                    <div
                        class="group relative overflow-hidden rounded-3xl border border-red-200/50 bg-white/70 backdrop-blur-xl transition-all duration-500 hover:border-red-300/60 hover:bg-white/80 hover:shadow-xl dark:border-red-500/20 dark:bg-slate-900/70 dark:hover:border-red-500/30 dark:hover:bg-slate-900/80"
                    >
                        <div
                            class="absolute inset-0 bg-gradient-to-br from-red-500/5 via-transparent to-rose-500/5 opacity-0 transition-opacity duration-500 group-hover:opacity-100"
                        />
                        <div class="relative p-8">
                            <DeleteUser />
                        </div>
                    </div>
                </div>

                <!-- Company Settings Section (Only for Owners/Admins) -->
                <div
                    v-if="isOwnerOrAdmin"
                    class="space-y-6"
                    style="
                        animation: slideInUp 0.8s cubic-bezier(0.16, 1, 0.3, 1);
                        animation-delay: 0.2s;
                        animation-fill-mode: both;
                    "
                >
                    <form @submit.prevent="handleCompanySubmit" class="space-y-6">
                        <!-- Company Information Card -->
                        <div
                            class="group relative overflow-hidden rounded-3xl border border-white/60 bg-white/70 backdrop-blur-xl transition-all duration-500 hover:border-white/80 hover:bg-white/80 hover:shadow-xl dark:border-white/10 dark:bg-slate-900/70 dark:hover:border-white/20 dark:hover:bg-slate-900/80"
                        >
                            <div
                                class="absolute inset-0 bg-gradient-to-br from-indigo-500/5 via-transparent to-purple-500/5 opacity-0 transition-opacity duration-500 group-hover:opacity-100"
                            />

                            <div class="relative p-8">
                                <div class="mb-6 flex items-center gap-3">
                                    <div
                                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 shadow-md"
                                    >
                                        <Building2 class="h-5 w-5 text-white" />
                                    </div>
                                    <div>
                                        <h2
                                            class="text-xl font-bold text-slate-900 dark:text-white"
                                        >
                                            Company Settings
                                        </h2>
                                        <p
                                            class="text-sm text-slate-600 dark:text-slate-400"
                                        >
                                            Configure organization preferences
                                        </p>
                                    </div>
                                </div>

                                <div class="space-y-6">
                                    <!-- Company Name -->
                                    <div class="grid gap-2">
                                        <Label
                                            for="company-name"
                                            class="text-slate-700 dark:text-slate-300"
                                        >
                                            Company Name
                                        </Label>
                                        <Input
                                            id="company-name"
                                            v-model="companyForm.name"
                                            type="text"
                                            required
                                            placeholder="Acme Corporation"
                                            class="border-slate-200/50 bg-white/60 backdrop-blur-sm transition-all duration-300 focus:border-indigo-500 focus:bg-white dark:border-white/10 dark:bg-white/5 dark:focus:border-indigo-400 dark:focus:bg-white/10"
                                            :class="{
                                                'border-red-500 dark:border-red-400':
                                                    companyForm.errors.name,
                                            }"
                                        />
                                        <p
                                            v-if="companyForm.errors.name"
                                            class="text-sm text-red-600 dark:text-red-400"
                                        >
                                            {{ companyForm.errors.name }}
                                        </p>
                                    </div>

                                    <!-- Annual Days -->
                                    <div class="grid gap-2">
                                        <Label
                                            for="annual-days"
                                            class="text-slate-700 dark:text-slate-300"
                                        >
                                            Annual Vacation Days
                                        </Label>
                                        <Input
                                            id="annual-days"
                                            v-model.number="companyForm.annual_days"
                                            type="number"
                                            min="0"
                                            max="365"
                                            required
                                            placeholder="20"
                                            class="border-slate-200/50 bg-white/60 backdrop-blur-sm transition-all duration-300 focus:border-indigo-500 focus:bg-white dark:border-white/10 dark:bg-white/5 dark:focus:border-indigo-400 dark:focus:bg-white/10"
                                            :class="{
                                                'border-red-500 dark:border-red-400':
                                                    companyForm.errors.annual_days,
                                            }"
                                        />
                                        <p
                                            class="text-sm text-slate-600 dark:text-slate-400"
                                        >
                                            Default paid vacation days per year for
                                            employees
                                        </p>
                                        <p
                                            v-if="companyForm.errors.annual_days"
                                            class="text-sm text-red-600 dark:text-red-400"
                                        >
                                            {{ companyForm.errors.annual_days }}
                                        </p>
                                    </div>

                                    <!-- Approval Required -->
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
                                                Vacation requests must be approved before
                                                confirmation
                                            </p>
                                        </div>
                                        <Checkbox
                                            id="approval-required"
                                            :checked="companyForm.approval_required"
                                            @update:checked="
                                                companyForm.approval_required = $event
                                            "
                                            class="ml-4 h-6 w-6"
                                        />
                                    </div>

                                    <!-- Timezone -->
                                    <div class="grid gap-2">
                                        <Label
                                            for="timezone"
                                            class="text-slate-700 dark:text-slate-300"
                                        >
                                            Company Timezone
                                        </Label>
                                        <Select v-model="companyForm.timezone">
                                            <SelectTrigger
                                                id="timezone"
                                                class="border-slate-200/50 bg-white/60 backdrop-blur-sm transition-all duration-300 focus:border-indigo-500 focus:bg-white dark:border-white/10 dark:bg-white/5 dark:focus:border-indigo-400 dark:focus:bg-white/10"
                                            >
                                                <SelectValue
                                                    placeholder="Select a timezone"
                                                />
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
                                        <p
                                            class="text-sm text-slate-600 dark:text-slate-400"
                                        >
                                            Used for date calculations and scheduling
                                        </p>
                                        <p
                                            v-if="companyForm.errors.timezone"
                                            class="text-sm text-red-600 dark:text-red-400"
                                        >
                                            {{ companyForm.errors.timezone }}
                                        </p>
                                    </div>

                                    <!-- Save Button -->
                                    <div class="flex items-center justify-end gap-4 pt-2">
                                        <Button
                                            type="submit"
                                            :disabled="
                                                !companyChanged || companyForm.processing
                                            "
                                            class="group relative overflow-hidden bg-gradient-to-r from-indigo-500 to-purple-600 px-6 py-2.5 text-sm font-semibold text-white shadow-md transition-all duration-300 hover:from-indigo-600 hover:to-purple-700 hover:shadow-lg disabled:from-slate-400 disabled:to-slate-500 disabled:opacity-50"
                                        >
                                            <span class="relative z-10 flex items-center gap-2">
                                                <Save class="h-4 w-4" />
                                                {{
                                                    companyForm.processing
                                                        ? 'Saving...'
                                                        : 'Save Company Settings'
                                                }}
                                            </span>
                                        </Button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
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

