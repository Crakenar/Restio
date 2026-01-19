<script setup lang="ts">
import { computed } from 'vue';
import { Bell } from 'lucide-vue-next';

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
    showPanel?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    notifications: () => [],
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
</script>

<template>
    <div class="relative">
        <button
            data-notification-bell
            @click.stop="togglePanel"
            :class="[
                'group relative flex h-12 w-12 items-center justify-center rounded-full transition-all duration-300',
                'bg-white/90 backdrop-blur-xl shadow-lg border border-slate-200/50',
                'hover:bg-white hover:shadow-xl hover:scale-105 hover:border-slate-300',
                'dark:bg-slate-800/90 dark:border-slate-700/50 dark:hover:bg-slate-800 dark:hover:border-slate-600',
                showPanel
                    ? 'bg-gradient-to-br from-orange-500 to-rose-600 !border-transparent shadow-xl shadow-orange-500/30 scale-105'
                    : '',
            ]"
            :aria-label="`Notifications${hasUnread ? ` (${unreadCount} unread)` : ''}`"
        >
            <!-- Bell Icon -->
            <div class="relative">
                <Bell
                    :class="[
                        'h-5 w-5 transition-all duration-300',
                        showPanel
                            ? 'text-white animate-bell-ring'
                            : 'text-slate-600 group-hover:text-slate-900 dark:text-slate-400 dark:group-hover:text-slate-100',
                    ]"
                />

                <!-- Unread Badge -->
                <Transition name="badge-pop">
                    <div
                        v-if="hasUnread"
                        :class="[
                            'absolute -top-2 -right-2 flex h-5 min-w-[20px] items-center justify-center rounded-full px-1.5 text-[10px] font-bold text-white shadow-lg',
                            'bg-gradient-to-br from-orange-500 to-rose-600',
                        ]"
                    >
                        <span class="relative z-10">{{ unreadCount > 9 ? '9+' : unreadCount }}</span>

                        <!-- Pulsing glow for unread -->
                        <div
                            class="absolute inset-0 rounded-full bg-orange-400 opacity-75 animate-pulse-ring"
                        />
                    </div>
                </Transition>
            </div>
        </button>
    </div>
</template>

<style scoped>
/* Bell ring animation */
@keyframes bell-ring {
    0%,
    100% {
        transform: rotate(0deg);
    }
    10%,
    30% {
        transform: rotate(-12deg);
    }
    20%,
    40% {
        transform: rotate(12deg);
    }
    50% {
        transform: rotate(0deg);
    }
}

.animate-bell-ring {
    animation: bell-ring 0.6s ease-in-out;
}

/* Pulse ring animation */
@keyframes pulse-ring {
    0% {
        transform: scale(1);
        opacity: 0.75;
    }
    50% {
        transform: scale(1.1);
        opacity: 0.4;
    }
    100% {
        transform: scale(1);
        opacity: 0.75;
    }
}

.animate-pulse-ring {
    animation: pulse-ring 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

/* Badge pop animation */
.badge-pop-enter-active {
    animation: badge-pop 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.badge-pop-leave-active {
    animation: badge-pop 0.3s cubic-bezier(0.4, 0, 0.2, 1) reverse;
}

@keyframes badge-pop {
    0% {
        transform: scale(0);
        opacity: 0;
    }
    50% {
        transform: scale(1.2);
    }
    100% {
        transform: scale(1);
        opacity: 1;
    }
}
</style>
