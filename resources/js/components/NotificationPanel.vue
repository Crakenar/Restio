<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';
import { useToast } from '@/composables/useToast';
import { Bell, CheckCheck, Clock, XCircle, CheckCircle, Calendar, Sparkles } from 'lucide-vue-next';

interface Notification {
    id: string;
    type: string;
    data: {
        message: string;
        vacation_request_id?: number;
        employee_name?: string;
        employee_id?: number;
        type?: string;
        start_date?: string;
        end_date?: string;
        rejection_reason?: string;
        [key: string]: any;
    };
    read_at: string | null;
    created_at: string;
}

interface Props {
    notifications?: Notification[];
    show?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    notifications: () => [],
    show: false,
});

const emit = defineEmits<{
    close: [];
}>();

const toast = useToast();
const panelRef = ref<HTMLElement | null>(null);

// Click outside to close
const handleClickOutside = (event: MouseEvent) => {
    const target = event.target as Node;

    if (panelRef.value && !panelRef.value.contains(target)) {
        const bellButton = document.querySelector('[data-notification-bell]');
        if (bellButton && !bellButton.contains(target)) {
            emit('close');
        }
    }
};

onMounted(() => {
    setTimeout(() => {
        document.addEventListener('click', handleClickOutside);
    }, 100);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});

// Mark notification as read
const markAsRead = (notificationId: string) => {
    router.post(
        `/notifications/${notificationId}/read`,
        {},
        {
            preserveScroll: true,
            only: ['notifications'],
        },
    );
};

// Mark all as read
const markAllAsRead = () => {
    router.post(
        '/notifications/read-all',
        {},
        {
            preserveScroll: true,
            only: ['notifications'],
            onSuccess: () => {
                toast.success('All notifications marked as read!');
            },
            onError: () => {
                toast.error('Failed to mark notifications as read.');
            },
        },
    );
};

// View notification
const viewNotification = (notification: Notification) => {
    if (!notification.read_at) {
        markAsRead(notification.id);
    }

    router.visit('/requests');
    emit('close');
};

// Time ago helper
const timeAgo = (dateString: string): string => {
    const date = new Date(dateString);
    const now = new Date();
    const seconds = Math.floor((now.getTime() - date.getTime()) / 1000);

    if (seconds < 60) return 'just now';
    if (seconds < 3600) return `${Math.floor(seconds / 60)}m ago`;
    if (seconds < 86400) return `${Math.floor(seconds / 3600)}h ago`;
    if (seconds < 604800) return `${Math.floor(seconds / 86400)}d ago`;
    return date.toLocaleDateString();
};

// Get notification styling
const getNotificationStyle = (notification: Notification) => {
    const notifType = notification.type.split('\\').pop()?.toLowerCase() || '';

    if (notifType.includes('approved')) {
        return {
            icon: CheckCircle,
            iconColor: 'text-emerald-600 dark:text-emerald-400',
            iconBg: 'bg-emerald-500/10 dark:bg-emerald-500/20',
            accentColor: 'bg-emerald-500',
        };
    }

    if (notifType.includes('rejected')) {
        return {
            icon: XCircle,
            iconColor: 'text-rose-600 dark:text-rose-400',
            iconBg: 'bg-rose-500/10 dark:bg-rose-500/20',
            accentColor: 'bg-rose-500',
        };
    }

    return {
        icon: Clock,
        iconColor: 'text-blue-600 dark:text-blue-400',
        iconBg: 'bg-blue-500/10 dark:bg-blue-500/20',
        accentColor: 'bg-blue-500',
    };
};

const hasUnread = computed(() => props.notifications.some((n) => !n.read_at));
const unreadNotifications = computed(() => props.notifications.filter((n) => !n.read_at));
const readNotifications = computed(() => props.notifications.filter((n) => n.read_at));
</script>

