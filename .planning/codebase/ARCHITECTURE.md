# Architecture

**Analysis Date:** 2026-01-09

## Pattern Overview

**Overall:** Laravel SPA with Inertia.js (Modern Full-Stack Monolith)

**Key Characteristics:**
- Server-Side Rendering (SSR) enabled via Inertia.js
- Single Page Application feel with full Laravel backend
- Component-based UI with Vue 3 Composition API
- Multi-tenant architecture (company-based)
- Type-safe frontend-backend integration via Wayfinder

## Layers

**Frontend Layer (`resources/js/`):**
- Purpose: User interface and client-side interactions
- Contains: Vue components, pages, composables, types
- Depends on: Inertia.js for data, Wayfinder for type-safe routes
- Used by: Browser clients

**Middleware Layer (`app/Http/Middleware/`):**
- Purpose: Request/response processing pipeline
- Contains: `HandleInertiaRequests.php` (shared props), `HandleAppearance.php` (theme)
- Depends on: Laravel core, authentication
- Used by: All HTTP requests

**Routing Layer (`routes/`):**
- Purpose: URL to controller mapping
- Contains: `web.php` (main routes), `settings.php` (settings routes)
- Depends on: Middleware, controllers
- Used by: Incoming HTTP requests

**Controller Layer (`app/Http/Controllers/`):**
- Purpose: Handle HTTP requests, coordinate responses
- Contains: Settings controllers (Profile, Password, TwoFactor)
- Depends on: Form Requests, Models, Inertia
- Used by: Routes

**Request Validation Layer (`app/Http/Requests/`):**
- Purpose: Validate and authorize incoming data
- Contains: `ProfileUpdateRequest.php`, `TwoFactorAuthenticationRequest.php`
- Depends on: Laravel validation
- Used by: Controllers

**Action Layer (`app/Actions/`):**
- Purpose: Business logic encapsulation
- Contains: Fortify actions (CreateNewUser, ResetUserPassword)
- Depends on: Models, validation rules
- Used by: Fortify authentication flows

**Model Layer (`app/Models/`):**
- Purpose: Data access and business entities
- Contains: `User.php`, `Company.php`, `VacationRequest.php`, `CompanySetting.php`
- Depends on: Eloquent ORM, database
- Used by: Controllers, Actions, Factories

**Enum Layer (`app/Enum/`):**
- Purpose: Type-safe constants and status values
- Contains: `UserRole.php`, `VacationRequestStatus.php`, `VacationRequestType.php`
- Depends on: PHP 8.1+ backed enums
- Used by: Models, validation, business logic

## Data Flow

**HTTP Request Lifecycle:**

1. Request enters via `public/index.php`
2. Middleware chain executes (auth, appearance, Inertia)
3. Router matches URL to controller action
4. Form Request validates input (if present)
5. Controller calls services/models
6. Eloquent models interact with database
7. Inertia renders Vue page with props
8. SSR renders initial HTML (optional)
9. Client hydrates Vue application

**Inertia Page Load:**

1. Browser navigates to URL
2. Laravel route matched to controller
3. Controller returns `Inertia::render('PageName', $props)`
4. Inertia middleware adds shared data (auth, app name, quote)
5. Vue page component receives props
6. Page renders with reactive data

**State Management:**
- Server-side: Database via Eloquent models
- Client-side: Vue reactive refs and composables
- Session: Database-stored Laravel sessions
- Cache: Database-stored (Redis available)

## Key Abstractions

**Inertia Page:**
- Purpose: Full-page Vue components receiving backend data
- Examples: `resources/js/pages/Dashboard.vue`, `resources/js/pages/Team.vue`, `resources/js/pages/Requests.vue`
- Pattern: SFC with `<script setup>` receiving typed props from Laravel

**Layout:**
- Purpose: Shared page structure and navigation
- Examples: `resources/js/layouts/AppLayout.vue`, `resources/js/layouts/AuthLayout.vue`
- Pattern: Wrapper components with slots

**UI Component:**
- Purpose: Reusable UI elements (Reka UI based)
- Examples: `resources/js/components/ui/button/`, `resources/js/components/ui/card/`
- Pattern: Headless components with Tailwind styling

**Composable:**
- Purpose: Reusable reactive logic
- Examples: `resources/js/composables/useAppearance.ts`, `resources/js/composables/useTwoFactorAuth.ts`
- Pattern: Vue Composition API functions returning reactive state

**Wayfinder Action:**
- Purpose: Type-safe route helpers generated from Laravel routes
- Examples: `resources/js/actions/App/Http/Controllers/Settings/ProfileController.ts`
- Pattern: Auto-generated TypeScript functions matching PHP controller methods

**Eloquent Model:**
- Purpose: Database entity with relationships and casts
- Examples: `app/Models/User.php`, `app/Models/VacationRequest.php`
- Pattern: Laravel Eloquent with typed relationships and enum casts

**Form Request:**
- Purpose: Validation and authorization encapsulation
- Examples: `app/Http/Requests/Settings/ProfileUpdateRequest.php`
- Pattern: Laravel Form Request with `rules()` and `authorize()`

**Fortify Action:**
- Purpose: Authentication business logic
- Examples: `app/Actions/Fortify/CreateNewUser.php`, `app/Actions/Fortify/ResetUserPassword.php`
- Pattern: Invokable classes implementing Fortify contracts

## Entry Points

**Web Entry:**
- Location: `public/index.php`
- Triggers: All HTTP requests
- Responsibilities: Bootstrap Laravel, handle request

**JavaScript Entry:**
- Location: `resources/js/app.ts`
- Triggers: Client-side page load
- Responsibilities: Create Inertia app, mount Vue, initialize theme

**SSR Entry:**
- Location: `resources/js/ssr.ts`
- Triggers: Server-side rendering requests
- Responsibilities: Render Vue to HTML on server

**Console Entry:**
- Location: `routes/console.php`, `artisan`
- Triggers: CLI commands
- Responsibilities: Scheduled tasks, maintenance commands

## Error Handling

**Strategy:** Laravel exception handling with Inertia error pages

**Patterns:**
- Form validation errors returned via Inertia session flash
- Exceptions caught by Laravel exception handler
- 404/500 pages rendered via Inertia error handling
- Vue error boundaries for component errors

## Cross-Cutting Concerns

**Logging:**
- Laravel logging with daily rotation
- Structured logging via `Log` facade
- Levels: debug, info, warning, error, critical

**Validation:**
- Form Request classes for HTTP validation
- Array-based validation rules
- Custom error messages supported

**Authentication:**
- Laravel Fortify for auth flows
- Session-based authentication
- Two-factor authentication support
- Email verification

**Authorization:**
- Not yet implemented (policies/gates needed)
- Route-level auth middleware present

**Theming:**
- `HandleAppearance.php` middleware
- `useAppearance.ts` composable
- Light/dark mode via CSS classes

---

*Architecture analysis: 2026-01-09*
*Update when major patterns change*
