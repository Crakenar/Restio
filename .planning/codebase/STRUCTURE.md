# Codebase Structure

**Analysis Date:** 2026-01-09

## Directory Layout

```
Restio/
├── app/                    # PHP application code
│   ├── Actions/           # Business logic actions
│   ├── Enum/              # PHP backed enums
│   ├── Http/              # HTTP layer (controllers, middleware, requests)
│   ├── Models/            # Eloquent models
│   └── Providers/         # Service providers
├── bootstrap/              # Application bootstrap
├── config/                 # Configuration files
├── database/               # Database migrations, factories, seeders
├── lang/                   # Localization files
├── public/                 # Public assets (entry point)
├── resources/              # Frontend assets
│   ├── css/               # Stylesheets
│   ├── js/                # TypeScript/Vue application
│   └── views/             # Blade templates (SSR)
├── routes/                 # Route definitions
├── storage/                # Application storage
├── tests/                  # Test suite
├── docker/                 # Docker configuration
├── vendor/                 # Composer dependencies
└── node_modules/           # npm dependencies
```

## Directory Purposes

**app/Actions/Fortify/**
- Purpose: Authentication business logic
- Contains: `CreateNewUser.php`, `ResetUserPassword.php`, `PasswordValidationRules.php`
- Key files: User creation and password handling for Fortify

**app/Enum/**
- Purpose: Type-safe constants
- Contains: `UserRole.php`, `VacationRequestStatus.php`, `VacationRequestType.php`
- Key files: ADMIN/MANAGER/EMPLOYEE roles, APPROVED/PENDING/REJECTED statuses

**app/Http/Controllers/Settings/**
- Purpose: Settings page controllers
- Contains: `ProfileController.php`, `PasswordController.php`, `TwoFactorAuthenticationController.php`
- Key files: User profile and security settings handlers

**app/Http/Middleware/**
- Purpose: Request processing middleware
- Contains: `HandleInertiaRequests.php`, `HandleAppearance.php`
- Key files: Inertia shared data, theme handling

**app/Http/Requests/Settings/**
- Purpose: Form validation
- Contains: `ProfileUpdateRequest.php`, `TwoFactorAuthenticationRequest.php`
- Key files: Validation rules for settings forms

**app/Models/**
- Purpose: Database entities
- Contains: `User.php`, `Company.php`, `VacationRequest.php`, `CompanySetting.php`
- Key files: Core domain models with relationships

**database/migrations/**
- Purpose: Database schema definitions
- Contains: 9 migrations for users, companies, vacation_requests, cache, jobs, pulse
- Key files: Multi-tenant schema setup

**database/factories/**
- Purpose: Test data generation
- Contains: `UserFactory.php`, `CompanyFactory.php`, `VacationRequestFactory.php`, `CompanySettingFactory.php`
- Key files: Factory states for approved/pending/rejected requests

**database/seeders/**
- Purpose: Database population
- Contains: `DatabaseSeeder.php` and individual seeders
- Key files: Realistic data seeding with role distribution

**resources/js/pages/**
- Purpose: Inertia page components
- Contains: `Dashboard.vue`, `Requests.vue`, `Team.vue`, `Welcome.vue`
- Subdirectories: `auth/` (login, register), `settings/` (profile, password, appearance)

**resources/js/layouts/**
- Purpose: Layout wrapper components
- Contains: `AppLayout.vue`, `AuthLayout.vue`
- Subdirectories: `app/` (sidebar, header layouts), `settings/` (settings layout)

**resources/js/components/**
- Purpose: Reusable components
- Contains: `AdminDashboard.vue`, `TeamCalendar.vue`, `VacationCalendar.vue`, `RequestsTable.vue`
- Subdirectories: `ui/` (Reka UI component library)

**resources/js/components/ui/**
- Purpose: Headless UI component library
- Contains: `button/`, `card/`, `dialog/`, `table/`, `input/`, etc.
- Pattern: Reka UI based, Tailwind styled

**resources/js/composables/**
- Purpose: Reusable Vue composition functions
- Contains: `useAppearance.ts`, `useInitials.ts`, `useTwoFactorAuth.ts`
- Key files: Theme management, avatar initials, 2FA logic

**resources/js/types/**
- Purpose: TypeScript type definitions
- Contains: `index.d.ts`, domain-specific types
- Key files: `Auth`, `User`, `AppPageProps`, `NavItem` interfaces

**resources/js/actions/**
- Purpose: Wayfinder-generated route helpers
- Contains: Auto-generated TypeScript from PHP controllers
- Subdirectories: Mirrors controller namespace structure

## Key File Locations

**Entry Points:**
- `public/index.php` - PHP application entry
- `resources/js/app.ts` - Vue/Inertia client entry
- `resources/js/ssr.ts` - Server-side rendering entry
- `bootstrap/app.php` - Application bootstrap configuration

**Configuration:**
- `config/app.php` - Application settings
- `config/database.php` - Database connections
- `config/fortify.php` - Authentication features
- `config/inertia.php` - Inertia SSR settings
- `vite.config.ts` - Vite build configuration
- `tsconfig.json` - TypeScript configuration
- `.env` - Environment variables

**Core Logic:**
- `app/Models/` - All Eloquent models
- `app/Http/Controllers/` - HTTP handlers
- `app/Actions/Fortify/` - Auth business logic
- `resources/js/pages/` - All page components

**Testing:**
- `tests/Feature/` - Integration tests
- `tests/Unit/` - Unit tests
- `tests/TestCase.php` - Base test class
- `phpunit.xml` - PHPUnit configuration

**Documentation:**
- `CLAUDE.md` - AI assistant guidelines
- `.junie/guidelines.md` - Additional guidelines

## Naming Conventions

**Files:**
- PHP classes: `PascalCase.php` matching class name
- Vue components: `PascalCase.vue`
- TypeScript utilities: `camelCase.ts`
- Config files: `kebab-case` or `lowercase`

**Directories:**
- PHP namespaces: `PascalCase` (Actions, Http, Models)
- Frontend features: `lowercase` (pages, components, layouts)
- UI components: `lowercase` (button, card, dialog)

**Special Patterns:**
- Test files: `*Test.php` in `tests/Feature/` or `tests/Unit/`
- Factory files: `*Factory.php` in `database/factories/`
- Seeder files: `*Seeder.php` in `database/seeders/`
- Migration files: `YYYY_MM_DD_HHMMSS_action_name.php`

## Where to Add New Code

**New Feature:**
- Model: `app/Models/{FeatureName}.php`
- Controller: `app/Http/Controllers/{FeatureName}Controller.php`
- Form Request: `app/Http/Requests/{FeatureName}Request.php`
- Page: `resources/js/pages/{FeatureName}.vue`
- Tests: `tests/Feature/{FeatureName}Test.php`

**New API Endpoint:**
- Route: Add to `routes/web.php` or create `routes/{feature}.php`
- Controller: `app/Http/Controllers/Api/{Name}Controller.php`
- Request: `app/Http/Requests/{Name}Request.php`

**New Vue Component:**
- App component: `resources/js/components/{ComponentName}.vue`
- UI component: `resources/js/components/ui/{name}/{Name}.vue`
- Page: `resources/js/pages/{PageName}.vue`

**New Composable:**
- Location: `resources/js/composables/use{Name}.ts`
- Pattern: Export function returning reactive state

**New Type:**
- Location: `resources/js/types/index.d.ts` or `resources/js/types/{name}.ts`
- Pattern: TypeScript interfaces/types

## Special Directories

**resources/js/actions/**
- Purpose: Auto-generated Wayfinder route helpers
- Source: Generated by `php artisan wayfinder:generate`
- Committed: Yes (provides type-safe route usage)
- Note: Regenerate after route changes

**resources/js/wayfinder/**
- Purpose: Wayfinder runtime and generated routes
- Source: Auto-generated
- Committed: Yes

**storage/**
- Purpose: Application storage (logs, cache, sessions, uploads)
- Committed: No (gitignored except structure)

**vendor/** and **node_modules/**
- Purpose: Dependencies
- Committed: No (gitignored)

---

*Structure analysis: 2026-01-09*
*Update when directory structure changes*
