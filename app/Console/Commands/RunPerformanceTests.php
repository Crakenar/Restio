<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class RunPerformanceTests extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:performance
                            {--filter= : Filter specific test}
                            {--stop-on-failure : Stop on first failure}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run performance and load tests for the application';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🚀 Running Performance Tests for Restio');
        $this->newLine();

        $this->displaySystemInfo();
        $this->newLine();

        $this->warn('⚠️  Performance tests will create LARGE datasets in PostgreSQL');
        $this->warn('   Database: restio_performance_test');
        $this->warn('   Expected data: Up to 2000 users and 20,000+ vacation requests');
        $this->newLine();

        if (! $this->confirm('Continue with performance tests?', true)) {
            $this->info('Performance tests cancelled.');

            return Command::SUCCESS;
        }

        // Check if performance database exists
        $this->info('Creating/refreshing performance test database...');
        $this->call('db:wipe', ['--database' => 'pgsql', '--force' => true]);
        $this->call('migrate', ['--database' => 'pgsql', '--force' => true]);

        $this->newLine();
        $this->info('═══════════════════════════════════════════════════════════');
        $this->info('Starting Performance Test Suite');
        $this->info('═══════════════════════════════════════════════════════════');
        $this->newLine();

        $startTime = microtime(true);

        // Build the test command using performance-specific config
        $command = ['php', 'vendor/bin/phpunit', '-c', 'phpunit.performance.xml'];

        if ($filter = $this->option('filter')) {
            $command[] = '--filter='.$filter;
        }

        if ($this->option('stop-on-failure')) {
            $command[] = '--stop-on-failure';
        }

        // Run the tests using Process
        $process = new Process($command, base_path());
        $process->setTimeout(300); // 5 minutes timeout
        $process->setEnv(['APP_ENV' => 'testing']); // Ensure testing environment

        $exitCode = $process->run(function ($type, $buffer) {
            echo $buffer;
        });

        $endTime = microtime(true);
        $duration = round($endTime - $startTime, 2);

        $this->newLine();
        $this->info('═══════════════════════════════════════════════════════════');

        if ($exitCode === 0) {
            $this->info('✅ All performance tests passed!');
        } else {
            $this->error('❌ Some performance tests failed');
        }

        $this->info("⏱️  Total execution time: {$duration}s");
        $this->info('═══════════════════════════════════════════════════════════');
        $this->newLine();

        $this->displayPerformanceReport();

        return $exitCode;
    }

    /**
     * Display system information
     */
    private function displaySystemInfo(): void
    {
        $this->info('System Information:');
        $this->line('  • PHP Version: '.PHP_VERSION);
        $this->line('  • Laravel Version: '.app()->version());
        $this->line('  • Test Database: PostgreSQL (restio_performance_test)');
        $this->line('  • Test Environment: testing');
        $this->line('  • Memory Limit: '.ini_get('memory_limit'));
        $this->newLine();
        $this->comment('Note: Tests use PostgreSQL for production-like conditions');
    }

    /**
     * Display performance benchmarking report
     */
    private function displayPerformanceReport(): void
    {
        $this->info('📊 Performance Benchmarks:');
        $this->newLine();

        $this->table(
            ['Metric', 'Target', 'Description'],
            [
                ['Dashboard Load', '< 500ms', 'Small company (100 users, 1000 requests)'],
                ['Dashboard Load', '< 1000ms', 'Medium company (500 users, 5000 requests)'],
                ['Dashboard Load', '< 2000ms', 'Large company (2000 users, 20000 requests)'],
                ['Concurrent Requests (P95)', '< 1500ms', '50 concurrent dashboard loads'],
                ['Requests Page', '< 1500ms', 'Page with 15000+ vacation requests'],
                ['Calendar Page', '< 1200ms', 'Calendar with 6000+ events'],
                ['Request Submission', '< 500ms', 'Average time to submit request (100 submissions)'],
                ['Request Approval', '< 500ms', 'Average approval with 9000+ requests (300 approvals)'],
                ['Bulk User Assignment', '< 2000ms', 'Assign 500 users to team'],
                ['Query Count', '< 30', 'Dashboard queries (warn if > 20)'],
                ['Memory Usage', '< 100MB', 'Dashboard with 1000 users, 16000+ requests'],
            ]
        );

        $this->newLine();
        $this->comment('💡 Tips for optimizing performance:');
        $this->line('  1. Run: php artisan optimize');
        $this->line('  2. Enable OPcache in production');
        $this->line('  3. Use Redis for caching');
        $this->line('  4. Enable database query caching');
        $this->line('  5. Review slow query logs');
    }
}
