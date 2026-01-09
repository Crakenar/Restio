<script setup lang="ts">
import { SelectScrollUpButton as SelectScrollUpButtonPrimitive } from 'reka-ui'
import { ChevronUp } from 'lucide-vue-next'
import { cn } from '@/lib/utils'

defineProps<{
    class?: string
}>()
</script>

<template>
    <SelectScrollUpButtonPrimitive
        data-slot="select-scroll-up-button"
        :class="cn('flex cursor-default items-center justify-center py-1', $props.class)"
    >
        <ChevronUp class="size-4" />
    </SelectScrollUpButtonPrimitive>
</template>
