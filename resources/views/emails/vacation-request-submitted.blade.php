@extends('emails.layout')

@section('content')
    <div class="greeting">New Time Off Request</div>

    <div class="content">
        <p>Hello {{ $managerName }},</p>

        <p>
            <strong>{{ $employeeName }}</strong> has submitted a new time off request that requires your approval.
        </p>

        <div class="details-box">
            <div class="detail-row">
                <span class="detail-label">Employee</span>
                <span class="detail-value">{{ $employeeName }}</span>
            </div>
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
            @if($reason)
            <div class="detail-row">
                <span class="detail-label">Reason</span>
                <span class="detail-value">{{ $reason }}</span>
            </div>
            @endif
        </div>

        <div class="button-container">
            <a href="{{ $actionUrl }}" class="button">Review Request</a>
        </div>

        <div class="footer-message">
            <p>Please review this request and approve or decline it as soon as possible. {{ $employeeName }} is waiting for your response.</p>
        </div>
    </div>
@endsection
