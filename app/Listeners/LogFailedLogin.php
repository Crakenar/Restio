<?php

namespace App\Listeners;

use App\Services\AuditLogger;
use Illuminate\Auth\Events\Failed;

class LogFailedLogin
{
    /**
     * Create the event listener.
     */
    public function __construct(protected AuditLogger $auditLogger) {}

    /**
     * Handle the event.
     */
    public function handle(Failed $event): void
    {
        // Get the email from credentials if available
        $email = $event->credentials['email'] ?? 'unknown';

        $this->auditLogger->failedLogin($email, [
            'guard' => $event->guard,
            'attempt_time' => now()->toIso8601String(),
        ]);
    }
}
