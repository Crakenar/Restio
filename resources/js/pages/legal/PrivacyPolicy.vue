<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, Eye, Lock, Database, Share2, Cookie, Mail } from 'lucide-vue-next';
import { onMounted, ref } from 'vue';

const sections = [
    { id: 'introduction', title: '1. Introduction', icon: Eye },
    { id: 'collection', title: '2. Information We Collect', icon: Database },
    { id: 'usage', title: '3. How We Use Your Information', icon: Share2 },
    { id: 'sharing', title: '4. Information Sharing', icon: Share2 },
    { id: 'security', title: '5. Data Security', icon: Lock },
    { id: 'cookies', title: '6. Cookies & Tracking', icon: Cookie },
    { id: 'rights', title: '7. Your Rights', icon: Eye },
    { id: 'contact', title: '8. Contact Us', icon: Mail },
];

const isLoaded = ref(false);

onMounted(() => {
    setTimeout(() => {
        isLoaded.value = true;
    }, 50);
});
</script>

<template>
    <Head title="Privacy Policy">
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
        <link
            href="https://fonts.googleapis.com/css2?family=Crimson+Pro:wght@300;400;600;700&family=Albert+Sans:wght@300;400;500;600;700&display=swap"
            rel="stylesheet"
        />
    </Head>

    <div class="legal-document min-h-screen bg-gradient-to-br from-blue-50 via-indigo-50 to-violet-50">
        <!-- Header -->
        <header class="sticky top-0 z-50 border-b border-blue-200/60 bg-white/80 backdrop-blur-xl">
            <div class="mx-auto max-w-5xl px-6 py-6 md:px-12">
                <div class="flex items-center justify-between">
                    <Link
                        :href="'/'"
                        class="group flex items-center gap-2 text-sm font-medium text-blue-600 transition-colors hover:text-blue-900"
                    >
                        <ArrowLeft class="h-4 w-4 transition-transform group-hover:-translate-x-1" />
                        <span>Back to Home</span>
                    </Link>
                    <div class="text-xs font-medium tracking-wide text-blue-400">
                        PRIVACY
                    </div>
                </div>
            </div>
        </header>

        <main class="relative">
            <!-- Hero Section -->
            <section
                class="relative overflow-hidden border-b border-blue-200 bg-gradient-to-br from-white via-blue-50 to-indigo-100 px-6 py-24 md:px-12 md:py-32"
            >
                <!-- Decorative elements -->
                <div class="pointer-events-none absolute inset-0">
                    <div
                        class="absolute right-0 top-0 h-96 w-96 rounded-full bg-gradient-to-br from-blue-200/40 to-indigo-200/40 blur-3xl"
                    />
                    <div
                        class="absolute bottom-0 left-0 h-96 w-96 rounded-full bg-gradient-to-tr from-violet-200/40 to-purple-200/40 blur-3xl"
                    />
                </div>

                <div class="relative mx-auto max-w-5xl">
                    <div
                        class="mb-6 transition-all duration-1000"
                        :class="
                            isLoaded
                                ? 'translate-y-0 opacity-100'
                                : 'translate-y-8 opacity-0'
                        "
                    >
                        <div
                            class="mb-4 inline-block rounded-full border border-blue-300/50 bg-white/60 px-4 py-1.5 text-xs font-semibold uppercase tracking-widest text-blue-600 backdrop-blur-sm"
                        >
                            Your Privacy Matters
                        </div>
                    </div>

                    <h1
                        class="mb-8 text-6xl font-light leading-[1.1] tracking-tight text-blue-950 transition-all duration-1000 md:text-7xl lg:text-8xl"
                        :class="
                            isLoaded
                                ? 'translate-y-0 opacity-100'
                                : 'translate-y-8 opacity-0'
                        "
                        style="font-family: 'Crimson Pro', serif; transition-delay: 100ms"
                    >
                        Privacy<br />Policy
                    </h1>

                    <div
                        class="flex flex-col gap-6 text-blue-700 transition-all duration-1000 md:flex-row md:items-center md:gap-12"
                        :class="
                            isLoaded
                                ? 'translate-y-0 opacity-100'
                                : 'translate-y-8 opacity-0'
                        "
                        style="transition-delay: 200ms"
                    >
                        <div class="flex items-center gap-3">
                            <div class="h-px w-12 bg-blue-300" />
                            <p class="text-sm font-medium" style="font-family: 'Albert Sans', sans-serif">
                                Last Updated
                            </p>
                        </div>
                        <p class="text-lg font-light" style="font-family: 'Crimson Pro', serif">
                            January 22, 2026
                        </p>
                    </div>
                </div>
            </section>

            <!-- Main Content -->
            <div class="mx-auto max-w-5xl px-6 py-16 md:px-12">
                <div class="grid gap-16 lg:grid-cols-[280px_1fr]">
                    <!-- Sticky TOC -->
                    <aside class="lg:sticky lg:top-32 lg:h-fit">
                        <div class="rounded-2xl border border-blue-200 bg-white/60 p-6 backdrop-blur-sm">
                            <h2
                                class="mb-6 text-xs font-bold uppercase tracking-widest text-blue-900"
                                style="font-family: 'Albert Sans', sans-serif"
                            >
                                Table of Contents
                            </h2>
                            <nav class="space-y-3">
                                <a
                                    v-for="section in sections"
                                    :key="section.id"
                                    :href="`#${section.id}`"
                                    class="group flex items-start gap-3 rounded-lg px-3 py-2 text-sm transition-all hover:bg-blue-100/60"
                                >
                                    <component
                                        :is="section.icon"
                                        class="mt-0.5 h-4 w-4 flex-shrink-0 text-blue-400 transition-colors group-hover:text-blue-700"
                                    />
                                    <span
                                        class="font-medium text-blue-600 transition-colors group-hover:text-blue-900"
                                        style="font-family: 'Albert Sans', sans-serif"
                                    >
                                        {{ section.title }}
                                    </span>
                                </a>
                            </nav>
                        </div>
                    </aside>

                    <!-- Main Content -->
                    <article class="legal-content space-y-16">
                        <!-- Section 1 -->
                        <section id="introduction" class="scroll-mt-32">
                            <div class="mb-6 flex items-center gap-4">
                                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-blue-600 to-indigo-600 text-white">
                                    <Eye class="h-6 w-6" />
                                </div>
                                <h2
                                    class="text-3xl font-semibold text-blue-950"
                                    style="font-family: 'Crimson Pro', serif"
                                >
                                    Introduction
                                </h2>
                            </div>
                            <div class="prose-legal space-y-4">
                                <p>
                                    At Restio, we take your privacy seriously. This Privacy Policy explains how we
                                    collect, use, disclose, and safeguard your information when you use our vacation
                                    management platform.
                                </p>
                                <p>
                                    We are committed to protecting your personal data and ensuring transparency in our
                                    data practices. Please read this policy carefully to understand our views and
                                    practices regarding your personal data.
                                </p>
                            </div>
                        </section>

                        <!-- Section 2 -->
                        <section id="collection" class="scroll-mt-32">
                            <div class="mb-6 flex items-center gap-4">
                                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-blue-600 to-indigo-600 text-white">
                                    <Database class="h-6 w-6" />
                                </div>
                                <h2
                                    class="text-3xl font-semibold text-blue-950"
                                    style="font-family: 'Crimson Pro', serif"
                                >
                                    Information We Collect
                                </h2>
                            </div>
                            <div class="prose-legal space-y-6">
                                <p>
                                    We collect several types of information to provide and improve our services:
                                </p>

                                <div class="space-y-4">
                                    <div class="rounded-xl border border-blue-200 bg-blue-50/50 p-6">
                                        <h3 class="mb-3 text-lg font-semibold text-blue-900" style="font-family: 'Albert Sans', sans-serif">
                                            Personal Information
                                        </h3>
                                        <ul class="space-y-2 text-blue-800">
                                            <li>• Name, email address, and contact details</li>
                                            <li>• Job title, department, and company information</li>
                                            <li>• Account credentials and authentication data</li>
                                            <li>• Profile information and preferences</li>
                                        </ul>
                                    </div>

                                    <div class="rounded-xl border border-indigo-200 bg-indigo-50/50 p-6">
                                        <h3 class="mb-3 text-lg font-semibold text-indigo-900" style="font-family: 'Albert Sans', sans-serif">
                                            Usage Information
                                        </h3>
                                        <ul class="space-y-2 text-indigo-800">
                                            <li>• Vacation requests and time-off data</li>
                                            <li>• Activity logs and usage patterns</li>
                                            <li>• Device information and IP addresses</li>
                                            <li>• Browser type and operating system</li>
                                        </ul>
                                    </div>

                                    <div class="rounded-xl border border-violet-200 bg-violet-50/50 p-6">
                                        <h3 class="mb-3 text-lg font-semibold text-violet-900" style="font-family: 'Albert Sans', sans-serif">
                                            Communication Data
                                        </h3>
                                        <ul class="space-y-2 text-violet-800">
                                            <li>• Messages and notifications within the platform</li>
                                            <li>• Support tickets and customer service communications</li>
                                            <li>• Feedback and survey responses</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <!-- Section 3 -->
                        <section id="usage" class="scroll-mt-32">
                            <div class="mb-6 flex items-center gap-4">
                                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-blue-600 to-indigo-600 text-white">
                                    <Share2 class="h-6 w-6" />
                                </div>
                                <h2
                                    class="text-3xl font-semibold text-blue-950"
                                    style="font-family: 'Crimson Pro', serif"
                                >
                                    How We Use Your Information
                                </h2>
                            </div>
                            <div class="prose-legal space-y-4">
                                <p>
                                    We use the information we collect for various purposes, including:
                                </p>
                                <ul class="space-y-3 text-blue-800">
                                    <li>• Providing, maintaining, and improving our services</li>
                                    <li>• Processing vacation requests and managing time-off workflows</li>
                                    <li>• Sending you notifications, updates, and administrative messages</li>
                                    <li>• Analyzing usage patterns to enhance user experience</li>
                                    <li>• Ensuring platform security and preventing fraud</li>
                                    <li>• Complying with legal obligations and enforcing our terms</li>
                                    <li>• Responding to your inquiries and providing customer support</li>
                                </ul>
                                <div class="my-6 rounded-xl border-l-4 border-blue-500 bg-blue-50/50 p-6">
                                    <p class="font-semibold text-blue-900" style="font-family: 'Albert Sans', sans-serif">
                                        We will never sell your personal information to third parties for marketing
                                        purposes.
                                    </p>
                                </div>
                            </div>
                        </section>

                        <!-- Section 4 -->
                        <section id="sharing" class="scroll-mt-32">
                            <div class="mb-6 flex items-center gap-4">
                                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-blue-600 to-indigo-600 text-white">
                                    <Share2 class="h-6 w-6" />
                                </div>
                                <h2
                                    class="text-3xl font-semibold text-blue-950"
                                    style="font-family: 'Crimson Pro', serif"
                                >
                                    Information Sharing
                                </h2>
                            </div>
                            <div class="prose-legal space-y-4">
                                <p>
                                    We may share your information in the following circumstances:
                                </p>
                                <ul class="space-y-3 text-blue-800">
                                    <li>
                                        <strong>With Your Organization:</strong> Your employer or organization
                                        administrators have access to information necessary for managing vacation
                                        requests and team coordination
                                    </li>
                                    <li>
                                        <strong>Service Providers:</strong> We work with trusted third-party service
                                        providers who help us operate our platform (e.g., hosting, analytics, payment
                                        processing)
                                    </li>
                                    <li>
                                        <strong>Legal Requirements:</strong> When required by law, court order, or
                                        government regulation
                                    </li>
                                    <li>
                                        <strong>Business Transfers:</strong> In connection with a merger, acquisition,
                                        or sale of assets
                                    </li>
                                    <li>
                                        <strong>With Your Consent:</strong> When you explicitly authorize us to share
                                        specific information
                                    </li>
                                </ul>
                            </div>
                        </section>

                        <!-- Section 5 -->
                        <section id="security" class="scroll-mt-32">
                            <div class="mb-6 flex items-center gap-4">
                                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-blue-600 to-indigo-600 text-white">
                                    <Lock class="h-6 w-6" />
                                </div>
                                <h2
                                    class="text-3xl font-semibold text-blue-950"
                                    style="font-family: 'Crimson Pro', serif"
                                >
                                    Data Security
                                </h2>
                            </div>
                            <div class="prose-legal space-y-4">
                                <p>
                                    We implement appropriate technical and organizational measures to protect your
                                    personal information against unauthorized access, alteration, disclosure, or
                                    destruction.
                                </p>
                                <div class="my-6 rounded-xl border border-blue-200 bg-gradient-to-br from-blue-50 to-indigo-50 p-6">
                                    <h3 class="mb-3 text-lg font-semibold text-blue-900" style="font-family: 'Albert Sans', sans-serif">
                                        Our security measures include:
                                    </h3>
                                    <ul class="space-y-2 text-blue-800">
                                        <li>• End-to-end encryption for data transmission</li>
                                        <li>• Regular security audits and vulnerability assessments</li>
                                        <li>• Access controls and authentication mechanisms</li>
                                        <li>• Secure data centers with physical security measures</li>
                                        <li>• Employee training on data protection practices</li>
                                        <li>• Incident response procedures and breach notification protocols</li>
                                    </ul>
                                </div>
                                <p>
                                    While we strive to protect your information, no method of transmission over the
                                    internet or electronic storage is 100% secure. We cannot guarantee absolute
                                    security but continuously work to enhance our security measures.
                                </p>
                            </div>
                        </section>

                        <!-- Section 6 -->
                        <section id="cookies" class="scroll-mt-32">
                            <div class="mb-6 flex items-center gap-4">
                                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-blue-600 to-indigo-600 text-white">
                                    <Cookie class="h-6 w-6" />
                                </div>
                                <h2
                                    class="text-3xl font-semibold text-blue-950"
                                    style="font-family: 'Crimson Pro', serif"
                                >
                                    Cookies & Tracking Technologies
                                </h2>
                            </div>
                            <div class="prose-legal space-y-4">
                                <p>
                                    We use cookies and similar tracking technologies to enhance your experience,
                                    analyze usage patterns, and personalize content. Cookies are small data files
                                    stored on your device.
                                </p>
                                <div class="grid gap-4 md:grid-cols-2">
                                    <div class="rounded-xl border border-blue-200 bg-white/60 p-5">
                                        <h3 class="mb-2 text-base font-semibold text-blue-900" style="font-family: 'Albert Sans', sans-serif">
                                            Essential Cookies
                                        </h3>
                                        <p class="text-sm text-blue-700">
                                            Required for basic platform functionality, authentication, and security.
                                        </p>
                                    </div>
                                    <div class="rounded-xl border border-indigo-200 bg-white/60 p-5">
                                        <h3 class="mb-2 text-base font-semibold text-indigo-900" style="font-family: 'Albert Sans', sans-serif">
                                            Analytics Cookies
                                        </h3>
                                        <p class="text-sm text-indigo-700">
                                            Help us understand how users interact with our platform to improve
                                            services.
                                        </p>
                                    </div>
                                </div>
                                <p>
                                    You can control cookie preferences through your browser settings. Note that
                                    disabling certain cookies may affect platform functionality.
                                </p>
                            </div>
                        </section>

                        <!-- Section 7 -->
                        <section id="rights" class="scroll-mt-32">
                            <div class="mb-6 flex items-center gap-4">
                                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-blue-600 to-indigo-600 text-white">
                                    <Eye class="h-6 w-6" />
                                </div>
                                <h2
                                    class="text-3xl font-semibold text-blue-950"
                                    style="font-family: 'Crimson Pro', serif"
                                >
                                    Your Rights
                                </h2>
                            </div>
                            <div class="prose-legal space-y-4">
                                <p>
                                    Depending on your location, you may have certain rights regarding your personal
                                    information:
                                </p>
                                <ul class="space-y-3 text-blue-800">
                                    <li>
                                        <strong>Access:</strong> Request access to the personal information we hold
                                        about you
                                    </li>
                                    <li>
                                        <strong>Correction:</strong> Request correction of inaccurate or incomplete
                                        information
                                    </li>
                                    <li>
                                        <strong>Deletion:</strong> Request deletion of your personal information under
                                        certain circumstances
                                    </li>
                                    <li>
                                        <strong>Data Portability:</strong> Receive your personal information in a
                                        structured, commonly used format
                                    </li>
                                    <li>
                                        <strong>Objection:</strong> Object to processing of your personal information
                                        for certain purposes
                                    </li>
                                    <li>
                                        <strong>Restriction:</strong> Request restriction of processing in certain
                                        circumstances
                                    </li>
                                </ul>
                                <p>
                                    To exercise these rights, please contact us using the information provided in the
                                    Contact Us section.
                                </p>
                            </div>
                        </section>

                        <!-- Section 8 -->
                        <section id="contact" class="scroll-mt-32 border-t border-blue-200 pt-16">
                            <div class="rounded-2xl bg-gradient-to-br from-blue-600 to-indigo-700 p-8 text-white md:p-12">
                                <div class="mb-6 flex items-center gap-4">
                                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-white/20 backdrop-blur-sm">
                                        <Mail class="h-6 w-6" />
                                    </div>
                                    <h2
                                        class="text-3xl font-semibold"
                                        style="font-family: 'Crimson Pro', serif"
                                    >
                                        Contact Us
                                    </h2>
                                </div>
                                <p class="mb-6 text-blue-100" style="font-family: 'Albert Sans', sans-serif">
                                    If you have questions about this Privacy Policy or our data practices, please
                                    contact our Data Protection Officer:
                                </p>
                                <div class="space-y-3">
                                    <a
                                        href="mailto:privacy@restio.com"
                                        class="block text-lg font-medium text-white underline decoration-blue-300 decoration-2 underline-offset-4 transition-colors hover:decoration-white"
                                        style="font-family: 'Albert Sans', sans-serif"
                                    >
                                        privacy@restio.com
                                    </a>
                                </div>
                            </div>
                        </section>
                    </article>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="border-t border-blue-200 bg-white/60 backdrop-blur-sm">
            <div class="mx-auto max-w-5xl px-6 py-12 md:px-12">
                <div class="flex flex-col items-center justify-between gap-6 md:flex-row">
                    <div class="flex flex-wrap justify-center gap-6 text-sm">
                        <Link
                            href="/terms-of-service"
                            class="text-blue-600 transition-colors hover:text-blue-900"
                        >
                            Terms of Service
                        </Link>
                        <Link
                            href="/gdpr-compliance"
                            class="text-blue-600 transition-colors hover:text-blue-900"
                        >
                            GDPR Compliance
                        </Link>
                    </div>
                    <p class="text-sm text-blue-500">
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

.prose-legal p,
.prose-legal li {
    font-family: 'Crimson Pro', serif;
    font-size: 1.125rem;
    line-height: 1.8;
    font-weight: 300;
}

.prose-legal h3 {
    font-family: 'Albert Sans', sans-serif;
}

@media (max-width: 768px) {
    .prose-legal p,
    .prose-legal li {
        font-size: 1rem;
    }
}
</style>
