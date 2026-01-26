<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Tabs --}}
        <div class="border-b border-gray-200 dark:border-gray-700">
            <nav class="-mb-px flex space-x-8">
                <button
                    wire:click="setActiveTab('application')"
                    class="@if($activeTab === 'application') border-primary-500 text-primary-600 dark:text-primary-400 @else border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 @endif whitespace-nowrap border-b-2 px-1 py-4 text-sm font-medium transition"
                >
                    Application Logs
                </button>
                <button
                    wire:click="setActiveTab('audit')"
                    class="@if($activeTab === 'audit') border-primary-500 text-primary-600 dark:text-primary-400 @else border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 @endif whitespace-nowrap border-b-2 px-1 py-4 text-sm font-medium transition"
                >
                    Audit Logs
                </button>
            </nav>
        </div>

        {{-- Controls --}}
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Show lines:</label>
                <select
                    wire:model.live="logLines"
                    class="rounded-lg border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                >
                    <option value="50">50</option>
                    <option value="100">100</option>
                    <option value="200">200</option>
                    <option value="500">500</option>
                </select>
            </div>
        </div>

        {{-- Application Logs Tab --}}
        @if($activeTab === 'application')
            <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Timestamp
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Level
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Message
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                            @forelse($this->getApplicationLogs() as $log)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                        {{ $log['timestamp'] }}
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-sm">
                                        <span class="inline-flex rounded-full px-2 text-xs font-semibold leading-5
                                            @if($log['level'] === 'ERROR') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                                            @elseif($log['level'] === 'WARNING') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                                            @elseif($log['level'] === 'INFO') bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200
                                            @else bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200
                                            @endif">
                                            {{ $log['level'] }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">
                                        <div class="max-w-4xl break-words font-mono text-xs">
                                            {{ $log['message'] }}
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                        No application logs found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        {{-- Audit Logs Tab --}}
        @if($activeTab === 'audit')
            <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Timestamp
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    User
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Action
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Model
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    IP Address
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                            @forelse($this->getAuditLogs() as $log)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                        {{ $log['created_at'] }}
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                        {{ $log['user'] }}
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-sm">
                                        <span class="inline-flex rounded-full px-2 text-xs font-semibold leading-5
                                            @if($log['action'] === 'deleted') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                                            @elseif($log['action'] === 'updated') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                                            @elseif($log['action'] === 'created') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                            @else bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200
                                            @endif">
                                            {{ $log['action'] }}
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-700 dark:text-gray-300">
                                        {{ $log['model'] }}
                                        @if($log['model_id'])
                                            <span class="text-gray-500">#{{ $log['model_id'] }}</span>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                                        {{ $log['ip_address'] }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                        No audit logs found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
