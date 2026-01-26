<script setup lang="ts">
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import { Shield } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import InputError from '@/components/InputError.vue';

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('admin.login.post'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Admin Login" />

    <div class="flex min-h-screen items-center justify-center bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900">
        <!-- Background pattern -->
        <div class="pointer-events-none fixed inset-0 overflow-hidden opacity-20">
            <div class="absolute -top-1/2 -left-1/2 h-full w-full animate-pulse rounded-full bg-gradient-to-br from-blue-500 via-purple-500 to-pink-500 blur-3xl" style="animation-duration: 10s" />
            <div class="absolute -bottom-1/2 -right-1/2 h-full w-full animate-pulse rounded-full bg-gradient-to-tr from-cyan-500 via-blue-500 to-indigo-500 blur-3xl" style="animation-duration: 12s" />
        </div>

        <!-- Login Card -->
        <div class="relative w-full max-w-md px-6">
            <div class="rounded-3xl border border-white/10 bg-slate-900/80 p-8 shadow-2xl backdrop-blur-2xl">
                <!-- Header -->
                <div class="mb-8 text-center">
                    <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-500 to-purple-600 shadow-lg shadow-blue-500/50">
                        <Shield class="h-8 w-8 text-white" />
                    </div>
                    <h1 class="text-3xl font-bold text-white">
                        Admin Panel
                    </h1>
                    <p class="mt-2 text-sm text-slate-400">
                        Sign in to access the back office
                    </p>
                </div>

                <!-- Login Form -->
                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Email Field -->
                    <div class="space-y-2">
                        <Label for="email" class="text-slate-200">
                            Email Address
                        </Label>
                        <Input
                            id="email"
                            v-model="form.email"
                            type="email"
                            required
                            autofocus
                            autocomplete="username"
                            class="border-slate-700 bg-slate-800/50 text-white placeholder:text-slate-500 focus:border-blue-500 focus:ring-blue-500"
                            placeholder="admin@example.com"
                        />
                        <InputError :message="form.errors.email" />
                    </div>

                    <!-- Password Field -->
                    <div class="space-y-2">
                        <Label for="password" class="text-slate-200">
                            Password
                        </Label>
                        <Input
                            id="password"
                            v-model="form.password"
                            type="password"
                            required
                            autocomplete="current-password"
                            class="border-slate-700 bg-slate-800/50 text-white placeholder:text-slate-500 focus:border-blue-500 focus:ring-blue-500"
                            placeholder="••••••••"
                        />
                        <InputError :message="form.errors.password" />
                    </div>

                    <!-- Remember Me -->
                    <div class="flex items-center">
                        <input
                            id="remember"
                            v-model="form.remember"
                            type="checkbox"
                            class="h-4 w-4 rounded border-slate-700 bg-slate-800/50 text-blue-600 focus:ring-2 focus:ring-blue-500 focus:ring-offset-0"
                        />
                        <label for="remember" class="ml-2 text-sm text-slate-300">
                            Remember me
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <Button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full bg-gradient-to-r from-blue-600 to-purple-600 text-white shadow-lg shadow-blue-500/50 hover:from-blue-700 hover:to-purple-700"
                    >
                        <span v-if="!form.processing">Sign In</span>
                        <span v-else>Signing in...</span>
                    </Button>
                </form>
            </div>
        </div>
    </div>
</template>
