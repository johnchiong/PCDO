<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('payments:check-due')->daily()->withoutOverlapping();
Schedule::command('notification:process')->everySixHours()->withoutOverlapping();
Schedule::command('export:completed-loans')->everyMinute()->withoutOverlapping();
Schedule::command('archive:coop-programs')->everyMinute()->withoutOverlapping();
Schedule::command('notifications:cleanup')->everySixHours()->withoutOverlapping();
