<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject ?? 'Restio' }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            line-height: 1.6;
            color: #333333;
            background-color: #f5f5f7;
            padding: 20px;
        }

        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .email-header {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            padding: 40px 30px;
            text-align: center;
        }

        .email-logo {
            font-size: 32px;
            font-weight: 700;
            color: #ffffff;
            letter-spacing: -0.5px;
        }

        .email-body {
            padding: 40px 30px;
        }

        .greeting {
            font-size: 24px;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 20px;
        }

        .content p {
            margin-bottom: 16px;
            color: #4b5563;
            font-size: 16px;
        }

        .content strong {
            color: #1f2937;
            font-weight: 600;
        }

        .details-box {
            background-color: #f9fafb;
            border-left: 4px solid #6366f1;
            padding: 20px;
            margin: 24px 0;
            border-radius: 8px;
        }

        .details-box .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #e5e7eb;
        }

        .details-box .detail-row:last-child {
            border-bottom: none;
        }

        .details-box .detail-label {
            font-weight: 600;
            color: #6b7280;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .details-box .detail-value {
            color: #1f2937;
            font-weight: 500;
        }

        .button-container {
            text-align: center;
            margin: 32px 0;
        }

        .button {
            display: inline-block;
            padding: 14px 32px;
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            transition: transform 0.2s;
        }

        .button:hover {
            transform: translateY(-2px);
        }

        .button-success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }

        .button-error {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        }

        .footer-message {
            margin-top: 24px;
            padding-top: 24px;
            border-top: 1px solid #e5e7eb;
            color: #6b7280;
            font-size: 14px;
        }

        .email-footer {
            background-color: #f9fafb;
            padding: 30px;
            text-align: center;
            color: #6b7280;
            font-size: 14px;
        }

        .email-footer p {
            margin: 8px 0;
        }

        .email-footer a {
            color: #6366f1;
            text-decoration: none;
        }

        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .badge-vacation {
            background-color: #dbeafe;
            color: #1e40af;
        }

        .badge-sick {
            background-color: #fef3c7;
            color: #92400e;
        }

        .badge-personal {
            background-color: #e0e7ff;
            color: #3730a3;
        }

        .badge-unpaid {
            background-color: #fee2e2;
            color: #991b1b;
        }

        .badge-wfh {
            background-color: #d1fae5;
            color: #065f46;
        }

        @media only screen and (max-width: 600px) {
            .email-container {
                border-radius: 0;
            }

            .email-header,
            .email-body,
            .email-footer {
                padding: 24px 20px;
            }

            .greeting {
                font-size: 20px;
            }

            .content p {
                font-size: 15px;
            }

            .button {
                width: 100%;
                padding: 16px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="email-header">
            <div class="email-logo">Restio</div>
        </div>

        <!-- Body -->
        <div class="email-body">
            @yield('content')
        </div>

        <!-- Footer -->
        <div class="email-footer">
            <p><strong>Restio</strong> - Time Off Management Made Simple</p>
            <p>You're receiving this email because you have an account with Restio.</p>
            <p>
                <a href="{{ config('app.url') }}">Visit Dashboard</a> |
                <a href="{{ config('app.url') }}/settings">Settings</a>
            </p>
            <p style="margin-top: 16px; color: #9ca3af; font-size: 12px;">
                &copy; {{ date('Y') }} Restio. All rights reserved.
            </p>
        </div>
    </div>
</body>
</html>
