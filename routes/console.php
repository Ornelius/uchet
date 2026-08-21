<?php

use App\Console\Commands\GeneratePlannedWorkOrders;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command(GeneratePlannedWorkOrders::class)->dailyAt('06:00')->name('planned-work-orders');
