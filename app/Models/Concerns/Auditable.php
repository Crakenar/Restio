<?php

namespace App\Models\Concerns;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait Auditable
{
    /**
     * Boot the auditable trait for a model.
     */
    protected static function bootAuditable(): void
    {
        static::created(function ($model) {
            $model->auditEvent('created');
        });

        static::updated(function ($model) {
            $model->auditEvent('updated');
        });

        static::deleted(function ($model) {
            $model->auditEvent('deleted');
        });
    }

    /**
     * Get all audit logs for the model.
     */
    public function auditLogs(): MorphMany
    {
        return $this->morphMany(AuditLog::class, 'auditable')->latest();
    }

    /**
     * Create an audit log entry.
     */
    public function auditEvent(string $event, ?array $metadata = null): void
    {
        $user = auth()->user();

        AuditLog::create([
            'event' => $event,
            'auditable_type' => get_class($this),
            'auditable_id' => $this->id,
            'user_id' => $user?->id,
            'company_id' => $this->company_id ?? $user?->company_id,
            'old_values' => $event === 'updated' ? $this->getOriginal() : null,
            'new_values' => $event === 'updated' ? $this->getAttributes() : $this->getAttributes(),
            'metadata' => array_merge([
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ], $metadata ?? []),
        ]);
    }
}
