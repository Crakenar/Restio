# External Integrations

**Analysis Date:** 2026-01-09

## APIs & External Services

**Payment Processing:**
- Not detected - No Stripe, Paddle, or payment integrations present

**Email/SMS:**
- Mail driver configured but using `log` by default (`config/mail.php`)
- Supported drivers configured: SMTP, Mailgun, SES, Postmark, Resend
- No active external email service integrated yet

**External APIs:**
- Not detected - No third-party API integrations

## Data Storage

**Databases:**
- SQLite - Default development database (`config/database.php`, `.env.example`)
- PostgreSQL - Production-ready configuration (`config/database.php`)
  - Connection: `DATABASE_URL` or individual `DB_*` env vars
  - Client: Laravel Eloquent ORM
  - Migrations: `database/migrations/` (9 migrations)

**File Storage:**
- Local filesystem - Default storage (`config/filesystems.php`)
- AWS S3 - Configured but not actively used
  - Credentials: `AWS_ACCESS_KEY_ID`, `AWS_SECRET_ACCESS_KEY`, `AWS_BUCKET`

**Caching:**
- Database cache - Default store (`config/cache.php`)
- Redis available - Predis client installed (`predis/predis`)
  - Connection: `REDIS_URL` or individual `REDIS_*` env vars

## Authentication & Identity

**Auth Provider:**
- Laravel Fortify - Headless authentication (`config/fortify.php`, `app/Providers/FortifyServiceProvider.php`)
  - Implementation: Session-based with `web` guard
  - Token storage: Laravel sessions (database driver)
  - Session management: Standard Laravel sessions

**Features Enabled:**
- Registration (`Features::registration()`)
- Password Reset (`Features::resetPasswords()`)
- Email Verification (`Features::emailVerification()`)
- Two-Factor Authentication (`Features::twoFactorAuthentication()`)
  - Requires password confirmation and OTP confirmation

**OAuth Integrations:**
- Not detected - No social login providers configured

## Monitoring & Observability

**Error Tracking:**
- Not detected - No Sentry, Rollbar, or external error tracking

**Analytics:**
- Not detected - No Google Analytics, Mixpanel, or analytics services

**Application Monitoring:**
- Laravel Pulse 1.4 - Built-in monitoring (`config/pulse.php`)
  - Storage: Database tables (`database/migrations/2026_01_09_163723_create_pulse_tables.php`)
  - Metrics: Request tracking, slow queries, exceptions

**Logs:**
- Laravel logging - Stack driver with daily rotation (`config/logging.php`)
- Vercel/platform logs when deployed (stdout/stderr)
- Laravel Pail available for log streaming (`composer.json`)

## CI/CD & Deployment

**Hosting:**
- Not detected - No deployment configuration found
- Platform agnostic - Can deploy to Vercel, Forge, Vapor, etc.

**CI Pipeline:**
- Not detected - No GitHub Actions, CircleCI, or similar configs
- PHPUnit available for local/CI testing

## Environment Configuration

**Development:**
- Required env vars: `APP_KEY`, `DB_CONNECTION`, `DB_DATABASE`
- Secrets location: `.env` file (gitignored)
- No mock services - Using real services in test mode

**Testing:**
- Database: In-memory SQLite (`:memory:`)
- Mail: Array driver (no emails sent)
- Cache/Session/Queue: Array drivers
- Pulse/Telescope/Nightwatch: Disabled

**Production:**
- Secrets management: Platform environment variables
- `APP_DEBUG=false` required
- `APP_KEY` must be generated and set

## Webhooks & Callbacks

**Incoming:**
- Not detected - No webhook endpoints defined

**Outgoing:**
- Not detected - No outgoing webhook integrations

## Multi-Tenancy

**Company-based Multi-Tenancy:**
- Implemented via Eloquent relationships (`app/Models/User.php`, `app/Models/Company.php`)
- User `belongsTo` Company
- Schema includes `company_id` foreign key on users and vacation_requests tables
- Company settings table for per-tenant configuration

**Data Isolation:**
- Not yet implemented - No query scopes for automatic company filtering
- Manual company_id checks needed in controllers

---

*Integration audit: 2026-01-09*
*Update when adding/removing external services*
