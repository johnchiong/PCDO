<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('notification:process')->everySixHours();
Schedule::command('export:completed-loans')->everyMinute();
Schedule::command('archive:coop-programs')->everyMinute();
Schedule::command('payments:check-due')->daily();
Schedule::command('notifications:cleanup')->everySixHours();
