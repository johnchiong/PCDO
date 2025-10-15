<?php

namespace App\Http\Controllers;

use App\Models\AmortizationOld;
use App\Models\CoopProgram;
use App\Models\Programs;
use App\Models\Resolved;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use setasign\Fpdi\Fpdi;

class DocumentationController extends Controller
{
    public function index()
    {
        // Fetch completed, exported, and archived cooperative programs
        $coopPrograms = CoopProgram::with(['cooperative', 'program'])
            ->whereIn('program_status', ['Finished', 'Resolved'])
            ->where('exported', 1)
            ->where('archived', 1)
            ->get();

        // Group completed cooperatives by year
        $groupedByYear = $coopPrograms->groupBy(function ($coopProgram) {
            return $coopProgram->updated_at->format('Y');
        });

        // Determine range of years (from earliest record to current year)
        $minYear = $coopPrograms->min(fn ($c) => $c->updated_at->year) ?? date('Y');
        $maxYear = date('Y');

        // Create all years even if empty
        $years = collect(range($minYear, $maxYear))->map(function ($year) use ($groupedByYear) {
            $items = $groupedByYear->get($year, collect());

            return [
                'year' => $year,
                'cooperatives' => $items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'name' => $item->cooperative->name ?? 'N/A',
                        'program_name' => $item->program->name ?? 'N/A',
                        'completed_at' => $item->updated_at->format('Y-m-d'),
                    ];
                })->values(),
            ];
        })->sortDesc()->values();

        return inertia('documentation/index', [
            'years' => $years,
        ]);
    }

    public function show($coopId)
    {
        // Load CoopProgram with related data
        $coopProgram = CoopProgram::with([
            'cooperative',
            'program',
            'coopDetails',
            'coopMemberFiles',
            'finishedChecklist',
            'programProgress',
            'delinquents',
            'resolvedItems',
        ])
            ->where('id', $coopId)
            ->whereIn('program_status', ['Finished', 'Resolved'])
            ->firstOrFail();

        // Map related data safely
        return inertia('documentation/show', [
            'cooperative' => [
                'id' => $coopProgram->cooperative->id,
                'name' => $coopProgram->cooperative->name,
                'program_id' => $coopProgram->id,
                'program_name' => $coopProgram->program->name,
                'program_status' => $coopProgram->program_status,
                'start_date' => optional($coopProgram->start_date)->format('F d, Y'),
                'completed_at' => optional($coopProgram->updated_at)->format('F d, Y'),

                // Coop Details
                'coop_details' => $coopProgram->coopDetails->map(function ($detail) {
                    return [
                        'id' => $detail->id,
                        'region' => $detail->region?->name ?? 'N/A',
                        'province' => $detail->province?->name ?? 'N/A',
                        'city' => $detail->city?->name ?? 'N/A',
                        'barangay' => $detail->barangay?->name ?? 'N/A',
                        'asset_size' => $detail->asset_size ?? 'N/A',
                        'coop_type' => $detail->coop_type ?? 'N/A',
                        'status_category' => $detail->status_category ?? 'N/A',
                        'bond_of_membership' => $detail->bond_of_membership ?? 'N/A',
                        'area_of_operation' => $detail->area_of_operation ?? 'N/A',
                        'citizenship' => $detail->citizenship ?? 'N/A',
                        'members_count' => $detail->members_count ?? 0,
                        'total_asset' => $detail->total_asset ?? 0,
                        'net_surplus' => $detail->net_surplus ?? 0,
                    ];
                }),

                // Coop Member Files (exclude file content)
                'coop_member_files' => $coopProgram->coopMemberFiles->map(function ($file) {
                    return [
                        'id' => $file->id,
                        'filename' => $file->filename ?? '',
                        'uploaded_at' => optional($file->created_at)->format('F d, Y'),
                    ];
                }),

                // Finished Checklist
                'finished_coop_program_checklist' => $coopProgram->finishedChecklist->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'task_name' => $item->task_name ?? '',
                        'status' => $item->status ?? '',
                        'file_url' => route('documentation.checklist.file', $item->id),
                    ];
                }),

                // Program Progress
                'coop_program_progress' => $coopProgram->programProgress->map(function ($progress) {
                    return [
                        'id' => $progress->id,
                        'progress_name' => $progress->progress_name ?? '',
                        'status' => $progress->status ?? '',
                    ];
                }),

                // Delinquents
                'delinquents' => $coopProgram->delinquents->map(function ($delinquent) {
                    return [
                        'id' => $delinquent->id,
                        'name' => $delinquent->name ?? '',
                        'amount_due' => $delinquent->amount_due ?? 0,
                    ];
                }),

                // Resolved
                'resolved' => $coopProgram->resolvedItems->map(function ($resolved) {
                    return [
                        'id' => $resolved->id,
                        'issue' => $resolved->issue ?? '',
                        'resolved_at' => optional($resolved->resolved_at)->format('F d, Y'),
                        'file_url' => route('documentation.resolved.file', ['id' => $resolved->id]),
                    ];
                }),
            ],
        ]);
    }

    public function amortizationFile($id)
    {
        $amortization = AmortizationOld::where('coop_program_id', $id)->first();

        if (! $amortization || ! $amortization->file_content) {
            abort(404, 'Amortization schedule not found.');
        }

        return new Response($amortization->file_content, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="amortization_schedule.pdf"',
        ]);
    }

    public function cooperativeDetailsFile($id)
    {
        // Load CoopProgram with related cooperative, program, and coopDetails + related location names
        $coopProgram = CoopProgram::with([
            'cooperative',
            'program',
            'coopDetails.region',
            'coopDetails.province',
            'coopDetails.city',
            'coopDetails.barangay',
        ])->findOrFail($id);

        // Get the first CoopDetail (with relationships loaded)
        $coopDetail = $coopProgram->coopDetails->first();

        // Generate PDF from the Blade view
        $pdf = Pdf::loadView('coop_details', compact('coopProgram', 'coopDetail'))
            ->setPaper('legal', 'portrait'); // 8.5in x 13in

        // Return as inline PDF
        return $pdf->stream('cooperative_details.pdf');
    }

    public function resolvedFile($coopId)
    {
        // Get the resolved record for this coop program
        $resolved = Resolved::where('coop_program_id', $coopId)->latest()->first();

        if (! $resolved || ! $resolved->file_content) {
            abort(404, 'Resolved file not found.');
        }

        // Detect file type automatically if you didn’t save file_type
        $finfo = finfo_open();
        $mimeType = finfo_buffer($finfo, $resolved->file_content, FILEINFO_MIME_TYPE);
        finfo_close($finfo);

        // Return the file inline (display inside iframe or <img>)
        return new Response($resolved->file_content, 200, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="resolved_file_'.$resolved->id.'"',
        ]);
    }

    public function checklistFile($coopProgramId)
    {
        $coopProgram = CoopProgram::with([
            'cooperative',
            'program.checklists',
            'finishedChecklist',
        ])->findOrFail($coopProgramId);

        $finishedChecklists = $coopProgram->finishedChecklist->keyBy('checklist_id');

        $checklists = $coopProgram->program->checklists->map(function ($item) use ($finishedChecklists) {
            $finished = $finishedChecklists->get($item->id);

            $status = 'Incomplete';
            $fileContent = null;
            $mimeType = null;

            if ($finished && ! empty($finished->file_content)) {
                $status = 'Complete';
                $fileContent = $finished->file_content;
                $mimeType = $finished->mime_type ?? 'application/pdf';
            }

            return (object) [
                'task_name' => $item->name,
                'status' => $status,
                'file_content' => $fileContent,
                'mime_type' => $mimeType,
            ];
        });

        if ($checklists->isEmpty()) {
            abort(404, 'No checklist found for this cooperative program.');
        }

        // Step 1: Generate your main checklist overview PDF
        $mainPdf = Pdf::loadView('coop_program_checklist', compact('checklists', 'coopProgram'))
            ->setPaper('legal', 'portrait');

        $mainPdfPath = storage_path('app/public/main_checklist.pdf');
        file_put_contents($mainPdfPath, $mainPdf->output());

        // Step 2: Initialize FPDI and add main PDF
        $pdf = new Fpdi;
        $pageCount = $pdf->setSourceFile($mainPdfPath);
        for ($i = 1; $i <= $pageCount; $i++) {
            $tpl = $pdf->importPage($i);
            $pdf->AddPage();
            $pdf->useTemplate($tpl);
        }

        // Step 3: Append attachments
        foreach ($checklists as $item) {
            if (! empty($item->file_content)) {
                $extension = str_contains($item->mime_type, 'pdf') ? 'pdf' : 'jpg';
                $tmpPath = storage_path('app/public/tmp_'.uniqid().'.'.$extension);
                file_put_contents($tmpPath, $item->file_content);

                if (str_contains($item->mime_type, 'pdf')) {
                    $pages = $pdf->setSourceFile($tmpPath);
                    for ($p = 1; $p <= $pages; $p++) {
                        $tpl = $pdf->importPage($p);
                        $pdf->AddPage();
                        $pdf->useTemplate($tpl);
                    }
                } elseif (str_contains($item->mime_type, 'image')) {
                    $pdf->AddPage();
                    $pdf->Image($tmpPath, 10, 10, 190, 260);
                }

                @unlink($tmpPath);
            }
        }

        @unlink($mainPdfPath);

        // Step 4: Stream final merged PDF
        return response($pdf->Output('S'), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="Checklist.pdf"');
    }
}
