<?php

namespace App\Console\Commands;

use App\Models\AmortizationOld;
use Illuminate\Console\Command;
use App\Models\CoopProgram;
use League\Csv\Writer;
use Carbon\Carbon;

class ExportCompletedLoans extends Command
{
    protected $signature = 'export:completed-loans';
    protected $description = 'Export fully paid cooperative loans to CSV';

    public function handle()
    {
        try {
            $this->info('Running export...');

            $coopPrograms = CoopProgram::with([
                'amortizationSchedules',
                'program',
                'cooperative',
                'cooperative.members'
            ])
            ->where('program_status', 'Finished')
            ->get();

            if ($coopPrograms->isEmpty()) {
                $this->info('No finished cooperative programs found.');
                return 0;
            }

            foreach ($coopPrograms as $coopProgram) {
                $schedules = $coopProgram->amortizationSchedules->sortBy('due_date');
                if ($schedules->isEmpty()) continue;

                $allPaid = $schedules->every(fn($s) => $s->status === 'Paid');
                if (!$allPaid) continue;

                $coop = $coopProgram->cooperative;

                // Members
                $chairman  = optional($coop->members->firstWhere('position', 'Chairman'))->last_name ?? 'N/A';
                $treasurer = optional($coop->members->firstWhere('position', 'Treasurer'))->last_name ?? 'N/A';
                $manager   = optional($coop->members->firstWhere('position', 'Manager'))->last_name ?? 'N/A';

                // Loan summary CSV
                $csvData = [];
                $csvData[] = ['Cooperative_name', $coop->name ?? 'Unknown Cooperative'];
                $csvData[] = ['Program_name', $coopProgram->program->name, 'Coop Chairman', $chairman];
                $csvData[] = ['Loan_amount', $coopProgram->loan_amount, 'Coop Treasurer', $treasurer];
                $csvData[] = ['Start_date', Carbon::parse($coopProgram->start_date)->format('Y-m-d'), 'Coop Manager', $manager];
                $csvData[] = ['Grace_period', $coopProgram->with_grace];
                $csvData[] = ['Term_months', $coopProgram->program->term_months, 'Project', $coopProgram->program->project ?? 'N/A'];
                $csvData[] = []; // blank row

                // Schedule header
                $csvData[] = ['due_date', 'installment', 'date_paid', 'amount_paid', 'status'];
                foreach ($schedules as $schedule) {
                    $csvData[] = [
                        Carbon::parse($schedule->due_date)->format('Y-m-d'),
                        $schedule->installment,
                        $schedule->date_paid ? Carbon::parse($schedule->date_paid)->format('Y-m-d') : '',
                        $schedule->amount_paid ?? '',
                        $schedule->status
                    ];
                }
                $csvData[] = []; // optional blank row

                // Create CSV in memory
                $csv = Writer::createFromString('');
                foreach ($csvData as $row) {
                    $csv->insertOne($row);
                }
                $csvContent = $csv->getContent();

                // Save to Old table
                AmortizationOld::create([
                    'coop_program_id' => $coopProgram->id,
                    'file_content' => $csvContent,
                ]);

                // Mark exported
                $coopProgram->exported = true;
                $coopProgram->save();

                // Delete schedules
                $coopProgram->amortizationSchedules()->delete();
            }

            $this->info('✅ CSV exported and saved to the Old table successfully!');
            return 0;

        } catch (\Exception $e) {
            \Log::error('ExportCompletedLoans failed: '.$e->getMessage(), [
                'stack' => $e->getTraceAsString()
            ]);
            $this->error('ExportCompletedLoans failed: '.$e->getMessage());
            return 1;
        }
    }
}
