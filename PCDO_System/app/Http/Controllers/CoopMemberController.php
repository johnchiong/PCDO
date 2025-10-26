<?php

namespace App\Http\Controllers;

use App\Models\CoopMember;
use App\Models\CoopProgram;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

class CoopMemberController extends Controller
{
    public function index(CoopProgram $program)
    {
        $members = CoopMember::where('coop_program_id', $program->id)
            ->select('id', 'name', 'position')
            ->latest()
            ->get();

        return Inertia::render('members/index', [
            'program' => $program,
            'members' => $members,
        ]);
    }

    public function create(CoopProgram $program)
    {
        return Inertia::render('members/create', ['program' => $program]);
    }

    public function store(Request $request, CoopProgram $program)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'biodata_form' => 'nullable|file|mimes:pdf|max:5120',
            'id_uploads.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:4096',
        ]);

        $biodataPath = null;
        if ($request->hasFile('biodata_form')) {
            $biodataPath = $request->file('biodata_form')->store('member_biodata');
        } else {
            $template = storage_path('app/public/forms/BioDataTemplate.pdf');
            if (file_exists($template)) {
                $biodataPath = 'member_biodata/'.Str::uuid().'.pdf';
                Storage::put($biodataPath, file_get_contents($template));
            }
        }

        $member = CoopMember::create([
            'coop_program_id' => $program->id,
            'name' => $validated['name'],
            'position' => $validated['position'],
            'biodata_path' => $biodataPath,
        ]);

        if ($request->hasFile('id_uploads')) {
            foreach ($request->file('id_uploads') as $file) {
                $path = $file->store('member_files');
                $member->files()->create([
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'file_type' => $file->getClientMimeType(),
                ]);
            }
        }

        return redirect()
            ->route('program.members.index', $program->id)
            ->with('success', 'Member added successfully.');
    }

    public function edit(CoopProgram $program, CoopMember $member)
    {
        return Inertia::render('members/edit', [
            'program' => $program,
            'member' => $member->load('files'),
        ]);
    }

    public function update(Request $request, CoopProgram $program, CoopMember $member)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'id_uploads.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:4096',
        ]);

        $member->update([
            'name' => $validated['name'],
            'position' => $validated['position'],
        ]);

        if ($request->hasFile('id_uploads')) {
            foreach ($request->file('id_uploads') as $file) {
                $path = $file->store('member_files');
                $member->files()->create([
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'file_type' => $file->getClientMimeType(),
                ]);
            }
        }

        return redirect()
            ->route('program.members.edit', [$program->id, $member->id])
            ->with('success', 'Member updated.');
    }

    public function destroy(CoopProgram $program, CoopMember $member)
    {
        foreach ($member->files as $file) {
            Storage::delete($file->file_path);
        }
        Storage::delete($member->biodata_path);
        $member->delete();

        return redirect()
            ->route('program.members.index', $program->id)
            ->with('success', 'Member deleted.');
    }

    public function downloadBiodata(CoopProgram $program, CoopMember $member)
    {
        if (! $member->biodata_path || ! Storage::exists($member->biodata_path)) {
            abort(404);
        }

        return Storage::download($member->biodata_path, basename($member->biodata_path));
    }

    public function downloadFile(CoopProgram $program, CoopMember $member, $fileId)
    {
        $file = $member->files()->findOrFail($fileId);

        return Storage::download($file->file_path, $file->file_name);
    }
}
