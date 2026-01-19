<script setup lang="ts">
import VacationCalendar from '@/components/VacationCalendar.vue';
import PremiumSidebar from '@/components/PremiumSidebar.vue';
import type { VacationRequest } from '@/types/vacation';
import { Head, usePage } from '@inertiajs/vue3';
const page = usePage();
import { computed, ref } from 'vue';
import { Calendar } from 'lucide-vue-next';

interface Props {
    requests: VacationRequest[];
    userName: string;
    userRole: string;
}

const props = defineProps<Props>();

const myRequests = computed(() =>
    props.requests.map((req) => ({
        ...req,
        startDate: new Date(req.startDate),
        endDate: new Date(req.endDate),
    })).filter((r) => r.employeeName === props.userName),
);
</script>

<template>
    <Head title="Vacation Calendar" />

    <div class="flex min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 dark:from-slate-950 dark:via-blue-950 dark:to-indigo-950">
        <!-- Sidebar -->
        <PremiumSidebar :notifications="$page.props.notifications || []" />

        <!-- Main content area -->
        <div class="ml-72 flex-1 p-4 transition-all duration-500 sm:p-6 lg:p-8">
            <!-- Animated gradient orbs -->
            <div class="pointer-events-none fixed inset-0 overflow-hidden">
                <div
                    class="absolute -top-1/2 -right-1/2 h-full w-full animate-pulse rounded-full bg-gradient-to-br from-violet-500/10 via-purple-500/10 to-fuchsia-500/10 blur-3xl dark:from-violet-500/20 dark:via-purple-500/20 dark:to-fuchsia-500/20"
                    style="animation-duration: 8s"
                />
                <div
                    class="absolute -bottom-1/2 -left-1/2 h-full w-full animate-pulse rounded-full bg-gradient-to-tr from-blue-500/10 via-cyan-500/10 to-teal-500/10 blur-3xl dark:from-blue-500/20 dark:via-cyan-500/20 dark:to-teal-500/20"
                    style="animation-duration: 10s; animation-delay: 1s"
                />
            </div>

            <!-- Content -->
            <div class="relative mx-auto max-w-7xl space-y-6">
                    <!-- Page Header -->
                    <div
                        class="group relative overflow-hidden rounded-3xl border border-white/60 bg-white/70 p-8 shadow-xl backdrop-blur-xl transition-all duration-500 hover:border-white/80 hover:bg-white/80 hover:shadow-2xl dark:border-white/10 dark:bg-slate-900/70 dark:hover:border-white/20 dark:hover:bg-slate-900/80"
                        style="animation: slideInDown 0.8s cubic-bezier(0.16, 1, 0.3, 1)"
                    >
                        <div
                            class="absolute top-0 right-0 h-full w-1/2 bg-gradient-to-l from-violet-500/5 via-transparent to-transparent opacity-0 transition-opacity duration-500 group-hover:opacity-100 dark:from-violet-400/10"
                        />

                        <div class="relative flex items-center gap-4">
                            <div
                                class="flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-violet-500 to-purple-600 shadow-lg transition-transform duration-500 group-hover:scale-110 group-hover:rotate-3"
                            >
                                <Calendar class="h-8 w-8 text-white" />
                            </div>
                            <div class="flex-1">
                                <h1
                                    class="mb-1 bg-gradient-to-r from-slate-800 to-slate-600 bg-clip-text text-3xl font-bold tracking-tight text-transparent dark:from-slate-100 dark:to-slate-300"
                                >
                                    Vacation Calendar
                                </h1>
                                <p class="text-slate-600 dark:text-slate-300">
                                    View and manage your time off requests
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Calendar Component -->
                    <div
                        style="
                            animation: slideInUp 0.8s cubic-bezier(0.16, 1, 0.3, 1);
                            animation-delay: 0.2s;
                            animation-fill-mode: both;
                        "
                    >
                        <VacationCalendar
                            :existing-requests="myRequests"
                            :user-role="userRole"
                        />
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
