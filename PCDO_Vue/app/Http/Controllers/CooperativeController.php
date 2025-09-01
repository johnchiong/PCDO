<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cooperative;
use Inertia\Inertia;

class CooperativeController extends Controller
{
    public function index()
    {
        $cooperatives = Cooperative::all();
        return Inertia::render('cooperative/index', [
            'cooperatives' => $cooperatives
        ]);
    }

    public function create()
    {
        return Inertia::render('cooperative/create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:cooperatives,name',
            // 'address' => 'nullable|string|max:255',
            // 'contact_number' => 'nullable|string|max:50',
            // 'email' => 'nullable|email|max:255',
        ]);

        Cooperative::create($data);

        return redirect()->route('cooperative.index')
            ->with('success', 'Cooperative created successfully!');
    }
}