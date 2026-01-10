<script setup lang="ts">
import { Spinner } from '@/components/ui/spinner';
import { logout } from '@/routes';
import { send } from '@/routes/verification';
import { Form, Head, Link } from '@inertiajs/vue3';
import { ArrowRight, CalendarDays, LogOut, Mail } from 'lucide-vue-next';
import { onMounted, ref } from 'vue';

defineProps<{
    status?: string;
}>();

const isLoaded = ref(false);

onMounted(() => {
    setTimeout(() => {
        isLoaded.value = true;
    }, 100);
});
</script>

<template>
    <Head title="Verify email">
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
        <link
            href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=Work+Sans:wght@300;400;500;600;700&display=swap"
            rel="stylesheet"
        />
    </Head>

    <div
        class="relative flex min-h-screen items-center justify-center overflow-hidden bg-gradient-to-br from-slate-950 via-blue-950 to-indigo-950 p-6"
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
        </div>

        <!-- Content -->
        <div
            class="relative z-10 w-full max-w-md transition-all duration-1000"
            :class="
                isLoaded
                    ? 'translate-y-0 opacity-100'
                    : 'translate-y-4 opacity-0'
            "
        >
            <!-- Logo -->
            <div class="mb-8 flex justify-center">
                <div class="flex items-center gap-3">
                    <div
                        class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-orange-400 to-rose-500 shadow-lg shadow-orange-500/30"
                    >
                        <CalendarDays class="h-6 w-6 text-white" />
                    </div>
                    <span
                        class="bg-gradient-to-r from-orange-200 via-amber-100 to-yellow-200 bg-clip-text text-3xl font-bold text-transparent"
                        style="font-family: 'DM Serif Display', serif"
                    >
                        Restio
                    </span>
                </div>
            </div>

            <!-- Card -->
            <div
                class="rounded-3xl border border-white/20 bg-white/10 p-8 shadow-2xl backdrop-blur-xl"
            >
                <!-- Header -->
                <div class="mb-8 text-center">
                    <div
                        class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-400 to-teal-500 shadow-lg shadow-emerald-500/30"
                    >
                        <Mail class="h-8 w-8 text-white" />
                    </div>
                    <h1
                        class="mb-2 text-3xl font-bold text-white"
                        style="font-family: 'DM Serif Display', serif"
                    >
                        Verify your email
                    </h1>
                    <p class="text-white/70">
                        Please verify your email address by clicking on the link
                        we just emailed to you
                    </p>
                </div>

                <!-- Success Message -->
                <div
                    v-if="status === 'verification-link-sent'"
                    class="mb-6 rounded-xl border border-emerald-500/20 bg-emerald-500/10 p-4 text-center text-sm text-emerald-400"
                >
                    A new verification link has been sent to your email address
                </div>

                <!-- Form -->
                <Form
                    v-bind="send.form()"
                    v-slot="{ processing }"
                    class="space-y-6 text-center"
                >
                    <button
                        type="submit"
                        :disabled="processing"
                        class="group flex h-12 w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-orange-500 to-rose-500 font-semibold text-white shadow-lg shadow-orange-500/30 transition-all duration-300 hover:from-orange-600 hover:to-rose-600 disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        <Spinner v-if="processing" class="h-5 w-5" />
                        <span v-else>Resend verification email</span>
                        <ArrowRight
                            v-if="!processing"
                            class="h-5 w-5 transition-transform group-hover:translate-x-1"
                        />
                    </button>

                    <Link
                        :href="logout()"
                        as="button"
                        class="flex items-center justify-center gap-2 text-sm text-white/70 transition-colors hover:text-white"
                    >
                        <LogOut class="h-4 w-4" />
                        Log out
                    </Link>
                </Form>
            </div>

            <!-- Footer Text -->
            <p class="mt-6 text-center text-sm text-white/40">
                Check your spam folder if you don't receive an email
            </p>
        </div>
    </div>
</template>
