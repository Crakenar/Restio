<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, Shield, Download, Trash2, Edit, AlertCircle, CheckCircle2 } from 'lucide-vue-next';
import { onMounted, ref } from 'vue';

const principles = [
    {
        icon: Shield,
        title: 'Lawfulness & Transparency',
        description: 'We process data lawfully, fairly, and transparently in accordance with GDPR requirements.',
    },
    {
        icon: CheckCircle2,
        title: 'Purpose Limitation',
        description: 'Data is collected for specific, explicit, and legitimate purposes only.',
    },
    {
        icon: Download,
        title: 'Data Minimization',
        description: 'We collect only data that is necessary for the intended purpose.',
    },
    {
        icon: Edit,
        title: 'Accuracy',
        description: 'Personal data is kept accurate and up to date with mechanisms for correction.',
    },
    {
        icon: Trash2,
        title: 'Storage Limitation',
        description: 'Data is retained only as long as necessary for the specified purposes.',
    },
    {
        icon: AlertCircle,
        title: 'Integrity & Confidentiality',
        description: 'Appropriate security measures protect data from unauthorized processing.',
    },
];

const rights = [
    {
        title: 'Right to Access',
        description: 'Request a copy of the personal data we hold about you.',
        action: 'Request your data',
    },
    {
        title: 'Right to Rectification',
        description: 'Correct inaccurate or incomplete personal data.',
        action: 'Update your information',
    },
    {
        title: 'Right to Erasure',
        description: 'Request deletion of your personal data ("right to be forgotten").',
        action: 'Delete your data',
    },
    {
        title: 'Right to Data Portability',
        description: 'Receive your data in a structured, machine-readable format.',
        action: 'Export your data',
    },
    {
        title: 'Right to Object',
        description: 'Object to processing of your personal data for specific purposes.',
        action: 'Manage preferences',
    },
    {
        title: 'Right to Restriction',
        description: 'Request restriction of processing under certain conditions.',
        action: 'Restrict processing',
    },
];

const isLoaded = ref(false);

onMounted(() => {
    setTimeout(() => {
        isLoaded.value = true;
    }, 50);
});
</script>

