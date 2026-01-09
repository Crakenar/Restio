<script setup lang="ts">
import { computed } from 'vue'
import { Calendar, FileText, Users, Settings, Palmtree } from 'lucide-vue-next'
import { cn } from '@/lib/utils'

const props = defineProps<{
    activeView: string
    userRole: 'employee' | 'manager' | 'admin'
}>()

const emit = defineEmits<{
    viewChange: [view: string]
}>()

const menuItems = computed(() => [
    {
        id: 'dashboard',
        label: 'Dashboard',
        icon: Calendar,
        visible: true,
    },
    {
        id: 'requests',
        label: 'Requests',
        icon: FileText,
        visible: true,
    },
    {
        id: 'team',
        label: 'Team',
        icon: Users,
        visible: props.userRole === 'manager' || props.userRole === 'admin',
    },
    {
        id: 'settings',
        label: 'Settings',
        icon: Settings,
        visible: true,
    },
])

const visibleMenuItems = computed(() => menuItems.value.filter((item) => item.visible))
</script>

<template>
    <div class="flex h-screen w-64 flex-col border-r bg-card">
        <!-- Logo/Brand -->
        <div class="border-b p-6">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-600">
                    <Palmtree class="h-6 w-6 text-white" />
                </div>
                <div>
                    <h2 class="text-lg font-semibold">Vacationly</h2>
                    <p class="text-xs text-muted-foreground">Manage your time off</p>
                </div>
            </div>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 p-4">
            <ul class="space-y-1">
                <li v-for="item in visibleMenuItems" :key="item.id">
                    <button
                        :class="
                            cn(
                                'flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm transition-colors',
                                activeView === item.id
                                    ? 'bg-blue-50 text-blue-700 dark:bg-blue-950 dark:text-blue-400'
                                    : 'text-muted-foreground hover:bg-accent hover:text-foreground'
                            )
                        "
                        @click="emit('viewChange', item.id)"
                    >
                        <component :is="item.icon" class="h-5 w-5" />
                        <span>{{ item.label }}</span>
                    </button>
                </li>
            </ul>
        </nav>

        <!-- User Info -->
        <div class="border-t p-4">
            <div class="flex items-center gap-3">
                <div
                    class="flex h-8 w-8 items-center justify-center rounded-full bg-gradient-to-br from-blue-500 to-purple-600 text-sm font-semibold text-white"
                >
                    JD
                </div>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-medium">John Doe</p>
                    <p class="text-xs capitalize text-muted-foreground">{{ userRole }}</p>
                </div>
            </div>
        </div>
    </div>
</template>
