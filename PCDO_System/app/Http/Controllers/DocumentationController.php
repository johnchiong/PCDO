<?php

namespace App\Http\Controllers;

use App\Models\Programs;

class DocumentationController extends Controller
{
    public function index()
    {
        $programs = Programs::withCount([
            'coopProgram as completed_coops_count' => function ($q) {
                $q->where('program_status', 'Finished')
                    ->where('exported', 1)
                    ->where('archived', 1);
            },
        ])->get();

        return inertia('documentation/index', [
            'programs' => $programs->map(fn ($program) => [
                'id' => $program->id,
                'name' => $program->name,
                'completed_coops_count' => $program->completed_coops_count,
            ]),
        ]);
    }

    public function show($id)
    {
        $year = request('year'); // catch the year filter if provided

        $program = Programs::with(['coopProgram.cooperative'])
            ->withCount([
                'coopProgram as completed_coops_count' => function ($q) {
                    $q->where('program_status', 'Finished')
                        ->where('exported', 1)
                        ->where('archived', 1);
                },
            ])
            ->findOrFail($id);

        $completedCoops = $program->coopProgram()
            ->where('program_status', 'Finished')
            ->where('exported', 1)
            ->where('archived', 1)
            ->when($year, function ($q) use ($year) {
                $q->whereYear('updated_at', $year); // or 'finished_at' if you have one
            })
            ->with('cooperative')
            ->get();

        return inertia('documentation/show', [
            'program' => [
                'id' => $program->id,
                'name' => $program->name,
                'description' => '',
            ],
            'cooperatives' => $completedCoops->map(fn ($coopProgram) => [
                'id' => $coopProgram->id,
                'name' => $coopProgram->cooperative->name ?? 'N/A',
                'program_status' => $coopProgram->program_status,
                'year' => $coopProgram->updated_at->format('Y'), // pass year to frontend
            ]),
            'selectedYear' => $year,
        ]);
    }

    public function history()
    {
        $programId = request()->query('program_id');
        $coopId = request()->query('coop_id');

        $programsQuery = Programs::with(['coopProgram.cooperative']);

        if ($programId) {
            $programsQuery->where('id', $programId);
        }

        $programs = $programsQuery->get()->map(function ($program) {
            // Create completed_coops array from coopProgram
            $program->completed_coops = $program->coopProgram
                ->where('program_status', 'Finished')
                ->where('exported', 1)
                ->where('archived', 1)
                ->map(fn ($c) => [
                    'id' => $c->id,
                    'name' => $c->cooperative->name ?? 'N/A',
                    'completed_at' => $c->updated_at->format('Y-m-d'),
                    'files' => $c->files ?? [],
                ])
                ->values();

            return $program;
        });

        // Now filter by coop_id if provided
        if ($coopId) {
            $programs->transform(function ($program) use ($coopId) {
                $program->completed_coops = $program->completed_coops
                    ->filter(fn ($c) => $c['id'] == $coopId)
                    ->values();

                return $program;
            });
        }

        return inertia('documentation/history', [
            'programs' => $programs,
        ]);
    }
}
