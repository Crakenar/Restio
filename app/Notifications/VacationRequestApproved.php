<?php

namespace App\Notifications;

use App\Models\VacationRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class VacationRequestApproved extends Notification implements ShouldQueue
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
        return ['database'];
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
