<script setup lang="ts">
import { computed, ref } from 'vue';
import {
    Calendar,
    Clock,
    Wrench,
    Newspaper,
    Bell,
    ChevronDown,
    User,
    FileText,
    BarChart3,
    Users,
    CalendarDays,
    Download,
} from 'lucide-vue-next';

interface Props {
    userName?: string;
    notificationCount?: number;
}

const props = withDefaults(defineProps<Props>(), {
    userName: 'Utilisateur',
    notificationCount: 0,
});

// Feature cards data
const featureCards = [
    {
        id: 'plannings',
        title: 'Plannings',
        icon: Calendar,
        color: 'emerald',
        gradient: 'from-emerald-500 to-teal-500',
        links: [
            { label: 'Semaine', href: '/planning/week' },
            { label: 'Mois', href: '/planning/month' },
            { label: 'Période personnalisée', href: '/planning/custom' },
            { label: 'Planning de mes collègues', href: '/planning/colleagues' },
            { label: 'Planning par Journée Type', href: '/planning/day-type' },
        ],
    },
    {
        id: 'absences',
        title: 'Absences',
        icon: Clock,
        color: 'blue',
        gradient: 'from-blue-500 to-indigo-500',
        links: [
            { label: 'Consulter mes soldes', href: '/absences/balance' },
            { label: "Demande d'absence", href: '/absences/request' },
            { label: 'Historique des demandes', href: '/absences/history' },
            { label: 'Demandes prévisionnelles', href: '/absences/forecast' },
            { label: 'Tableau récapitulatif annuel', href: '/absences/annual' },
        ],
    },
    {
        id: 'outils',
        title: 'Outils',
        icon: Wrench,
        color: 'pink',
        gradient: 'from-pink-500 to-rose-500',
        links: [
            { label: 'Tableau de présence', href: '/tools/attendance' },
            { label: 'Gestion de Documents', href: '/tools/documents' },
        ],
    },
    {
        id: 'actualites',
        title: 'Actualités',
        icon: Newspaper,
        color: 'violet',
        gradient: 'from-violet-500 to-purple-500',
        isNews: true,
    },
];

const expandedCard = ref<string | null>(null);

const toggleCard = (cardId: string) => {
    expandedCard.value = expandedCard.value === cardId ? null : cardId;
};
</script>

