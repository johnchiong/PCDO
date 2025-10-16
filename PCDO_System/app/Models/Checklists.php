<?php

namespace App\Models;

use App\Traits\SyncLogger;
use Illuminate\Database\Eloquent\Model;

class Checklists extends Model
{
    use SyncLogger;

    protected $fillable = ['name'];

    public $timestamps = false;

    public function programs()
    {
        return $this->belongsToMany(Programs::class, 'program_checklists', 'checklist_id', 'program_id')->withPivot('id');
    }
}
