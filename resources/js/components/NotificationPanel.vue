<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';
import { useToast } from '@/composables/useToast';
import { Bell, CheckCheck, Clock, XCircle, CheckCircle, Calendar } from 'lucide-vue-next';

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

// Click outside to close (with small delay to prevent immediate close)
const handleClickOutside = (event: MouseEvent) => {
    const target = event.target as Node;

    // Check if click is outside the panel
    if (panelRef.value && !panelRef.value.contains(target)) {
        // Also check if click is not on the notification bell button
        const bellButton = document.querySelector('[data-notification-bell]');
        if (bellButton && !bellButton.contains(target)) {
            emit('close');
        }
    }
};

onMounted(() => {
    // Add click listener with a small delay to prevent immediate close on open
    setTimeout(() => {
        document.addEventListener('click', handleClickOutside);
    }, 100);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});

// Mark notification as read
const markAsRead = (notificationId: string) => {
    router.post(`/notifications/${notificationId}/read`, {}, {
        preserveScroll: true,
        only: ['notifications'],
    });
};

// Mark all as read
const markAllAsRead = () => {
    router.post('/notifications/read-all', {}, {
        preserveScroll: true,
        only: ['notifications'],
        onSuccess: () => {
            toast.success('All notifications marked as read!');
        },
        onError: () => {
            toast.error('Failed to mark notifications as read.');
        },
    });
};

