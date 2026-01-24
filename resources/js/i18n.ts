import { createI18n } from 'vue-i18n';

// Import translation messages
import en from './locales/en.json';
import fr from './locales/fr.json';

export type MessageSchema = typeof en;

// Get initial locale from HTML lang attribute or default to 'en'
const initialLocale = (document.documentElement.lang || 'en') as 'en' | 'fr';

const i18n = createI18n<[MessageSchema], 'en' | 'fr'>({
    legacy: false,
    locale: initialLocale,
    fallbackLocale: 'en',
    messages: {
        en,
        fr,
    },
    globalInjection: true,
    missingWarn: false,
    fallbackWarn: false,
});

export default i18n;
