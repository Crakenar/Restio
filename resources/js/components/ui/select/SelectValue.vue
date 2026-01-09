<script setup lang="ts">
import { SelectValue as SelectValuePrimitive } from 'reka-ui'

defineProps<{
    placeholder?: string
}>()
</script>

<template>
    <SelectValuePrimitive data-slot="select-value" :placeholder="placeholder">
        <slot />
    </SelectValuePrimitive>
</template>
