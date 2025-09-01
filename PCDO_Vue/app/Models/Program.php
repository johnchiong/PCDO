<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Program extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $fillable = ['name'];

    public function programs()
    {
        return $this->belongsToMany(Program::class, 'cooperative_programs');
    }
}