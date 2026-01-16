<script setup lang="ts">
/**
 * ScrollableModalContent - A reusable wrapper for modal content that needs scrolling
 *
 * This component solves the common problem of modal content overflowing outside
 * the modal boundaries. It provides:
 * - Fixed header at top (shrink-0)
 * - Scrollable content area (flex-1 overflow-y-auto)
 * - Optional fixed footer at bottom (shrink-0)
 * - Custom styled scrollbar
 *
 * Usage:
 * <ScrollableModalContent>
 *   <template #header>Your header content</template>
 *   <template #default>Your scrollable content</template>
 *   <template #footer>Your optional footer (buttons, etc)</template>
 * </ScrollableModalContent>
 */

defineSlots<{
    header?: () => any;
    default: () => any;
    footer?: () => any;
}>();
</script>

<template>
    <div class="flex max-h-[85vh] flex-col">
        <!-- Fixed Header -->
        <div v-if="$slots.header" class="shrink-0">
            <slot name="header" />
        </div>

        <!-- Scrollable Content -->
        <div class="scrollable-content flex-1 space-y-6 overflow-y-auto py-4 pr-2" style="scrollbar-gutter: stable">
            <slot />
        </div>

        <!-- Fixed Footer -->
        <div v-if="$slots.footer" class="shrink-0 border-t border-slate-200/50 pt-4 dark:border-white/10">
            <slot name="footer" />
        </div>
    </div>
</template>

<style scoped>
/* Custom scrollbar styling */
.scrollable-content {
    scrollbar-width: thin;
    scrollbar-color: rgb(203 213 225 / 0.5) transparent;
}

.scrollable-content::-webkit-scrollbar {
    width: 6px;
}

.scrollable-content::-webkit-scrollbar-track {
    background: transparent;
}

.scrollable-content::-webkit-scrollbar-thumb {
    background-color: rgb(203 213 225 / 0.5);
    border-radius: 3px;
}

.scrollable-content::-webkit-scrollbar-thumb:hover {
    background-color: rgb(203 213 225 / 0.8);
}

/* Dark mode scrollbar */
:global(.dark) .scrollable-content {
    scrollbar-color: rgb(255 255 255 / 0.2) transparent;
}

:global(.dark) .scrollable-content::-webkit-scrollbar-thumb {
    background-color: rgb(255 255 255 / 0.2);
}

:global(.dark) .scrollable-content::-webkit-scrollbar-thumb:hover {
    background-color: rgb(255 255 255 / 0.3);
}
</style>
