<script setup lang="ts">
import { useLocale } from '@/composables/useLocale';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Globe } from 'lucide-vue-next';

const { currentLocale, availableLocales, switchLocale } = useLocale();

const localeNames: Record<string, string> = {
    en: 'English',
    fr: 'Français',
};
</script>

<template>
    <DropdownMenu>
        <DropdownMenuTrigger as-child>
            <Button variant="ghost" size="icon">
                <Globe class="h-5 w-5" />
                <span class="sr-only">{{ $t('common.changeLanguage') }}</span>
            </Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent align="end">
            <DropdownMenuItem
                v-for="locale in availableLocales"
                :key="locale"
                @click="switchLocale(locale)"
                :class="{ 'bg-accent': currentLocale === locale }"
            >
                <span>{{ localeNames[locale] || locale }}</span>
            </DropdownMenuItem>
        </DropdownMenuContent>
    </DropdownMenu>
</template>
