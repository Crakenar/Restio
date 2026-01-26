<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Redis;

class RedisStatsWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected function getHeading(): ?string
    {
        return 'Redis Statistics';
    }

    protected function getStats(): array
    {
        try {
            $info = Redis::info();
            $dbSize = Redis::dbsize();

            $usedMemory = $this->formatBytes($info['used_memory'] ?? 0);
            $peakMemory = $this->formatBytes($info['used_memory_peak'] ?? 0);
            $connectedClients = $info['connected_clients'] ?? 0;
            $uptimeInDays = isset($info['uptime_in_seconds']) ? round($info['uptime_in_seconds'] / 86400, 1) : 0;

            return [
                Stat::make('Redis Status', 'Connected')
                    ->description('Server is running')
                    ->descriptionIcon('heroicon-m-check-circle')
                    ->color('success'),

                Stat::make('Total Keys', number_format($dbSize))
                    ->description('Keys in database')
                    ->descriptionIcon('heroicon-m-key')
                    ->color('info'),

                Stat::make('Memory Usage', $usedMemory)
                    ->description("Peak: {$peakMemory}")
                    ->descriptionIcon('heroicon-m-cpu-chip')
                    ->color('warning'),

                Stat::make('Connected Clients', $connectedClients)
                    ->description("Uptime: {$uptimeInDays} days")
                    ->descriptionIcon('heroicon-m-users')
                    ->color('info'),
            ];
        } catch (\Exception $e) {
            return [
                Stat::make('Redis Status', 'Disconnected')
                    ->description('Unable to connect to Redis')
                    ->descriptionIcon('heroicon-m-x-circle')
                    ->color('danger'),

                Stat::make('Error', 'N/A')
                    ->description($e->getMessage())
                    ->descriptionIcon('heroicon-m-exclamation-triangle')
                    ->color('danger'),
            ];
        }
    }

    protected function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, 2).' '.$units[$pow];
    }
}
