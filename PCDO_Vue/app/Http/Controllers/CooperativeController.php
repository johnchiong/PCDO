<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cooperative;
use App\Models\CoopUpload;
use Inertia\Inertia;

class CooperativeController extends Controller
{
    public function index()
    {
        return inertia('cooperative/index');
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
        $checklistItems = \App\Models\Checklist::all();

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

    CoopUpload::updateOrCreate(
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
}
