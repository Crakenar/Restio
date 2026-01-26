<?php

namespace App\Listeners;

use App\Services\AuditLogger;
use Illuminate\Auth\Events\Login;

class LogSuccessfulLogin
{
    /**
     * Create the event listener.
     */
    public function __construct(protected AuditLogger $auditLogger) {}

    /**
     * Handle the event.
     */
    public function handle(Login $event): void
    {
        $this->auditLogger->successfulLogin(
            metadata: [
                'user_id' => $event->user->id,
                'user_name' => $event->user->name,
                'user_email' => $event->user->email,
                'guard' => $event->guard,
                'login_time' => now()->toIso8601String(),
            ],
            guard: $event->guard
        );
    }
}
