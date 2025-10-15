<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Resolved extends Model
{
    protected $fillable = [
        'coop_program_id',
        'file_content',

    ];
    protected $table = 'resolved';
    protected $hidden = ['file_content'];


    public function coopProgram()
    {
        return $this->belongsTo(CoopProgram::class, 'coop_program_id');
    }

    public function cooperative()
    {
        return $this->belongsTo(Cooperative::class, 'coop_id');
    }
}