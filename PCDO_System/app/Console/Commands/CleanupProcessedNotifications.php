<?php

namespace App\Console\Commands;

use App\Models\Notifications;
use App\Models\PendingNotification;
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
            // Fetch all processed pending notifications
            $processed = PendingNotification::where('processed', 1)->get();

            if ($processed->isEmpty()) {
                $this->info('No processed notifications found.');

                return;
            }

            foreach ($processed as $pn) {
                // Insert into main notifications table (fires 'created' event)
                Notifications::create([
                    'schedule_id' => $pn->schedule_id,
                    'coop_id' => $pn->coop_id,
                    'type' => $pn->type,
                    'subject' => $pn->subject,
                    'body' => $pn->body,
                    'created_at' => $pn->created_at,
                    'processed' => $pn->processed,
                ]);

                // Delete from pending (fires 'deleted' event)
                $pn->delete();
            }

            DB::commit();
            $this->info('✅ Processed notifications moved and cleaned up successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error('❌ Cleanup failed: '.$e->getMessage());
        }
    }
}
