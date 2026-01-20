# Restio Email Templates Documentation

This document explains how to use and customize the branded email templates in Restio.

## Overview

Restio uses beautiful, branded HTML email templates for all notifications. All templates are fully responsive and tested with Mailtrap.

**Template Location:** `resources/views/emails/`

**Features:**
- ✅ Responsive design (mobile-friendly)
- ✅ Branded with Restio colors and logo
- ✅ Professional gradient backgrounds
- ✅ Color-coded request type badges
- ✅ Clear call-to-action buttons
- ✅ Accessible and easy to read

---

## Available Templates

### 1. **Request Submitted** (`vacation-request-submitted.blade.php`)

**Sent to:** Managers when an employee submits a time off request

**Variables:**
- `$managerName` - Manager's name
- `$employeeName` - Employee who submitted the request
- `$requestType` - Type of request (Vacation, Sick, Personal, etc.)
- `$startDate` - Start date formatted as "Jan 15, 2026"
- `$endDate` - End date formatted as "Jan 20, 2026"
- `$days` - Number of business days
- `$reason` - Optional reason for the request
- `$actionUrl` - URL to review the request

**Preview:**
```
Subject: New Time Off Request from John Doe

Hello Jane Manager,

John Doe has submitted a new time off request that requires your approval.

[Details Box with request information]

[Review Request Button]

Please review this request and approve or decline it as soon as possible.
```

### 2. **Request Approved** (`vacation-request-approved.blade.php`)

**Sent to:** Employee when their request is approved

**Variables:**
- `$employeeName` - Employee's name
- `$requestType` - Type of request
- `$startDate` - Start date
- `$endDate` - End date
- `$days` - Number of business days
- `$approvedBy` - Name of the approver (optional)
- `$approvedDate` - Date approved (optional)
- `$actionUrl` - URL to view the request

**Preview:**
```
Subject: Your Time Off Request Has Been Approved!

🎉 Your Time Off Request is Approved!

Great news, John Doe!

Your time off request has been approved. You're all set for your upcoming time away!

[Details Box with request information]

[View Request Button]

Enjoy your time off! Remember to set up an out-of-office message if needed.
Have a great break! 🌴
```

### 3. **Request Rejected** (`vacation-request-rejected.blade.php`)

**Sent to:** Employee when their request is declined

**Variables:**
- `$employeeName` - Employee's name
- `$requestType` - Type of request
- `$startDate` - Start date
- `$endDate` - End date
- `$days` - Number of business days
- `$rejectedBy` - Name of who declined it (optional)
- `$rejectionReason` - Reason for rejection (optional)
- `$actionUrl` - URL to view the request

**Preview:**
```
Subject: Time Off Request Not Approved

Time Off Request Update

Hello John Doe,

We wanted to let you know that your time off request has been declined.

[Details Box with request information and reason]

[View Request Button]

If you have questions about this decision, please contact your manager
to discuss alternative dates or arrangements.
```

### 4. **Welcome Email** (`welcome.blade.php`)

**Sent to:** New employees when their account is created

**Variables:**
- `$userName` - Employee's name
- `$userEmail` - Employee's email
- `$companyName` - Company name
- `$userRole` - User's role (Employee, Manager, Admin, Owner)
- `$annualDays` - Annual vacation days available
- `$temporaryPassword` - Temporary password (optional)
- `$loginUrl` - URL to login

**Preview:**
```
Subject: Welcome to Restio!

Welcome to Restio! 👋

Hello John Doe,

Welcome to Restio! Your account has been created and you're all set to
start managing your time off requests.

[Account Details]

🔐 First Time Login
Your temporary password is: RestioDemo2024!
Please change your password after your first login.

[Log In to Restio Button]

Getting Started:
• Submit your first time off request from the dashboard
• View your vacation balance and track used days
• Check the calendar to see team availability
• Update your profile and preferences in settings
```

---

## Base Layout (`layout.blade.php`)

All email templates extend a base layout that provides:

### Brand Colors
- **Primary Gradient:** `#6366f1` to `#8b5cf6` (Purple/Indigo)
- **Success:** `#10b981` (Green)
- **Error:** `#ef4444` (Red)

### Request Type Badges
Each request type has a color-coded badge:
- **Vacation:** Blue (`#dbeafe` / `#1e40af`)
- **Sick:** Yellow (`#fef3c7` / `#92400e`)
- **Personal:** Indigo (`#e0e7ff` / `#3730a3`)
- **Unpaid:** Red (`#fee2e2` / `#991b1b`)
- **WFH:** Green (`#d1fae5` / `#065f46`)

### Responsive Design
- Desktop: Full 600px width
- Mobile: Full width with adjusted padding
- Readable fonts and clear hierarchy

---

## Testing Emails with Mailtrap

### 1. Configure Mailtrap

Update your `.env` file:
```env
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_mailtrap_username
MAIL_PASSWORD=your_mailtrap_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@restio.com
MAIL_FROM_NAME="${APP_NAME}"
```

### 2. Send Test Emails

Use the built-in test command:

```bash
# Test all notification types
php artisan email:test your@email.com

# Test specific notification
php artisan email:test your@email.com --type=submitted
php artisan email:test your@email.com --type=approved
php artisan email:test your@email.com --type=rejected
php artisan email:test your@email.com --type=welcome
```

