<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { useToast } from '@/composables/useToast';
import { X, Check, AlertTriangle } from 'lucide-vue-next';

interface Props {
    show: boolean;
    action: 'approve' | 'reject' | null;
    requestId: string | null;
    employeeName?: string;
}

const props = withDefaults(defineProps<Props>(), {
    show: false,
    action: null,
    requestId: null,
    employeeName: 'this employee',
});

const emit = defineEmits<{
    close: [];
}>();

const toast = useToast();
const rejectionReason = ref('');
const processing = ref(false);

// Reset form when modal closes
watch(() => props.show, (newShow) => {
    if (!newShow) {
        rejectionReason.value = '';
        processing.value = false;
    }
});

const modalConfig = computed(() => {
    if (props.action === 'approve') {
        return {
            title: 'Approve Time Off Request',
            message: `Are you sure you want to approve ${props.employeeName}'s time off request?`,
            confirmText: 'Approve Request',
            confirmClass: 'bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700',
            icon: Check,
            iconBg: 'from-emerald-500 to-teal-600',
        };
    } else {
        return {
            title: 'Reject Time Off Request',
            message: `Please provide a reason for rejecting ${props.employeeName}'s time off request.`,
            confirmText: 'Reject Request',
            confirmClass: 'bg-gradient-to-r from-rose-500 to-red-600 hover:from-rose-600 hover:to-red-700',
            icon: AlertTriangle,
            iconBg: 'from-rose-500 to-red-600',
        };
    }
});

const handleConfirm = () => {
    if (!props.requestId || !props.action) return;

    processing.value = true;

    const endpoint = props.action === 'approve'
        ? `/vacation-requests/${props.requestId}/approve`
        : `/vacation-requests/${props.requestId}/reject`;

    const data = props.action === 'reject'
        ? { rejection_reason: rejectionReason.value }
        : {};

    router.post(endpoint, data, {
        preserveScroll: true,
        onSuccess: () => {
            const message = props.action === 'approve'
                ? 'Request approved successfully!'
                : 'Request rejected successfully!';
            toast.success(message);
            emit('close');
        },
        onError: () => {
            const message = props.action === 'approve'
                ? 'Failed to approve request. Please try again.'
                : 'Failed to reject request. Please try again.';
            toast.error(message);
            processing.value = false;
        },
        onFinish: () => {
            processing.value = false;
        },
    });
};

const handleClose = () => {
    if (!processing.value) {
        emit('close');
    }
};
</script>

<template>
    <!-- Backdrop -->
    <Transition name="fade">
        <div
            v-if="show"
            class="fixed inset-0 z-50 bg-black/40 backdrop-blur-sm"
            @click="handleClose"
        />
    </Transition>

    <!-- Modal -->
    <Transition name="modal">
        <div
            v-if="show"
            class="fixed left-1/2 top-1/2 z-50 w-full max-w-md -translate-x-1/2 -translate-y-1/2 overflow-hidden rounded-3xl border border-white/40 bg-white/90 shadow-2xl backdrop-blur-2xl dark:border-white/20 dark:bg-slate-900/90"
        >
            <!-- Animated gradient overlay -->
            <div class="pointer-events-none absolute inset-0 overflow-hidden opacity-30">
                <div
                    :class="[
                        'absolute -top-20 -right-20 h-40 w-40 animate-pulse rounded-full blur-3xl',
                        'bg-gradient-to-br',
                        modalConfig.iconBg,
                    ]"
                    style="animation-duration: 4s"
                />
            </div>

            <!-- Content -->
            <div class="relative p-8">
                <!-- Close button -->
                <button
                    @click="handleClose"
                    :disabled="processing"
                    class="absolute right-4 top-4 flex h-8 w-8 items-center justify-center rounded-xl text-slate-400 transition-all duration-300 hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-white/10 dark:hover:text-slate-300"
                >
                    <X class="h-5 w-5" />
                </button>

                <!-- Icon -->
                <div class="mb-6 flex justify-center">
                    <div
                        :class="[
                            'flex h-20 w-20 items-center justify-center rounded-3xl shadow-2xl',
                            'bg-gradient-to-br',
                            modalConfig.iconBg,
                        ]"
                    >
                        <component :is="modalConfig.icon" class="h-10 w-10 text-white" />
                    </div>
                </div>

                <!-- Title -->
                <h2 class="mb-3 text-center text-2xl font-bold text-slate-900 dark:text-white">
                    {{ modalConfig.title }}
                </h2>

                <!-- Message -->
                <p class="mb-6 text-center text-sm text-slate-600 dark:text-slate-400">
                    {{ modalConfig.message }}
                </p>

                <!-- Rejection reason input -->
                <div v-if="action === 'reject'" class="mb-6">
                    <label class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-300">
                        Reason for Rejection
                    </label>
                    <textarea
                        v-model="rejectionReason"
                        rows="4"
                        placeholder="Please provide a clear reason for the rejection..."
                        class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 placeholder-slate-400 transition-all duration-300 focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500/20 dark:border-white/20 dark:bg-slate-800/50 dark:text-white dark:placeholder-slate-500 dark:focus:border-rose-400"
                        :disabled="processing"
                    />
                </div>

                <!-- Actions -->
                <div class="flex gap-3">
                    <button
                        @click="handleClose"
                        :disabled="processing"
                        class="flex-1 rounded-xl border border-slate-300 bg-white px-6 py-3 font-semibold text-slate-700 transition-all duration-300 hover:bg-slate-50 disabled:opacity-50 disabled:cursor-not-allowed dark:border-white/20 dark:bg-slate-800/50 dark:text-slate-300 dark:hover:bg-slate-800"
                    >
                        Cancel
                    </button>
                    <button
                        @click="handleConfirm"
                        :disabled="processing"
                        :class="[
                            'flex-1 rounded-xl px-6 py-3 font-semibold text-white shadow-lg transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed',
                            modalConfig.confirmClass,
                        ]"
                    >
                        <span v-if="processing">Processing...</span>
                        <span v-else>{{ modalConfig.confirmText }}</span>
                    </button>
                </div>
            </div>
        </div>
    </Transition>
</template>

<style scoped>
/* Fade transition for backdrop */
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

/* Modal transition */
.modal-enter-active {
    transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.modal-leave-active {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.modal-enter-from {
    transform: translate(-50%, -50%) scale(0.9);
    opacity: 0;
}

.modal-leave-to {
    transform: translate(-50%, -50%) scale(0.95);
    opacity: 0;
}
</style>
