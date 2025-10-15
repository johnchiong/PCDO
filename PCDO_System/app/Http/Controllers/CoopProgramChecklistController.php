<?php

namespace App\Http\Controllers;

use App\Models\CoopProgram;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Settings;
use App\Models\CoopProgramChecklist;
use Illuminate\Http\Request;
use Inertia\Inertia;


class CoopProgramChecklistController extends Controller
{
    // Show the checklist for a cooperative
    public function show($programId, $cooperativeId)
    {
        $coopProgram = CoopProgram::with(['program.checklists', 'cooperative'])
            ->where('program_id', $programId)
            ->where('coop_id', $cooperativeId)
            ->firstOrFail();

        $checklistItems = $coopProgram->program->checklists;

        $uploads = CoopProgramChecklist::where('coop_program_id', $coopProgram->id)
            ->get(['id', 'program_checklist_id', 'file_name', 'mime_type']);

        $checklistWithUploads = $checklistItems->map(function ($item) use ($uploads) {
            $upload = $uploads->firstWhere('program_checklist_id', $item->id);

            return [
                'id' => $item->id,
                'name' => $item->name,
                'upload' => $upload ? [
                    'id' => $upload->id,
                    'file_name' => $upload->file_name,
                    'mime_type' => $upload->mime_type,
                ] : null,
            ];
        });

        return Inertia::render('programs/checklist', [
            'cooperative' => [
                'id' => $coopProgram->id,
                'loan_amount' => $coopProgram->loan_amount,      // added
                'with_grace' => $coopProgram->with_grace,       // added
                'cooperative' => $coopProgram->cooperative,
                'program' => $coopProgram->program,
            ],
            'checklistItems' => $checklistWithUploads,
        ]);
    }

    // Upload a file
    public function upload(Request $request, $programId, $cooperativeId)
    {
        $request->validate([
            'program_checklist_id' => 'required|exists:program_checklists,id',
            'file' => 'required|file|max:5120', // max 5MB
        ]);

        $coopProgram = CoopProgram::where('program_id', $programId)
            ->where('coop_id', $cooperativeId)
            ->firstOrFail();

        $file = $request->file('file');
        $mime = $file->getClientMimeType();
        $fileName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

        $pdfBinary = null;

        // Convert DOCX → PDF (in memory, no storage)
        if (str_contains($mime, 'word')) {
            $phpWord = IOFactory::load($file->getRealPath());

            // Configure PDF renderer
            Settings::setPdfRendererName('DomPDF');
            Settings::setPdfRendererPath(base_path('vendor/dompdf/dompdf'));

            // Use output buffering to capture PDF data
            $tempPdf = tempnam(sys_get_temp_dir(), 'pdf_');
            $pdfWriter = IOFactory::createWriter($phpWord, 'PDF');
            $pdfWriter->save($tempPdf);

            $pdfBinary = file_get_contents($tempPdf);
            unlink($tempPdf); // delete temp file
            $mime = 'application/pdf';
            $fileName .= '.pdf';
        } else {
            // Non-DOCX files saved as-is
            $pdfBinary = file_get_contents($file->getRealPath());
        }

        // Save or update in DB
        $existing = CoopProgramChecklist::where('coop_program_id', $coopProgram->id)
            ->where('program_checklist_id', $request->program_checklist_id)
            ->first();

        if ($existing) {
            $existing->update([
                'file_name' => $fileName,
                'mime_type' => $mime,
                'file_content' => $pdfBinary,
            ]);
        } else {
            CoopProgramChecklist::create([
                'coop_program_id' => $coopProgram->id,
                'program_checklist_id' => $request->program_checklist_id,
                'file_name' => $fileName,
                'mime_type' => $mime,
                'file_content' => $pdfBinary,
            ]);
        }

        return redirect()->route('programs.cooperatives.checklist.show', [
            'program' => $programId,
            'cooperative' => $cooperativeId,
        ]);
    }

    // Download a file
    public function download($programId, $cooperativeId, $fileId)
    {
        $upload = CoopProgramChecklist::findOrFail($fileId);

        return response($upload->file_content)
            ->header('Content-Type', $upload->mime_type)
            ->header('Content-Disposition', 'attachment; filename="'.$upload->file_name.'"');
    }

    // Delete a file
    public function delete($programId, $cooperativeId, $fileId)
    {
        $upload = CoopProgramChecklist::findOrFail($fileId);
        $upload->delete();

        return redirect()->back()->with('success', 'File deleted successfully!');
    }
}
