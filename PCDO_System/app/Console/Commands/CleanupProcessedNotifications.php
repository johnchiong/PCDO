<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanupProcessedNotifications extends Command
{
    protected $signature = 'notifications:cleanup';

    protected $description = 'Move processed pending notifications to the main notifications table and delete them.';

    public function handle()
    {
        DB::beginTransaction();
        try {
            // Step 1: Move processed rows
            DB::insert('
                INSERT INTO notifications (schedule_id, coop_id, type, subject, body, created_at, processed)
                SELECT schedule_id, coop_id, type, subject, body, created_at, processed
                FROM pending_notifications
                WHERE processed = 1
            ');

            // Step 2: Delete processed rows
            DB::delete('
                DELETE FROM pending_notifications
                WHERE processed = 1
            ');

            DB::commit();
            $this->info('✅ Processed notifications moved and cleaned up successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error('❌ Cleanup failed: '.$e->getMessage());
        }
    }
}
