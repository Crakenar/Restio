<?php

return [
    'vacation_request' => 'Vacation Request',
    'vacation_requests' => 'Vacation Requests',
    'request_time_off' => 'Request Time Off',
    'my_requests' => 'My Requests',
    'team_requests' => 'Team Requests',

    'fields' => [
        'type' => 'Type',
        'start_date' => 'Start Date',
        'end_date' => 'End Date',
        'reason' => 'Reason',
        'status' => 'Status',
        'days' => 'Days',
    ],

    'types' => [
        'vacation' => 'Vacation',
        'sick_leave' => 'Sick Leave',
        'personal_day' => 'Personal Day',
        'unpaid_leave' => 'Unpaid Leave',
        'other' => 'Other',
    ],

    'status' => [
        'pending' => 'Pending',
        'approved' => 'Approved',
        'rejected' => 'Rejected',
    ],

    'actions' => [
        'submit_request' => 'Submit Request',
        'approve' => 'Approve',
        'reject' => 'Reject',
        'cancel' => 'Cancel Request',
        'view' => 'View Request',
    ],

    'messages' => [
        'request_submitted' => 'Your vacation request has been submitted',
        'request_approved' => 'Vacation request has been approved',
        'request_rejected' => 'Vacation request has been rejected',
        'request_cancelled' => 'Vacation request has been cancelled',
        'insufficient_days' => 'You do not have enough vacation days',
        'overlapping_request' => 'You already have a request for these dates',
    ],

    'notifications' => [
        'new_request' => 'New vacation request from :name',
        'request_approved' => 'Your vacation request has been approved',
        'request_rejected' => 'Your vacation request has been rejected',
    ],

    'stats' => [
        'days_remaining' => 'Days Remaining',
        'days_used' => 'Days Used',
        'days_total' => 'Total Days',
        'pending_requests' => 'Pending Requests',
    ],
];
