<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\DatabaseRouter;

class SyncLocalDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-local-database';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync local database with remote database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tables = ['workloads','checklists','jobs','cooperatives','file_uploads','verifications','reviews'];

        foreach ($tables as $table) {
            DatabaseRouter::syncToLocal($table);
        }

        $this->info('Cloud → Local sync completed safely.');
    }
}
