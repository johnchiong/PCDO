<?php

namespace App\Http\Controllers;

use App\Models\Cooperative;
use App\Models\CoopMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Settings;

class CoopMemberController extends Controller
{
    public function index(Cooperative $cooperative)
    {
        $members = $cooperative->members()
            ->with('files')
            ->orderBy('active_year', 'desc')
            ->orderBy('position')
            ->get();

        $years = $cooperative->members()
            ->selectRaw('active_year, COUNT(*) as member_count')
            ->groupBy('active_year')
            ->orderBy('active_year', 'desc')
            ->get();

        return Inertia::render('cooperatives/members/index', [
            'cooperative' => $cooperative->only('id', 'name'),
            'members' => $members,
            'years' => $years,
            'breadcrumbs' => [
                ['title' => 'Cooperatives', 'href' => route('cooperatives.index')],
                ['title' => $cooperative->name, 'href' => route('cooperatives.show', $cooperative->id)],
                ['title' => 'Members', 'href' => route('cooperatives.members.index', $cooperative->id)],
            ],
        ]);
    }

    public function create(Cooperative $cooperative)
    {
        return Inertia::render('cooperatives/members/create', [
            'cooperative' => $cooperative->only('id', 'name'),
            'breadcrumbs' => [
                ['title' => 'Cooperatives', 'href' => route('cooperatives.index')],
                ['title' => $cooperative->name, 'href' => route('cooperatives.show', $cooperative->id)],
                ['title' => 'Members', 'href' => route('cooperatives.members.index', $cooperative->id)],
                ['title' => 'Add Member', 'href' => route('cooperatives.members.create', $cooperative->id)],
            ],
        ]);
    }