<template>
    <div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50/30 to-pink-50/20 p-4 sm:p-6 lg:p-8 dark:from-slate-950 dark:via-blue-950/30 dark:to-pink-950/20">
        <!-- Background decorative elements -->
        <div class="pointer-events-none fixed inset-0 overflow-hidden">
            <div
                class="absolute -top-1/4 -right-1/4 h-[600px] w-[600px] animate-pulse rounded-full bg-gradient-to-br from-blue-200/40 via-indigo-200/30 to-purple-200/20 blur-3xl dark:from-blue-500/20 dark:via-indigo-500/15 dark:to-purple-500/10"
                style="animation-duration: 15s"
            />
            <div
                class="absolute -bottom-1/4 -left-1/4 h-[600px] w-[600px] animate-pulse rounded-full bg-gradient-to-tr from-pink-200/40 via-rose-200/30 to-orange-200/20 blur-3xl dark:from-pink-500/20 dark:via-rose-500/15 dark:to-orange-500/10"
                style="animation-duration: 20s; animation-delay: 2s"
            />
        </div>

        <!-- Main content -->
        <div class="relative mx-auto max-w-7xl">
            <!-- Header Section -->
            <div class="mb-8 grid gap-6 lg:grid-cols-3">
                <!-- Welcome Card -->
                <div
                    class="group relative overflow-hidden rounded-3xl border border-white/60 bg-white/70 p-8 shadow-xl shadow-blue-500/5 backdrop-blur-xl transition-all duration-500 hover:scale-[1.02] hover:border-white/80 hover:bg-white/80 hover:shadow-2xl hover:shadow-blue-500/10 dark:border-white/10 dark:bg-slate-900/70 dark:hover:border-white/20 dark:hover:bg-slate-900/80 lg:col-span-2"
                    style="
                        animation: slideInLeft 0.8s cubic-bezier(0.16, 1, 0.3, 1);
                    "
                >
                    <!-- Gradient overlay -->
                    <div
                        class="absolute top-0 right-0 h-full w-1/2 bg-gradient-to-l from-blue-500/5 via-transparent to-transparent opacity-0 transition-opacity duration-500 group-hover:opacity-100 dark:from-blue-400/10"
                    />

                    <div class="relative flex items-center gap-6">
                        <!-- Avatar with gradient border -->
                        <div class="relative">
                            <div
                                class="absolute inset-0 animate-pulse rounded-full bg-gradient-to-br from-blue-500 to-pink-500 blur-md"
                                style="animation-duration: 3s"
                            />
                            <div
                                class="relative flex h-20 w-20 items-center justify-center rounded-full border-4 border-white bg-gradient-to-br from-blue-500 to-indigo-600 shadow-lg"
                            >
                                <User class="h-10 w-10 text-white" />
                            </div>
                        </div>

                        <!-- Welcome text -->
                        <div class="flex-1">
                            <h1
                                class="mb-2 bg-gradient-to-r from-slate-800 to-slate-600 bg-clip-text text-4xl font-bold tracking-tight text-transparent dark:from-slate-100 dark:to-slate-300"
                                style="font-family: 'Georgia', 'Times New Roman', serif"
                            >
                                Bonjour {{ userName }}
                            </h1>
                            <p class="text-lg text-slate-600 dark:text-slate-300">
                                Bienvenue sur votre
                                <span
                                    class="font-semibold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent dark:from-blue-400 dark:to-indigo-400"
                                >
                                    espace utilisateur
                                </span>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Notifications Card -->
                <div
                    class="group relative overflow-hidden rounded-3xl border border-white/60 bg-white/70 p-8 shadow-xl shadow-pink-500/5 backdrop-blur-xl transition-all duration-500 hover:scale-[1.02] hover:border-white/80 hover:bg-white/80 hover:shadow-2xl hover:shadow-pink-500/10 dark:border-white/10 dark:bg-slate-900/70 dark:hover:border-white/20 dark:hover:bg-slate-900/80"
                    style="
                        animation: slideInRight 0.8s cubic-bezier(0.16, 1, 0.3, 1);
                        animation-delay: 0.1s;
                        animation-fill-mode: both;
                    "
                >
                    <!-- Gradient overlay -->
                    <div
                        class="absolute top-0 left-0 h-full w-1/2 bg-gradient-to-r from-pink-500/5 via-transparent to-transparent opacity-0 transition-opacity duration-500 group-hover:opacity-100 dark:from-pink-400/10"
                    />

                    <div class="relative">
                        <div class="mb-4 flex items-center justify-between">
                            <h2 class="text-2xl font-bold text-slate-800 dark:text-slate-100">
                                Notifications
                            </h2>
                            <div class="relative">
                                <div
                                    v-if="notificationCount > 0"
                                    class="absolute -top-2 -right-2 flex h-6 w-6 items-center justify-center rounded-full bg-gradient-to-br from-pink-500 to-rose-500 text-xs font-bold text-white shadow-lg"
                                >
                                    {{ notificationCount }}
                                </div>
                                <div
                                    class="rounded-full bg-gradient-to-br from-pink-500 to-rose-500 p-3 shadow-lg"
                                >
                                    <Bell class="h-6 w-6 text-white" />
                                </div>
                            </div>
                        </div>
                        <p class="text-slate-600 dark:text-slate-300">
                            Vous avez
                            <span
                                class="font-bold bg-gradient-to-r from-pink-600 to-rose-600 bg-clip-text text-transparent dark:from-pink-400 dark:to-rose-400"
                            >
                                {{ notificationCount }} notification{{
                                    notificationCount !== 1 ? 's' : ''
                                }}
                            </span>
                        </p>
                    </div>
                </div>

                <!-- Support Card -->
                <div
                    class="relative overflow-hidden rounded-3xl border border-white/60 bg-gradient-to-br from-blue-500/5 to-indigo-500/5 p-6 backdrop-blur-xl transition-all duration-500 hover:scale-[1.02] hover:border-white/80 hover:from-blue-500/10 hover:to-indigo-500/10 dark:border-white/10 dark:from-blue-500/10 dark:to-indigo-500/10 dark:hover:border-white/20 dark:hover:from-blue-500/20 dark:hover:to-indigo-500/20 lg:col-span-3"
                    style="
                        animation: slideInUp 0.8s cubic-bezier(0.16, 1, 0.3, 1);
                        animation-delay: 0.2s;
                        animation-fill-mode: both;
                    "
                >
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div
                                class="rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 p-3 shadow-lg"
                            >
                                <FileText class="h-6 w-6 text-white" />
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100">
                                    Support utilisateur
                                </h3>
                                <p class="text-sm text-slate-600 dark:text-slate-300">
                                    Documentation et ressources
                                </p>
                            </div>
                        </div>
                        <button
                            class="group/btn flex items-center gap-2 rounded-2xl bg-gradient-to-r from-blue-500 to-indigo-600 px-6 py-3 font-semibold text-white shadow-lg shadow-blue-500/25 transition-all duration-300 hover:scale-105 hover:shadow-xl hover:shadow-blue-500/40"
                        >
                            <Download class="h-5 w-5" />
                            <span>Télécharger</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Feature Cards Grid -->
            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-4">
                <div
                    v-for="(card, index) in featureCards"
                    :key="card.id"
                    class="group relative overflow-hidden rounded-3xl border border-white/60 bg-white/70 shadow-xl backdrop-blur-xl transition-all duration-500 hover:scale-[1.02] hover:border-white/80 hover:bg-white/80 hover:shadow-2xl dark:border-white/10 dark:bg-slate-900/70 dark:hover:border-white/20 dark:hover:bg-slate-900/80"
                    :style="{
                        animation: `slideInUp 0.8s cubic-bezier(0.16, 1, 0.3, 1)`,
                        animationDelay: `${0.3 + index * 0.1}s`,
                        animationFillMode: 'both',
                    }"
                >
                    <!-- Top gradient accent -->
                    <div
                        class="h-2 bg-gradient-to-r transition-all duration-500 group-hover:h-3"
                        :class="card.gradient"
                    />

                    <div class="p-6">
                        <!-- Card header -->
                        <div class="mb-6 flex items-center gap-4">
                            <div
                                class="rounded-2xl bg-gradient-to-br p-3 shadow-lg transition-all duration-500 group-hover:scale-110 group-hover:rotate-3"
                                :class="card.gradient"
                            >
                                <component :is="card.icon" class="h-7 w-7 text-white" />
                            </div>
                            <h3
                                class="text-2xl font-bold text-slate-800 transition-colors duration-300 dark:text-slate-100"
                            >
                                {{ card.title }}
                            </h3>
                        </div>

                        <!-- Links or News content -->
                        <div v-if="!card.isNews" class="space-y-2">
                            <a
                                v-for="link in card.links"
                                :key="link.label"
                                :href="link.href"
                                class="group/link flex items-center justify-between rounded-xl px-4 py-3 text-slate-700 transition-all duration-300 hover:bg-gradient-to-r hover:pl-5 hover:text-white hover:shadow-md dark:text-slate-300"
                                :class="`hover:${card.gradient}`"
                            >
                                <span class="text-sm font-medium">
                                    {{ link.label }}
                                </span>
                                <ChevronDown
                                    class="h-4 w-4 -rotate-90 opacity-0 transition-all duration-300 group-hover/link:opacity-100"
                                />
                            </a>
                        </div>

                        <!-- News section -->
                        <div v-else class="space-y-4">
                            <div
                                class="rounded-2xl border border-slate-200/50 bg-gradient-to-br from-slate-50 to-white p-4 dark:border-slate-700/50 dark:from-slate-800/50 dark:to-slate-900/50"
                            >
                                <h4 class="mb-2 font-bold text-slate-800 dark:text-slate-100">
                                    Actualités utilisateur
                                </h4>
                                <p class="mb-1 text-sm font-semibold text-slate-700 dark:text-slate-200">
                                    Titre
                                </p>
                                <p class="text-sm leading-relaxed text-slate-600 dark:text-slate-400">
                                    Placer ici votre message d'actualité personnalisé
                                    pour tous les utilisateurs de la société.
                                </p>
                            </div>
                            <button
                                class="flex w-full items-center justify-center gap-2 rounded-xl bg-slate-100 py-3 text-sm font-semibold text-slate-700 transition-all duration-300 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700"
                            >
                                <ChevronDown class="h-4 w-4" />
                                <span>Voir plus</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
@keyframes slideInLeft {
    from {
        opacity: 0;
        transform: translateX(-30px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

@keyframes slideInRight {
    from {
        opacity: 0;
        transform: translateX(30px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

@keyframes slideInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Custom scrollbar */
::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}

::-webkit-scrollbar-track {
    background: rgba(241, 245, 249, 0.5);
    border-radius: 10px;
}

.dark ::-webkit-scrollbar-track {
    background: rgba(15, 23, 42, 0.5);
}

::-webkit-scrollbar-thumb {
    background: linear-gradient(to bottom, #3b82f6, #8b5cf6);
    border-radius: 10px;
}

::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(to bottom, #2563eb, #7c3aed);
}

/* Smooth page transitions */
* {
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
}
</style>
