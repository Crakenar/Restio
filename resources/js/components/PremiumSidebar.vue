<script setup lang="ts">
import { computed, ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import {
    LayoutGrid,
    Calendar,
    FileText,
    Users,
    UserCog,
    Palmtree,
    ChevronLeft,
    ChevronRight,
    LogOut,
    Settings,
    User,
} from 'lucide-vue-next';
import NotificationCenter from './NotificationCenter.vue';

interface NavItem {
    title: string;
    href: string;
    icon: any;
    badge?: number;
    roles?: string[];
}

const page = usePage();
const isCollapsed = ref(false);

const user = computed(() => page.props.auth?.user as any);
const userRole = computed(() => user.value?.role);

// Get current route
const currentRoute = computed(() => page.url);

// Navigation items
const navItems = computed<NavItem[]>(() => {
    const items: NavItem[] = [
        {
            title: 'Dashboard',
            href: '/dashboard',
            icon: LayoutGrid,
        },
        {
            title: 'Calendar',
            href: '/calendar',
            icon: Calendar,
        },
        {
            title: 'Requests',
            href: '/requests',
            icon: FileText,
        },
    ];

    // Team - for managers and admins
    if (userRole.value === 'manager' || userRole.value === 'admin') {
        items.push({
            title: 'Team',
            href: '/teams',
            icon: Users,
            roles: ['manager', 'admin'],
        });
    }

    // Employees - for admins only
    if (userRole.value === 'admin' || userRole.value === 'owner') {
        items.push({
            title: 'Employees',
            href: '/employees',
            icon: Users,
            roles: ['admin', 'owner'],
        });
    }

    // Team Management - for admins and owners
    if (userRole.value === 'admin' || userRole.value === 'owner') {
        items.push({
            title: 'Team Management',
            href: '/team-management',
            icon: UserCog,
            roles: ['admin', 'owner'],
        });
    }

    return items;
});

const isActive = (href: string) => {
    return currentRoute.value === href || currentRoute.value.startsWith(href + '/');
};

const toggleSidebar = () => {
    isCollapsed.value = !isCollapsed.value;
};
</script>

<template>
    <aside
        :class="[
            'fixed left-0 top-0 z-50 h-screen transition-all duration-500 ease-in-out',
            isCollapsed ? 'w-20' : 'w-72',
        ]"
    >
        <!-- Glass morphism background -->
        <div
            class="relative h-full border-r border-white/20 bg-white/70 backdrop-blur-2xl dark:border-white/10 dark:bg-slate-900/70"
        >
            <!-- Animated gradient overlay -->
            <div class="pointer-events-none absolute inset-0 overflow-hidden">
                <div
                    class="absolute -top-1/4 -left-1/4 h-1/2 w-1/2 animate-pulse rounded-full bg-gradient-to-br from-blue-500/10 via-indigo-500/10 to-purple-500/10 blur-3xl dark:from-blue-500/20 dark:via-indigo-500/20 dark:to-purple-500/20"
                    style="animation-duration: 8s"
                />
                <div
                    class="absolute -bottom-1/4 -right-1/4 h-1/2 w-1/2 animate-pulse rounded-full bg-gradient-to-tr from-pink-500/10 via-rose-500/10 to-orange-500/10 blur-3xl dark:from-pink-500/20 dark:via-rose-500/20 dark:to-orange-500/20"
                    style="animation-duration: 10s; animation-delay: 2s"
                />
            </div>

            <!-- Content -->
            <div class="relative flex h-full flex-col">
                <!-- Header - Logo/Brand -->
                <div class="border-b border-white/20 p-6 dark:border-white/10">
                    <Link
                        href="/dashboard"
                        class="group flex items-center gap-3 transition-all duration-300"
                    >
                        <!-- Logo -->
                        <div
                            class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 shadow-lg transition-all duration-500 group-hover:scale-110 group-hover:rotate-6 group-hover:shadow-xl group-hover:shadow-blue-500/50"
                        >
                            <Palmtree class="h-6 w-6 text-white" />
                        </div>

                        <!-- Brand text -->
                        <div
                            v-if="!isCollapsed"
                            class="flex flex-col transition-all duration-300"
                            style="animation: fadeInRight 0.5s ease-out"
                        >
                            <span
                                class="bg-gradient-to-r from-slate-800 to-slate-600 bg-clip-text text-lg font-bold tracking-tight text-transparent dark:from-slate-100 dark:to-slate-300"
                            >
                                Vacationly
                            </span>
                            <span class="text-xs text-slate-600 dark:text-slate-400">
                                Manage your time off
                            </span>
                        </div>
                    </Link>
                </div>

                <!-- Navigation -->
                <nav class="flex-1 space-y-1 overflow-y-auto p-4">
                    <Link
                        v-for="item in navItems"
                        :key="item.href"
                        :href="item.href"
                        :class="[
                            'group relative flex items-center gap-3 rounded-2xl px-4 py-3 transition-all duration-300',
                            isActive(item.href)
                                ? 'bg-gradient-to-r from-blue-500 to-indigo-600 text-white shadow-lg shadow-blue-500/30'
                                : 'text-slate-700 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-white/5',
                            isCollapsed ? 'justify-center' : '',
                        ]"
                    >
                        <!-- Active indicator -->
                        <div
                            v-if="isActive(item.href)"
                            class="absolute left-0 top-1/2 h-8 w-1 -translate-y-1/2 rounded-r-full bg-white shadow-lg"
                        />

                        <!-- Icon -->
                        <div
                            :class="[
                                'flex h-8 w-8 shrink-0 items-center justify-center rounded-xl transition-all duration-300',
                                isActive(item.href)
                                    ? 'bg-white/20'
                                    : 'group-hover:scale-110 group-hover:bg-slate-200 dark:group-hover:bg-white/10',
                            ]"
                        >
                            <component
                                :is="item.icon"
                                :class="[
                                    'h-5 w-5 transition-all duration-300',
                                    isActive(item.href)
                                        ? 'text-white'
                                        : 'text-slate-600 group-hover:text-blue-600 dark:text-slate-400 dark:group-hover:text-blue-400',
                                ]"
                            />
                        </div>

                        <!-- Label -->
                        <span
                            v-if="!isCollapsed"
                            :class="[
                                'flex-1 text-sm font-semibold transition-all duration-300',
                                isActive(item.href) ? 'text-white' : '',
                            ]"
                        >
                            {{ item.title }}
                        </span>

                        <!-- Tooltip for collapsed state -->
                        <div
                            v-if="isCollapsed"
                            class="pointer-events-none absolute left-full top-1/2 ml-4 -translate-y-1/2 whitespace-nowrap rounded-xl border border-white/20 bg-white/90 px-3 py-2 text-sm font-semibold text-slate-800 opacity-0 shadow-xl backdrop-blur-xl transition-all duration-300 group-hover:opacity-100 dark:border-white/10 dark:bg-slate-900/90 dark:text-slate-100"
                        >
                            {{ item.title }}
                            <div
                                class="absolute right-full top-1/2 -mr-1 -translate-y-1/2 border-4 border-transparent border-r-white/90 dark:border-r-slate-900/90"
                            />
                        </div>

                        <!-- Badge -->
                        <div
                            v-if="item.badge && !isCollapsed"
                            class="flex h-6 w-6 items-center justify-center rounded-full bg-white/20 text-xs font-bold"
                        >
                            {{ item.badge }}
                        </div>
                    </Link>

                    <!-- Notifications -->
                    <div :class="['flex', isCollapsed ? 'justify-center' : 'justify-end']">
                        <NotificationCenter />
                    </div>
                </nav>

                <!-- Footer - User Profile -->
                <div class="border-t border-white/20 p-4 dark:border-white/10">
                    <!-- Settings Link -->
                    <Link
                        href="/settings"
                        :class="[
                            'group mb-2 flex items-center gap-3 rounded-2xl px-4 py-2 text-slate-700 transition-all duration-300 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-white/5',
                            isCollapsed ? 'justify-center' : '',
                        ]"
                    >
                        <div
                            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl transition-all duration-300 group-hover:scale-110 group-hover:bg-slate-200 dark:group-hover:bg-white/10"
                        >
                            <Settings class="h-5 w-5" />
                        </div>
                        <span v-if="!isCollapsed" class="text-sm font-medium">
                            Settings
                        </span>

                        <!-- Tooltip -->
                        <div
                            v-if="isCollapsed"
                            class="pointer-events-none absolute left-full top-1/2 ml-4 -translate-y-1/2 whitespace-nowrap rounded-xl border border-white/20 bg-white/90 px-3 py-2 text-sm font-semibold text-slate-800 opacity-0 shadow-xl backdrop-blur-xl transition-all duration-300 group-hover:opacity-100 dark:border-white/10 dark:bg-slate-900/90 dark:text-slate-100"
                        >
                            Settings
                            <div
                                class="absolute right-full top-1/2 -mr-1 -translate-y-1/2 border-4 border-transparent border-r-white/90 dark:border-r-slate-900/90"
                            />
                        </div>
                    </Link>

                    <!-- User Profile -->
                    <div
                        class="group relative rounded-2xl border border-white/20 bg-gradient-to-r from-slate-50 to-white p-3 transition-all duration-300 hover:border-white/40 hover:shadow-lg dark:border-white/10 dark:from-slate-800/50 dark:to-slate-900/50 dark:hover:border-white/20"
                    >
                        <div :class="['flex items-center gap-3', isCollapsed ? 'justify-center' : '']">
                            <!-- Avatar -->
                            <div
                                class="relative flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 shadow-lg transition-all duration-500 group-hover:scale-110 group-hover:rotate-3"
                            >
                                <User class="h-5 w-5 text-white" />
                                <div
                                    class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100"
                                />
                            </div>

                            <!-- User info -->
                            <div
                                v-if="!isCollapsed"
                                class="flex-1 overflow-hidden"
                                style="animation: fadeInRight 0.5s ease-out"
                            >
                                <p
                                    class="truncate text-sm font-bold text-slate-800 dark:text-slate-100"
                                >
                                    {{ user?.name || 'User' }}
                                </p>
                                <p class="truncate text-xs text-slate-600 dark:text-slate-400">
                                    {{ user?.email || 'user@example.com' }}
                                </p>
                            </div>

                            <!-- Logout button -->
                            <Link
                                v-if="!isCollapsed"
                                href="/logout"
                                method="post"
                                as="button"
                                class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl text-slate-600 transition-all duration-300 hover:bg-red-100 hover:text-red-600 dark:text-slate-400 dark:hover:bg-red-900/20 dark:hover:text-red-400"
                            >
                                <LogOut class="h-4 w-4" />
                            </Link>
                        </div>

                        <!-- Tooltip for collapsed state -->
                        <div
                            v-if="isCollapsed"
                            class="pointer-events-none absolute left-full top-1/2 ml-4 -translate-y-1/2 whitespace-nowrap rounded-xl border border-white/20 bg-white/90 px-3 py-2 opacity-0 shadow-xl backdrop-blur-xl transition-all duration-300 group-hover:opacity-100 dark:border-white/10 dark:bg-slate-900/90"
                        >
                            <p class="text-sm font-bold text-slate-800 dark:text-slate-100">
                                {{ user?.name || 'User' }}
                            </p>
                            <p class="text-xs text-slate-600 dark:text-slate-400">
                                {{ user?.email || 'user@example.com' }}
                            </p>
                            <div
                                class="absolute right-full top-1/2 -mr-1 -translate-y-1/2 border-4 border-transparent border-r-white/90 dark:border-r-slate-900/90"
                            />
                        </div>
                    </div>
                </div>

                <!-- Toggle button -->
                <button
                    @click="toggleSidebar"
                    class="absolute -right-4 top-20 flex h-8 w-8 items-center justify-center rounded-full border border-white/40 bg-white shadow-lg backdrop-blur-xl transition-all duration-300 hover:scale-110 hover:bg-gradient-to-r hover:from-blue-500 hover:to-indigo-600 hover:text-white hover:shadow-xl dark:border-white/20 dark:bg-slate-900"
                >
                    <ChevronLeft
                        v-if="!isCollapsed"
                        class="h-4 w-4 transition-transform duration-300"
                    />
                    <ChevronRight
                        v-else
                        class="h-4 w-4 transition-transform duration-300"
                    />
                </button>
            </div>
        </div>

    </aside>
</template>

<style scoped>
@keyframes fadeInRight {
    from {
        opacity: 0;
        transform: translateX(-10px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

/* Custom scrollbar */
nav::-webkit-scrollbar {
    width: 6px;
}

nav::-webkit-scrollbar-track {
    background: transparent;
}

nav::-webkit-scrollbar-thumb {
    background: linear-gradient(to bottom, #3b82f6, #8b5cf6);
    border-radius: 10px;
}

nav::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(to bottom, #2563eb, #7c3aed);
}
</style>