<template>
    <!-- Backdrop -->
    <Transition name="backdrop-fade">
        <div v-if="show" class="fixed inset-0 z-40" @click="emit('close')" />
    </Transition>

    <!-- Panel -->
    <Transition name="dropdown-slide">
        <div
            v-if="show"
            ref="panelRef"
            class="fixed right-4 top-20 z-50 w-[420px] max-w-[calc(100vw-2rem)] overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-2xl dark:border-slate-700/80 dark:bg-slate-900"
        >
            <!-- Ambient gradient overlay -->
            <div class="pointer-events-none absolute inset-0 overflow-hidden opacity-30">
                <div
                    class="absolute -right-20 -top-20 h-48 w-48 rounded-full bg-gradient-to-br from-orange-400 to-rose-500 blur-3xl animate-ambient-float"
                />
                <div
                    class="absolute -bottom-20 -left-20 h-48 w-48 rounded-full bg-gradient-to-tr from-blue-400 to-indigo-500 blur-3xl animate-ambient-float-delayed"
                />
            </div>

            <!-- Header -->
            <div class="relative border-b border-slate-200 bg-slate-50/50 p-5 backdrop-blur-xl dark:border-slate-700 dark:bg-slate-800/50">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Notifications</h3>
                        <p class="mt-0.5 text-sm text-slate-600 dark:text-slate-400">
                            {{ unreadNotifications.length }} unread
                        </p>
                    </div>

                    <!-- Mark all as read -->
                    <button
                        v-if="hasUnread"
                        @click="markAllAsRead"
                        class="group flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-semibold text-slate-700 transition-all duration-200 hover:bg-white dark:text-slate-300 dark:hover:bg-slate-800"
                    >
                        <CheckCheck class="h-4 w-4 transition-transform group-hover:scale-110" />
                        <span class="hidden sm:inline">Mark all read</span>
                    </button>
                </div>
            </div>

            <!-- Notifications List -->
            <div class="relative max-h-[calc(100vh-12rem)] overflow-y-auto bg-gradient-to-b from-slate-50/50 to-white dark:from-slate-800/50 dark:to-slate-900">
                <!-- Empty state -->
                <div
                    v-if="notifications.length === 0"
                    class="flex flex-col items-center justify-center py-12 text-center"
                >
                    <div
                        class="mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-slate-100 to-slate-200 dark:from-slate-700 dark:to-slate-800"
                    >
                        <Bell class="h-8 w-8 text-slate-400 dark:text-slate-500" />
                    </div>
                    <h4 class="mb-1 text-base font-bold text-slate-900 dark:text-white">All caught up!</h4>
                    <p class="text-sm text-slate-600 dark:text-slate-400">
                        You have no notifications
                    </p>
                </div>

                <div v-else class="p-2">
                    <!-- Unread Notifications -->
                    <div v-if="unreadNotifications.length > 0" class="mb-3">
                        <div
                            class="mb-2 flex items-center gap-2 px-3 py-1"
                        >
                            <Sparkles class="h-3.5 w-3.5 text-orange-500" />
                            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                New
                            </h4>
                        </div>
                        <div class="space-y-1.5">
                            <div
                                v-for="(notification, index) in unreadNotifications"
                                :key="notification.id"
                                @click="viewNotification(notification)"
                                :style="{ animationDelay: `${index * 30}ms` }"
                                class="notification-item group relative cursor-pointer overflow-hidden rounded-xl border border-slate-200 bg-white p-3.5 transition-all duration-200 hover:scale-[1.01] hover:border-orange-200 hover:shadow-md dark:border-slate-700 dark:bg-slate-800 dark:hover:border-orange-900/50"
                            >
                                <!-- Left accent bar -->
                                <div
                                    :class="[
                                        'absolute left-0 top-0 h-full w-1 transition-all duration-200 group-hover:w-1.5',
                                        getNotificationStyle(notification).accentColor,
                                    ]"
                                />

                                <!-- Content -->
                                <div class="flex gap-3 pl-2">
                                    <!-- Icon -->
                                    <div
                                        :class="[
                                            'flex h-10 w-10 shrink-0 items-center justify-center rounded-xl transition-all duration-200 group-hover:scale-105',
                                            getNotificationStyle(notification).iconBg,
                                        ]"
                                    >
                                        <component
                                            :is="getNotificationStyle(notification).icon"
                                            :class="['h-5 w-5', getNotificationStyle(notification).iconColor]"
                                        />
                                    </div>

                                    <!-- Text -->
                                    <div class="flex-1 overflow-hidden">
                                        <p class="text-sm font-semibold text-slate-900 dark:text-white">
                                            {{ notification.data.message }}
                                        </p>

                                        <p
                                            v-if="notification.data.employee_name"
                                            class="mt-1 text-xs text-slate-600 dark:text-slate-400"
                                        >
                                            {{ notification.data.employee_name }}
                                        </p>

                                        <!-- Dates -->
                                        <div
                                            v-if="notification.data.start_date"
                                            class="mt-2 flex items-center gap-1.5 text-xs text-slate-600 dark:text-slate-400"
                                        >
                                            <Calendar class="h-3 w-3" />
                                            <span>
                                                {{ new Date(notification.data.start_date).toLocaleDateString() }}
                                                -
                                                {{ new Date(notification.data.end_date!).toLocaleDateString() }}
                                            </span>
                                        </div>

                                        <p class="mt-2 text-xs font-medium text-slate-500">
                                            {{ timeAgo(notification.created_at) }}
                                        </p>
                                    </div>

                                    <!-- Unread indicator -->
                                    <div class="flex items-start pt-1">
                                        <div
                                            class="h-2 w-2 rounded-full bg-gradient-to-br from-orange-500 to-rose-600 shadow-lg"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Read Notifications -->
                    <div v-if="readNotifications.length > 0">
                        <h4
                            class="mb-2 px-3 py-1 text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400"
                        >
                            Earlier
                        </h4>
                        <div class="space-y-1.5">
                            <div
                                v-for="notification in readNotifications"
                                :key="notification.id"
                                @click="viewNotification(notification)"
                                class="group relative cursor-pointer overflow-hidden rounded-xl border border-slate-200/50 bg-slate-50/50 p-3.5 opacity-70 transition-all duration-200 hover:scale-[1.01] hover:border-slate-300 hover:opacity-100 hover:shadow-md dark:border-slate-700/50 dark:bg-slate-800/50 dark:hover:border-slate-600"
                            >
                                <!-- Content -->
                                <div class="flex gap-3">
                                    <!-- Icon -->
                                    <div
                                        :class="[
                                            'flex h-10 w-10 shrink-0 items-center justify-center rounded-xl',
                                            getNotificationStyle(notification).iconBg,
                                        ]"
                                    >
                                        <component
                                            :is="getNotificationStyle(notification).icon"
                                            :class="['h-5 w-5', getNotificationStyle(notification).iconColor]"
                                        />
                                    </div>

                                    <!-- Text -->
                                    <div class="flex-1 overflow-hidden">
                                        <p class="text-sm font-semibold text-slate-900 dark:text-white">
                                            {{ notification.data.message }}
                                        </p>

                                        <p
                                            v-if="notification.data.employee_name"
                                            class="mt-1 text-xs text-slate-600 dark:text-slate-400"
                                        >
                                            {{ notification.data.employee_name }}
                                        </p>

                                        <p class="mt-2 text-xs font-medium text-slate-500">
                                            {{ timeAgo(notification.created_at) }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </Transition>
</template>

<style scoped>
/* Backdrop fade */
.backdrop-fade-enter-active,
.backdrop-fade-leave-active {
    transition: opacity 0.2s ease;
}

.backdrop-fade-enter-from,
.backdrop-fade-leave-to {
    opacity: 0;
}

/* Dropdown slide animation */
.dropdown-slide-enter-active {
    animation: dropdown-slide-in 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}

.dropdown-slide-leave-active {
    animation: dropdown-slide-out 0.2s cubic-bezier(0.4, 0, 1, 1);
}

@keyframes dropdown-slide-in {
    from {
        opacity: 0;
        transform: translateY(-12px) scale(0.96);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

@keyframes dropdown-slide-out {
    from {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
    to {
        opacity: 0;
        transform: translateY(-8px) scale(0.98);
    }
}

/* Notification item entrance */
.notification-item {
    animation: notification-fade-in 0.3s cubic-bezier(0.16, 1, 0.3, 1) both;
}

@keyframes notification-fade-in {
    from {
        opacity: 0;
        transform: translateY(8px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Ambient float animations */
@keyframes ambient-float {
    0%,
    100% {
        transform: translate(0, 0) scale(1);
    }
    33% {
        transform: translate(30px, -30px) scale(1.1);
    }
    66% {
        transform: translate(-20px, 20px) scale(0.9);
    }
}

.animate-ambient-float {
    animation: ambient-float 20s ease-in-out infinite;
}

.animate-ambient-float-delayed {
    animation: ambient-float 25s ease-in-out infinite;
    animation-delay: -10s;
}

/* Custom scrollbar */
::-webkit-scrollbar {
    width: 6px;
}

::-webkit-scrollbar-track {
    background: transparent;
}

::-webkit-scrollbar-thumb {
    background: linear-gradient(to bottom, rgba(148, 163, 184, 0.3), rgba(100, 116, 139, 0.3));
    border-radius: 10px;
}

::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(to bottom, rgba(148, 163, 184, 0.5), rgba(100, 116, 139, 0.5));
}

.dark ::-webkit-scrollbar-thumb {
    background: linear-gradient(to bottom, rgba(71, 85, 105, 0.3), rgba(51, 65, 85, 0.3));
}

.dark ::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(to bottom, rgba(71, 85, 105, 0.5), rgba(51, 65, 85, 0.5));
}
</style>
