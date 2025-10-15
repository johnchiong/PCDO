<?php

namespace App\Http\Controllers;

use App\Models\Cooperative;
use App\Models\CoopProgram;
use App\Models\CoopProgramChecklist;
use App\Models\FinishedCoopProgramChecklist;
use Illuminate\Support\Facades\DB;
use App\Models\Programs;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProgramController extends Controller
{
    /**
     * Display a listing of the programs with cooperative count.
     */
    public function index()
    {
        $programs = Programs::withCount('coopProgram')->get();

        return inertia('programs/index', [
            'programs' => $programs->map(fn ($program) => [
                'id' => $program->id,
                'name' => $program->name,
                'cooperatives_count' => $program->coop_program_count, // from withCount
            ]),
        ]);
    }

    /**
     * Show one program and its cooperatives.
     */
    public function show($id)
    {
        $program = Programs::findOrFail($id);

        $cooperatives = CoopProgram::with('cooperative')
            ->where('program_id', $id)
            ->get()
            ->map(fn ($cp) => [
                'id' => $cp->cooperative->id,
                'name' => $cp->cooperative->name,
                'program_status' => $cp->program_status,
            ]);

        return inertia('programs/show', [
            'program' => $program,
            'cooperatives' => $cooperatives,
        ]);
    }

    public function createCooperative(Programs $program): Response
    {
        $cooperatives = Cooperative::all(['id', 'name']);

        return Inertia::render('programs/create', [
            'program' => $program,          // ✅ pass program so props.program.id works
            'cooperatives' => $cooperatives,
        ]);
    }

    public function storeCooperative(Request $request, Programs $program)
    {
        $data = $request->validate([
            'cooperative_id' => 'required|exists:cooperatives,id',
            'email' => 'required|email',
            'phone_number' => ['required', 'regex:/^09\d{9}$/'],
            'project'=>'required|string|max:255',
        ]);

        $cooperative = Cooperative::findOrFail($data['cooperative_id']);

        // ✅ Same validation logic you had before...
        $ongoingPrograms = CoopProgram::where('coop_id', $cooperative->id)
            ->where('program_status', 'Ongoing')
            ->with('program')
            ->get();

        foreach ($ongoingPrograms as $ongoing) {
            if ($ongoing->program_id === $program->id) {
                return back()->withErrors(['program_id' => 'This program is already ongoing.']);
            }
            if ($program->name === 'LICAP' && $ongoing->program->name === 'LICAP') {
                return back()->withErrors(['program_id' => 'LICAP program already ongoing.']);
            }
            if ($program->name !== 'LICAP' && $ongoing->program->name !== 'LICAP') {
                return back()->withErrors(['program_id' => 'Cannot enroll in another non-LICAP program while one is ongoing.']);
            }
        }

        $coopProgram = CoopProgram::create([
            'coop_id' => $cooperative->id,
            'program_id' => $program->id,
            'project'=>$data['project'],
            'start_date' => now(),
            'end_date' => now()->addMonths($program->term_months),
            'program_status' => 'Ongoing',
            'number' => $data['phone_number'],
            'email' => $data['email'],
            'loan_amount' => null,
            'with_grace' => null,
        ]);

        return redirect()->route('programs.cooperatives.checklist.show',
            [
                'program' => $program->id,
                'cooperative' => $cooperative->id,
            ]
        )->with('success', 'Program enrolled successfully. Please complete required documents.');
    }

    public function finalizeLoan(Request $request, Programs $program, Cooperative $cooperative)
    {
        $request->validate([
            'loan_amount' => 'required|numeric|min:1',
            'with_grace' => 'required|numeric',
        ]);

        $coopProgram = CoopProgram::where('program_id', $program->id)
            ->where('coop_id', $cooperative->id)
            ->first();

        if (! $coopProgram) {
            return back()->withErrors(['loan_amount' => 'Program does not exist for this cooperative.']);
        }

        if ($request->loan_amount < $program->min_amount || $request->loan_amount > $program->max_amount) {
            return back()->withErrors([
                'loan_amount' => "Loan amount must be between ₱{$program->min_amount} and ₱{$program->max_amount}",
            ]);
        }

        // ✅ Server-side enforcement
        $totalChecklists = $program->checklists()->count();
        $completedChecklists = $coopProgram->checklist()->whereNotNull('file_name')->count();

        // if ($completedChecklists < $totalChecklists) {
        //     return back()->withErrors(['loan_amount' => 'Checklist is not yet complete.']);
        // }

        $coopProgram->update([
            'loan_amount' => $request->loan_amount,
            'with_grace' => $request->with_grace,
        ]);

        return redirect()
            ->route('programs.cooperatives.checklist.show', [
                'program' => $program->id,
                'cooperative' => $cooperative->id,
            ])
            ->with('success', 'Loan details finalized successfully!');
    }

    public function archiveFinishedProgram($coopProgramId)
    {
        DB::transaction(function () use ($coopProgramId) {
            // 1. Get the coop program
            $coopProgram = CoopProgram::with('checklists.uploads')->findOrFail($coopProgramId);

            if ($coopProgram->program_status !== 'Finished' || $coopProgram->exported !== 1) {
                throw new \Exception('Program must be finished and exported before archiving.');
            }

            // 2. Move CoopProgram to FinishedCoopProgram
            $finished = FinishedCoopProgram::create([
                'coop_id' => $coopProgram->coop_id,
                'program_id' => $coopProgram->program_id,
                'start_date' => $coopProgram->start_date,
                'end_date' => $coopProgram->end_date,
                'program_status' => $coopProgram->program_status,
                'loan_amount' => $coopProgram->loan_amount,
                'with_grace' => $coopProgram->with_grace,
                'email' => $coopProgram->email,
                'number' => $coopProgram->number,
                'exported' => true,
            ]);

            // 3. Move Checklists + Uploads
            foreach ($coopProgram->checklists as $checklist) {
                foreach ($checklist->uploads as $upload) {
                    FinishedCoopProgramChecklist::create([
                        'finished_coop_program_id' => $finished->id,
                        'checklist_id' => $checklist->checklist_id,
                        'is_completed' => true,
                        'file_name' => $upload->file_name,
                        'mime_type' => $upload->mime_type,
                        'file_content' => $upload->file_content,
                    ]);
                }
            }

            // 4. Delete original records
            CoopProgramChecklist::where('coop_program_id', $coopProgram->id)->delete();
            $coopProgram->delete();
        });

        return redirect()->route('programs.index')->with('success', 'Program archived successfully!');
    }
}
