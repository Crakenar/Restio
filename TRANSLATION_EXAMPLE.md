# Translation Implementation Example

## Example: Updating Welcome.vue to Use Translations

### Before (Hardcoded Text)

```vue
<template>
    <Link
        :href="login()"
        class="rounded-full px-6 py-2.5 text-sm font-medium text-white transition-all duration-300 hover:bg-white/10"
    >
        Log in
    </Link>

    <h1 class="text-5xl font-bold">
        Time off, beautifully managed
    </h1>

    <p class="text-lg text-white/70">
        Transform how your team plans vacations and time off.
    </p>
</template>
```

### After (Using Translations)

```vue
<template>
    <Link
        :href="login()"
        class="rounded-full px-6 py-2.5 text-sm font-medium text-white transition-all duration-300 hover:bg-white/10"
    >
        {{ $t('nav.login') }}
    </Link>

    <h1 class="text-5xl font-bold">
        {{ $t('welcome.title') }}
    </h1>

    <p class="text-lg text-white/70">
        {{ $t('welcome.subtitle') }}
    </p>
</template>
```

## Example: Backend Controller with Translations

### Before

```php
class VacationRequestController extends Controller
{
    public function store(Request $request)
    {
        // ... validation and creation logic ...

        return redirect()->back()->with('success', 'Your vacation request has been submitted');
    }

    public function approve(VacationRequest $request)
    {
        $request->update(['status' => 'approved']);

        return redirect()->back()->with('success', 'Vacation request has been approved');
    }
}
```

### After

```php
class VacationRequestController extends Controller
{
    public function store(Request $request)
    {
        // ... validation and creation logic ...

        return redirect()->back()->with('success', __('vacation.messages.request_submitted'));
    }

    public function approve(VacationRequest $request)
    {
        $request->update(['status' => 'approved']);

        return redirect()->back()->with('success', __('vacation.messages.request_approved'));
    }
}
```

## Example: Email Template

### Before

```blade
<!-- resources/views/emails/vacation-request-submitted.blade.php -->
<p>Hello {{ $user->name }},</p>

<p>Your vacation request has been submitted and is awaiting approval.</p>

<p><strong>Details:</strong></p>
<ul>
    <li>Start Date: {{ $request->start_date }}</li>
    <li>End Date: {{ $request->end_date }}</li>
    <li>Type: {{ $request->type }}</li>
</ul>
```

### After

```blade
<!-- resources/views/emails/vacation-request-submitted.blade.php -->
<p>{{ __('email.vacation.greeting', ['name' => $user->name]) }},</p>

<p>{{ __('email.vacation.submitted') }}</p>

<p><strong>{{ __('email.vacation.details') }}:</strong></p>
<ul>
    <li>{{ __('vacation.fields.start_date') }}: {{ $request->start_date }}</li>
    <li>{{ __('vacation.fields.end_date') }}: {{ $request->end_date }}</li>
    <li>{{ __('vacation.fields.type') }}: {{ __('vacation.types.' . $request->type) }}</li>
</ul>
```

## Example: Form Validation Messages

### Before

```php
class VacationRequestFormRequest extends FormRequest
{
    public function rules()
    {
        return [
            'type' => 'required|string',
            'start_date' => 'required|date|after:today',
            'end_date' => 'required|date|after:start_date',
        ];
    }

    public function messages()
    {
        return [
            'start_date.after' => 'Start date must be in the future',
            'end_date.after' => 'End date must be after start date',
        ];
    }
}
```

### After

```php
class VacationRequestFormRequest extends FormRequest
{
    public function rules()
    {
        return [
            'type' => 'required|string',
            'start_date' => 'required|date|after:today',
            'end_date' => 'required|date|after:start_date',
        ];
    }

    public function messages()
    {
        return [
            'start_date.after' => __('validation.vacation.start_date_future'),
            'end_date.after' => __('validation.vacation.end_date_after_start'),
        ];
    }
}
```

And add to `lang/en/validation.php`:

```php
'vacation' => [
    'start_date_future' => 'Start date must be in the future',
    'end_date_after_start' => 'End date must be after start date',
],
```

And `lang/fr/validation.php`:

```php
'vacation' => [
    'start_date_future' => 'La date de début doit être dans le futur',
    'end_date_after_start' => 'La date de fin doit être après la date de début',
],
```

## Quick Find & Replace Patterns

To help speed up translation implementation, here are some common patterns:

### Vue Templates

1. Simple buttons and labels:
   - Find: `>Save<`
   - Replace: `>{{ $t('common.save') }}<`

2. Placeholders:
   - Find: `placeholder="Email"`
   - Replace: `:placeholder="$t('auth.login.email')"`

3. Titles and headings:
   - Find: `<h1>Dashboard</h1>`
   - Replace: `<h1>{{ $t('dashboard.title') }}</h1>`

### Backend PHP

1. Flash messages:
   - Find: `'success' => 'Saved successfully'`
   - Replace: `'success' => __('app.messages.saved')`

2. Validation messages:
   - Find: `'required' => 'This field is required'`
   - Replace: `'required' => __('validation.required')`

## Testing Your Translations

After updating a page:

1. Visit the page in English - verify text displays correctly
2. Switch to French using the language switcher
3. Verify French translations appear
4. Check that dynamic content (names, dates) still works
5. Test form validation messages in both languages

## Common Gotchas

1. **Don't translate technical terms**: Keep terms like "API", "URL", "Email" unchanged
2. **Watch for string concatenation**: Use placeholders instead
   - Bad: `"Hello " + name`
   - Good: `$t('greeting', { name })`
3. **Pluralization**: Use proper plural forms
   - Good: `$t('items.count', { count }, count)`
4. **HTML in translations**: Keep HTML minimal in translation strings
5. **Date formatting**: Use date-fns with locale support, not just raw dates

## Need Help?

- See `TRANSLATIONS.md` for full documentation
- Check `I18N_IMPLEMENTATION_SUMMARY.md` for overview
- Translation files: `resources/js/locales/` and `lang/`
