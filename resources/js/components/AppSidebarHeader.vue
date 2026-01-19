<script setup lang="ts">
import { ref, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import { SidebarTrigger } from '@/components/ui/sidebar';
import NotificationBell from '@/components/NotificationBell.vue';
import NotificationPanel from '@/components/NotificationPanel.vue';
import type { BreadcrumbItemType } from '@/types';

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

withDefaults(
    defineProps<{
        breadcrumbs?: BreadcrumbItemType[];
    }>(),
    {
        breadcrumbs: () => [],
    },
);

const page = usePage();
const notifications = computed<Notification[]>(() => page.props.notifications || []);
const showNotificationPanel = ref(false);

const toggleNotificationPanel = () => {
    showNotificationPanel.value = !showNotificationPanel.value;
};

const closeNotificationPanel = () => {
    showNotificationPanel.value = false;
};
</script>

<template>
    <header
        class="sticky top-0 z-30 flex h-16 shrink-0 items-center justify-between gap-4 border-b border-sidebar-border/70 bg-white/80 px-6 backdrop-blur-xl transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12 dark:bg-slate-950/80 md:px-4"
    >
        <!-- Left side: Sidebar trigger and breadcrumbs -->
        <div class="flex items-center gap-2">
            <SidebarTrigger class="-ml-1" />
            <template v-if="breadcrumbs && breadcrumbs.length > 0">
                <Breadcrumbs :breadcrumbs="breadcrumbs" />
            </template>
        </div>

        <!-- Right side: Notification bell -->
        <div class="flex items-center gap-2">
            <NotificationBell
                :notifications="notifications"
                :show-panel="showNotificationPanel"
                @toggle-panel="toggleNotificationPanel"
            />
        </div>

        <!-- Notification Panel -->
        <NotificationPanel
            :notifications="notifications"
            :show="showNotificationPanel"
            @close="closeNotificationPanel"
        />
    </header>
</template>
