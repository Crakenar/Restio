<script setup lang="ts">
import { dashboard, login, register } from '@/routes';
import { Head, Link } from '@inertiajs/vue3';
import {
    CalendarDays,
    Check,
    ChevronRight,
    Sparkles,
    TrendingUp,
    Users,
} from 'lucide-vue-next';
import { onMounted, ref } from 'vue';

withDefaults(
    defineProps<{
        canRegister: boolean;
    }>(),
    {
        canRegister: true,
    },
);

const isLoaded = ref(false);

onMounted(() => {
    setTimeout(() => {
        isLoaded.value = true;
    }, 100);
});

const features = [
    {
        icon: CalendarDays,
        title: 'Smart Time Off',
        description:
            'Effortlessly manage vacation requests, sick days, and time off with an intuitive calendar interface.',
    },
    {
        icon: Users,
        title: 'Team Coordination',
        description:
            'Keep your team aligned with real-time availability tracking and coverage planning.',
    },
    {
        icon: TrendingUp,
        title: 'Analytics Dashboard',
        description:
            'Gain insights into team utilization, pending approvals, and vacation trends at a glance.',
    },
];

const benefits = [
    'Automated approval workflows',
    'Real-time team calendar',
    'Mobile-friendly interface',
    'Customizable leave policies',
    'Detailed reporting & analytics',
    'Seamless integrations',
];
</script>

