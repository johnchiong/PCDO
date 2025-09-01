<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Program;

class ProgramSeeder extends Seeder
{
    public function run(): void
    {
        $programs = [         
            ['name' => 'USAD',   'details' => 'USAD Program is a',   'loan_term' => 12, 'grace_period' => 4],
            ['name' => 'LICAP',  'details' => 'LICAP Program is a',  'loan_term' => 16, 'grace_period' => 4],
            ['name' => 'COPSE',  'details' => 'COPSE Program is a',  'loan_term' => 24, 'grace_period' => 4],
            ['name' => 'SULONG', 'details' => 'SULONG Program is a', 'loan_term' => 24, 'grace_period' => 4],
            ['name' => 'PCRLP',  'details' => 'PCRLP Program is a',  'loan_term' => 48,  'grace_period' => 4],
        ];

        foreach ($programs as $program) {
            Program::create($program);
        }
    }
}