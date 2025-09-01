<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cooperative extends Model
{
    use HasFactory;

    // Add fields you want to be mass assignable
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