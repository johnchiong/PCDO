<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CoopProgram;
use App\Models\CoopUploads;
use App\Models\Checklist;
use App\Models\Cooperative;
use App\Models\Program;
use Inertia\Inertia;

class CoopProgramController extends Controller
{
    public function index()
    {
        $enrollments = CoopProgram::with(['cooperative', 'program'])->get();
        return Inertia::render('coop_programs/index', [
            'enrollments' => $enrollments
        ]);
    }

    public function create()
    {
        return Inertia::render('coop_programs/create', [
            'cooperatives' => Cooperative::all(),
            'programs' => Program::all(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'cooperative_id' => 'required|exists:cooperatives,id',
            'program_id' => 'required|exists:programs,id',
        ]);

        $cooperativeProgram = CoopProgram::create($data);

        return redirect()
            ->route('coop_programs.document', $cooperativeProgram)
            ->with('success', 'Cooperative enrolled in program successfully!');
    }

    public function document(CoopProgram $coopProgram)
    {
        $checklistItems = Checklist::all();

        return Inertia::render('coop_programs/document', [
            'coopProgram' => $coopProgram->load(['program', 'cooperative']),
            'checklistItems' => $checklistItems,
        ]);
    }

    public function storeUpload(Request $request, CoopProgram $coopProgram)
    {
        $request->validate([
            'checklist_item_id' => 'required|exists:checklist_items,id',
            'file' => 'required|file|max:5120',
        ]);

        $file = $request->file('file');

        CoopUploads::updateOrCreate(
            [
                'coop_program_id' => $coopProgram->id,
                'checklist_item_id' => $request->checklist_item_id,
            ],
            [
                'file_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'file_content' => base64_encode(file_get_contents($file)),
            ]
        );

        return back()->with('success', 'File uploaded successfully!');
    }
}