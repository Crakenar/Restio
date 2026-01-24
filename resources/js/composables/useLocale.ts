import { router, usePage } from '@inertiajs/vue3';
import { computed, watch } from 'vue';
import { useI18n } from 'vue-i18n';

export function useLocale() {
    const page = usePage();
    const { locale } = useI18n();

    const currentLocale = computed(() => page.props.locale as string || 'en');
    const availableLocales = computed(() => page.props.availableLocales as string[] || ['en', 'fr']);

    // Sync i18n locale with page locale
    watch(
        currentLocale,
        (newLocale) => {
            if (locale.value !== newLocale) {
                locale.value = newLocale;
                document.documentElement.lang = newLocale;
            }
        },
        { immediate: true }
    );

    const switchLocale = (newLocale: string) => {
        if (availableLocales.value.includes(newLocale)) {
            // Visit current page with new locale parameter
            router.get(
                window.location.pathname,
                { locale: newLocale },
                {
                    preserveState: true,
                    preserveScroll: true,
                    only: ['locale'], // Only refresh locale prop
                }
            );
        }
    };

    return {
        currentLocale,
        availableLocales,
        switchLocale,
    };
}
