@extends('emails.layout')

@section('content')
    <div class="greeting">Welcome to Restio! 👋</div>

    <div class="content">
        <p>Hello {{ $userName }},</p>

        <p>
            Welcome to <strong>Restio</strong>! Your account has been created and you're all set to start managing your time off requests.
        </p>

        <div class="details-box">
            <div class="detail-row">
                <span class="detail-label">Your Email</span>
                <span class="detail-value">{{ $userEmail }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Company</span>
                <span class="detail-value">{{ $companyName }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Role</span>
                <span class="detail-value">
                    <span class="badge badge-vacation">{{ ucfirst($userRole) }}</span>
                </span>
            </div>
            @if($annualDays)
            <div class="detail-row">
                <span class="detail-label">Annual Vacation Days</span>
                <span class="detail-value">{{ $annualDays }} days</span>
            </div>
            @endif
        </div>

        <div style="background-color: #eff6ff; border-left: 4px solid #3b82f6; padding: 16px; margin: 24px 0; border-radius: 8px;">
            <p style="margin: 0; color: #1e40af; font-weight: 600;">🔐 First Time Login</p>
            <p style="margin: 8px 0 0 0; color: #3730a3;">
                @if($temporaryPassword)
                    Your temporary password is: <strong>{{ $temporaryPassword }}</strong><br>
                    Please change your password after your first login.
                @else
                    Please check your email for password reset instructions to set up your account.
                @endif
            </p>
        </div>

        <div class="button-container">
            <a href="{{ $loginUrl }}" class="button">Log In to Restio</a>
        </div>

        <div style="margin-top: 32px; padding-top: 24px; border-top: 1px solid #e5e7eb;">
            <p style="font-weight: 600; color: #1f2937; margin-bottom: 12px;">Getting Started:</p>
            <ul style="color: #6b7280; margin-left: 20px;">
                <li style="margin-bottom: 8px;">Submit your first time off request from the dashboard</li>
                <li style="margin-bottom: 8px;">View your vacation balance and track used days</li>
                <li style="margin-bottom: 8px;">Check the calendar to see team availability</li>
                <li style="margin-bottom: 8px;">Update your profile and preferences in settings</li>
            </ul>
        </div>

        <div class="footer-message">
            <p>If you have any questions or need assistance, please don't hesitate to reach out to your manager or HR team.</p>
            <p style="margin-top: 12px; font-weight: 600; color: #6366f1;">Welcome aboard! 🚀</p>
        </div>
    </div>
@endsection
