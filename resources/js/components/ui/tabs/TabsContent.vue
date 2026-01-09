<script setup lang="ts">
import { TabsContent as TabsContentPrimitive } from 'reka-ui'
import { cn } from '@/lib/utils'

defineProps<{
    class?: string
    value: string
}>()
</script>

<template>
    <TabsContentPrimitive
        data-slot="tabs-content"
        :value="value"
        :class="cn('flex-1 outline-none', $props.class)"
    >
        <slot />
    </TabsContentPrimitive>
</template>
