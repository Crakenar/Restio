<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import {
    InputOTP,
    InputOTPGroup,
    InputOTPSlot,
} from '@/components/ui/input-otp';
import { Spinner } from '@/components/ui/spinner';
import { store } from '@/routes/two-factor/login';
import { Form, Head } from '@inertiajs/vue3';
import { ArrowRight, CalendarDays, ShieldCheck } from 'lucide-vue-next';
import { computed, onMounted, ref } from 'vue';

interface AuthConfigContent {
    title: string;
    description: string;
    toggleText: string;
}

const authConfigContent = computed<AuthConfigContent>(() => {
    if (showRecoveryInput.value) {
        return {
            title: 'Recovery Code',
            description:
                'Enter one of your emergency recovery codes to access your account',
            toggleText: 'Use authentication code instead',
        };
    }

    return {
        title: 'Authentication Code',
        description: 'Enter the 6-digit code from your authenticator app',
        toggleText: 'Use recovery code instead',
    };
});

const showRecoveryInput = ref<boolean>(false);
const code = ref<string>('');
const isLoaded = ref(false);

const toggleRecoveryMode = (clearErrors: () => void): void => {
    showRecoveryInput.value = !showRecoveryInput.value;
    clearErrors();
    code.value = '';
};

onMounted(() => {
    setTimeout(() => {
        isLoaded.value = true;
    }, 100);
});
</script>

<template>
    <Head title="Two-Factor Authentication">
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
                        class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-400 to-indigo-500 shadow-lg shadow-blue-500/30"
                    >
                        <ShieldCheck class="h-8 w-8 text-white" />
                    </div>
                    <h1
                        class="mb-2 text-3xl font-bold text-white"
                        style="font-family: 'DM Serif Display', serif"
                    >
                        {{ authConfigContent.title }}
                    </h1>
                    <p class="text-white/70">
                        {{ authConfigContent.description }}
                    </p>
                </div>

                <!-- Authentication Code Form -->
                <template v-if="!showRecoveryInput">
                    <Form
                        v-bind="store.form()"
                        class="space-y-6"
                        :reset-on-error="true"
                        @error="code = ''"
                        #default="{ errors, processing, clearErrors }"
                    >
                        <input type="hidden" name="code" :value="code" />
                        <div
                            class="flex flex-col items-center justify-center space-y-4"
                        >
                            <div
                                class="flex w-full items-center justify-center"
                            >
                                <InputOTP
                                    id="otp"
                                    v-model="code"
                                    :maxlength="6"
                                    :disabled="processing"
                                    autofocus
                                    class="gap-2"
                                >
                                    <InputOTPGroup class="gap-2">
                                        <InputOTPSlot
                                            v-for="index in 6"
                                            :key="index"
                                            :index="index - 1"
                                            class="h-14 w-14 border-white/10 bg-white/5 text-lg text-white focus:border-orange-500/50 focus:ring-orange-500/20"
                                        />
                                    </InputOTPGroup>
                                </InputOTP>
                            </div>
                            <InputError
                                :message="errors.code"
                                class="text-sm text-rose-400"
                            />
                        </div>

                        <button
                            type="submit"
                            :disabled="processing"
                            class="group flex h-12 w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-orange-500 to-rose-500 font-semibold text-white shadow-lg shadow-orange-500/30 transition-all duration-300 hover:from-orange-600 hover:to-rose-600 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            <Spinner v-if="processing" class="h-5 w-5" />
                            <span v-else>Verify</span>
                            <ArrowRight
                                v-if="!processing"
                                class="h-5 w-5 transition-transform group-hover:translate-x-1"
                            />
                        </button>

                        <div class="text-center text-sm text-white/60">
                            <span>or you can </span>
                            <button
                                type="button"
                                class="text-orange-400 underline underline-offset-4 transition-colors hover:text-orange-300"
                                @click="() => toggleRecoveryMode(clearErrors)"
                            >
                                {{ authConfigContent.toggleText }}
                            </button>
                        </div>
                    </Form>
                </template>

                <!-- Recovery Code Form -->
                <template v-else>
                    <Form
                        v-bind="store.form()"
                        class="space-y-6"
                        :reset-on-error="true"
                        #default="{ errors, processing, clearErrors }"
                    >
                        <div class="space-y-2">
                            <Input
                                name="recovery_code"
                                type="text"
                                placeholder="Enter your recovery code"
                                :autofocus="showRecoveryInput"
                                required
                                class="h-12 border-white/10 bg-white/5 text-white placeholder:text-white/40 focus:border-orange-500/50 focus:ring-orange-500/20"
                            />
                            <InputError
                                :message="errors.recovery_code"
                                class="text-sm text-rose-400"
                            />
                        </div>

                        <button
                            type="submit"
                            :disabled="processing"
                            class="group flex h-12 w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-orange-500 to-rose-500 font-semibold text-white shadow-lg shadow-orange-500/30 transition-all duration-300 hover:from-orange-600 hover:to-rose-600 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            <Spinner v-if="processing" class="h-5 w-5" />
                            <span v-else>Verify</span>
                            <ArrowRight
                                v-if="!processing"
                                class="h-5 w-5 transition-transform group-hover:translate-x-1"
                            />
                        </button>

                        <div class="text-center text-sm text-white/60">
                            <span>or you can </span>
                            <button
                                type="button"
                                class="text-orange-400 underline underline-offset-4 transition-colors hover:text-orange-300"
                                @click="() => toggleRecoveryMode(clearErrors)"
                            >
                                {{ authConfigContent.toggleText }}
                            </button>
                        </div>
                    </Form>
                </template>
            </div>

            <!-- Footer Text -->
            <p class="mt-6 text-center text-sm text-white/40">
                Your account is protected with two-factor authentication
            </p>
        </div>
    </div>
</template>
