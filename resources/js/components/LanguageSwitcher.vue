<script setup lang="ts">
import { useLocale } from '@/composables/useLocale';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Globe, Check } from 'lucide-vue-next';

const { currentLocale, availableLocales, switchLocale } = useLocale();

const localeNames: Record<string, string> = {
    en: 'English',
    fr: 'Français',
};
</script>

<template>
    <DropdownMenu>
        <DropdownMenuTrigger as-child>
            <Button variant="outline" class="gap-2">
                <Globe class="h-4 w-4" />
                <span>{{ localeNames[currentLocale] || currentLocale }}</span>
            </Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent align="end">
            <DropdownMenuItem
                v-for="locale in availableLocales"
                :key="locale"
                @click="switchLocale(locale)"
                :class="{ 'bg-accent': currentLocale === locale }"
                class="flex items-center justify-between gap-4"
            >
                <span>{{ localeNames[locale] || locale }}</span>
                <Check v-if="currentLocale === locale" class="h-4 w-4" />
            </DropdownMenuItem>
        </DropdownMenuContent>
    </DropdownMenu>
</template>
