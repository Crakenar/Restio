@extends('emails.layout')

@section('content')
    <div class="greeting">Time Off Request Update</div>

    <div class="content">
        <p>Hello {{ $employeeName }},</p>

        <p>
            We wanted to let you know that your time off request has been <strong style="color: #ef4444;">declined</strong>.
        </p>

        <div class="details-box">
            <div class="detail-row">
                <span class="detail-label">Type</span>
                <span class="detail-value">
                    <span class="badge badge-{{ strtolower($requestType) }}">{{ $requestType }}</span>
                </span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Start Date</span>
                <span class="detail-value">{{ $startDate }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">End Date</span>
                <span class="detail-value">{{ $endDate }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Duration</span>
                <span class="detail-value">{{ $days }} {{ $days == 1 ? 'business day' : 'business days' }}</span>
            </div>
            @if($rejectedBy)
            <div class="detail-row">
                <span class="detail-label">Declined By</span>
                <span class="detail-value">{{ $rejectedBy }}</span>
            </div>
            @endif
            @if($rejectionReason)
            <div class="detail-row">
                <span class="detail-label">Reason</span>
                <span class="detail-value" style="color: #ef4444;">{{ $rejectionReason }}</span>
            </div>
            @endif
        </div>

        <div class="button-container">
            <a href="{{ $actionUrl }}" class="button button-error">View Request</a>
        </div>

        <div class="footer-message">
            <p>If you have questions about this decision, please contact your manager to discuss alternative dates or arrangements.</p>
            <p style="margin-top: 12px;">You can submit a new request at any time.</p>
        </div>
    </div>
@endsection
