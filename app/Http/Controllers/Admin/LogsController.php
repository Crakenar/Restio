<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Inertia\Inertia;
use Inertia\Response;

class LogsController extends Controller
{
    /**
     * Show the logs viewer.
     */
    public function index(Request $request): Response
    {
        $logType = $request->get('type', 'audit');
        $search = $request->get('search');
        $perPage = $request->get('per_page', 50);

        $logs = match ($logType) {
            'audit' => $this->getAuditLogs($search, $perPage),
            'laravel' => $this->getLaravelLogs($search),
            'errors' => $this->getErrorLogs($search),
            default => $this->getAuditLogs($search, $perPage),
        };

        return Inertia::render('admin/Logs', [
            'logs' => $logs,
            'log_type' => $logType,
            'search' => $search,
        ]);
    }

    /**
     * Get audit logs from database.
     */
    private function getAuditLogs(?string $search, int $perPage): array
    {
        $query = AuditLog::with(['user', 'company'])
            ->latest();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('event', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($q) => $q->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('company', fn ($q) => $q->where('name', 'like', "%{$search}%"));
            });
        }

        return $query->paginate($perPage)
            ->through(fn ($log) => [
                'id' => $log->id,
                'event' => $log->event,
                'description' => $log->description,
                'user_id' => $log->user_id,
                'user_name' => $log->user?->name,
                'user_email' => $log->user?->email,
                'company_id' => $log->company_id,
                'company_name' => $log->company?->name,
                'ip_address' => $log->ip_address,
                'user_agent' => $log->user_agent,
                'properties' => $log->properties,
                'created_at' => $log->created_at->format('Y-m-d H:i:s'),
                'created_at_human' => $log->created_at->diffForHumans(),
            ])
            ->toArray();
    }

    /**
     * Get Laravel application logs.
     */
    private function getLaravelLogs(?string $search): array
    {
        $logPath = storage_path('logs/laravel.log');

        if (! File::exists($logPath)) {
            return ['logs' => [], 'message' => 'No log file found'];
        }

        $content = File::get($logPath);
        $lines = array_reverse(explode("\n", $content));

        // Parse log entries
        $logs = [];
        $currentLog = null;

        foreach ($lines as $line) {
            if (empty($line)) {
                continue;
            }

            // Check if line starts with timestamp pattern [YYYY-MM-DD HH:MM:SS]
            if (preg_match('/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\]/', $line, $matches)) {
                if ($currentLog) {
                    $logs[] = $currentLog;
                }

                // Parse log level and message
                preg_match('/\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] \w+\.(\w+): (.*)/', $line, $parts);

                $currentLog = [
                    'timestamp' => $matches[1] ?? '',
                    'level' => $parts[2] ?? 'info',
                    'message' => $parts[3] ?? $line,
                    'stack' => [],
                ];
            } elseif ($currentLog) {
                $currentLog['stack'][] = $line;
            }

            if (count($logs) >= 100) {
                break;
            }
        }

        if ($currentLog) {
            $logs[] = $currentLog;
        }

        // Filter by search
        if ($search) {
            $logs = array_filter($logs, fn ($log) => str_contains(strtolower($log['message']), strtolower($search)) ||
                str_contains(strtolower($log['level']), strtolower($search))
            );
        }

        return ['logs' => array_values($logs)];
    }

    /**
     * Get error logs only.
     */
    private function getErrorLogs(?string $search): array
    {
        $allLogs = $this->getLaravelLogs($search);

        if (isset($allLogs['logs'])) {
            $allLogs['logs'] = array_filter(
                $allLogs['logs'],
                fn ($log) => in_array(strtolower($log['level']), ['error', 'critical', 'alert', 'emergency'])
            );
        }

        return $allLogs;
    }

    /**
     * Download logs file.
     */
    public function download(Request $request)
    {
        $logType = $request->get('type', 'laravel');

        $logPath = match ($logType) {
            'laravel' => storage_path('logs/laravel.log'),
            'errors' => storage_path('logs/laravel.log'),
            default => storage_path('logs/laravel.log'),
        };

        if (! File::exists($logPath)) {
            return back()->with('error', 'Log file not found');
        }

        return response()->download($logPath);
    }

    /**
     * Clear logs file.
     */
    public function clear(Request $request)
    {
        $logType = $request->get('type', 'laravel');

        $logPath = match ($logType) {
            'laravel' => storage_path('logs/laravel.log'),
            'errors' => storage_path('logs/laravel.log'),
            default => storage_path('logs/laravel.log'),
        };

        if (File::exists($logPath)) {
            File::put($logPath, '');
        }

        return back()->with('success', 'Logs cleared successfully');
    }
}
