<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CoopProgramChecklist extends Model
{
    use HasFactory;

    protected $fillable = [
        'coop_program_id',
        'checklist_id',
        'file_name',
        'mime_type',
        'file_content',
    ];

    function coopProgram()
    {
        return $this->belongsTo(CoopProgram::class);
    }

    function checklist()
    {
        return $this->belongsTo(Checklists::class);
    }

    public function getFileContentAttribute($value)
    {
        return base64_encode($value);
    }
}
