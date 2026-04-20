<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('email:rentree-pre-inscrits')->yearlyOn(9, 1, '10:00');
Schedule::command('seances:generate-season')->yearlyOn(9, 1, '02:00'); 
