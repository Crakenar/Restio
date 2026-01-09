<script setup lang="ts">
import { TabsRoot } from 'reka-ui'
import { cn } from '@/lib/utils'

defineProps<{
    class?: string
    defaultValue?: string
    modelValue?: string
}>()

const emit = defineEmits<{
    'update:modelValue': [value: string]
}>()
</script>

<template>
    <TabsRoot
        data-slot="tabs"
        :class="cn('flex flex-col gap-2', $props.class)"
        :default-value="defaultValue"
        :model-value="modelValue"
        @update:model-value="emit('update:modelValue', $event)"
    >
        <slot />
    </TabsRoot>
</template>
