# Restio Security Documentation

This document outlines the security measures implemented in the Restio application.

## Table of Contents

1. [Security Overview](#security-overview)
2. [Rate Limiting](#rate-limiting)
3. [Audit Logging](#audit-logging)
4. [Security Headers](#security-headers)
5. [Session & Cookie Security](#session--cookie-security)
6. [CSRF Protection](#csrf-protection)
7. [Authentication Security](#authentication-security)
8. [Authorization](#authorization)
9. [Production Configuration](#production-configuration)
10. [Security Best Practices](#security-best-practices)

---

## Security Overview

Restio implements defense-in-depth security with multiple layers:

- **Rate limiting** on all routes with stricter limits on sensitive actions
- **Audit logging** for all critical actions (vacation requests, user changes, settings)
- **Security headers** (CSP, HSTS, X-Frame-Options, etc.)
- **Secure session management** with encrypted sessions and HTTP-only cookies
- **CSRF protection** on all state-changing requests
- **Multi-factor authentication** (2FA) support
- **Role-based access control** (Owner, Admin, Manager, Employee)
- **Multi-tenant data isolation** with company scoping

---

## Rate Limiting

### Rate Limiter Configuration

Configured in `app/Providers/AppServiceProvider.php`:

| Limiter | Limit | Applied To | Purpose |
|---------|-------|------------|---------|
| `global` | 60 requests/minute per IP | All web routes | Prevent general abuse |
| `api` | 60 requests/minute per user/IP | API routes | API protection |
| `sensitive` | 20 requests/minute per user/IP | Vacation approve/reject | Prevent abuse of critical actions |
| `admin` | 30 requests/minute per user/IP | Employee/team/company management | Admin action protection |
| `billing` | 10 requests/minute per user/IP | Subscription changes | Billing operation protection |
| `import` | 5 requests/hour per user/IP | CSV imports | Prevent bulk operation abuse |
| `login` | 5 requests/minute per email+IP | Login attempts | Brute force protection |
| `two-factor` | 5 requests/minute per session | 2FA verification | 2FA brute force protection |
| `registration` | 3 requests/minute per IP | User registration | Registration spam protection |
| `password` | 6 requests/minute per user | Password updates | Password change protection |

### Testing Rate Limiting

Run the rate limiting tests:

```bash
php artisan test --filter=RateLimitingTest
```

### Customizing Rate Limits

To adjust rate limits, edit `app/Providers/AppServiceProvider.php`:

```php
RateLimiter::for('admin', function (Request $request) {
    return Limit::perMinute(30) // Change this number
        ->by($request->user()?->id ?: $request->ip());
});
```

---

## Audit Logging

### Overview

All critical actions are automatically logged to the `audit_logs` table, including:

- Vacation request creation, updates, approvals, rejections, deletions
- User role changes, profile updates, account deletions
- Company settings modifications
- Any model using the `Auditable` trait

### Audit Log Structure

| Field | Description |
|-------|-------------|
| `event` | Action type (created, updated, deleted, approved, rejected) |
| `auditable_type` | Model class name |
| `auditable_id` | Model ID |
| `user_id` | User who performed the action |
| `company_id` | Company context |
| `old_values` | State before change (JSON) |
| `new_values` | State after change (JSON) |
| `metadata` | IP address, user agent, additional context (JSON) |
| `created_at` | When the action occurred |

### Using Audit Logging

#### Enable on a Model

```php
use App\Models\Concerns\Auditable;

class YourModel extends Model
{
    use Auditable;
}
```

#### Viewing Audit Logs

```php
// Get all audit logs for a model
$vacationRequest->auditLogs;

// Get audit logs for a specific user
$user->auditLogs;

// Query audit logs
AuditLog::where('event', 'updated')
    ->where('company_id', $companyId)
    ->with(['user', 'auditable'])
    ->latest()
    ->get();
```

#### Custom Audit Events

```php
// Log a custom event
$model->auditEvent('approved', [
    'approved_by' => $user->id,
    'reason' => 'Met all requirements',
]);
```

### Testing Audit Logging

Run the audit logging tests:

```bash
php artisan test --filter=AuditLoggingTest
```

---

## Security Headers

### Headers Applied

Configured in `app/Http/Middleware/AddSecurityHeaders.php`:

| Header | Value | Purpose |
|--------|-------|---------|
| `Strict-Transport-Security` | `max-age=31536000; includeSubDomains` | Force HTTPS (production only) |
| `X-Frame-Options` | `SAMEORIGIN` | Prevent clickjacking |
| `X-Content-Type-Options` | `nosniff` | Prevent MIME sniffing |
| `X-XSS-Protection` | `1; mode=block` | Enable XSS filtering |
| `Referrer-Policy` | `strict-origin-when-cross-origin` | Control referrer information |
| `Permissions-Policy` | `camera=(), microphone=()...` | Restrict dangerous features |
| `Content-Security-Policy` | See below | Control resource loading |

### Content Security Policy (CSP)

```
default-src 'self'
script-src 'self' 'unsafe-inline' 'unsafe-eval' https://js.stripe.com
style-src 'self' 'unsafe-inline'
img-src 'self' data: https:
font-src 'self' data:
connect-src 'self' https://api.stripe.com
frame-src 'self' https://js.stripe.com https://hooks.stripe.com
object-src 'none'
base-uri 'self'
form-action 'self'
```

**Note:** CSP includes Stripe domains for payment processing. Adjust if using different services.

### Testing Security Headers

Run the security headers tests:

```bash
php artisan test --filter=SecurityHeadersTest
```

---

## Session & Cookie Security

### Production Session Configuration

In `.env.production`:

```env
SESSION_DRIVER=redis              # Use Redis for performance
SESSION_LIFETIME=120              # 2-hour session timeout
SESSION_ENCRYPT=true              # Encrypt session data
SESSION_SECURE_COOKIE=true        # HTTPS-only cookies
SESSION_HTTP_ONLY=true            # Prevent JavaScript access
SESSION_SAME_SITE=lax             # CSRF protection
```

### Cookie Encryption

Cookies are encrypted by default except:
- `appearance` (light/dark mode preference)
- `sidebar_state` (sidebar collapsed state)

See `bootstrap/app.php`:

```php
$middleware->encryptCookies(except: ['appearance', 'sidebar_state']);
```

---

## CSRF Protection

### Overview

All state-changing requests (POST, PUT, PATCH, DELETE) require a valid CSRF token. This is handled automatically by Laravel and Inertia.js.

### How It Works

1. Laravel generates a CSRF token for each session
2. Inertia.js automatically includes the token in requests
3. Laravel validates the token on state-changing requests

### Excluding Routes (Not Recommended)

If you need to exclude routes (e.g., webhooks), edit `bootstrap/app.php`:

```php
$middleware->validateCsrfTokens(except: [
    'stripe/webhook',
]);
```

---

## Authentication Security

### Password Security

- Passwords hashed with bcrypt (12 rounds)
- Password reset tokens expire after 1 hour
- Password updates rate-limited to 6/minute

### Two-Factor Authentication (2FA)

- QR code generation for authenticator apps
- Recovery codes (8 codes, single-use)
- Password confirmation required to enable 2FA
- OTP confirmation required before enabling
- Rate limited to 5 attempts/minute

### Email Verification

- Users must verify email before accessing the application
- Verification links expire after 1 hour

### Login Security

- Rate limited to 5 attempts/minute per email+IP
- Failed attempts tracked
- Lockout after exceeding limit

---

## Authorization

### Role-Based Access Control

Four roles with hierarchical permissions:

| Role | Permissions |
|------|-------------|
| **Owner** | Full access, billing, company settings, user management |
| **Admin** | User management, team management, company settings (no billing) |
| **Manager** | Approve/reject team member requests, view team data |
| **Employee** | Submit vacation requests, view own data |

### Policy-Based Authorization

Policies defined in `app/Policies/`:

- `VacationRequestPolicy` - Vacation request permissions
- `UserPolicy` - User management permissions
- `TeamPolicy` - Team management permissions
- `CompanyPolicy` - Company settings permissions

### Checking Permissions

```php
// In controllers
$this->authorize('approve', $vacationRequest);

// In Blade/Inertia
@can('approve', $vacationRequest)

// In code
if (auth()->user()->can('approve', $vacationRequest)) {
    // ...
}
```

---

## Production Configuration

### Critical Production Settings

**In `.env.production`:**

```env
# CRITICAL
APP_ENV=production
APP_DEBUG=false
SESSION_SECURE_COOKIE=true
SESSION_ENCRYPT=true
STRIPE_FAKE_MODE=false

# RECOMMENDED
SESSION_DRIVER=redis
CACHE_STORE=redis
QUEUE_CONNECTION=redis
LOG_LEVEL=error
```

### Pre-Deployment Security Checklist

- [ ] `APP_DEBUG=false`
- [ ] `SESSION_SECURE_COOKIE=true`
- [ ] `SESSION_ENCRYPT=true`
- [ ] Strong `APP_KEY` generated
- [ ] Strong database password
- [ ] `STRIPE_FAKE_MODE=false`
- [ ] Force HTTPS in web server config
- [ ] SSL certificate installed
- [ ] Redis configured for sessions/cache
- [ ] Error tracking configured (Sentry)
- [ ] Log rotation configured
- [ ] Database backups scheduled
- [ ] Rate limiting tested
- [ ] All tests passing

### Web Server Configuration

**Nginx - Force HTTPS:**

```nginx
server {
    listen 80;
    server_name your-domain.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name your-domain.com;

    ssl_certificate /path/to/cert.pem;
    ssl_certificate_key /path/to/key.pem;

    # SSL configuration
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    ssl_prefer_server_ciphers on;

    # Rest of your configuration...
}
```

---

## Security Best Practices

### For Developers

1. **Never commit secrets** - Use `.env` for all sensitive data
2. **Validate all input** - Use Form Requests for validation
3. **Use parameterized queries** - Eloquent handles this automatically
4. **Escape output** - Vue/Blade templates handle this automatically
5. **Keep dependencies updated** - Run `composer update` and `npm update` regularly
6. **Review logs regularly** - Check for suspicious activity
7. **Use policies for authorization** - Don't check permissions manually
8. **Enable audit logging on sensitive models** - Add the `Auditable` trait

### For Administrators

1. **Enable 2FA for all admin users**
2. **Use strong, unique passwords**
3. **Review audit logs monthly** for suspicious activity
4. **Keep annual vacation days reasonable** to prevent balance manipulation
5. **Monitor rate limit violations** - Investigate repeated 429 errors
6. **Review user roles quarterly** - Ensure least privilege
7. **Set up error monitoring** - Use Sentry or similar
8. **Configure database backups** - Daily at minimum
9. **Test backup restoration** - Quarterly at minimum
10. **Keep Laravel and dependencies updated** - Monthly security patches

---

## Reporting Security Issues

If you discover a security vulnerability, please email security@restio.com (replace with actual email). Do not open a public issue.

We take security seriously and will respond within 24 hours.

---

## Security Roadmap

### Planned Enhancements

- [ ] API authentication with Laravel Sanctum
- [ ] Audit log viewer in admin panel
- [ ] Automated security scanning (CI/CD)
- [ ] Rate limit notifications for admins
- [ ] Session management (view/revoke active sessions)
- [ ] IP whitelist/blacklist for admin access
- [ ] Webhook signature verification for Stripe
- [ ] Advanced anomaly detection

---

## Additional Resources

- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [Laravel Security Best Practices](https://laravel.com/docs/12.x/security)
- [Stripe Security Best Practices](https://stripe.com/docs/security/guide)

---

**Last Updated:** 2026-01-20
**Version:** 1.0.0
