<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, Scale, FileText, Shield } from 'lucide-vue-next';
import { onMounted, ref } from 'vue';

const sections = [
    { id: 'acceptance', title: '1. Acceptance of Terms', icon: FileText },
    { id: 'services', title: '2. Description of Services', icon: Scale },
    { id: 'accounts', title: '3. User Accounts', icon: Shield },
    { id: 'conduct', title: '4. User Conduct', icon: Shield },
    { id: 'intellectual', title: '5. Intellectual Property', icon: Scale },
    { id: 'termination', title: '6. Termination', icon: FileText },
    { id: 'liability', title: '7. Limitation of Liability', icon: Shield },
    { id: 'changes', title: '8. Changes to Terms', icon: FileText },
];

const isLoaded = ref(false);

onMounted(() => {
    setTimeout(() => {
        isLoaded.value = true;
    }, 50);
});
</script>

<template>
    <Head title="Terms of Service">
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
        <link
            href="https://fonts.googleapis.com/css2?family=Crimson+Pro:wght@300;400;600;700&family=Albert+Sans:wght@300;400;500;600;700&display=swap"
            rel="stylesheet"
        />
    </Head>

    <div class="legal-document min-h-screen bg-gradient-to-br from-stone-50 via-neutral-50 to-zinc-100">
        <!-- Elegant header -->
        <header class="sticky top-0 z-50 border-b border-stone-200/60 bg-white/80 backdrop-blur-xl">
            <div class="mx-auto max-w-5xl px-6 py-6 md:px-12">
                <div class="flex items-center justify-between">
                    <Link
                        :href="'/'"
                        class="group flex items-center gap-2 text-sm font-medium text-stone-600 transition-colors hover:text-stone-900"
                    >
                        <ArrowLeft class="h-4 w-4 transition-transform group-hover:-translate-x-1" />
                        <span>Back to Home</span>
                    </Link>
                    <div class="text-xs font-medium tracking-wide text-stone-400">
                        LEGAL
                    </div>
                </div>
            </div>
        </header>

        <main class="relative">
            <!-- Hero Section - Magazine Style -->
            <section
                class="relative overflow-hidden border-b border-stone-200 bg-gradient-to-br from-white via-stone-50 to-neutral-100 px-6 py-24 md:px-12 md:py-32"
            >
                <!-- Decorative elements -->
                <div class="pointer-events-none absolute inset-0">
                    <div
                        class="absolute right-0 top-0 h-96 w-96 rounded-full bg-gradient-to-br from-amber-100/40 to-orange-100/40 blur-3xl"
                    />
                    <div
                        class="absolute bottom-0 left-0 h-96 w-96 rounded-full bg-gradient-to-tr from-stone-200/40 to-zinc-200/40 blur-3xl"
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
                            class="mb-4 inline-block rounded-full border border-stone-300/50 bg-white/60 px-4 py-1.5 text-xs font-semibold uppercase tracking-widest text-stone-600 backdrop-blur-sm"
                        >
                            Legal Document
                        </div>
                    </div>

                    <h1
                        class="mb-8 text-6xl font-light leading-[1.1] tracking-tight text-stone-900 transition-all duration-1000 md:text-7xl lg:text-8xl"
                        :class="
                            isLoaded
                                ? 'translate-y-0 opacity-100'
                                : 'translate-y-8 opacity-0'
                        "
                        style="font-family: 'Crimson Pro', serif; transition-delay: 100ms"
                    >
                        Terms of<br />Service
                    </h1>

                    <div
                        class="flex flex-col gap-6 text-stone-600 transition-all duration-1000 md:flex-row md:items-center md:gap-12"
                        :class="
                            isLoaded
                                ? 'translate-y-0 opacity-100'
                                : 'translate-y-8 opacity-0'
                        "
                        style="transition-delay: 200ms"
                    >
                        <div class="flex items-center gap-3">
                            <div class="h-px w-12 bg-stone-300" />
                            <p class="text-sm font-medium" style="font-family: 'Albert Sans', sans-serif">
                                Effective Date
                            </p>
                        </div>
                        <p class="text-lg font-light" style="font-family: 'Crimson Pro', serif">
                            January 22, 2026
                        </p>
                    </div>
                </div>
            </section>

            <!-- Table of Contents - Sidebar Style -->
            <div class="mx-auto max-w-5xl px-6 py-16 md:px-12">
                <div class="grid gap-16 lg:grid-cols-[280px_1fr]">
                    <!-- Sticky TOC -->
                    <aside class="lg:sticky lg:top-32 lg:h-fit">
                        <div class="rounded-2xl border border-stone-200 bg-white/60 p-6 backdrop-blur-sm">
                            <h2
                                class="mb-6 text-xs font-bold uppercase tracking-widest text-stone-900"
                                style="font-family: 'Albert Sans', sans-serif"
                            >
                                Table of Contents
                            </h2>
                            <nav class="space-y-3">
                                <a
                                    v-for="section in sections"
                                    :key="section.id"
                                    :href="`#${section.id}`"
                                    class="group flex items-start gap-3 rounded-lg px-3 py-2 text-sm transition-all hover:bg-stone-100/80"
                                >
                                    <component
                                        :is="section.icon"
                                        class="mt-0.5 h-4 w-4 flex-shrink-0 text-stone-400 transition-colors group-hover:text-stone-700"
                                    />
                                    <span
                                        class="font-medium text-stone-600 transition-colors group-hover:text-stone-900"
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
                        <section id="acceptance" class="scroll-mt-32">
                            <div class="mb-6 flex items-center gap-4">
                                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-stone-900 text-white">
                                    <FileText class="h-6 w-6" />
                                </div>
                                <h2
                                    class="text-3xl font-semibold text-stone-900"
                                    style="font-family: 'Crimson Pro', serif"
                                >
                                    Acceptance of Terms
                                </h2>
                            </div>
                            <div class="prose-legal space-y-4">
                                <p>
                                    By accessing and using Restio's services, you acknowledge that you have read,
                                    understood, and agree to be bound by these Terms of Service and all applicable
                                    laws and regulations.
                                </p>
                                <p>
                                    If you do not agree with any part of these terms, you must not use our services.
                                    Your continued use of the platform constitutes acceptance of these terms and any
                                    modifications we may make from time to time.
                                </p>
                            </div>
                        </section>

                        <!-- Section 2 -->
                        <section id="services" class="scroll-mt-32">
                            <div class="mb-6 flex items-center gap-4">
                                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-stone-900 text-white">
                                    <Scale class="h-6 w-6" />
                                </div>
                                <h2
                                    class="text-3xl font-semibold text-stone-900"
                                    style="font-family: 'Crimson Pro', serif"
                                >
                                    Description of Services
                                </h2>
                            </div>
                            <div class="prose-legal space-y-4">
                                <p>
                                    Restio provides a comprehensive vacation and time-off management platform designed
                                    to streamline your organization's leave request processes, team coordination, and
                                    workforce planning.
                                </p>
                                <div class="my-6 rounded-xl border border-stone-200 bg-stone-50/50 p-6">
                                    <h3 class="mb-3 text-lg font-semibold text-stone-900" style="font-family: 'Albert Sans', sans-serif">
                                        Our services include:
                                    </h3>
                                    <ul class="space-y-2 text-stone-700">
                                        <li>• Vacation request submission and approval workflows</li>
                                        <li>• Team calendar and availability tracking</li>
                                        <li>• Analytics and reporting dashboards</li>
                                        <li>• Notification and communication systems</li>
                                        <li>• Integration with third-party services</li>
                                    </ul>
                                </div>
                                <p>
                                    We reserve the right to modify, suspend, or discontinue any aspect of our services
                                    at any time, with or without notice, though we will make reasonable efforts to
                                    communicate significant changes to our users.
                                </p>
                            </div>
                        </section>

                        <!-- Section 3 -->
                        <section id="accounts" class="scroll-mt-32">
                            <div class="mb-6 flex items-center gap-4">
                                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-stone-900 text-white">
                                    <Shield class="h-6 w-6" />
                                </div>
                                <h2
                                    class="text-3xl font-semibold text-stone-900"
                                    style="font-family: 'Crimson Pro', serif"
                                >
                                    User Accounts
                                </h2>
                            </div>
                            <div class="prose-legal space-y-4">
                                <p>
                                    To access certain features of our platform, you must create an account. You are
                                    responsible for maintaining the confidentiality of your account credentials and for
                                    all activities that occur under your account.
                                </p>
                                <p>
                                    You must provide accurate, current, and complete information during registration
                                    and keep your account information updated. You agree to notify us immediately of
                                    any unauthorized use of your account or any other breach of security.
                                </p>
                                <div class="my-6 rounded-xl border-l-4 border-amber-500 bg-amber-50/50 p-6">
                                    <p class="font-semibold text-amber-900" style="font-family: 'Albert Sans', sans-serif">
                                        Important: You must be at least 18 years old to create an account and use our
                                        services.
                                    </p>
                                </div>
                            </div>
                        </section>

                        <!-- Section 4 -->
                        <section id="conduct" class="scroll-mt-32">
                            <div class="mb-6 flex items-center gap-4">
                                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-stone-900 text-white">
                                    <Shield class="h-6 w-6" />
                                </div>
                                <h2
                                    class="text-3xl font-semibold text-stone-900"
                                    style="font-family: 'Crimson Pro', serif"
                                >
                                    User Conduct
                                </h2>
                            </div>
                            <div class="prose-legal space-y-4">
                                <p>
                                    You agree to use our services only for lawful purposes and in accordance with these
                                    Terms. You specifically agree not to:
                                </p>
                                <ul class="space-y-3 text-stone-700">
                                    <li>• Use the services in any way that violates applicable laws or regulations</li>
                                    <li>• Attempt to gain unauthorized access to our systems or networks</li>
                                    <li>• Interfere with or disrupt the services or servers</li>
                                    <li>• Upload or transmit viruses, malware, or other malicious code</li>
                                    <li>• Impersonate any person or entity or misrepresent your affiliation</li>
                                    <li>• Harvest or collect information about other users</li>
                                </ul>
                            </div>
                        </section>

                        <!-- Section 5 -->
                        <section id="intellectual" class="scroll-mt-32">
                            <div class="mb-6 flex items-center gap-4">
                                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-stone-900 text-white">
                                    <Scale class="h-6 w-6" />
                                </div>
                                <h2
                                    class="text-3xl font-semibold text-stone-900"
                                    style="font-family: 'Crimson Pro', serif"
                                >
                                    Intellectual Property
                                </h2>
                            </div>
                            <div class="prose-legal space-y-4">
                                <p>
                                    All content, features, and functionality of our services, including but not limited
                                    to text, graphics, logos, icons, images, audio clips, and software, are the
                                    exclusive property of Restio and are protected by international copyright,
                                    trademark, and other intellectual property laws.
                                </p>
                                <p>
                                    You may not reproduce, distribute, modify, create derivative works from, publicly
                                    display, or exploit any of our content without our prior written consent.
                                </p>
                            </div>
                        </section>

                        <!-- Section 6 -->
                        <section id="termination" class="scroll-mt-32">
                            <div class="mb-6 flex items-center gap-4">
                                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-stone-900 text-white">
                                    <FileText class="h-6 w-6" />
                                </div>
                                <h2
                                    class="text-3xl font-semibold text-stone-900"
                                    style="font-family: 'Crimson Pro', serif"
                                >
                                    Termination
                                </h2>
                            </div>
                            <div class="prose-legal space-y-4">
                                <p>
                                    We reserve the right to suspend or terminate your account and access to our
                                    services at our sole discretion, without notice, for conduct that we believe
                                    violates these Terms or is harmful to other users, us, or third parties, or for any
                                    other reason.
                                </p>
                                <p>
                                    You may terminate your account at any time by following the account closure process
                                    in your account settings. Upon termination, your right to use the services will
                                    immediately cease.
                                </p>
                            </div>
                        </section>

                        <!-- Section 7 -->
                        <section id="liability" class="scroll-mt-32">
                            <div class="mb-6 flex items-center gap-4">
                                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-stone-900 text-white">
                                    <Shield class="h-6 w-6" />
                                </div>
                                <h2
                                    class="text-3xl font-semibold text-stone-900"
                                    style="font-family: 'Crimson Pro', serif"
                                >
                                    Limitation of Liability
                                </h2>
                            </div>
                            <div class="prose-legal space-y-4">
                                <p>
                                    To the fullest extent permitted by applicable law, Restio shall not be liable for
                                    any indirect, incidental, special, consequential, or punitive damages, or any loss
                                    of profits or revenues, whether incurred directly or indirectly, or any loss of
                                    data, use, goodwill, or other intangible losses.
                                </p>
                                <div class="my-6 rounded-xl border border-stone-200 bg-stone-50/50 p-6">
                                    <p class="text-sm text-stone-700">
                                        Our aggregate liability to you for all claims arising out of or relating to
                                        these Terms or the services shall not exceed the amount you have paid us in the
                                        twelve (12) months preceding the claim.
                                    </p>
                                </div>
                            </div>
                        </section>

                        <!-- Section 8 -->
                        <section id="changes" class="scroll-mt-32">
                            <div class="mb-6 flex items-center gap-4">
                                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-stone-900 text-white">
                                    <FileText class="h-6 w-6" />
                                </div>
                                <h2
                                    class="text-3xl font-semibold text-stone-900"
                                    style="font-family: 'Crimson Pro', serif"
                                >
                                    Changes to Terms
                                </h2>
                            </div>
                            <div class="prose-legal space-y-4">
                                <p>
                                    We reserve the right to modify these Terms at any time. We will provide notice of
                                    material changes by posting the updated Terms on our website and updating the
                                    "Effective Date" at the top of this document.
                                </p>
                                <p>
                                    Your continued use of our services after such modifications constitutes your
                                    acknowledgment and acceptance of the modified Terms. If you do not agree to the
                                    modified Terms, you must stop using our services.
                                </p>
                            </div>
                        </section>

                        <!-- Contact Section -->
                        <section class="scroll-mt-32 border-t border-stone-200 pt-16">
                            <div class="rounded-2xl bg-gradient-to-br from-stone-900 to-stone-800 p-8 text-white md:p-12">
                                <h2
                                    class="mb-4 text-3xl font-semibold"
                                    style="font-family: 'Crimson Pro', serif"
                                >
                                    Questions?
                                </h2>
                                <p class="mb-6 text-stone-300" style="font-family: 'Albert Sans', sans-serif">
                                    If you have any questions about these Terms of Service, please contact us at:
                                </p>
                                <a
                                    href="mailto:legal@restio.com"
                                    class="inline-flex items-center gap-2 text-lg font-medium text-white underline decoration-stone-500 decoration-2 underline-offset-4 transition-colors hover:decoration-white"
                                    style="font-family: 'Albert Sans', sans-serif"
                                >
                                    legal@restio.com
                                </a>
                            </div>
                        </section>
                    </article>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="border-t border-stone-200 bg-white/60 backdrop-blur-sm">
            <div class="mx-auto max-w-5xl px-6 py-12 md:px-12">
                <div class="flex flex-col items-center justify-between gap-6 md:flex-row">
                    <div class="flex flex-wrap justify-center gap-6 text-sm">
                        <Link
                            href="/privacy-policy"
                            class="text-stone-600 transition-colors hover:text-stone-900"
                        >
                            Privacy Policy
                        </Link>
                        <Link
                            href="/gdpr-compliance"
                            class="text-stone-600 transition-colors hover:text-stone-900"
                        >
                            GDPR Compliance
                        </Link>
                    </div>
                    <p class="text-sm text-stone-500">
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
