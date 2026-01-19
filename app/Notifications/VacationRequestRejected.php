<?php

namespace App\Notifications;

use App\Models\User;
use App\Models\VacationRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VacationRequestRejected extends Notification
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
        $type = str_replace('_', ' ', ucfirst($this->vacationRequest->type->value));
        $rejectedBy = User::find($this->vacationRequest->approved_by);

        return (new MailMessage)
            ->error()
            ->subject('Time Off Request Not Approved')
            ->greeting('Hello '.$notifiable->name.',')
            ->line('Unfortunately, your time off request has been declined.')
            ->line('**Type:** '.$type)
            ->line('**From:** '.$startDate)
            ->line('**To:** '.$endDate)
            ->when($rejectedBy, function ($mail) use ($rejectedBy) {
                return $mail->line('**Declined by:** '.$rejectedBy->name);
            })
            ->when($this->vacationRequest->rejection_reason, function ($mail) {
                return $mail->line('**Reason:** '.$this->vacationRequest->rejection_reason);
            })
            ->action('View Request', url('/requests'))
            ->line('If you have questions about this decision, please contact your manager.')
            ->salutation('Best regards, '.config('app.name'));
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
            'rejected_by' => $this->vacationRequest->approved_by,
            'rejection_reason' => $this->vacationRequest->rejection_reason,
            'message' => 'Your time off request has been rejected',
        ];
    }
}
