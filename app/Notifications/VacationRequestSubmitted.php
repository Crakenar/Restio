<?php

namespace App\Notifications;

use App\Models\VacationRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VacationRequestSubmitted extends Notification implements ShouldQueue
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
        $employee = $this->vacationRequest->user;
        $startDate = $this->vacationRequest->start_date->format('M d, Y');
        $endDate = $this->vacationRequest->end_date->format('M d, Y');
        $type = str_replace('_', ' ', ucwords(str_replace('_', ' ', $this->vacationRequest->type->value)));

        return (new MailMessage)
            ->subject('New Time Off Request from '.$employee->name)
            ->view('emails.vacation-request-submitted', [
                'managerName' => $notifiable->name,
                'employeeName' => $employee->name,
                'requestType' => $type,
                'startDate' => $startDate,
                'endDate' => $endDate,
                'days' => $this->vacationRequest->days,
                'reason' => $this->vacationRequest->reason,
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
            'employee_name' => $this->vacationRequest->user->name,
            'employee_id' => $this->vacationRequest->user_id,
            'type' => $this->vacationRequest->type->value,
            'start_date' => $this->vacationRequest->start_date->toDateString(),
            'end_date' => $this->vacationRequest->end_date->toDateString(),
            'reason' => $this->vacationRequest->reason,
            'message' => $this->vacationRequest->user->name.' submitted a time off request',
        ];
    }
}
