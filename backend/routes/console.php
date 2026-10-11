<?php

use App\Console\Commands\ExpireEstimatesCommand;
use App\Console\Commands\RefreshGoogleReviewsCommand;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command(RefreshGoogleReviewsCommand::class)->twiceDaily(7, 19);
Schedule::command(ExpireEstimatesCommand::class)->dailyAt('00:10');
