<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AuditLogger
{
    /**
     * Log an audit event.
     */
    public function log(
        string $event,
        ?Model $auditable = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?array $metadata = null
    ): AuditLog {
        $user = Auth::user();

        return AuditLog::create([
            'event' => $event,
            'auditable_type' => $auditable?->getMorphClass(),
            'auditable_id' => $auditable?->getKey(),
            'user_id' => $user?->id,
            'company_id' => $user?->company_id,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'metadata' => $metadata,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Log a created event.
     */
    public function created(Model $model, ?array $metadata = null): AuditLog
    {
        return $this->log(
            event: 'created',
            auditable: $model,
            newValues: $model->getAttributes(),
            metadata: $metadata
        );
    }

    /**
     * Log an updated event.
     */
    public function updated(Model $model, ?array $metadata = null): AuditLog
    {
        return $this->log(
            event: 'updated',
            auditable: $model,
            oldValues: $model->getOriginal(),
            newValues: $model->getChanges(),
            metadata: $metadata
        );
    }

    /**
     * Log a deleted event.
     */
    public function deleted(Model $model, ?array $metadata = null): AuditLog
    {
        return $this->log(
            event: 'deleted',
            auditable: $model,
            oldValues: $model->getAttributes(),
            metadata: $metadata
        );
    }

    /**
     * Log a failed login attempt.
     */
    public function failedLogin(string $email, ?array $metadata = null): AuditLog
    {
        return $this->log(
            event: 'login.failed',
            metadata: array_merge(['email' => $email], $metadata ?? [])
        );
    }

    /**
     * Log a successful login.
     */
    public function successfulLogin(?array $metadata = null): AuditLog
    {
        return $this->log(
            event: 'login.success',
            metadata: $metadata
        );
    }

    /**
     * Log a logout event.
     */
    public function logout(?array $metadata = null): AuditLog
    {
        return $this->log(
            event: 'logout',
            metadata: $metadata
        );
    }

    /**
     * Log a role change event.
     */
    public function roleChanged(Model $user, string $oldRole, string $newRole, ?array $metadata = null): AuditLog
    {
        return $this->log(
            event: 'role.changed',
            auditable: $user,
            oldValues: ['role' => $oldRole],
            newValues: ['role' => $newRole],
            metadata: $metadata
        );
    }

    /**
     * Log a request approval event.
     */
    public function requestApproved(Model $request, ?array $metadata = null): AuditLog
    {
        return $this->log(
            event: 'request.approved',
            auditable: $request,
            metadata: $metadata
        );
    }

    /**
     * Log a request rejection event.
     */
    public function requestRejected(Model $request, ?array $metadata = null): AuditLog
    {
        return $this->log(
            event: 'request.rejected',
            auditable: $request,
            metadata: $metadata
        );
    }

    /**
     * Log a settings change event.
     */
    public function settingsChanged(Model $settings, ?array $metadata = null): AuditLog
    {
        return $this->log(
            event: 'settings.changed',
            auditable: $settings,
            oldValues: $settings->getOriginal(),
            newValues: $settings->getChanges(),
            metadata: $metadata
        );
    }

    /**
     * Log a subscription change event.
     */
    public function subscriptionChanged(string $changeType, ?array $metadata = null): AuditLog
    {
        return $this->log(
            event: "subscription.{$changeType}",
            metadata: $metadata
        );
    }
}
