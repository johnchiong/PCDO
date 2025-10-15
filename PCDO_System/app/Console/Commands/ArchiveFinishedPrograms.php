<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\CoopProgram;
use App\Models\CoopProgramChecklist;

class ArchiveFinishedPrograms extends Command
{
    protected $signature = 'archive:coop-programs';
    protected $description = 'Archive finished and exported cooperative program checklists/documents only';

    public function handle()
    {
        try {
            DB::transaction(function () {
                // Fetch programs that are Finished or Resolved, exported, and not archived
                $programs = CoopProgram::whereIn('program_status', ['Finished', 'Resolved'])
                    ->where('exported', 1)
                    ->where('archived', 0)
                    ->get();

                foreach ($programs as $program) {
                    $this->info("Processing Coop Program ID: {$program->id}");

                    // Only fetch metadata first to avoid loading large file_content
                    $checklists = CoopProgramChecklist::where('coop_program_id', $program->id)
                        ->select('id', 'program_checklist_id', 'file_name', 'mime_type')
                        ->get();

                    if ($checklists->isEmpty()) {
                        $this->warn("No checklists found for program {$program->id}");
                        $program->archived = 1;
                        $program->save();
                        continue;
                    }

                    foreach ($checklists as $checklist) {
                        // Fetch the file content individually
                        $checklistWithContent = CoopProgramChecklist::find($checklist->id);

                        DB::table('finished_coop_program_checklist')->insert([
                            'coop_program_id' => $program->id,
                            'checklist_id'    => $checklistWithContent->program_checklist_id,
                            'file_name'       => $checklistWithContent->file_name,
                            'mime_type'       => $checklistWithContent->mime_type,
                            'file_content'    => $checklistWithContent->file_content,
                            'created_at'      => now(),
                            'updated_at'      => now(),
                        ]);

                        // Delete original checklist
                        $checklistWithContent->delete();
                    }

                    // Mark program as archived
                    $program->archived = 1;
                    $program->save();

                    $this->info("✔ Program {$program->id} archived successfully.");
                }
            });

            $this->info("✅ Archiving process completed successfully.");
        } catch (\Throwable $e) {
            $this->error("❌ Error: " . $e->getMessage());
            \Log::error("Archive command failed", [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
