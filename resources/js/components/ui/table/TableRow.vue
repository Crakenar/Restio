<script setup lang="ts">
import { cn } from '@/lib/utils'

defineProps<{
    class?: string
}>()
</script>

<template>
    <tr
        data-slot="table-row"
        :class="
            cn(
                'hover:bg-muted/50 data-[state=selected]:bg-muted border-b transition-colors',
                $props.class
            )
        "
    >
        <slot />
    </tr>
</template>