    public function store(Request $request, Cooperative $cooperative)
    {
        $rules = [
            'position' => 'required|in:Chairman,Manager,Treasurer,Member',
            'active_year' => 'required|digits:4|integer|min:1900|max:'.(date('Y') + 1),
            'contact' => 'required|string',
            'email' => 'nullable|email',
            'first_name' => 'required|string',
            'middle_name' => 'nullable|string',
            'last_name' => 'required|string',
            'marital_status' => 'nullable|string',
            'street' => 'nullable|string',
            'city' => 'nullable|string',
            'telephone' => 'nullable|string',
            'birthdate' => 'required|date',
            'age' => 'required|integer|min:0',
            'sex' => 'required|in:Male,Female',
            'citizenship' => 'required|in:Filipino,Others',
            'birthplace' => 'required|string',
            'height' => 'nullable|integer',
            'weight' => 'nullable|integer',
            'religion' => 'required|string',
            'spouse_name' => 'nullable|string',
            'spouse_occupation' => 'nullable|string',
            'spouse_age' => 'nullable|integer',
            'father_name' => 'nullable|string',
            'father_occupation' => 'nullable|string',
            'father_age' => 'nullable|integer',
            'mother_name' => 'nullable|string',
            'mother_occupation' => 'nullable|string',
            'mother_age' => 'nullable|integer',
            'parent_address' => 'nullable|string',
            'emergency_name' => 'required|string',
            'emergency_contact' => 'required|string',
            'dependent1_name' => 'nullable|string',
            'dependent1_relationship' => 'nullable|string',
            'dependent1_birthdate' => 'nullable|date',
            'dependent1_age' => 'nullable|integer',
            'dependent2_name' => 'nullable|string',
            'dependent2_relationship' => 'nullable|string',
            'dependent2_birthdate' => 'nullable|date',
            'dependent2_age' => 'nullable|integer',
            'elementary_start' => 'nullable|digits:4',
            'elementary_end' => 'nullable|digits:4',
            'elementary_name' => 'nullable|string',
            'elementary_degree' => 'nullable|string',
            'hs_start' => 'nullable|digits:4',
            'hs_end' => 'nullable|digits:4',
            'hs_name' => 'nullable|string',
            'hs_degree' => 'nullable|string',
            'college_start' => 'nullable|digits:4',
            'college_end' => 'nullable|digits:4',
            'college_name' => 'nullable|string',
            'college_degree' => 'nullable|string',
            'course_start' => 'nullable|digits:4',
            'course_end' => 'nullable|digits:4',
            'course_name' => 'nullable|string',
            'course_degree' => 'nullable|string',
            'others_start' => 'nullable|digits:4',
            'others_end' => 'nullable|digits:4',
            'others_name' => 'nullable|string',
            'others_degree' => 'nullable|string',
            'company1_start' => 'nullable|date',
            'company1_end' => 'nullable|date',
            'company1_name' => 'nullable|string',
            'company1_position' => 'nullable|string',
            'company1_rfl' => 'nullable|string',
            'company2_start' => 'nullable|date',
            'company2_end' => 'nullable|date',
            'company2_name' => 'nullable|string',
            'company2_position' => 'nullable|string',
            'company2_rfl' => 'nullable|string',
            'company3_start' => 'nullable|date',
            'company3_end' => 'nullable|date',
            'company3_name' => 'nullable|string',
            'company3_position' => 'nullable|string',
            'company3_rfl' => 'nullable|string',
            'company4_start' => 'nullable|date',
            'company4_end' => 'nullable|date',
            'company4_name' => 'nullable|string',
            'company4_position' => 'nullable|string',
            'company4_rfl' => 'nullable|string',
            'company5_start' => 'nullable|date',
            'company5_end' => 'nullable|date',
            'company5_name' => 'nullable|string',
            'company5_position' => 'nullable|string',
            'company5_rfl' => 'nullable|string',
            'ref1_name' => 'nullable|string',
            'ref1_company' => 'nullable|string',
            'ref1_position' => 'nullable|string',
            'ref1_contact' => 'nullable|string',
            'ref2_name' => 'nullable|string',
            'ref2_company' => 'nullable|string',
            'ref2_position' => 'nullable|string',
            'ref2_contact' => 'nullable|string',
            'is_representative' => 'boolean',
            'files.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx',
        ];

        $validated = $request->validate($rules);
        $memberData = collect($validated)->except('files')->toArray();
        $member = $cooperative->members()->create($memberData);

        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $uploaded) {
                $mime = $uploaded->getClientMimeType();
                $originalName = pathinfo($uploaded->getClientOriginalName(), PATHINFO_FILENAME);
                $extension = strtolower($uploaded->getClientOriginalExtension());
                $finalPath = null;
                $finalFileName = null;
                $finalMime = 'application/pdf';

                // Convert DOC or DOCX → PDF
                if (in_array($extension, ['doc', 'docx'])) {
                    // Load DOC/DOCX
                    $phpWord = IOFactory::load($uploaded->getRealPath());

                    // Setup PDF renderer (Dompdf)
                    Settings::setPdfRendererName('DomPDF');
                    Settings::setPdfRendererPath(base_path('vendor/dompdf/dompdf'));

                    // Save temporary PDF
                    $tempPdf = tempnam(sys_get_temp_dir(), 'member_pdf_');
                    $pdfWriter = IOFactory::createWriter($phpWord, 'PDF');
                    $pdfWriter->save($tempPdf);

                    // Store in storage/app/private/member_files
                    $pdfContent = file_get_contents($tempPdf);
                    unlink($tempPdf);

                    $finalFileName = $originalName.'.pdf';
                    $finalPath = 'member_files/'.Str::uuid().'.pdf';
                    Storage::put($finalPath, $pdfContent);
                } else {
                    // For other file types: pdf, jpg, jpeg, png
                    $finalFileName = $uploaded->getClientOriginalName();
                    $finalMime = $mime;
                    $finalPath = $uploaded->store('member_files');
                }

                // Save to database
                $member->files()->create([
                    'file_path' => $finalPath,
                    'file_name' => $finalFileName,
                    'file_type' => $finalMime,
                ]);
            }
        }

        return redirect()
            ->route('cooperatives.members.index', $cooperative->id)
            ->with('success', 'Member created successfully.');
    }

    public function show(Cooperative $cooperative, CoopMember $member)
    {
        return Inertia::render('cooperatives/members/show', [
            'cooperative' => $cooperative,
            'member' => $member->load('files'),
            'breadcrumbs' => [
                ['title' => 'Cooperatives', 'href' => route('cooperatives.index')],
                ['title' => $cooperative->name, 'href' => route('cooperatives.show', $cooperative->id)],
                ['title' => 'Members', 'href' => route('cooperatives.members.index', $cooperative->id)],
                ['title' => $member->id.' - '.$member->first_name.' '.$member->last_name, 'href' => route('cooperatives.members.show', [$cooperative->id, $member->id])],
            ],
        ]);
    }

    public function edit(Cooperative $cooperative, CoopMember $member)
    {
        if ($member->first_name) {
            $details = $member->id.' - '.$member->first_name.' '.$member->last_name;
        } else {
            $details = $member->id;
        }

        return Inertia::render('cooperatives/members/edit', [
            'cooperative' => $cooperative,
            'member' => $member->load('files'),
            'breadcrumbs' => [
                ['title' => 'Cooperatives', 'href' => route('cooperatives.index')],
                ['title' => $cooperative->name, 'href' => route('cooperatives.show', $cooperative->id)],
                ['title' => 'Members', 'href' => route('cooperatives.members.index', $cooperative->id)],
                ['title' => $details, 'href' => route('cooperatives.members.show', [$cooperative->id, $member->id])],
                ['title' => 'Edit', 'href' => route('cooperatives.members.edit', [$cooperative->id, $member->id])],
            ],
        ]);
    }

    public function update(Request $request, Cooperative $cooperative, CoopMember $member)
    {
        $validated = $request->validate([
            'position' => 'required|in:Chairman,Manager,Treasurer,Member',
            'active_year' => 'required|digits:4|integer|min:1900|max:'.(date('Y') + 1), // Example: 1900 to next year
            'contact' => 'required|string',
            'email' => 'nullable|email',
            'first_name' => 'required|string',
            'middle_name' => 'nullable|string',
            'last_name' => 'required|string',
            'marital_status' => 'nullable|string',
            'street' => 'nullable|string',
            'city' => 'nullable|string',
            'telephone' => 'nullable|string',
            'birthdate' => 'required|date',
            'age' => 'required|integer|min:0',
            'sex' => 'required|in:Male,Female',
            'citizenship' => 'required|in:Filipino,Others',
            'birthplace' => 'required|string',
            'height' => 'nullable|integer',
            'weight' => 'nullable|integer',
            'religion' => 'required|string',
            'spouse_name' => 'nullable|string',
            'spouse_occupation' => 'nullable|string',
            'spouse_age' => 'nullable|integer',
            'father_name' => 'nullable|string',
            'father_occupation' => 'nullable|string',
            'father_age' => 'nullable|integer',
            'mother_name' => 'nullable|string',
            'mother_occupation' => 'nullable|string',
            'mother_age' => 'nullable|integer',
            'parent_address' => 'nullable|string',
            'emergency_name' => 'required|string',
            'emergency_contact' => 'required|string',
            'dependent1_name' => 'nullable|string',
            'dependent1_relationship' => 'nullable|string',
            'dependent1_birthdate' => 'nullable|date',
            'dependent1_age' => 'nullable|integer',
            'dependent2_name' => 'nullable|string',
            'dependent2_relationship' => 'nullable|string',
            'dependent2_birthdate' => 'nullable|date',
            'dependent2_age' => 'nullable|integer',
            'elementary_start' => 'nullable|digits:4',
            'elementary_end' => 'nullable|digits:4',
            'elementary_name' => 'nullable|string',
            'elementary_degree' => 'nullable|string',
            'hs_start' => 'nullable|digits:4',
            'hs_end' => 'nullable|digits:4',
            'hs_name' => 'nullable|string',
            'hs_degree' => 'nullable|string',
            'college_start' => 'nullable|digits:4',
            'college_end' => 'nullable|digits:4',
            'college_name' => 'nullable|string',
            'college_degree' => 'nullable|string',
            'course_start' => 'nullable|digits:4',
            'course_end' => 'nullable|digits:4',
            'course_name' => 'nullable|string',
            'course_degree' => 'nullable|string',
            'others_start' => 'nullable|digits:4',
            'others_end' => 'nullable|digits:4',
            'others_name' => 'nullable|string',
            'others_degree' => 'nullable|string',
            'company1_start' => 'nullable|date',
            'company1_end' => 'nullable|date',
            'company1_name' => 'nullable|string',
            'company1_position' => 'nullable|string',
            'company1_rfl' => 'nullable|string',
            'company2_start' => 'nullable|date',
            'company2_end' => 'nullable|date',
            'company2_name' => 'nullable|string',
            'company2_position' => 'nullable|string',
            'company2_rfl' => 'nullable|string',
            'company3_start' => 'nullable|date',
            'company3_end' => 'nullable|date',
            'company3_name' => 'nullable|string',
            'company3_position' => 'nullable|string',
            'company3_rfl' => 'nullable|string',
            'company4_start' => 'nullable|date',
            'company4_end' => 'nullable|date',
            'company4_name' => 'nullable|string',
            'company4_position' => 'nullable|string',
            'company4_rfl' => 'nullable|string',
            'company5_start' => 'nullable|date',
            'company5_end' => 'nullable|date',
            'company5_name' => 'nullable|string',
            'company5_position' => 'nullable|string',
            'company5_rfl' => 'nullable|string',
            'ref1_name' => 'nullable|string',
            'ref1_company' => 'nullable|string',
            'ref1_position' => 'nullable|string',
            'ref1_contact' => 'nullable|string',
            'ref2_name' => 'nullable|string',
            'ref2_company' => 'nullable|string',
            'ref2_position' => 'nullable|string',
            'ref2_contact' => 'nullable|string',
            'is_representative' => 'boolean',
            'files.*' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png',
        ]);

        $memberData = collect($validated)->except('files')->toArray();
        $member->update($memberData);

        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $uploaded) {
                $mime = $uploaded->getClientMimeType();
                $originalName = pathinfo($uploaded->getClientOriginalName(), PATHINFO_FILENAME);
                $extension = strtolower($uploaded->getClientOriginalExtension());
                $finalPath = null;
                $finalFileName = null;
                $finalMime = 'application/pdf';

                // Convert DOC or DOCX → PDF
                if (in_array($extension, ['doc', 'docx'])) {
                    // Load DOC/DOCX
                    $phpWord = IOFactory::load($uploaded->getRealPath());

                    // Setup PDF renderer (Dompdf)
                    Settings::setPdfRendererName('DomPDF');
                    Settings::setPdfRendererPath(base_path('vendor/dompdf/dompdf'));

                    // Save temporary PDF
                    $tempPdf = tempnam(sys_get_temp_dir(), 'member_pdf_');
                    $pdfWriter = IOFactory::createWriter($phpWord, 'PDF');
                    $pdfWriter->save($tempPdf);

                    // Store in storage/app/private/member_files
                    $pdfContent = file_get_contents($tempPdf);
                    unlink($tempPdf);

                    $finalFileName = $originalName.'.pdf';
                    $finalPath = 'member_files/'.Str::uuid().'.pdf';
                    Storage::put($finalPath, $pdfContent);
                } else {
                    // For other file types: pdf, jpg, jpeg, png
                    $finalFileName = $uploaded->getClientOriginalName();
                    $finalMime = $mime;
                    $finalPath = $uploaded->store('member_files');
                }

                // Save to database
                $member->files()->create([
                    'file_path' => $finalPath,
                    'file_name' => $finalFileName,
                    'file_type' => $finalMime,
                ]);
            }
        }

        return redirect()
            ->route('cooperatives.members.index', $cooperative->id)
            ->with('success', 'Member updated successfully.');
    }

    public function destroy(Cooperative $cooperative, CoopMember $member)
    {
        foreach ($member->files as $file) {
            Storage::delete($file->file_path);
            $file->delete();
        }
        $member->delete();

        return redirect()
            ->route('cooperatives.members.index', $cooperative->id)
            ->with('success', 'Member deleted successfully.');
    }

    public function downloadFile(Cooperative $cooperative, CoopMember $member, $fileId)
    {
        $file = $member->files()->where('id', $fileId)->first();
        if (! $file) {
            return redirect()
                ->route('cooperatives.members.show', [$cooperative->id, $member->id])
                ->with('error', 'File not found.');
        }

        return Storage::download($file->file_path, $file->file_name);
    }

    public function deleteFile(Cooperative $cooperative, CoopMember $member, $fileId)
    {
        if (! $member->files()->where('id', $fileId)->exists()) {
            return redirect()
                ->route('cooperatives.members.show', [$cooperative->id, $member->id])
                ->with('error', 'File not found.');
        }
        $file = $member->files()->where('id', $fileId)->first();
        Storage::delete($file->file_path);
        $file->delete();

        return redirect()
            ->route('cooperatives.members.show', [$cooperative->id, $member->id])
            ->with('success', 'File deleted successfully.');
    }
}
