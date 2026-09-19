<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Schedule::command('chatbot:end-inactive')
    ->everyMinute();

Schedule::command('chatbot:cleanup-expired-data')
    ->dailyAt('02:30')
    ->withoutOverlapping();

Schedule::command('agent:send-follow-up-reminders')
    ->everyMinute()
    ->withoutOverlapping();

Schedule::command('agent:delete-expired-notifications')
    ->hourly()
    ->withoutOverlapping();

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
