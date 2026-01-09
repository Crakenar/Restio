# Technology Stack

**Analysis Date:** 2026-01-09

## Languages

**Primary:**
- PHP 8.3.6 - Backend application code (`composer.json`)
- TypeScript 5.2.2 - Frontend application code (`package.json`, `tsconfig.json`)

**Secondary:**
- Vue 3 SFC templates - Component markup (`resources/js/**/*.vue`)
- SQL - Database migrations (`database/migrations/`)

## Runtime

**Environment:**
- PHP 8.3+ (required via `composer.json`)
- Node.js (inferred from npm tooling)
- No explicit Node version file (.nvmrc/.node-version not present)

**Package Manager:**
- Composer - PHP dependencies (`composer.json`, `composer.lock`)
- npm - JavaScript dependencies (`package.json`, `package-lock.json`)

## Frameworks

**Core:**
- Laravel 12.x - Full-stack web framework (`composer.json`, `config/`)
- Vue 3.5.13 - Frontend UI framework (`package.json`)
- Inertia.js 2.x - SPA adapter bridging Laravel and Vue (`inertiajs/inertia-laravel`, `@inertiajs/vue3`)

**Authentication:**
- Laravel Fortify 1.30 - Headless authentication (`composer.json`, `config/fortify.php`)

**Testing:**
- PHPUnit 11.5.3 - Backend testing (`composer.json`, `phpunit.xml`)

**Build/Dev:**
- Vite 7.0.4 - JavaScript bundler (`package.json`, `vite.config.ts`)
- Laravel Vite Plugin 2.0.0 - Laravel + Vite integration (`package.json`)
- Tailwind CSS 4.1.1 - Utility-first CSS (`package.json`, Tailwind v4 CSS-first config)
- Vue TSC 2.2.4 - Vue TypeScript type checking (`package.json`)

## Key Dependencies

**Critical:**
- Inertia.js Laravel/Vue - Full-stack SPA architecture (`inertiajs/inertia-laravel`, `@inertiajs/vue3`)
- Laravel Wayfinder 0.1.9 - Type-safe routing from PHP to TypeScript (`composer.json`, `vite.config.ts`)
- Reka UI 2.6.1 - Headless UI component library (`package.json`, `resources/js/components/ui/`)
- Laravel Pulse 1.4 - Application monitoring (`composer.json`, `config/pulse.php`)

**Infrastructure:**
- Predis 3.3 - Redis client for caching/queues (`composer.json`)
- Lucide Vue Next 0.468.0 - Icon library (`package.json`)
- Date-fns 4.1.0 - Date manipulation for calendars (`package.json`)
- VueUse 12.8.2 - Vue composition utilities (`package.json`)

**Development:**
- Laravel Pint 1.24 - PHP code formatter (`composer.json`)
- ESLint 9.17.0 - JavaScript linting (`package.json`, `eslint.config.js`)
- Prettier 3.4.2 - Code formatting (`.prettierrc`)
- Mockery 1.6 - PHP mocking library (`composer.json`)
- FakerPHP 1.23 - Test data generation (`composer.json`)

## Configuration

**Environment:**
- `.env` files for configuration (`.env`, `.env.example`)
- Key variables: `APP_KEY`, `DB_CONNECTION`, `DB_DATABASE`, `MAIL_MAILER`
- Database: SQLite (default dev), PostgreSQL (production ready)
- Cache/Queue: Database driver by default

**Build:**
- `vite.config.ts` - Vite bundler with Laravel plugin, Tailwind v4, Vue devtools
- `tsconfig.json` - TypeScript with strict mode, path alias `@/*` → `resources/js/*`
- `phpunit.xml` - PHPUnit with Feature/Unit test suites
- `.prettierrc` - Prettier with Tailwind plugin, organize-imports
- `eslint.config.js` - ESLint for Vue 3 + TypeScript

## Platform Requirements

**Development:**
- Any platform with PHP 8.3+ and Node.js
- Composer and npm required
- Docker optional via Laravel Sail (`composer.json`)

**Production:**
- PHP 8.3+ with standard extensions
- PostgreSQL recommended (SQLite for development)
- Redis optional for caching/queues
- Node.js required for build step only

---

*Stack analysis: 2026-01-09*
*Update after major dependency changes*