<template>
    <Head title="Welcome to Restio">
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
        <link
            href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=Work+Sans:wght@300;400;500;600;700&display=swap"
            rel="stylesheet"
        />
    </Head>

    <div
        class="relative min-h-screen overflow-hidden bg-gradient-to-br from-slate-950 via-blue-950 to-indigo-950"
    >
        <!-- Animated gradient orbs -->
        <div class="pointer-events-none absolute inset-0 overflow-hidden">
            <div
                class="absolute -top-1/2 -right-1/2 h-full w-full animate-pulse rounded-full bg-gradient-to-br from-orange-500/20 via-amber-500/20 to-yellow-500/20 blur-3xl"
                style="animation-duration: 8s"
            />
            <div
                class="absolute -bottom-1/2 -left-1/2 h-full w-full animate-pulse rounded-full bg-gradient-to-tr from-blue-500/20 via-teal-500/20 to-emerald-500/20 blur-3xl"
                style="animation-duration: 10s; animation-delay: 1s"
            />
            <div
                class="absolute top-1/4 left-1/3 h-96 w-96 animate-pulse rounded-full bg-gradient-to-br from-rose-500/10 to-pink-500/10 blur-3xl"
                style="animation-duration: 12s; animation-delay: 2s"
            />
        </div>

        <!-- Navigation -->
        <nav
            class="relative z-10 flex items-center justify-between px-6 py-6 transition-all duration-700 md:px-12 lg:px-16"
            :class="
                isLoaded
                    ? 'translate-y-0 opacity-100'
                    : '-translate-y-4 opacity-0'
            "
        >
            <div class="flex items-center gap-3">
                <div
                    class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-orange-400 to-rose-500 shadow-lg shadow-orange-500/30"
                >
                    <CalendarDays class="h-5 w-5 text-white" />
                </div>
                <span
                    class="bg-gradient-to-r from-orange-200 via-amber-100 to-yellow-200 bg-clip-text text-2xl font-bold text-transparent"
                    style="font-family: 'DM Serif Display', serif"
                >
                    Restio
                </span>
            </div>

            <div class="flex items-center gap-3">
                <Link
                    v-if="$page.props.auth.user"
                    :href="dashboard()"
                    class="rounded-full border border-white/20 bg-white/10 px-6 py-2.5 text-sm font-medium text-white backdrop-blur-sm transition-all duration-300 hover:bg-white/20"
                >
                    Dashboard
                </Link>
                <template v-else>
                    <Link
                        :href="login()"
                        class="rounded-full px-6 py-2.5 text-sm font-medium text-white transition-all duration-300 hover:bg-white/10"
                    >
                        Log in
                    </Link>
                    <Link
                        v-if="canRegister"
                        :href="register()"
                        class="rounded-full bg-gradient-to-r from-orange-500 to-rose-500 px-6 py-2.5 text-sm font-medium text-white shadow-lg shadow-orange-500/30 transition-all duration-300 hover:from-orange-600 hover:to-rose-600"
                    >
                        Get Started
                    </Link>
                </template>
            </div>
        </nav>

        <!-- Hero Section -->
        <div class="relative z-10 px-6 py-12 md:px-12 lg:px-16">
            <div class="mx-auto max-w-7xl">
                <div class="grid items-center gap-12 lg:grid-cols-2 lg:gap-20">
                    <!-- Left Column - Content -->
                    <div
                        class="space-y-8 transition-all delay-200 duration-1000"
                        :class="
                            isLoaded
                                ? 'translate-x-0 opacity-100'
                                : '-translate-x-8 opacity-0'
                        "
                    >
                        <!-- Badge -->
                        <div
                            class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-4 py-2 text-sm text-white/90 backdrop-blur-sm"
                        >
                            <Sparkles class="h-4 w-4 text-yellow-400" />
                            <span>Modern Vacation Management</span>
                        </div>

                        <!-- Headline -->
                        <div class="space-y-4">
                            <h1
                                class="bg-gradient-to-br from-white via-orange-100 to-rose-200 bg-clip-text text-5xl leading-tight font-bold text-transparent md:text-6xl lg:text-7xl"
                                style="font-family: 'DM Serif Display', serif"
                            >
                                Time off,
                                <br />
                                beautifully
                                <br />
                                managed
                            </h1>
                            <p
                                class="max-w-xl text-lg leading-relaxed text-white/70 md:text-xl"
                                style="font-family: 'Work Sans', sans-serif"
                            >
                                Transform how your team plans vacations and time
                                off. Restio brings clarity, simplicity, and joy
                                to leave management.
                            </p>
                        </div>

                        <!-- CTA Buttons -->
                        <div class="flex flex-col gap-4 sm:flex-row">
                            <Link
                                v-if="!$page.props.auth.user"
                                :href="register()"
                                class="group flex items-center justify-center gap-2 rounded-full bg-gradient-to-r from-orange-500 to-rose-500 px-8 py-4 text-base font-semibold text-white shadow-2xl shadow-orange-500/40 transition-all duration-300 hover:from-orange-600 hover:to-rose-600"
                            >
                                <span>Start Free Trial</span>
                                <ChevronRight
                                    class="h-5 w-5 transition-transform group-hover:translate-x-1"
                                />
                            </Link>
                            <Link
                                v-else
                                :href="dashboard()"
                                class="group flex items-center justify-center gap-2 rounded-full bg-gradient-to-r from-orange-500 to-rose-500 px-8 py-4 text-base font-semibold text-white shadow-2xl shadow-orange-500/40 transition-all duration-300 hover:from-orange-600 hover:to-rose-600"
                            >
                                <span>Go to Dashboard</span>
                                <ChevronRight
                                    class="h-5 w-5 transition-transform group-hover:translate-x-1"
                                />
                            </Link>
                            <a
                                href="#features"
                                class="flex items-center justify-center rounded-full border border-white/20 bg-white/10 px-8 py-4 text-base font-semibold text-white backdrop-blur-sm transition-all duration-300 hover:bg-white/20"
                            >
                                Learn More
                            </a>
                        </div>

                        <!-- Stats -->
                        <div
                            class="grid grid-cols-3 gap-6 border-t border-white/10 pt-8"
                        >
                            <div class="space-y-1">
                                <div class="text-3xl font-bold text-white">
                                    99%
                                </div>
                                <div class="text-sm text-white/60">
                                    Approval Rate
                                </div>
                            </div>
                            <div class="space-y-1">
                                <div class="text-3xl font-bold text-white">
                                    2.5h
                                </div>
                                <div class="text-sm text-white/60">
                                    Time Saved
                                </div>
                            </div>
                            <div class="space-y-1">
                                <div class="text-3xl font-bold text-white">
                                    100+
                                </div>
                                <div class="text-sm text-white/60">
                                    Companies
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column - Visual -->
                    <div
                        class="relative transition-all delay-300 duration-1000"
                        :class="
                            isLoaded
                                ? 'translate-x-0 opacity-100'
                                : 'translate-x-8 opacity-0'
                        "
                    >
                        <!-- Floating Cards Mockup -->
                        <div class="relative h-[600px]">
                            <!-- Card 1 - Calendar -->
                            <div
                                class="animate-float absolute top-0 right-0 w-80 rounded-2xl border border-white/20 bg-white/95 p-6 shadow-2xl backdrop-blur-xl"
                                style="animation-delay: 0s"
                            >
                                <div
                                    class="mb-4 flex items-center justify-between"
                                >
                                    <div
                                        class="text-sm font-semibold text-gray-900"
                                    >
                                        January 2026
                                    </div>
                                    <CalendarDays
                                        class="h-5 w-5 text-orange-500"
                                    />
                                </div>
                                <div class="mb-3 grid grid-cols-7 gap-2">
                                    <div
                                        v-for="day in [
                                            'S',
                                            'M',
                                            'T',
                                            'W',
                                            'T',
                                            'F',
                                            'S',
                                        ]"
                                        :key="day"
                                        class="text-center text-xs font-medium text-gray-500"
                                    >
                                        {{ day }}
                                    </div>
                                </div>
                                <div class="grid grid-cols-7 gap-2">
                                    <div
                                        v-for="i in 31"
                                        :key="i"
                                        class="aspect-square"
                                    >
                                        <div
                                            :class="[
                                                'flex h-full w-full items-center justify-center rounded-lg text-sm',
                                                i >= 20 && i <= 24
                                                    ? 'bg-gradient-to-br from-orange-400 to-rose-500 font-semibold text-white'
                                                    : i === 5 || i === 7
                                                      ? 'bg-blue-100 font-medium text-blue-700'
                                                      : 'text-gray-700 hover:bg-gray-100',
                                            ]"
                                        >
                                            {{ i }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Card 2 - Request -->
                            <div
                                class="animate-float absolute top-32 left-0 w-72 rounded-2xl bg-gradient-to-br from-blue-500 to-teal-500 p-5 shadow-2xl"
                                style="animation-delay: 0.5s"
                            >
                                <div class="mb-4 flex items-center gap-3">
                                    <div
                                        class="flex h-10 w-10 items-center justify-center rounded-full bg-white/20 font-semibold text-white backdrop-blur-sm"
                                    >
                                        JD
                                    </div>
                                    <div>
                                        <div
                                            class="text-sm font-semibold text-white"
                                        >
                                            Vacation Request
                                        </div>
                                        <div class="text-xs text-white/70">
                                            John Doe • Engineering
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-4 space-y-2">
                                    <div
                                        class="flex justify-between text-sm text-white/90"
                                    >
                                        <span>Jan 20 - Jan 24</span>
                                        <span class="font-semibold"
                                            >5 days</span
                                        >
                                    </div>
                                    <div
                                        class="h-1 overflow-hidden rounded-full bg-white/20"
                                    >
                                        <div
                                            class="h-full w-3/4 rounded-full bg-white"
                                        />
                                    </div>
                                </div>
                                <div class="flex gap-2">
                                    <button
                                        class="flex-1 rounded-lg bg-white px-3 py-2 text-sm font-semibold text-blue-600 transition-colors hover:bg-white/90"
                                    >
                                        Approve
                                    </button>
                                    <button
                                        class="flex-1 rounded-lg bg-white/20 px-3 py-2 text-sm font-semibold text-white backdrop-blur-sm transition-colors hover:bg-white/30"
                                    >
                                        Decline
                                    </button>
                                </div>
                            </div>

                            <!-- Card 3 - Stats -->
                            <div
                                class="animate-float absolute right-12 bottom-0 w-64 rounded-2xl border border-white/20 bg-white/95 p-5 shadow-2xl backdrop-blur-xl"
                                style="animation-delay: 1s"
                            >
                                <div class="mb-3 flex items-center gap-2">
                                    <TrendingUp
                                        class="h-5 w-5 text-emerald-500"
                                    />
                                    <span
                                        class="text-sm font-semibold text-gray-900"
                                        >Team Overview</span
                                    >
                                </div>
                                <div class="space-y-3">
                                    <div
                                        class="flex items-center justify-between"
                                    >
                                        <span class="text-sm text-gray-600"
                                            >Days Used</span
                                        >
                                        <span
                                            class="text-sm font-bold text-gray-900"
                                            >128 / 250</span
                                        >
                                    </div>
                                    <div
                                        class="h-2 overflow-hidden rounded-full bg-gray-100"
                                    >
                                        <div
                                            class="h-full w-1/2 rounded-full bg-gradient-to-r from-emerald-400 to-teal-500"
                                        />
                                    </div>
                                    <div class="flex gap-2 pt-2">
                                        <div
                                            class="flex-1 rounded-lg bg-emerald-50 p-2 text-center"
                                        >
                                            <div
                                                class="text-lg font-bold text-emerald-700"
                                            >
                                                12
                                            </div>
                                            <div
                                                class="text-xs text-emerald-600"
                                            >
                                                Approved
                                            </div>
                                        </div>
                                        <div
                                            class="flex-1 rounded-lg bg-amber-50 p-2 text-center"
                                        >
                                            <div
                                                class="text-lg font-bold text-amber-700"
                                            >
                                                3
                                            </div>
                                            <div class="text-xs text-amber-600">
                                                Pending
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Features Section -->
        <div
            id="features"
            class="relative z-10 mt-20 px-6 py-20 md:px-12 lg:px-16"
        >
            <div class="mx-auto max-w-7xl">
                <div class="mb-16 space-y-4 text-center">
                    <h2
                        class="bg-gradient-to-br from-white via-orange-100 to-rose-200 bg-clip-text text-4xl font-bold text-transparent md:text-5xl"
                        style="font-family: 'DM Serif Display', serif"
                    >
                        Everything you need
                    </h2>
                    <p class="mx-auto max-w-2xl text-lg text-white/70">
                        A complete solution for modern teams to manage time off
                        with ease and transparency
                    </p>
                </div>

                <!-- Feature Cards -->
                <div class="mb-16 grid gap-8 md:grid-cols-3">
                    <div
                        v-for="(feature, index) in features"
                        :key="index"
                        class="group rounded-2xl border border-white/10 bg-white/5 p-8 backdrop-blur-sm transition-all duration-300 hover:-translate-y-1 hover:border-white/20 hover:bg-white/10"
                    >
                        <div class="mb-4">
                            <div
                                class="inline-flex rounded-xl border border-orange-500/20 bg-gradient-to-br from-orange-500/20 to-rose-500/20 p-3"
                            >
                                <component
                                    :is="feature.icon"
                                    class="h-6 w-6 text-orange-400"
                                />
                            </div>
                        </div>
                        <h3 class="mb-3 text-xl font-bold text-white">
                            {{ feature.title }}
                        </h3>
                        <p class="leading-relaxed text-white/60">
                            {{ feature.description }}
                        </p>
                    </div>
                </div>

                <!-- Benefits List -->
                <div
                    class="rounded-3xl border border-white/10 bg-white/5 p-8 backdrop-blur-xl md:p-12"
                >
                    <div class="grid gap-8 md:grid-cols-2">
                        <div class="space-y-4">
                            <h3
                                class="mb-6 bg-gradient-to-br from-white to-orange-200 bg-clip-text text-3xl font-bold text-transparent"
                                style="font-family: 'DM Serif Display', serif"
                            >
                                Built for productivity
                            </h3>
                            <p class="leading-relaxed text-white/70">
                                Restio streamlines every aspect of leave
                                management, from submission to approval, giving
                                your team more time to focus on what matters.
                            </p>
                        </div>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div
                                v-for="(benefit, index) in benefits"
                                :key="index"
                                class="flex items-center gap-3 text-white/80"
                            >
                                <div
                                    class="flex h-5 w-5 flex-shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-emerald-400 to-teal-500"
                                >
                                    <Check class="h-3 w-3 text-white" />
                                </div>
                                <span class="text-sm">{{ benefit }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- CTA Section -->
        <div class="relative z-10 px-6 py-20 md:px-12 lg:px-16">
            <div class="mx-auto max-w-4xl space-y-8 text-center">
                <h2
                    class="bg-gradient-to-br from-white via-orange-100 to-rose-200 bg-clip-text text-4xl font-bold text-transparent md:text-5xl lg:text-6xl"
                    style="font-family: 'DM Serif Display', serif"
                >
                    Ready to get started?
                </h2>
                <p class="mx-auto max-w-2xl text-xl text-white/70">
                    Join hundreds of teams already managing their time off with
                    Restio
                </p>
                <div
                    class="flex flex-col justify-center gap-4 pt-4 sm:flex-row"
                >
                    <Link
                        v-if="!$page.props.auth.user && canRegister"
                        :href="register()"
                        class="group flex items-center justify-center gap-2 rounded-full bg-gradient-to-r from-orange-500 to-rose-500 px-10 py-5 text-lg font-semibold text-white shadow-2xl shadow-orange-500/40 transition-all duration-300 hover:from-orange-600 hover:to-rose-600"
                    >
                        <span>Create Account</span>
                        <ChevronRight
                            class="h-5 w-5 transition-transform group-hover:translate-x-1"
                        />
                    </Link>
                    <Link
                        v-if="!$page.props.auth.user"
                        :href="login()"
                        class="flex items-center justify-center rounded-full border border-white/20 bg-white/10 px-10 py-5 text-lg font-semibold text-white backdrop-blur-sm transition-all duration-300 hover:bg-white/20"
                    >
                        Sign In
                    </Link>
                    <Link
                        v-else
                        :href="dashboard()"
                        class="group flex items-center justify-center gap-2 rounded-full bg-gradient-to-r from-orange-500 to-rose-500 px-10 py-5 text-lg font-semibold text-white shadow-2xl shadow-orange-500/40 transition-all duration-300 hover:from-orange-600 hover:to-rose-600"
                    >
                        <span>Go to Dashboard</span>
                        <ChevronRight
                            class="h-5 w-5 transition-transform group-hover:translate-x-1"
                        />
                    </Link>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <footer
            class="relative z-10 border-t border-white/10 px-6 py-8 md:px-12 lg:px-16"
        >
            <div
                class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-4 text-sm text-white/50 md:flex-row"
            >
                <div class="flex items-center gap-2">
                    <CalendarDays class="h-4 w-4" />
                    <span>&copy; 2026 Restio. All rights reserved.</span>
                </div>
                <div class="flex gap-6">
                    <a href="#" class="transition-colors hover:text-white"
                        >Privacy</a
                    >
                    <a href="#" class="transition-colors hover:text-white"
                        >Terms</a
                    >
                    <a href="#" class="transition-colors hover:text-white"
                        >Support</a
                    >
                </div>
            </div>
        </footer>
    </div>
</template>

<style scoped>
@keyframes float {
    0%,
    100% {
        transform: translateY(0px);
    }
    50% {
        transform: translateY(-20px);
    }
}

.animate-float {
    animation: float 6s ease-in-out infinite;
}
</style>
