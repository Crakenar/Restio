<script setup lang="ts">
import { useToast } from '@/composables/useToast';
import { CheckCircle2, XCircle, Info, AlertTriangle, X } from 'lucide-vue-next';

const { toasts, removeToast } = useToast();

const getToastStyles = (type: string) => {
    const styles = {
        success:
            'bg-emerald-50 border-emerald-200 text-emerald-800 dark:bg-emerald-500/10 dark:border-emerald-500/20 dark:text-emerald-300',
        error: 'bg-red-50 border-red-200 text-red-800 dark:bg-red-500/10 dark:border-red-500/20 dark:text-red-300',
        warning:
            'bg-amber-50 border-amber-200 text-amber-800 dark:bg-amber-500/10 dark:border-amber-500/20 dark:text-amber-300',
        info: 'bg-blue-50 border-blue-200 text-blue-800 dark:bg-blue-500/10 dark:border-blue-500/20 dark:text-blue-300',
    };
    return styles[type as keyof typeof styles] || styles.info;
};

const getIcon = (type: string) => {
    const icons = {
        success: CheckCircle2,
        error: XCircle,
        warning: AlertTriangle,
        info: Info,
    };
    return icons[type as keyof typeof icons] || Info;
};
</script>

<template>
    <div
        class="pointer-events-none fixed top-4 right-4 z-[9999] flex flex-col gap-3"
        style="max-width: 420px"
    >
        <TransitionGroup
            enter-active-class="transition ease-out duration-300"
            enter-from-class="translate-x-full opacity-0"
            enter-to-class="translate-x-0 opacity-100"
            leave-active-class="transition ease-in duration-200"
            leave-from-class="translate-x-0 opacity-100"
            leave-to-class="translate-x-full opacity-0"
        >
            <div
                v-for="toast in toasts"
                :key="toast.id"
                :class="[
                    'pointer-events-auto flex items-start gap-3 rounded-xl border p-4 shadow-lg backdrop-blur-xl',
                    getToastStyles(toast.type),
                ]"
            >
                <component
                    :is="getIcon(toast.type)"
                    class="mt-0.5 h-5 w-5 shrink-0"
                />
                <p class="flex-1 text-sm font-medium">{{ toast.message }}</p>
                <button
                    @click="removeToast(toast.id)"
                    class="shrink-0 rounded-lg p-1 transition-colors hover:bg-black/10 dark:hover:bg-white/10"
                >
                    <X class="h-4 w-4" />
                </button>
            </div>
        </TransitionGroup>
    </div>
</template>
