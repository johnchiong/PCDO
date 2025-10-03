<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule; 

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('notification:process')->everyMinute();
Schedule::command('export:completed-loans')->everyMinute();
Schedule::command('archive:coop-programs')->everyMinute();