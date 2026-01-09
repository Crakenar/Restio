# Coding Conventions

**Analysis Date:** 2026-01-09

## Naming Patterns

**Files:**
- PHP: PascalCase matching class name (`ProfileController.php`, `VacationRequest.php`)
- Vue: PascalCase (`Dashboard.vue`, `TeamCalendar.vue`, `AppLayout.vue`)
- TypeScript utilities: camelCase (`useAppearance.ts`, `utils.ts`)
- Config: lowercase/kebab-case (`vite.config.ts`, `phpunit.xml`)

**Functions:**
- PHP: camelCase for methods (`vacation_requests()`, `company()`)
- TypeScript: camelCase (`handleCreateRequest`, `isDateSelected`)
- Vue event handlers: `handle{Action}` pattern (`handleApprove`, `handleCreateRequest`)

**Variables:**
- PHP: snake_case for properties (`email_verified_at`, `approved_by`)
- TypeScript: camelCase (`currentMonth`, `selectedDates`, `isDialogOpen`)
- Constants: SCREAMING_SNAKE_CASE (`TYPE_LABELS`, `TYPE_COLORS`)

**Types:**
- PHP Enums: PascalCase name, SCREAMING_SNAKE_CASE cases (`UserRole::ADMIN`)
- TypeScript interfaces: PascalCase (`BreadcrumbItem`, `VacationRequest`, `Auth`)
- TypeScript types: PascalCase (`AppPageProps<T>`)

## Code Style

**Formatting:**
- PHP: Laravel Pint (`vendor/bin/pint`)
- TypeScript/Vue: Prettier (`.prettierrc`)
  - Semicolons: required (`semi: true`)
  - Quotes: single (`singleQuote: true`)
  - Print width: 80 characters
  - Tab width: 4 spaces
  - Plugins: organize-imports, tailwindcss

**Linting:**
- PHP: Laravel Pint for formatting
- TypeScript: ESLint 9 (`eslint.config.js`)
  - Vue 3 + TypeScript rules
  - Prettier compatibility
- Run: `npm run lint`, `npm run format`

## Import Organization

**Order (TypeScript):**
1. External packages (`vue`, `@inertiajs/vue3`, `date-fns`)
2. Internal paths (`@/components`, `@/composables`, `@/lib`)
3. Relative imports (`./`, `../`)
4. Type imports (`import type { ... }`)

**Grouping:**
- Automatic via prettier-plugin-organize-imports
- Alphabetical within groups

**Path Aliases:**
- `@/*` maps to `resources/js/*` (configured in `tsconfig.json`, `vite.config.ts`)

## Error Handling

**Patterns (PHP):**
- Form Request validation with `rules()` method
- Array-style rules: `['required', 'string', 'max:255']`
- Custom error messages in `messages()` method

**Patterns (TypeScript):**
- Try/catch in composables
- Error messages pushed to reactive arrays
- Generic error handling (could be improved)

**Example from `useTwoFactorAuth.ts`:**
```typescript
try {
    await router.post(enable.url(), {}, { ... });
} catch (err) {
    errors.value.push('Failed to enable two-factor authentication');
}
```

## Logging

**Framework:**
- PHP: Laravel `Log` facade
- TypeScript: `console.log` (should be removed for production)

**Patterns:**
- Log at service boundaries
- Structured logging with context where needed

## Comments

**When to Comment:**
- Explain "why" for non-obvious logic
- Document complex algorithms or business rules
- Avoid obvious comments

**PHPDoc:**
- Used for complex types: `@var list<string>`, `@return array<string, string>`
- Factory types: `/** @use HasFactory<\Database\Factories\UserFactory> */`
- Not required for simple, typed methods

**Vue/TypeScript:**
- Minimal inline comments
- Type definitions serve as documentation
- Example: `// FAKE DATA - Replace with real API calls later`

## Function Design

**Size:**
- Keep functions focused and single-purpose
- Extract helpers for complex logic
- Large Vue components (300+ lines) could be split

**Parameters:**
- PHP: Use typed parameters and return types
- TypeScript: Use interfaces for complex props
- Destructure objects in Vue `defineProps<Props>()`

**Return Values:**
- PHP: Explicit return type declarations
- TypeScript: Inferred or explicit typing

## Module Design

**Exports (TypeScript):**
- Named exports preferred
- Default exports for Vue components
- Re-export from index files where appropriate

**Vue Components:**
- `<script setup lang="ts">` syntax
- Props via `defineProps<Props>()`
- Emits via `defineEmits<{ eventName: [args] }>()`
- Single root element required

**Example Vue Component Structure:**
```vue
<script setup lang="ts">
interface Props {
    title: string;
    description?: string;
}

defineProps<Props>();
</script>

<template>
    <div class="...">
        {{ title }}
    </div>
</template>
```

## PHP Specific

**Constructors:**
- Use PHP 8 constructor property promotion
- Example: `public function __construct(public GitHub $github) { }`

**Type Declarations:**
- Explicit return types on all methods
- Parameter type hints required
- Example: `public function edit(Request $request): Response`

**Eloquent Models:**
- Casts in `casts()` method (Laravel 11+ style)
- Relationships with return type hints
- `$fillable` and `$hidden` as typed arrays

**Example Model Pattern:**
```php
protected $fillable = [
    'name', 'email', 'password', 'company_id', 'role',
];

protected function casts(): array
{
    return [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'role' => UserRole::class,
    ];
}

public function company(): BelongsTo
{
    return $this->belongsTo(Company::class);
}
```

## Tailwind CSS

**Class Organization:**
- Use Tailwind utility classes directly
- Group by concern (layout, spacing, colors)
- Dark mode via `dark:` prefix

**Patterns:**
- Gap utilities for spacing in flex/grid (no margins)
- Responsive prefixes when needed (`md:`, `lg:`)
- Component extraction for repeated patterns

---

*Convention analysis: 2026-01-09*
*Update when patterns change*
