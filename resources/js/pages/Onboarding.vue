<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { useToast } from '@/composables/useToast';
import { Building2, Check, CreditCard, Shield } from 'lucide-vue-next';
import { ref } from 'vue';
import { logout } from '@/routes';
import axios from 'axios';

interface Plan {
    id: number;
    name: string;
    slug: string;
    price: string;
    interval: string;
}

interface Props {
    plans: Plan[];
    fake_mode: boolean;
}

const props = defineProps<Props>();

const toast = useToast();
const selectedPlan = ref<number | null>(null);
const processing = ref(false);

const formatPrice = (price: string) => {
    return new Intl.NumberFormat('de-DE', {
        style: 'currency',
        currency: 'EUR',
    }).format(Number(price));
};

const getFeatures = (interval: string) => {
    switch (interval) {
        case 'month':
            return ['Up to 10 Employees', 'Basic Support', 'All Core Features'];
        case 'year':
            return [
                'Up to 50 Employees',
                'Priority Support',
                'Advanced Analytics',
                '2 Months Free',
            ];
        case 'one_time':
            return [
                'Unlimited Employees',
                'Premium Support',
                'Access to All Future Updates',
                'Never Pay Again',
            ];
        default:
            return [];
    }
};

const handlePayment = async () => {
    if (!selectedPlan.value) return;

    processing.value = true;

    try {
        // Create Stripe checkout session
        const response = await axios.post('/onboarding/checkout', {
            plan_id: selectedPlan.value,
        });

        const { checkout_url, fake } = response.data;

        if (fake) {
            // In fake mode, simulate a short delay then redirect
            setTimeout(() => {
                window.location.href = checkout_url;
            }, 1000);
        } else {
            // In real Stripe mode, redirect immediately
            window.location.href = checkout_url;
        }
    } catch (error) {
        console.error('Payment error:', error);
        processing.value = false;
        toast.error('Failed to create checkout session. Please try again.');
    }
};

const handleLogout = () => {
    router.post(logout());
};
</script>

