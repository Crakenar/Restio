# Translations Guide

This application supports multiple languages with full internationalization (i18n) for both frontend (Vue.js) and backend (Laravel).

## Supported Languages

- English (`en`) - Default
- French (`fr`)

## Frontend Translations (Vue/Inertia)

### Using Translations in Vue Components

The application uses `vue-i18n` for frontend translations. You can access translations using the `$t()` function or the `useI18n()` composable.

#### Option 1: Template Syntax

```vue
<template>
    <h1>{{ $t('welcome.title') }}</h1>
    <p>{{ $t('welcome.subtitle') }}</p>
    <button>{{ $t('common.save') }}</button>
</template>
```

#### Option 2: Script Setup with Composable

```vue
<script setup lang="ts">
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const title = t('welcome.title');
</script>

<template>
    <h1>{{ title }}</h1>
</template>
```

#### With Interpolation

```vue
<template>
    <!-- Using named placeholders -->
    <p>{{ $t('dashboard.welcome', { name: user.name }) }}</p>

    <!-- Using numbered placeholders -->
    <p>{{ $t('auth.register.agreeToTerms', [$t('auth.register.termsOfService'), $t('auth.register.privacyPolicy')]) }}</p>
</template>
```

### Translation Files

Frontend translations are located in:
- `resources/js/locales/en.json` - English translations
- `resources/js/locales/fr.json` - French translations

### Language Switcher Component

A ready-to-use language switcher component is available:

```vue
<script setup lang="ts">
import LanguageSwitcher from '@/components/LanguageSwitcher.vue';
</script>

<template>
    <LanguageSwitcher />
</template>
```

### Programmatic Locale Switching

```vue
<script setup lang="ts">
import { useLocale } from '@/composables/useLocale';

const { currentLocale, availableLocales, switchLocale } = useLocale();

// Switch to French
function switchToFrench() {
    switchLocale('fr');
}
</script>
```

## Backend Translations (Laravel)

### Using Translations in Controllers/PHP

```php
// Simple translation
$message = __('app.welcome');

// Translation with parameters
$message = __('vacation.notifications.new_request', ['name' => $user->name]);

// Translation from specific file
$error = __('auth.failed');

// Translation with choice (pluralization)
$message = trans_choice('vacation.stats.days_remaining', $daysCount);
```

### Translation Files

Backend translations are located in:
- `lang/en/` - English translations
- `lang/fr/` - French translations

Files:
- `app.php` - General application strings
- `auth.php` - Authentication messages
- `vacation.php` - Vacation/leave management strings
- `validation.php` - Form validation messages
- `passwords.php` - Password reset messages

### Using Translations in Blade Templates

```blade
<h1>{{ __('app.welcome') }}</h1>
<button>{{ __('app.actions.save') }}</button>
```

### Using Translations in Notifications/Emails

```php
class VacationRequestNotification extends Notification
{
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject(__('vacation.notifications.new_request', ['name' => $this->requester->name]))
            ->line(__('vacation.messages.request_submitted'));
    }
}
```

## Adding a New Language

### 1. Update Configuration

Add the new locale to `config/app.php`:

```php
'available_locales' => ['en', 'fr', 'es'], // Added Spanish
```

### 2. Create Frontend Translation File

Create `resources/js/locales/es.json` by copying and translating `en.json`.

### 3. Import in i18n Setup

Update `resources/js/i18n.ts`:

```typescript
import es from './locales/es.json';

const i18n = createI18n<[MessageSchema], 'en' | 'fr' | 'es'>({
    // ...
    messages: {
        en,
        fr,
        es, // Add new language
    },
});
```

### 4. Create Backend Translation Directory

Create `lang/es/` directory and copy/translate all PHP files from `lang/en/`.

### 5. Update Language Switcher

Update `resources/js/components/LanguageSwitcher.vue`:

```typescript
const localeNames: Record<string, string> = {
    en: 'English',
    fr: 'Français',
    es: 'Español', // Add new language
};
```

## Best Practices

1. **Always use translation keys** - Never hardcode user-facing text
2. **Consistent naming** - Use dot notation: `category.subcategory.key`
3. **Keep keys organized** - Group related translations together
4. **Provide context** - Use descriptive keys that explain the context
5. **Use placeholders** - For dynamic content: `{ name }` in JSON, `:name` in PHP
6. **Sync translations** - Keep all language files in sync with the same keys
7. **Test all languages** - Verify translations display correctly in all supported languages

## Testing Translations

```bash
# Set application locale in .env
APP_LOCALE=fr

# Or test by switching language in the application UI using the language switcher

# Test backend translations in Tinker
php artisan tinker
>>> app()->setLocale('fr');
>>> __('app.welcome');
```

## Translation Status

### Fully Translated
- ✅ Welcome page
- ✅ Authentication pages
- ✅ Common UI elements
- ✅ Vacation requests
- ✅ Navigation

### Partially Translated
- ⚠️ Settings pages
- ⚠️ Employee management
- ⚠️ Calendar views

### Not Translated
- ❌ Admin panels
- ❌ Error pages
- ❌ Email templates (partial)

## Contributing Translations

1. Identify missing translations
2. Add keys to all language files (even if English-only initially)
3. Request native speaker review for accuracy
4. Test the translation in context
5. Submit a pull request

## Resources

- [Vue I18n Documentation](https://vue-i18n.intlify.dev/)
- [Laravel Localization Documentation](https://laravel.com/docs/localization)
- Translation files location: `lang/` and `resources/js/locales/`
