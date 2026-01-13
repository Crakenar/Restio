<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { Head, Form, Link } from '@inertiajs/vue3'
import { CalendarDays, Mail, Lock, User, ArrowRight, Building2 } from 'lucide-vue-next'
import { login } from '@/routes'
import { store } from '@/routes/register'
import InputError from '@/components/InputError.vue'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Spinner } from '@/components/ui/spinner'

const isLoaded = ref(false)

onMounted(() => {
    setTimeout(() => {
        isLoaded.value = true
    }, 100)
})
</script>

<template>
    <Head title="Register">
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
                        Create account
                    </h1>
                    <p class="text-white/70">Start managing your time off beautifully</p>
                </div>

                <!-- Form -->
                <Form
                    v-bind="store.form()"
                    :reset-on-success="['password', 'password_confirmation']"
                    v-slot="{ errors, processing }"
                    class="space-y-5"
                >
                    <!-- Name -->
                    <div class="space-y-2">
                        <Label for="name" class="text-white/90 text-sm font-medium">
                            Full name
                        </Label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <User class="h-5 w-5 text-white/40" />
                            </div>
                            <Input
                                id="name"
                                type="text"
                                required
                                autofocus
                                :tabindex="1"
                                autocomplete="name"
                                name="name"
                                placeholder="John Doe"
                                class="pl-12 bg-white/5 border-white/10 text-white placeholder:text-white/40 focus:border-orange-500/50 focus:ring-orange-500/20 h-12"
                            />
                        </div>
                        <InputError :message="errors.name" class="text-rose-400 text-sm" />
                        <InputError :message="errors.name" class="text-rose-400 text-sm" />
                    </div>

                    <!-- Company Name -->
                    <div class="space-y-2">
                        <Label for="company_name" class="text-white/90 text-sm font-medium">
                            Company Name
                        </Label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <Building2 class="h-5 w-5 text-white/40" />
                            </div>
                            <Input
                                id="company_name"
                                type="text"
                                required
                                :tabindex="1"
                                autocomplete="organization"
                                name="company_name"
                                placeholder="Acme Inc."
                                class="pl-12 bg-white/5 border-white/10 text-white placeholder:text-white/40 focus:border-orange-500/50 focus:ring-orange-500/20 h-12"
                            />
                        </div>
                        <InputError :message="errors.company_name" class="text-rose-400 text-sm" />
                    </div>

                    <!-- Email -->
                    <div class="space-y-2">
                        <Label for="email" class="text-white/90 text-sm font-medium">
                            Email address
                        </Label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <Mail class="h-5 w-5 text-white/40" />
                            </div>
                            <Input
                                id="email"
                                type="email"
                                required
                                :tabindex="2"
                                autocomplete="email"
                                name="email"
                                placeholder="email@example.com"
                                class="pl-12 bg-white/5 border-white/10 text-white placeholder:text-white/40 focus:border-orange-500/50 focus:ring-orange-500/20 h-12"
                            />
                        </div>
                        <InputError :message="errors.email" class="text-rose-400 text-sm" />
                    </div>

                    <!-- Password -->
                    <div class="space-y-2">
                        <Label for="password" class="text-white/90 text-sm font-medium">
                            Password
                        </Label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <Lock class="h-5 w-5 text-white/40" />
                            </div>
                            <Input
                                id="password"
                                type="password"
                                required
                                :tabindex="3"
                                autocomplete="new-password"
                                name="password"
                                placeholder="••••••••"
                                class="pl-12 bg-white/5 border-white/10 text-white placeholder:text-white/40 focus:border-orange-500/50 focus:ring-orange-500/20 h-12"
                            />
                        </div>
                        <InputError :message="errors.password" class="text-rose-400 text-sm" />
                    </div>

                    <!-- Confirm Password -->
                    <div class="space-y-2">
                        <Label for="password_confirmation" class="text-white/90 text-sm font-medium">
                            Confirm password
                        </Label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <Lock class="h-5 w-5 text-white/40" />
                            </div>
                            <Input
                                id="password_confirmation"
                                type="password"
                                required
                                :tabindex="4"
                                autocomplete="new-password"
                                name="password_confirmation"
                                placeholder="••••••••"
                                class="pl-12 bg-white/5 border-white/10 text-white placeholder:text-white/40 focus:border-orange-500/50 focus:ring-orange-500/20 h-12"
                            />
                        </div>
                        <InputError :message="errors.password_confirmation" class="text-rose-400 text-sm" />
                    </div>

                    <!-- Submit Button -->
                    <button
                        type="submit"
                        tabindex="5"
                        :disabled="processing"
                        class="group w-full h-12 rounded-xl bg-gradient-to-r from-orange-500 to-rose-500 text-white font-semibold shadow-lg shadow-orange-500/30 hover:from-orange-600 hover:to-rose-600 transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2 mt-6"
                        data-test="register-user-button"
                    >
                        <Spinner v-if="processing" class="h-5 w-5" />
                        <span v-else>Create account</span>
                        <ArrowRight v-if="!processing" class="h-5 w-5 group-hover:translate-x-1 transition-transform" />
                    </button>
                </Form>

                <!-- Login Link -->
                <div class="mt-8 text-center text-sm text-white/70">
                    Already have an account?
                    <Link
                        :href="login()"
                        class="text-orange-400 hover:text-orange-300 font-medium transition-colors ml-1"
                        :tabindex="6"
                    >
                        Sign in
                    </Link>
                </div>
            </div>

            <!-- Footer Text -->
            <p class="text-center text-white/40 text-sm mt-6">
                By creating an account, you agree to our Terms & Privacy Policy
            </p>
        </div>
    </div>
</template>
