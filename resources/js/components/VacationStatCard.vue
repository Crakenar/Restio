<script setup lang="ts">
import type { Component } from 'vue'
import { Card, CardContent } from '@/components/ui/card'

defineProps<{
    title: string
    value: string | number
    subtitle?: string
    icon: Component
    iconColor?: string
}>()
</script>

<template>
    <Card class="relative overflow-hidden">
        <CardContent class="p-6">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <p class="mb-1 text-sm text-muted-foreground">{{ title }}</p>
                    <p class="mb-1 text-3xl font-semibold">{{ value }}</p>
                    <p v-if="subtitle" class="text-xs text-muted-foreground">
                        {{ subtitle }}
                    </p>
                </div>
                <div :class="[iconColor || 'text-blue-600', 'opacity-80']">
                    <component :is="icon" class="h-8 w-8" />
                </div>
            </div>
        </CardContent>
    </Card>
</template>
