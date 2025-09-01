<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CoopProgram extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'program_id',
    ];

     public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function uploads()
    {
        return $this->hasMany(CoopUploads::class);
    }
}
