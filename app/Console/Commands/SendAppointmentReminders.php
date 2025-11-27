<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendAppointmentReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'appointments:send-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send reminder notifications for upcoming appointments';

    protected NotificationService $notificationService;

    /**
     * Create a new command instance.
     */
    public function __construct(NotificationService $notificationService)
    {
        parent::__construct();
        $this->notificationService = $notificationService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for upcoming appointments...');

        // Send 24-hour reminders
        $this->send24HourReminders();

        // Send 1-hour reminders
        $this->send1HourReminders();

        $this->info('Appointment reminders sent successfully!');
    }

    /**
     * Send reminders for appointments in 24 hours.
     */
    protected function send24HourReminders(): void
    {
        $tomorrow = Carbon::now()->addDay()->startOfDay();
        $tomorrowEnd = Carbon::now()->addDay()->endOfDay();

        $appointments = Appointment::where('status', 'scheduled')
            ->whereBetween('scheduled_at', [$tomorrow, $tomorrowEnd])
            ->whereDoesntHave('notifications', function ($query) {
                $query->where('type', 'appointment_reminder')
                      ->whereJsonContains('data->reminder_type', '24_hours')
                      ->where('created_at', '>=', Carbon::now()->subHours(24));
            })
            ->with(['clinic', 'owner', 'service'])
            ->get();

        foreach ($appointments as $appointment) {
            $this->notificationService->appointmentReminder24Hours($appointment);
            $this->info("24-hour reminder sent for appointment #{$appointment->id}");
        }

        $this->info("Sent {$appointments->count()} 24-hour reminders");
    }

    /**
     * Send reminders for appointments in 1 hour.
     */
    protected function send1HourReminders(): void
    {
        $oneHourLater = Carbon::now()->addHour();
        $oneHourWindow = Carbon::now()->addHour()->addMinutes(15); // 15-minute window

        $appointments = Appointment::where('status', 'scheduled')
            ->whereBetween('scheduled_at', [$oneHourLater, $oneHourWindow])
            ->whereDoesntHave('notifications', function ($query) {
                $query->where('type', 'appointment_reminder')
                      ->whereJsonContains('data->reminder_type', '1_hour')
                      ->where('created_at', '>=', Carbon::now()->subHours(1));
            })
            ->with(['clinic', 'owner', 'service'])
            ->get();

        foreach ($appointments as $appointment) {
            $this->notificationService->appointmentReminder1Hour($appointment);
            $this->info("1-hour reminder sent for appointment #{$appointment->id}");
        }

        $this->info("Sent {$appointments->count()} 1-hour reminders");
    }
}
