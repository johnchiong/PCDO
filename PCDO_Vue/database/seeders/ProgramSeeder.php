<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Program;

class ProgramSeeder extends Seeder
{
    public function run(): void
    {
        $programs = ['USAD', 'LICAP', 'COPSE', 'SULONG', 'PCRLP'];

        foreach ($programs as $program) {
            Program::create(['name' => $program]);
        }
    }
}