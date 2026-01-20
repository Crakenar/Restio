@extends('emails.layout')

@section('content')
    <div class="greeting">🎉 Your Time Off Request is Approved!</div>

    <div class="content">
        <p>Great news, {{ $employeeName }}!</p>

        <p>
            Your time off request has been <strong style="color: #10b981;">approved</strong>.
            You're all set for your upcoming time away!
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
            @if($approvedBy)
            <div class="detail-row">
                <span class="detail-label">Approved By</span>
                <span class="detail-value">{{ $approvedBy }}</span>
            </div>
            @endif
            @if($approvedDate)
            <div class="detail-row">
                <span class="detail-label">Approved On</span>
                <span class="detail-value">{{ $approvedDate }}</span>
            </div>
            @endif
        </div>

        <div class="button-container">
            <a href="{{ $actionUrl }}" class="button button-success">View Request</a>
        </div>

        <div class="footer-message">
            <p>Enjoy your time off! Remember to set up an out-of-office message if needed.</p>
            <p style="margin-top: 12px; color: #10b981; font-weight: 600;">Have a great break! 🌴</p>
        </div>
    </div>
@endsection
