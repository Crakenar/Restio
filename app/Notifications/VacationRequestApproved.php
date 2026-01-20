<?php

namespace App\Notifications;

use App\Models\User;
use App\Models\VacationRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VacationRequestApproved extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public VacationRequest $vacationRequest
    ) {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $startDate = $this->vacationRequest->start_date->format('M d, Y');
        $endDate = $this->vacationRequest->end_date->format('M d, Y');
        $type = str_replace('_', ' ', ucwords(str_replace('_', ' ', $this->vacationRequest->type->value)));
        $approver = User::find($this->vacationRequest->approved_by);

        return (new MailMessage)
            ->subject('Your Time Off Request Has Been Approved!')
            ->view('emails.vacation-request-approved', [
                'employeeName' => $notifiable->name,
                'requestType' => $type,
                'startDate' => $startDate,
                'endDate' => $endDate,
                'days' => $this->vacationRequest->days,
                'approvedBy' => $approver?->name,
                'approvedDate' => $this->vacationRequest->approved_date?->format('M d, Y'),
                'actionUrl' => url('/requests'),
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'vacation_request_id' => $this->vacationRequest->id,
            'type' => $this->vacationRequest->type->value,
            'start_date' => $this->vacationRequest->start_date->toDateString(),
            'end_date' => $this->vacationRequest->end_date->toDateString(),
            'approved_by' => $this->vacationRequest->approved_by,
            'approved_date' => $this->vacationRequest->approved_date?->toDateString(),
            'message' => 'Your time off request has been approved',
        ];
    }
}