<template>
    <Head title="Welcome to Company" />

    <div
        class="relative min-h-screen overflow-hidden bg-slate-900 text-white selection:bg-blue-500 selection:text-white"
    >
        <!-- Background Gradients -->
        <div
            class="pointer-events-none fixed top-0 left-0 h-full w-full overflow-hidden"
        >
            <div
                class="absolute top-[-10%] left-[-10%] h-[50%] w-[50%] rounded-full bg-blue-600/20 blur-[120px]"
            ></div>
            <div
                class="absolute right-[-10%] bottom-[-10%] h-[50%] w-[50%] rounded-full bg-indigo-600/20 blur-[120px]"
            ></div>
        </div>

        <div class="relative z-10 container mx-auto px-6 py-20">
            <!-- Logout Button -->
            <div class="absolute top-6 right-6">
                <button
                    @click="handleLogout"
                    class="flex items-center gap-2 text-sm font-medium text-slate-400 transition-colors hover:text-white"
                >
                    Log Out
                </button>
            </div>

            <!-- Header -->
            <div class="mx-auto mb-16 max-w-2xl text-center">
                <div
                    class="mb-6 inline-flex items-center justify-center rounded-2xl border border-blue-500/20 bg-gradient-to-br from-blue-500/10 to-indigo-500/10 p-3 backdrop-blur-xl"
                >
                    <Building2 class="h-8 w-8 text-blue-400" />
                </div>
                <h1
                    class="mb-6 bg-gradient-to-r from-white via-blue-100 to-indigo-200 bg-clip-text text-4xl font-bold text-transparent md:text-5xl"
                >
                    Select Your Plan
                </h1>
                <p class="text-lg text-slate-400">
                    Choose the perfect plan for your company. All plans include
                    a 14-day free trial.
                </p>
            </div>

            <!-- Plans Grid -->
            <div class="mx-auto mb-16 grid max-w-5xl gap-8 md:grid-cols-3">
                <div
                    v-for="plan in plans"
                    :key="plan.id"
                    class="group relative cursor-pointer transition-all duration-300"
                    @click="selectedPlan = plan.id"
                >
                    <div
                        class="absolute inset-0 rounded-3xl transition-opacity duration-300"
                        :class="
                            selectedPlan === plan.id
                                ? 'bg-gradient-to-b from-blue-500/20 to-indigo-500/20 opacity-100'
                                : 'bg-white/5 opacity-0 group-hover:opacity-100'
                        "
                    ></div>

                    <div
                        class="relative flex h-full flex-col rounded-3xl border p-8 backdrop-blur-xl transition-all duration-300"
                        :class="[
                            selectedPlan === plan.id
                                ? 'scale-105 border-blue-500/50 bg-white/10 shadow-2xl shadow-blue-500/20'
                                : 'border-white/10 bg-white/5 hover:border-white/20',
                        ]"
                    >
                        <div
                            v-if="plan.slug === 'yearly'"
                            class="absolute -top-4 left-1/2 -translate-x-1/2 rounded-full bg-gradient-to-r from-blue-500 to-indigo-500 px-4 py-1 text-xs font-bold tracking-wider uppercase shadow-lg"
                        >
                            Best Value
                        </div>

                        <h3 class="mb-2 text-xl font-semibold">
                            {{ plan.name }}
                        </h3>
                        <div class="mb-6">
                            <span class="text-4xl font-bold">{{
                                formatPrice(plan.price)
                            }}</span>
                            <span class="ml-1 text-sm text-slate-400"
                                >/ {{ plan.interval }}</span
                            >
                        </div>

                        <ul class="mb-8 flex-1 space-y-4">
                            <li
                                v-for="feature in getFeatures(plan.interval)"
                                :key="feature"
                                class="flex items-start gap-3 text-sm text-slate-300"
                            >
                                <div
                                    class="mt-0.5 flex min-h-4 min-w-4 items-center justify-center rounded-full bg-blue-500/20"
                                >
                                    <Check class="h-2.5 w-2.5 text-blue-400" />
                                </div>
                                {{ feature }}
                            </li>
                        </ul>

                        <div
                            class="flex h-12 w-full items-center justify-center rounded-xl font-semibold transition-all duration-300"
                            :class="[
                                selectedPlan === plan.id
                                    ? 'bg-blue-500 text-white shadow-lg shadow-blue-500/30'
                                    : 'bg-white/5 text-slate-300 group-hover:bg-white/10',
                            ]"
                        >
                            {{
                                selectedPlan === plan.id
                                    ? 'Selected'
                                    : 'Select Plan'
                            }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Action -->
            <div
                class="fixed right-0 bottom-0 left-0 z-50 transform border-t border-white/10 bg-slate-900/80 p-6 backdrop-blur-xl transition-all duration-500"
                :class="selectedPlan ? 'translate-y-0' : 'translate-y-full'"
            >
                <div
                    class="mx-auto flex max-w-xl items-center justify-between gap-6"
                >
                    <div class="flex items-center gap-4">
                        <div
                            class="rounded-xl bg-blue-500/20 p-3 text-blue-400"
                        >
                            <Shield class="h-6 w-6" />
                        </div>
                        <div>
                            <div class="font-medium">
                                Secure Payment via Stripe
                            </div>
                            <div class="text-sm text-slate-400">
                                Encrypted & Safe
                            </div>
                        </div>
                    </div>

                    <button
                        @click="handlePayment"
                        :disabled="processing"
                        class="flex items-center gap-2 rounded-xl bg-gradient-to-r from-blue-500 to-indigo-600 px-8 py-3 font-bold text-white shadow-lg shadow-blue-500/20 transition-all duration-300 hover:from-blue-600 hover:to-indigo-700 disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        <template v-if="processing">
                            <div
                                class="h-5 w-5 animate-spin rounded-full border-2 border-white/30 border-t-white"
                            ></div>
                            Processing...
                        </template>
                        <template v-else>
                            Proceed to Payment
                            <CreditCard class="h-5 w-5" />
                        </template>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
