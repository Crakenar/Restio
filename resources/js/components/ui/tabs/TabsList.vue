<script setup lang="ts">
import { TabsList as TabsListPrimitive } from 'reka-ui'
import { cn } from '@/lib/utils'

defineProps<{
    class?: string
}>()
</script>

<template>
    <TabsListPrimitive
        data-slot="tabs-list"
        :class="
            cn(
                'bg-muted text-muted-foreground inline-flex h-9 w-fit items-center justify-center rounded-lg p-[3px]',
                $props.class
            )
        "
    >
        <slot />
    </TabsListPrimitive>
</template>
