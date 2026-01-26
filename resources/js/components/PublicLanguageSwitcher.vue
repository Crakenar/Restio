<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { Globe } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';

const { locale } = useI18n();

const languages = [
    { code: 'en', label: 'English', flag: '🇬🇧' },
    { code: 'fr', label: 'Français', flag: '🇫🇷' },
];

const switchLanguage = (newLocale: string) => {
    // Update locale via Inertia POST request
    router.post(
        '/locale',
        { locale: newLocale },
        {
            preserveState: true,
            preserveScroll: true,
            onSuccess: () => {
                // The locale will be updated server-side and reflected in the next page load
                locale.value = newLocale;
            },
        },
    );
};
</script>

<template>
    <div class="flex items-center gap-2 rounded-full border border-white/20 bg-white/10 backdrop-blur-sm p-1">
        <button
            v-for="lang in languages"
            :key="lang.code"
            @click="switchLanguage(lang.code)"
            :class="[
                'flex items-center gap-2 rounded-full px-3 py-1.5 text-sm font-medium transition-all duration-200',
                locale === lang.code
                    ? 'bg-white/20 text-white shadow-sm'
                    : 'text-white/70 hover:text-white hover:bg-white/10'
            ]"
        >
            <span>{{ lang.flag }}</span>
            <span class="hidden sm:inline">{{ lang.label }}</span>
        </button>
    </div>
</template>