// View notification (marks as read and navigates)
const viewNotification = (notification: Notification) => {
    if (!notification.read_at) {
        markAsRead(notification.id);
    }

    // Navigate to requests page
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

// Get notification icon and color based on type
const getNotificationStyle = (notification: Notification) => {
    const notifType = notification.type.split('\\').pop()?.toLowerCase() || '';

    if (notifType.includes('approved')) {
        return {
            icon: CheckCircle,
            gradient: 'from-emerald-500 to-teal-600',
            bgGradient: 'from-emerald-500/10 to-teal-600/10',
            iconBg: 'bg-emerald-500/20',
            iconColor: 'text-emerald-600 dark:text-emerald-400',
        };
    }

    if (notifType.includes('rejected')) {
        return {
            icon: XCircle,
            gradient: 'from-red-500 to-rose-600',
            bgGradient: 'from-red-500/10 to-rose-600/10',
            iconBg: 'bg-red-500/20',
            iconColor: 'text-red-600 dark:text-red-400',
        };
    }

    // Submitted (default)
    return {
        icon: Clock,
        gradient: 'from-blue-500 to-indigo-600',
        bgGradient: 'from-blue-500/10 to-indigo-600/10',
        iconBg: 'bg-blue-500/20',
        iconColor: 'text-blue-600 dark:text-blue-400',
    };
};

const hasUnread = computed(() => props.notifications.some((n) => !n.read_at));
const unreadNotifications = computed(() => props.notifications.filter((n) => !n.read_at));
const readNotifications = computed(() => props.notifications.filter((n) => n.read_at));
</script>

<template>
    <!-- Backdrop -->
    <Transition name="fade">
        <div
            v-if="show"
            class="fixed inset-0 z-40 bg-black/20 backdrop-blur-sm"
            @click="emit('close')"
        />
    </Transition>

    <!-- Panel -->
    <Transition name="slide-fade">
        <div
            v-if="show"
            ref="panelRef"
            class="fixed left-72 top-4 z-50 w-[420px] max-h-[calc(100vh-2rem)] overflow-hidden rounded-3xl border border-white/20 bg-white/80 shadow-2xl backdrop-blur-3xl dark:border-white/10 dark:bg-slate-900/80"
        >
            <!-- Animated gradient overlay -->
            <div class="pointer-events-none absolute inset-0 overflow-hidden opacity-50">
                <div
                    class="absolute -top-20 -right-20 h-40 w-40 animate-pulse rounded-full bg-gradient-to-br from-orange-500/30 via-rose-500/30 to-pink-500/30 blur-3xl"
                    style="animation-duration: 8s"
                />
                <div
                    class="absolute -bottom-20 -left-20 h-40 w-40 animate-pulse rounded-full bg-gradient-to-tr from-blue-500/30 via-indigo-500/30 to-purple-500/30 blur-3xl"
                    style="animation-duration: 10s; animation-delay: 2s"
                />
            </div>

            <!-- Header -->
            <div class="relative border-b border-white/20 p-6 dark:border-white/10">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-slate-800 dark:text-white">
                            Notifications
                        </h3>
                        <p class="text-sm text-slate-600 dark:text-slate-400">
                            {{ unreadNotifications.length }} unread
                        </p>
                    </div>

                    <!-- Mark all as read -->
                    <button
                        v-if="hasUnread"
                        @click="markAllAsRead"
                        class="group flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-semibold text-slate-700 transition-all duration-300 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-white/5"
                    >
                        <CheckCheck class="h-4 w-4 transition-transform group-hover:scale-110" />
                        <span>Mark all read</span>
                    </button>
                </div>
            </div>

            <!-- Notifications List -->
            <div class="relative max-h-[calc(100vh-10rem)] overflow-y-auto p-4">
                <!-- Empty state -->
                <div
                    v-if="notifications.length === 0"
                    class="flex flex-col items-center justify-center py-16 text-center"
                >
                    <div
                        class="mb-4 flex h-20 w-20 items-center justify-center rounded-full bg-gradient-to-br from-slate-200 to-slate-300 dark:from-slate-700 dark:to-slate-800"
                    >
                        <Bell class="h-10 w-10 text-slate-400 dark:text-slate-500" />
                    </div>
                    <h4 class="mb-2 text-lg font-bold text-slate-800 dark:text-white">
                        No notifications
                    </h4>
                    <p class="text-sm text-slate-600 dark:text-slate-400">
                        You're all caught up!
                    </p>
                </div>

                <!-- Unread Notifications -->
                <div v-if="unreadNotifications.length > 0" class="mb-4">
                    <h4 class="mb-3 text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                        New
                    </h4>
                    <div class="space-y-2">
                        <div
                            v-for="(notification, index) in unreadNotifications"
                            :key="notification.id"
                            @click="viewNotification(notification)"
                            :style="{ animationDelay: `${index * 50}ms` }"
                            class="notification-item group relative cursor-pointer overflow-hidden rounded-2xl border border-white/40 bg-white/60 p-4 backdrop-blur-xl transition-all duration-300 hover:scale-[1.02] hover:border-white/60 hover:shadow-lg dark:border-white/20 dark:bg-slate-800/60 dark:hover:border-white/30"
                        >
                            <!-- Gradient border animation -->
                            <div
                                :class="[
                                    'absolute inset-0 opacity-0 transition-opacity duration-300 group-hover:opacity-100',
                                    'bg-gradient-to-r',
                                    getNotificationStyle(notification).gradient,
                                ]"
                                style="
                                    -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
                                    -webkit-mask-composite: xor;
                                    mask-composite: exclude;
                                    padding: 2px;
                                "
                            />

                            <!-- Content -->
                            <div class="relative flex gap-3">
                                <!-- Icon -->
                                <div
                                    :class="[
                                        'flex h-10 w-10 shrink-0 items-center justify-center rounded-xl transition-all duration-300 group-hover:scale-110',
                                        getNotificationStyle(notification).iconBg,
                                    ]"
                                >
                                    <component
                                        :is="getNotificationStyle(notification).icon"
                                        :class="[
                                            'h-5 w-5',
                                            getNotificationStyle(notification).iconColor,
                                        ]"
                                    />
                                </div>

                                <!-- Text -->
                                <div class="flex-1 overflow-hidden">
                                    <p class="text-sm font-semibold text-slate-800 dark:text-white">
                                        {{ notification.data.message }}
                                    </p>

                                    <!-- Employee name if submitted -->
                                    <p
                                        v-if="notification.data.employee_name"
                                        class="mt-1 text-xs text-slate-600 dark:text-slate-400"
                                    >
                                        {{ notification.data.employee_name }}
                                    </p>

                                    <!-- Dates -->
                                    <div
                                        v-if="notification.data.start_date"
                                        class="mt-2 flex items-center gap-2 text-xs text-slate-600 dark:text-slate-400"
                                    >
                                        <Calendar class="h-3 w-3" />
                                        <span>
                                            {{ new Date(notification.data.start_date).toLocaleDateString() }}
                                            -
                                            {{ new Date(notification.data.end_date!).toLocaleDateString() }}
                                        </span>
                                    </div>

                                    <!-- Time ago -->
                                    <p class="mt-2 text-xs font-medium text-slate-500 dark:text-slate-500">
                                        {{ timeAgo(notification.created_at) }}
                                    </p>
                                </div>

                                <!-- Unread dot -->
                                <div class="flex h-10 items-center">
                                    <div class="h-2 w-2 rounded-full bg-gradient-to-r from-orange-500 to-rose-600 shadow-lg" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Read Notifications -->
                <div v-if="readNotifications.length > 0">
                    <h4 class="mb-3 text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                        Earlier
                    </h4>
                    <div class="space-y-2">
                        <div
                            v-for="notification in readNotifications"
                            :key="notification.id"
                            @click="viewNotification(notification)"
                            class="group relative cursor-pointer overflow-hidden rounded-2xl border border-white/20 bg-white/40 p-4 opacity-60 backdrop-blur-xl transition-all duration-300 hover:scale-[1.02] hover:border-white/40 hover:opacity-100 hover:shadow-lg dark:border-white/10 dark:bg-slate-800/40 dark:hover:border-white/20"
                        >
                            <!-- Content -->
                            <div class="relative flex gap-3">
                                <!-- Icon -->
                                <div
                                    :class="[
                                        'flex h-10 w-10 shrink-0 items-center justify-center rounded-xl',
                                        getNotificationStyle(notification).iconBg,
                                    ]"
                                >
                                    <component
                                        :is="getNotificationStyle(notification).icon"
                                        :class="[
                                            'h-5 w-5',
                                            getNotificationStyle(notification).iconColor,
                                        ]"
                                    />
                                </div>

                                <!-- Text -->
                                <div class="flex-1 overflow-hidden">
                                    <p class="text-sm font-semibold text-slate-800 dark:text-white">
                                        {{ notification.data.message }}
                                    </p>

                                    <!-- Employee name if submitted -->
                                    <p
                                        v-if="notification.data.employee_name"
                                        class="mt-1 text-xs text-slate-600 dark:text-slate-400"
                                    >
                                        {{ notification.data.employee_name }}
                                    </p>

                                    <!-- Time ago -->
                                    <p class="mt-2 text-xs font-medium text-slate-500 dark:text-slate-500">
                                        {{ timeAgo(notification.created_at) }}
                                    </p>
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
/* Slide fade transition */
.slide-fade-enter-active {
    transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.slide-fade-leave-active {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.slide-fade-enter-from {
    transform: translateY(-20px) scale(0.95);
    opacity: 0;
}

.slide-fade-leave-to {
    transform: translateY(-10px) scale(0.98);
    opacity: 0;
}

/* Fade transition for backdrop */
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

/* Notification item entrance animation */
.notification-item {
    animation: slideInUp 0.4s cubic-bezier(0.34, 1.56, 0.64, 1) both;
}

@keyframes slideInUp {
    from {
        transform: translateY(20px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

/* Custom scrollbar */
::-webkit-scrollbar {
    width: 8px;
}

::-webkit-scrollbar-track {
    background: transparent;
}

::-webkit-scrollbar-thumb {
    background: linear-gradient(to bottom, rgba(251, 146, 60, 0.5), rgba(251, 113, 133, 0.5));
    border-radius: 10px;
}

::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(to bottom, rgba(251, 146, 60, 0.8), rgba(251, 113, 133, 0.8));
}
</style>
