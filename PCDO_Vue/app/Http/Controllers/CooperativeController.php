<?php

namespace App\Http\Controllers;

use App\Models\CoopUploads;
use Illuminate\Http\Request;
use App\Models\Cooperative;
use App\Models\Checklist;
use Inertia\Inertia;

class CooperativeController extends Controller
{
    public function index()
    {
        $cooperatives = Cooperative::with('program')
            ->withCount(['uploads as uploaded_files_count'])
            ->get();

        $checklistCount = \App\Models\Checklist::count();

        // Add a computed status for each cooperative
        $cooperatives->transform(function ($coop) use ($checklistCount) {
            if ($coop->uploaded_files_count >= $checklistCount && $checklistCount > 0) {
                $coop->status = 'Complete';
            } elseif ($coop->uploaded_files_count > 0) {
                $coop->status = 'Pending';
            } else {
                $coop->status = 'Incomplete';
            }
            return $coop;
        });

        return inertia('cooperative/index', [
            'cooperatives' => $cooperatives,
        ]);
    }

    public function create()
    {
        return inertia('cooperative/create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:cooperatives,name',
            'program_id' => 'required|exists:programs,id',
        ]);

        $cooperative = Cooperative::create($data);

        // Redirect back to cooperative index with success message
        return redirect()
            ->route('cooperative.document', $cooperative)
            ->with('success', 'Cooperative created successfully!');
    }

    public function document(Cooperative $cooperative)
    {
        $checklistItems = Checklist::all()->map(function ($item) use ($cooperative) {
            // Find upload for this cooperative + checklist item
            $upload = CoopUploads::where('cooperative_id', $cooperative->id)
                ->where('checklist_item_id', $item->id)
                ->first();

            $item->upload = $upload; // attach upload (nullable if none)
            return $item;
        });

        return Inertia::render('cooperative/document', [
            'cooperative' => $cooperative->load('program'),
            'checklistItems' => $checklistItems,
        ]);
    }

    public function storeUpload(Request $request, Cooperative $cooperative)
    {
        $request->validate([
            'checklist_item_id' => 'required|exists:checklist_items,id',
            'file' => 'required|file|max:5120', // 5MB limit
        ]);

        $file = $request->file('file');

        CoopUploads::updateOrCreate(
            [
                'cooperative_id' => $cooperative->id,
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

    public function download(CoopUploads $upload)
    {
        return response(base64_decode($upload->file_content))
            ->header('Content-Type', $upload->mime_type)
            ->header('Content-Disposition', 'attachment; filename="' . $upload->file_name . '"');
    }

    public function destroyUpload(CoopUploads $upload)
    {
        $upload->delete();
        return back()->with('success', 'File deleted successfully!');
    }
}
