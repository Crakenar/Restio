<script setup lang="ts">
import { cn } from '@/lib/utils'

defineProps<{
    class?: string
    placeholder?: string
    rows?: number
    disabled?: boolean
    id?: string
    modelValue?: string
}>()

defineEmits<{
    'update:modelValue': [value: string]
}>()
</script>

<template>
    <textarea
        data-slot="textarea"
        :id="id"
        :class="
            cn(
                'border-input placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-ring/50 aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive dark:bg-input/30 flex field-sizing-content min-h-16 w-full resize-none rounded-md border bg-transparent px-3 py-2 text-base transition-[color,box-shadow] outline-none focus-visible:ring-[3px] disabled:cursor-not-allowed disabled:opacity-50 md:text-sm',
                $props.class
            )
        "
        :placeholder="placeholder"
        :rows="rows"
        :disabled="disabled"
        :value="modelValue"
        @input="$emit('update:modelValue', ($event.target as HTMLTextAreaElement).value)"
    />
</template>
