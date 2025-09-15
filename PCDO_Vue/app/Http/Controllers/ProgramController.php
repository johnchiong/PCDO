<?php

namespace App\Http\Controllers;

use App\Models\Program;
use Illuminate\Http\Request;

class ProgramController extends Controller
{
    public function index()
    {
        $programs = Program::withCount('cooperatives')->get();

        return inertia('programs/index', [
            'programs' => $programs,
        ]);
    }
}