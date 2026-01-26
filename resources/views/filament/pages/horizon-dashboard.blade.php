<x-filament-panels::page>
    <div class="space-y-6">
        <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="mb-4 flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                        Laravel Horizon
                    </h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Monitor your queues, jobs, and failed jobs in real-time.
                    </p>
                </div>
                <a
                    href="{{ $this->getHorizonUrl() }}"
                    target="_blank"
                    class="inline-flex items-center gap-2 rounded-lg bg-primary-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:bg-primary-500 dark:hover:bg-primary-600"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                    </svg>
                    Open in New Window
                </a>
            </div>
        </div>

        <div class="overflow-hidden rounded-lg border border-gray-200 shadow-sm dark:border-gray-700">
            <iframe
                src="{{ $this->getHorizonUrl() }}"
                class="h-[calc(100vh-16rem)] w-full"
                frameborder="0"
                title="Laravel Horizon Dashboard"
            ></iframe>
        </div>

        <div class="rounded-lg border border-gray-200 bg-blue-50 p-4 dark:border-blue-900 dark:bg-blue-900/20">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-blue-800 dark:text-blue-200">
                        About Horizon
                    </h3>
                    <div class="mt-2 text-sm text-blue-700 dark:text-blue-300">
                        <p>
                            Horizon provides a dashboard for monitoring key queue metrics such as job throughput, runtime, and job failures.
                            You can also monitor, pause, and retry failed jobs directly from the dashboard.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
