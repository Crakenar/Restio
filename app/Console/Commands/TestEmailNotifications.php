<?php

namespace App\Console\Commands;

use App\Enum\VacationRequestStatus;
use App\Enum\VacationRequestType;
use App\Models\Company;
use App\Models\User;
use App\Models\VacationRequest;
use App\Notifications\VacationRequestApproved;
use App\Notifications\VacationRequestRejected;
use App\Notifications\VacationRequestSubmitted;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestEmailNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:test {email} {--type=all}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send test email notifications to verify templates and Mailtrap integration';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $email = $this->argument('email');
        $type = $this->option('type');

        // Create test data
        $company = Company::first() ?? Company::factory()->create(['name' => 'Test Company']);
        $employee = User::factory()->create([
            'name' => 'John Doe',
            'email' => $email,
            'company_id' => $company->id,
            'role' => 'employee',
        ]);
        $manager = User::factory()->create([
            'name' => 'Jane Manager',
            'email' => $email,
            'company_id' => $company->id,
            'role' => 'manager',
        ]);

        $vacationRequest = VacationRequest::factory()->create([
            'user_id' => $employee->id,
            'company_id' => $company->id,
            'start_date' => now()->addWeek(),
            'end_date' => now()->addWeek()->addDays(5),
            'type' => VacationRequestType::VACATION,
            'status' => VacationRequestStatus::PENDING,
            'reason' => 'Family vacation to the beach',
        ]);

        $this->info('Sending test email(s) to: '.$email);
        $this->newLine();

        if ($type === 'all' || $type === 'submitted') {
            $this->sendSubmittedNotification($manager, $vacationRequest);
        }

        if ($type === 'all' || $type === 'approved') {
            $this->sendApprovedNotification($employee, $vacationRequest);
        }

        if ($type === 'all' || $type === 'rejected') {
            $this->sendRejectedNotification($employee, $vacationRequest);
        }

        if ($type === 'welcome') {
            $this->sendWelcomeEmail($employee, $company);
        }

        $this->newLine();
        $this->info('✅ Test email(s) sent successfully!');
        $this->info('Check your Mailtrap inbox at: https://mailtrap.io');

        // Clean up test data
        $vacationRequest->delete();
        $employee->delete();
        $manager->delete();

        return Command::SUCCESS;
    }

    /**
     * Send vacation request submitted notification.
     */
    protected function sendSubmittedNotification(User $manager, VacationRequest $request): void
    {
        $this->line('📤 Sending "Request Submitted" notification...');
        $manager->notify(new VacationRequestSubmitted($request));
        $this->info('   ✓ Sent to manager');
    }

    /**
     * Send vacation request approved notification.
     */
    protected function sendApprovedNotification(User $employee, VacationRequest $request): void
    {
        $this->line('✅ Sending "Request Approved" notification...');
        $request->update([
            'status' => VacationRequestStatus::APPROVED,
            'approved_by' => User::where('role', 'manager')->first()?->id,
            'approved_date' => now(),
        ]);
        $employee->notify(new VacationRequestApproved($request));
        $this->info('   ✓ Sent to employee');
    }

    /**
     * Send vacation request rejected notification.
     */
    protected function sendRejectedNotification(User $employee, VacationRequest $request): void
    {
        $this->line('❌ Sending "Request Rejected" notification...');
        $request->update([
            'status' => VacationRequestStatus::REJECTED,
            'approved_by' => User::where('role', 'manager')->first()?->id,
            'rejection_reason' => 'We have critical project deadlines during this period. Please consider alternative dates.',
        ]);
        $employee->notify(new VacationRequestRejected($request));
        $this->info('   ✓ Sent to employee');
    }

    /**
     * Send welcome email.
     */
    protected function sendWelcomeEmail(User $user, Company $company): void
    {
        $this->line('👋 Sending "Welcome" email...');

        Mail::send('emails.welcome', [
            'userName' => $user->name,
            'userEmail' => $user->email,
            'companyName' => $company->name,
            'userRole' => $user->role,
            'annualDays' => 20,
            'temporaryPassword' => 'RestioDemo2024!',
            'loginUrl' => url('/login'),
        ], function ($message) use ($user) {
            $message->to($user->email)
                ->subject('Welcome to Restio!');
        });

        $this->info('   ✓ Sent welcome email');
    }
}
