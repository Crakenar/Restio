<script setup lang="ts">
import { SelectScrollDownButton as SelectScrollDownButtonPrimitive } from 'reka-ui'
import { ChevronDown } from 'lucide-vue-next'
import { cn } from '@/lib/utils'

defineProps<{
    class?: string
}>()
</script>

<template>
    <SelectScrollDownButtonPrimitive
        data-slot="select-scroll-down-button"
        :class="cn('flex cursor-default items-center justify-center py-1', $props.class)"
    >
        <ChevronDown class="size-4" />
    </SelectScrollDownButtonPrimitive>
</template>
