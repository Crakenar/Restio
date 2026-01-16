<script setup lang="ts">
import { ref, computed } from 'vue';
import { Bell } from 'lucide-vue-next';
import { router } from '@inertiajs/vue3';

interface Notification {
    id: string;
    type: string;
    data: {
        message: string;
        vacation_request_id?: number;
        employee_name?: string;
        type?: string;
        start_date?: string;
        end_date?: string;
        [key: string]: any;
    };
    read_at: string | null;
    created_at: string;
}

interface Props {
    notifications?: Notification[];
    isCollapsed?: boolean;
    showPanel?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    notifications: () => [],
    isCollapsed: false,
    showPanel: false,
});

interface Emits {
    (e: 'toggle-panel'): void;
}

const emit = defineEmits<Emits>();

const unreadCount = computed(() => {
    return props.notifications.filter((n) => !n.read_at).length;
});

const hasUnread = computed(() => unreadCount.value > 0);

const togglePanel = () => {
    emit('toggle-panel');
};

// Pulse animation for new notifications
const shouldPulse = computed(() => hasUnread.value);
</script>

<template>
    <div class="relative">
        <button
            data-notification-bell
            @click.stop="togglePanel"
            :class="[
                'group relative flex items-center gap-3 rounded-2xl px-4 py-3 transition-all duration-300',
                'text-slate-700 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-white/5',
                isCollapsed ? 'justify-center' : '',
                props.showPanel ? 'bg-gradient-to-r from-orange-500 to-rose-600 text-white shadow-lg shadow-orange-500/30' : '',
            ]"
        >
            <!-- Icon Container -->
            <div
                :class="[
                    'relative flex h-8 w-8 shrink-0 items-center justify-center rounded-xl transition-all duration-300',
                    props.showPanel
                        ? 'bg-white/20'
                        : 'group-hover:scale-110 group-hover:bg-slate-200 dark:group-hover:bg-white/10',
                ]"
            >
                <!-- Bell Icon -->
                <Bell
                    :class="[
                        'h-5 w-5 transition-all duration-300',
                        props.showPanel
                            ? 'text-white animate-wiggle'
                            : 'text-slate-600 group-hover:text-orange-600 dark:text-slate-400 dark:group-hover:text-orange-400',
                    ]"
                />

                <!-- Unread Badge -->
                <div
                    v-if="hasUnread"
                    :class="[
                        'absolute -top-1 -right-1 flex h-5 min-w-[20px] items-center justify-center rounded-full px-1.5',
                        'bg-gradient-to-r from-orange-500 to-rose-600 text-[10px] font-bold text-white shadow-lg',
                        shouldPulse ? 'animate-pulse-glow' : '',
                    ]"
                >
                    <span class="relative z-10">{{ unreadCount > 9 ? '9+' : unreadCount }}</span>

                    <!-- Glow effect -->
                    <div
                        v-if="shouldPulse"
                        class="absolute inset-0 rounded-full bg-orange-400 blur-md opacity-75 animate-ping"
                        style="animation-duration: 2s"
                    />
                </div>
            </div>

            <!-- Label -->
            <span
                v-if="!isCollapsed"
                :class="[
                    'flex-1 text-sm font-semibold transition-all duration-300',
                    props.showPanel ? 'text-white' : '',
                ]"
            >
                Notifications
            </span>

            <!-- Tooltip for collapsed state -->
            <div
                v-if="isCollapsed"
                class="pointer-events-none absolute left-full top-1/2 ml-4 -translate-y-1/2 whitespace-nowrap rounded-xl border border-white/20 bg-white/90 px-3 py-2 text-sm font-semibold text-slate-800 opacity-0 shadow-xl backdrop-blur-xl transition-all duration-300 group-hover:opacity-100 dark:border-white/10 dark:bg-slate-900/90 dark:text-slate-100"
            >
                Notifications
                <span
                    v-if="hasUnread"
                    class="ml-2 rounded-full bg-gradient-to-r from-orange-500 to-rose-600 px-2 py-0.5 text-xs text-white"
                >
                    {{ unreadCount }}
                </span>
                <div
                    class="absolute right-full top-1/2 -mr-1 -translate-y-1/2 border-4 border-transparent border-r-white/90 dark:border-r-slate-900/90"
                />
            </div>
        </button>
    </div>
</template>

<style scoped>
@keyframes wiggle {
    0%, 100% {
        transform: rotate(0deg);
    }
    25% {
        transform: rotate(-10deg);
    }
    75% {
        transform: rotate(10deg);
    }
}

@keyframes pulse-glow {
    0%, 100% {
        opacity: 1;
        transform: scale(1);
    }
    50% {
        opacity: 0.8;
        transform: scale(1.05);
    }
}

.animate-wiggle {
    animation: wiggle 0.5s ease-in-out;
}

.animate-pulse-glow {
    animation: pulse-glow 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
</style>
