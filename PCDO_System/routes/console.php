<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('notification:process')->everyMinute();
Schedule::command('export:completed-loans')->everyMinute();
Schedule::command('archive:coop-programs')->everyMinute();