### 3. Check Mailtrap Inbox

1. Go to [https://mailtrap.io](https://mailtrap.io)
2. Open your inbox
3. View the test emails
4. Check HTML rendering, mobile view, and spam score

---

## Customizing Email Templates

### Changing Brand Colors

Edit `resources/views/emails/layout.blade.php`:

```css
.email-header {
    background: linear-gradient(135deg, #YOUR_COLOR_1 0%, #YOUR_COLOR_2 100%);
}

.button {
    background: linear-gradient(135deg, #YOUR_COLOR_1 0%, #YOUR_COLOR_2 100%);
}
```

### Adding Your Logo

Replace the text logo in `layout.blade.php`:

```blade
<div class="email-header">
    <img src="{{ asset('images/logo-white.png') }}" alt="Restio" style="height: 40px;">
</div>
```

### Customizing Footer

Edit the footer section in `layout.blade.php`:

```blade
<div class="email-footer">
    <p><strong>Your Company</strong> - Your Tagline</p>
    <p>123 Your Street, City, State 12345</p>
    <p>
        <a href="{{ config('app.url') }}">Visit Website</a> |
        <a href="{{ config('app.url') }}/support">Support</a>
    </p>
</div>
```

### Adding Custom Templates

1. Create new template in `resources/views/emails/your-template.blade.php`:

```blade
@extends('emails.layout')

@section('content')
    <div class="greeting">Your Greeting</div>
    <div class="content">
        <p>Your content here</p>
    </div>
@endsection
```

2. Use in notification:

```php
return (new MailMessage)
    ->subject('Your Subject')
    ->view('emails.your-template', [
        'variable1' => $value1,
        'variable2' => $value2,
    ]);
```

---

## Production Setup

### 1. Switch to Production Mail Service

Update `.env.production`:

```env
# SendGrid
MAIL_MAILER=smtp
MAIL_HOST=smtp.sendgrid.net
MAIL_PORT=587
MAIL_USERNAME=apikey
MAIL_PASSWORD=your_sendgrid_api_key
MAIL_ENCRYPTION=tls

# OR Mailgun
MAIL_MAILER=mailgun
MAILGUN_DOMAIN=your-domain.com
MAILGUN_SECRET=your_mailgun_secret

# OR Amazon SES
MAIL_MAILER=ses
AWS_ACCESS_KEY_ID=your_access_key
AWS_SECRET_ACCESS_KEY=your_secret_key
AWS_DEFAULT_REGION=us-east-1
```

### 2. Configure From Address

```env
MAIL_FROM_ADDRESS=noreply@your-domain.com
MAIL_FROM_NAME="Restio"
```

### 3. Set Up SPF and DKIM

Add these DNS records for better deliverability:

**SPF Record:**
```
v=spf1 include:_spf.google.com include:sendgrid.net ~all
```

**DKIM Record:**
Follow your email provider's instructions to set up DKIM.

### 4. Queue Emails

For better performance, queue all emails:

```php
// Already implemented in notifications
class VacationRequestSubmitted extends Notification implements ShouldQueue
{
    use Queueable;
}
```

Set up queue worker:
```bash
php artisan queue:work --queue=default
```

---

## Email Best Practices

### Subject Lines
- ✅ Keep under 50 characters
- ✅ Be specific and actionable
- ✅ Use emojis sparingly in casual apps
- ❌ Don't use all caps or excessive punctuation

### Content
- ✅ Get to the point quickly
- ✅ Use clear hierarchy (greeting → body → CTA → footer)
- ✅ Include one clear call-to-action
- ✅ Mobile-friendly design
- ❌ Don't overload with information

### Deliverability
- ✅ Verify SPF and DKIM records
- ✅ Use a custom domain (not gmail.com)
- ✅ Include unsubscribe link (for marketing emails)
- ✅ Monitor bounce and spam rates
- ❌ Don't send too frequently

### Testing Checklist
- [ ] Test in Gmail, Outlook, Apple Mail
- [ ] Check mobile rendering
- [ ] Verify all links work
- [ ] Check spam score (<5 in Mailtrap)
- [ ] Proofread all text
- [ ] Test with real data

---

## Troubleshooting

### Emails Not Sending

1. **Check mail configuration:**
   ```bash
   php artisan tinker
   Mail::raw('Test', function($msg) {
       $msg->to('test@example.com')->subject('Test');
   });
   ```

2. **Check queue is running:**
   ```bash
   php artisan queue:work
   ```

3. **Check logs:**
   ```bash
   tail -f storage/logs/laravel.log
   ```

### Styling Issues

1. **Inline all CSS** - Email clients don't support `<style>` tags well
2. **Use tables for layout** - CSS Grid/Flexbox not supported in email
3. **Test in multiple clients** - Use Litmus or Email on Acid

### Images Not Loading

1. Use absolute URLs: `{{ asset('images/logo.png') }}`
2. Host images on CDN
3. Use inline base64 for small images

---

## Additional Resources

- [Laravel Mail Documentation](https://laravel.com/docs/12.x/mail)
- [Mailtrap](https://mailtrap.io)
- [Email Design Best Practices](https://www.campaignmonitor.com/dev-resources/)
- [Can I Email?](https://www.caniemail.com/) - CSS support in email clients

---

**Last Updated:** 2026-01-20
**Version:** 1.0.0