<template>
    <Head title="GDPR Compliance">
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
        <link
            href="https://fonts.googleapis.com/css2?family=Crimson+Pro:wght@300;400;600;700&family=Albert+Sans:wght@300;400;500;600;700&display=swap"
            rel="stylesheet"
        />
    </Head>

    <div class="legal-document min-h-screen bg-gradient-to-br from-emerald-50 via-teal-50 to-cyan-50">
        <!-- Header -->
        <header class="sticky top-0 z-50 border-b border-emerald-200/60 bg-white/80 backdrop-blur-xl">
            <div class="mx-auto max-w-6xl px-6 py-6 md:px-12">
                <div class="flex items-center justify-between">
                    <Link
                        :href="'/'"
                        class="group flex items-center gap-2 text-sm font-medium text-emerald-600 transition-colors hover:text-emerald-900"
                    >
                        <ArrowLeft class="h-4 w-4 transition-transform group-hover:-translate-x-1" />
                        <span>Back to Home</span>
                    </Link>
                    <div class="text-xs font-medium tracking-wide text-emerald-400">
                        GDPR COMPLIANCE
                    </div>
                </div>
            </div>
        </header>

        <main class="relative">
            <!-- Hero Section -->
            <section
                class="relative overflow-hidden border-b border-emerald-200 bg-gradient-to-br from-white via-emerald-50 to-teal-100 px-6 py-24 md:px-12 md:py-32"
            >
                <!-- Decorative elements -->
                <div class="pointer-events-none absolute inset-0">
                    <div
                        class="absolute right-0 top-0 h-96 w-96 rounded-full bg-gradient-to-br from-emerald-200/40 to-teal-200/40 blur-3xl"
                    />
                    <div
                        class="absolute bottom-0 left-0 h-96 w-96 rounded-full bg-gradient-to-tr from-cyan-200/40 to-blue-200/40 blur-3xl"
                    />
                </div>

                <div class="relative mx-auto max-w-6xl">
                    <div
                        class="mb-6 transition-all duration-1000"
                        :class="
                            isLoaded
                                ? 'translate-y-0 opacity-100'
                                : 'translate-y-8 opacity-0'
                        "
                    >
                        <div
                            class="mb-4 inline-flex items-center gap-2 rounded-full border border-emerald-300/50 bg-white/60 px-4 py-1.5 text-xs font-semibold uppercase tracking-widest text-emerald-600 backdrop-blur-sm"
                        >
                            <Shield class="h-3.5 w-3.5" />
                            <span>Data Protection</span>
                        </div>
                    </div>

                    <h1
                        class="mb-8 text-6xl font-light leading-[1.1] tracking-tight text-emerald-950 transition-all duration-1000 md:text-7xl lg:text-8xl"
                        :class="
                            isLoaded
                                ? 'translate-y-0 opacity-100'
                                : 'translate-y-8 opacity-0'
                        "
                        style="font-family: 'Crimson Pro', serif; transition-delay: 100ms"
                    >
                        GDPR<br />Compliance
                    </h1>

                    <p
                        class="max-w-2xl text-xl font-light leading-relaxed text-emerald-800 transition-all duration-1000"
                        :class="
                            isLoaded
                                ? 'translate-y-0 opacity-100'
                                : 'translate-y-8 opacity-0'
                        "
                        style="font-family: 'Crimson Pro', serif; transition-delay: 200ms"
                    >
                        We are committed to protecting your personal data and privacy rights under the General Data
                        Protection Regulation (GDPR). This page explains how we comply with GDPR and what rights you
                        have.
                    </p>
                </div>
            </section>

            <!-- GDPR Principles -->
            <section class="border-b border-emerald-200 bg-white px-6 py-20 md:px-12">
                <div class="mx-auto max-w-6xl">
                    <div class="mb-12 text-center">
                        <h2
                            class="mb-4 text-4xl font-semibold text-emerald-950"
                            style="font-family: 'Crimson Pro', serif"
                        >
                            Our GDPR Principles
                        </h2>
                        <p
                            class="mx-auto max-w-2xl text-lg text-emerald-700"
                            style="font-family: 'Albert Sans', sans-serif"
                        >
                            We adhere to the six fundamental principles of GDPR in all our data processing activities.
                        </p>
                    </div>

                    <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                        <div
                            v-for="(principle, index) in principles"
                            :key="index"
                            class="group relative overflow-hidden rounded-2xl border border-emerald-200 bg-gradient-to-br from-white to-emerald-50/30 p-8 transition-all duration-300 hover:shadow-xl hover:shadow-emerald-100/50"
                        >
                            <div class="mb-4 flex h-14 w-14 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 text-white transition-transform group-hover:scale-110">
                                <component :is="principle.icon" class="h-7 w-7" />
                            </div>
                            <h3
                                class="mb-3 text-xl font-semibold text-emerald-900"
                                style="font-family: 'Albert Sans', sans-serif"
                            >
                                {{ principle.title }}
                            </h3>
                            <p class="text-emerald-700" style="font-family: 'Crimson Pro', serif">
                                {{ principle.description }}
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Your Rights -->
            <section class="bg-gradient-to-br from-emerald-50 to-teal-50 px-6 py-20 md:px-12">
                <div class="mx-auto max-w-6xl">
                    <div class="mb-12 text-center">
                        <h2
                            class="mb-4 text-4xl font-semibold text-emerald-950"
                            style="font-family: 'Crimson Pro', serif"
                        >
                            Your GDPR Rights
                        </h2>
                        <p
                            class="mx-auto max-w-2xl text-lg text-emerald-700"
                            style="font-family: 'Albert Sans', sans-serif"
                        >
                            Under GDPR, you have comprehensive rights regarding your personal data. Here's what you can
                            do:
                        </p>
                    </div>

                    <div class="grid gap-6 lg:grid-cols-2">
                        <div
                            v-for="(right, index) in rights"
                            :key="index"
                            class="rounded-2xl border border-emerald-200 bg-white p-8 shadow-sm transition-all duration-300 hover:shadow-lg"
                        >
                            <div class="mb-4 flex items-start justify-between">
                                <h3
                                    class="text-xl font-semibold text-emerald-900"
                                    style="font-family: 'Albert Sans', sans-serif"
                                >
                                    {{ right.title }}
                                </h3>
                                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600">
                                    <CheckCircle2 class="h-5 w-5" />
                                </div>
                            </div>
                            <p
                                class="mb-6 text-emerald-700"
                                style="font-family: 'Crimson Pro', serif"
                            >
                                {{ right.description }}
                            </p>
                            <button
                                class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-emerald-600 to-teal-600 px-5 py-2.5 text-sm font-medium text-white transition-all hover:from-emerald-700 hover:to-teal-700 hover:shadow-lg"
                                style="font-family: 'Albert Sans', sans-serif"
                            >
                                {{ right.action }}
                                <ArrowLeft class="h-4 w-4 rotate-180" />
                            </button>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Data Processing -->
            <section class="border-b border-emerald-200 bg-white px-6 py-20 md:px-12">
                <div class="mx-auto max-w-4xl">
                    <h2
                        class="mb-12 text-center text-4xl font-semibold text-emerald-950"
                        style="font-family: 'Crimson Pro', serif"
                    >
                        How We Process Your Data
                    </h2>

                    <div class="space-y-8">
                        <div class="rounded-2xl border border-emerald-200 bg-gradient-to-br from-white to-emerald-50/30 p-8">
                            <h3
                                class="mb-4 flex items-center gap-3 text-2xl font-semibold text-emerald-900"
                                style="font-family: 'Albert Sans', sans-serif"
                            >
                                <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-600 text-white text-sm font-bold">1</span>
                                Legal Basis for Processing
                            </h3>
                            <p class="mb-4 text-emerald-800" style="font-family: 'Crimson Pro', serif">
                                We process your personal data based on the following legal grounds:
                            </p>
                            <ul class="space-y-2 text-emerald-700" style="font-family: 'Crimson Pro', serif">
                                <li>• <strong>Contract Performance:</strong> To provide our services as per our agreement with you</li>
                                <li>• <strong>Legitimate Interests:</strong> To improve and optimize our platform</li>
                                <li>• <strong>Legal Obligations:</strong> To comply with applicable laws and regulations</li>
                                <li>• <strong>Consent:</strong> Where you have given explicit consent for specific purposes</li>
                            </ul>
                        </div>

                        <div class="rounded-2xl border border-emerald-200 bg-gradient-to-br from-white to-emerald-50/30 p-8">
                            <h3
                                class="mb-4 flex items-center gap-3 text-2xl font-semibold text-emerald-900"
                                style="font-family: 'Albert Sans', sans-serif"
                            >
                                <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-600 text-white text-sm font-bold">2</span>
                                Data Retention
                            </h3>
                            <p class="text-emerald-800" style="font-family: 'Crimson Pro', serif">
                                We retain your personal data only for as long as necessary to fulfill the purposes for
                                which it was collected, comply with legal obligations, resolve disputes, and enforce our
                                agreements. When data is no longer needed, we securely delete or anonymize it.
                            </p>
                        </div>

                        <div class="rounded-2xl border border-emerald-200 bg-gradient-to-br from-white to-emerald-50/30 p-8">
                            <h3
                                class="mb-4 flex items-center gap-3 text-2xl font-semibold text-emerald-900"
                                style="font-family: 'Albert Sans', sans-serif"
                            >
                                <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-600 text-white text-sm font-bold">3</span>
                                International Data Transfers
                            </h3>
                            <p class="text-emerald-800" style="font-family: 'Crimson Pro', serif">
                                When we transfer personal data outside the European Economic Area (EEA), we ensure
                                appropriate safeguards are in place, such as Standard Contractual Clauses (SCCs)
                                approved by the European Commission, to protect your data.
                            </p>
                        </div>

                        <div class="rounded-2xl border border-emerald-200 bg-gradient-to-br from-white to-emerald-50/30 p-8">
                            <h3
                                class="mb-4 flex items-center gap-3 text-2xl font-semibold text-emerald-900"
                                style="font-family: 'Albert Sans', sans-serif"
                            >
                                <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-600 text-white text-sm font-bold">4</span>
                                Data Breach Notification
                            </h3>
                            <p class="text-emerald-800" style="font-family: 'Crimson Pro', serif">
                                In the event of a data breach that poses a risk to your rights and freedoms, we will
                                notify you and the relevant supervisory authority within 72 hours of becoming aware of
                                the breach, in accordance with GDPR requirements.
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Supervisory Authority -->
            <section class="bg-gradient-to-br from-emerald-50 to-teal-50 px-6 py-20 md:px-12">
                <div class="mx-auto max-w-4xl">
                    <div class="rounded-2xl border border-emerald-300 bg-white p-8 shadow-lg md:p-12">
                        <div class="mb-6 flex items-center gap-4">
                            <div class="flex h-14 w-14 items-center justify-center rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 text-white">
                                <AlertCircle class="h-7 w-7" />
                            </div>
                            <h2
                                class="text-3xl font-semibold text-emerald-950"
                                style="font-family: 'Crimson Pro', serif"
                            >
                                Supervisory Authority
                            </h2>
                        </div>
                        <p class="mb-6 text-lg text-emerald-800" style="font-family: 'Crimson Pro', serif">
                            You have the right to lodge a complaint with a supervisory authority if you believe we have
                            not complied with GDPR requirements. The supervisory authority in your jurisdiction can
                            investigate your complaint and take appropriate action.
                        </p>
                        <div class="rounded-xl border-l-4 border-emerald-500 bg-emerald-50 p-6">
                            <p class="text-sm text-emerald-900" style="font-family: 'Albert Sans', sans-serif">
                                However, we encourage you to contact us first so we can address your concerns directly
                                and work together to resolve any issues.
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Contact Section -->
            <section class="px-6 py-20 md:px-12">
                <div class="mx-auto max-w-4xl">
                    <div class="rounded-2xl bg-gradient-to-br from-emerald-600 via-teal-600 to-cyan-700 p-8 text-white md:p-12">
                        <div class="mb-8 text-center">
                            <h2
                                class="mb-4 text-4xl font-semibold"
                                style="font-family: 'Crimson Pro', serif"
                            >
                                Data Protection Officer
                            </h2>
                            <p class="text-lg text-emerald-50" style="font-family: 'Albert Sans', sans-serif">
                                For any questions about GDPR compliance or to exercise your rights, contact our Data
                                Protection Officer:
                            </p>
                        </div>

                        <div class="mx-auto max-w-lg space-y-4 rounded-xl border border-white/20 bg-white/10 p-8 backdrop-blur-sm">
                            <div>
                                <p class="mb-2 text-sm font-medium text-emerald-100" style="font-family: 'Albert Sans', sans-serif">
                                    Email
                                </p>
                                <a
                                    href="mailto:dpo@restio.com"
                                    class="block text-xl font-semibold underline decoration-emerald-300 decoration-2 underline-offset-4 transition-colors hover:decoration-white"
                                    style="font-family: 'Albert Sans', sans-serif"
                                >
                                    dpo@restio.com
                                </a>
                            </div>
                            <div class="border-t border-white/20 pt-4">
                                <p class="mb-2 text-sm font-medium text-emerald-100" style="font-family: 'Albert Sans', sans-serif">
                                    Response Time
                                </p>
                                <p class="text-lg" style="font-family: 'Crimson Pro', serif">
                                    We aim to respond to all GDPR-related requests within 30 days.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </main>

        <!-- Footer -->
        <footer class="border-t border-emerald-200 bg-white/60 backdrop-blur-sm">
            <div class="mx-auto max-w-6xl px-6 py-12 md:px-12">
                <div class="flex flex-col items-center justify-between gap-6 md:flex-row">
                    <div class="flex flex-wrap justify-center gap-6 text-sm">
                        <Link
                            href="/terms-of-service"
                            class="text-emerald-600 transition-colors hover:text-emerald-900"
                        >
                            Terms of Service
                        </Link>
                        <Link
                            href="/privacy-policy"
                            class="text-emerald-600 transition-colors hover:text-emerald-900"
                        >
                            Privacy Policy
                        </Link>
                    </div>
                    <p class="text-sm text-emerald-500">
                        © 2026 Restio. All rights reserved.
                    </p>
                </div>
            </div>
        </footer>
    </div>
</template>

<style scoped>
.legal-document {
    font-family: 'Albert Sans', sans-serif;
}
</style>
