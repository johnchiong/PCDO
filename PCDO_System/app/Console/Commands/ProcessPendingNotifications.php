<?php

namespace App\Console\Commands;

use App\Models\PendingNotification;
use App\Notifications\LoanOverdueNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;
use Symfony\Component\Console\Command\Command as SymfonyCommand;

class ProcessPendingNotifications extends Command
{
    protected $signature = 'notifications:process';

    protected $description = 'Process pending loan notifications and send emails';

    public function handle()
    {
        $notifications = PendingNotification::where('processed', 0)->get();

        if ($notifications->isEmpty()) {
            $this->info('No pending notifications to process.');

            return SymfonyCommand::SUCCESS;
        }

        foreach ($notifications as $notif) {
            $schedule = $notif->schedule;

            if (! $schedule) {
                $this->error("Schedule ID {$notif->schedule_id} not found.");

                continue;
            }

            $coopProgram = $schedule->coopProgram;
            $email = $coopProgram?->email;
            $coopName = $coopProgram?->name ?? 'Unknown Coop Program';

            if ($email) {
                try {
                    $notification = new LoanOverdueNotification($schedule);
                    $mailMessage = $notification->toMail($schedule);

                    Notification::route('mail', $email)->notify($notification);

                    // Update the notification (fires 'updated' event for SyncLogger)
                    $notif->update([
                        'subject' => $mailMessage->subject ?? 'Loan Notification',
                        'body' => implode("\n", $mailMessage->introLines ?? []),
                        'processed' => 1,
                    ]);

                    $this->info(" Notification sent to {$email} for {$coopName}, type: {$notif->type}");
                } catch (\Exception $e) {
                    $this->error(" Failed to send notification to {$email}: ".$e->getMessage());
                }
            } else {
                $this->warn(" No email found for coop program ID {$schedule->coop_program_id}");
            }
        }

        return SymfonyCommand::SUCCESS;
    }
}
