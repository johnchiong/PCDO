<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckDuePayments extends Command
{
    protected $signature = 'payments:check-due';

    protected $description = 'Insert pending notifications for payments before due, due today, and overdue.';

    public function handle()
    {
        $now = now();

        // --- 3 days before due ---
        DB::insert("
            INSERT INTO pending_notifications (schedule_id, coop_id, type, created_at)
            SELECT ps.id, cp.coop_id, 'before_due', ?
            FROM ammortization_schedules ps
            JOIN coop_programs cp ON cp.id = ps.coop_program_id
            WHERE ps.date_paid IS NULL
              AND DATE(ps.due_date) = DATE_ADD(CURDATE(), INTERVAL 3 DAY)
              AND NOT EXISTS (
                  SELECT 1 FROM pending_notifications pn
                  WHERE pn.schedule_id = ps.id
                    AND pn.type = 'before_due'
              )
        ", [$now]);

        // --- due today ---
        DB::insert("
            INSERT INTO pending_notifications (schedule_id, coop_id, type, created_at)
            SELECT ps.id, cp.coop_id, 'due_today', ?
            FROM ammortization_schedules ps
            JOIN coop_programs cp ON cp.id = ps.coop_program_id
            WHERE ps.date_paid IS NULL
              AND DATE(ps.due_date) = CURDATE()
              AND NOT EXISTS (
                  SELECT 1 FROM pending_notifications pn
                  WHERE pn.schedule_id = ps.id
                    AND pn.type = 'due_today'
              )
        ", [$now]);

        // --- 1 day after due (overdue) ---
        DB::insert("
            INSERT INTO pending_notifications (schedule_id, coop_id, type, created_at)
            SELECT ps.id, cp.coop_id, 'overdue', ?
            FROM ammortization_schedules ps
            JOIN coop_programs cp ON cp.id = ps.coop_program_id
            WHERE ps.date_paid IS NULL
              AND DATE(ps.due_date) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)
              AND NOT EXISTS (
                  SELECT 1 FROM pending_notifications pn
                  WHERE pn.schedule_id = ps.id
                    AND pn.type = 'overdue'
              )
        ", [$now]);

        $this->info('✅ Pending payment notifications inserted successfully.');
    }
}
