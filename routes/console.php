<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule automatic transition of scheduled appointments to in_progress
Schedule::command('appointments:transition-scheduled')
    ->everyMinute()
    ->withoutOverlapping()
    ->onOneServer()
    ->runInBackground()
    ->onSuccess(function () {
        \Log::info('Scheduled appointments transitioned successfully');
    })
    ->onFailure(function () {
        \Log::error('Failed to transition scheduled appointments');
    });

// Schedule automatic appointment status updates
Schedule::command('appointments:update-overdue --hours=2')
    ->hourly()
    ->withoutOverlapping()
    ->onOneServer()
    ->runInBackground();

// Schedule appointment reminders (24-hour and 1-hour notifications)
Schedule::command('appointments:send-reminders')
    ->hourly()
    ->withoutOverlapping()
    ->runInBackground()
    ->onSuccess(function () {
        \Log::info('Appointment reminders sent successfully');
    })
    ->onFailure(function () {
        \Log::error('Failed to send appointment reminders');
    });

// Schedule subscription billing processing (daily at 1:00 AM)
Schedule::command('subscription:process-billing')
    ->dailyAt('01:00')
    ->withoutOverlapping()
    ->onOneServer()
    ->runInBackground()
    ->onSuccess(function () {
        \Log::info('Subscription billing processed successfully');
    })
    ->onFailure(function () {
        \Log::error('Failed to process subscription billing');
    });

// Schedule monitoring logs cleanup (daily at 2:00 AM)
Schedule::command('monitoring:cleanup --force')
    ->dailyAt('02:00')
    ->withoutOverlapping()
    ->onOneServer()
    ->runInBackground()
    ->onSuccess(function () {
        \Log::info('Monitoring logs cleaned up successfully');
    })
    ->onFailure(function () {
        \Log::error('Failed to cleanup monitoring logs');
    });
