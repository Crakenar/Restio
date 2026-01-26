<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { Head, Form, Link } from '@inertiajs/vue3'
import { CalendarDays, Mail, Lock, ArrowRight } from 'lucide-vue-next'
import { register } from '@/routes'
import { store } from '@/routes/login'
import { request } from '@/routes/password'
import InputError from '@/components/InputError.vue'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Checkbox } from '@/components/ui/checkbox'
import { Spinner } from '@/components/ui/spinner'
import PublicLanguageSwitcher from '@/components/PublicLanguageSwitcher.vue'
import { useI18n } from 'vue-i18n'

defineProps<{
    status?: string
    canResetPassword: boolean
    canRegister: boolean
}>()

const { t } = useI18n()
const isLoaded = ref(false)

onMounted(() => {
    setTimeout(() => {
        isLoaded.value = true
    }, 100)
})
</script>

<template>
    <Head title="Log in">
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
        <link
            href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=Work+Sans:wght@300;400;500;600;700&display=swap"
            rel="stylesheet"
        />
    </Head>

    <div class="min-h-screen bg-gradient-to-br from-slate-950 via-blue-950 to-indigo-950 overflow-hidden relative flex items-center justify-center p-6">
        <!-- Animated gradient orbs -->
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div
                class="absolute -top-1/2 -right-1/2 w-full h-full rounded-full bg-gradient-to-br from-orange-500/20 via-amber-500/20 to-yellow-500/20 blur-3xl animate-pulse"
                style="animation-duration: 8s"
            />
            <div
                class="absolute -bottom-1/2 -left-1/2 w-full h-full rounded-full bg-gradient-to-tr from-blue-500/20 via-teal-500/20 to-emerald-500/20 blur-3xl animate-pulse"
                style="animation-duration: 10s; animation-delay: 1s"
            />
        </div>

        <!-- Content -->
        <div
            class="relative z-10 w-full max-w-md transition-all duration-1000"
            :class="isLoaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'"
        >
            <!-- Language Switcher -->
            <div class="flex justify-end mb-6">
                <PublicLanguageSwitcher />
            </div>

            <!-- Logo -->
            <div class="flex justify-center mb-8">
                <div class="flex items-center gap-3">
                    <div class="h-12 w-12 rounded-xl bg-gradient-to-br from-orange-400 to-rose-500 flex items-center justify-center shadow-lg shadow-orange-500/30">
                        <CalendarDays class="h-6 w-6 text-white" />
                    </div>
                    <span class="text-3xl font-bold bg-gradient-to-r from-orange-200 via-amber-100 to-yellow-200 bg-clip-text text-transparent" style="font-family: 'DM Serif Display', serif">
                        Restio
                    </span>
                </div>
            </div>

            <!-- Card -->
            <div class="rounded-3xl bg-white/10 backdrop-blur-xl border border-white/20 p-8 shadow-2xl">
                <!-- Header -->
                <div class="text-center mb-8">
                    <h1 class="text-3xl font-bold text-white mb-2" style="font-family: 'DM Serif Display', serif">
                        {{ t('auth.login.title') }}
                    </h1>
                    <p class="text-white/70">{{ t('auth.login.subtitle') }}</p>
                </div>

                <!-- Status Message -->
                <div
                    v-if="status"
                    class="mb-6 p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm text-center"
                >
                    {{ status }}
                </div>

                <!-- Form -->
                <Form
                    v-bind="store.form()"
                    :reset-on-success="['password']"
                    v-slot="{ errors, processing }"
                    class="space-y-6"
                >
                    <!-- Email -->
                    <div class="space-y-2">
                        <Label for="email" class="text-white/90 text-sm font-medium">
                            {{ t('auth.login.email') }}
                        </Label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <Mail class="h-5 w-5 text-white/40" />
                            </div>
                            <Input
                                id="email"
                                type="email"
                                name="email"
                                required
                                autofocus
                                :tabindex="1"
                                autocomplete="email"
                                placeholder="email@example.com"
                                class="pl-12 bg-white/5 border-white/10 text-white placeholder:text-white/40 focus:border-orange-500/50 focus:ring-orange-500/20 h-12"
                            />
                        </div>
                        <InputError :message="errors.email" class="text-rose-400 text-sm" />
                    </div>

                    <!-- Password -->
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <Label for="password" class="text-white/90 text-sm font-medium">
                                {{ t('auth.login.password') }}
                            </Label>
                            <Link
                                v-if="canResetPassword"
                                :href="request()"
                                class="text-sm text-orange-400 hover:text-orange-300 transition-colors"
                                :tabindex="5"
                            >
                                {{ t('auth.login.forgotPassword') }}
                            </Link>
                        </div>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <Lock class="h-5 w-5 text-white/40" />
                            </div>
                            <Input
                                id="password"
                                type="password"
                                name="password"
                                required
                                :tabindex="2"
                                autocomplete="current-password"
                                placeholder="••••••••"
                                class="pl-12 bg-white/5 border-white/10 text-white placeholder:text-white/40 focus:border-orange-500/50 focus:ring-orange-500/20 h-12"
                            />
                        </div>
                        <InputError :message="errors.password" class="text-rose-400 text-sm" />
                    </div>

                    <!-- Remember Me -->
                    <div class="flex items-center">
                        <Label for="remember" class="flex items-center gap-3 cursor-pointer text-white/80 hover:text-white transition-colors">
                            <Checkbox id="remember" name="remember" :tabindex="3" class="border-white/20 data-[state=checked]:bg-orange-500 data-[state=checked]:border-orange-500" />
                            <span class="text-sm">{{ t('auth.login.rememberMe') }}</span>
                        </Label>
                    </div>

                    <!-- Submit Button -->
                    <button
                        type="submit"
                        :tabindex="4"
                        :disabled="processing"
                        class="group w-full h-12 rounded-xl bg-gradient-to-r from-orange-500 to-rose-500 text-white font-semibold shadow-lg shadow-orange-500/30 hover:from-orange-600 hover:to-rose-600 transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
                        data-test="login-button"
                    >
                        <Spinner v-if="processing" class="h-5 w-5" />
                        <span v-else>{{ t('auth.login.signIn') }}</span>
                        <ArrowRight v-if="!processing" class="h-5 w-5 group-hover:translate-x-1 transition-transform" />
                    </button>
                </Form>

                <!-- Register Link -->
                <div v-if="canRegister" class="mt-8 text-center text-sm text-white/70">
                    {{ t('auth.login.noAccount') }}
                    <Link :href="register()" :tabindex="6" class="text-orange-400 hover:text-orange-300 font-medium transition-colors ml-1">
                        {{ t('auth.login.createAccount') }}
                    </Link>
                </div>
            </div>

            <!-- Footer Text -->
            <p class="text-center text-white/40 text-sm mt-6">
                {{ t('auth.login.footer') }}
            </p>
        </div>
    </div>
</template>
