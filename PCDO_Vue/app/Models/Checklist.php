<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Checklist extends Model
{
    use HasFactory;

    protected $table = 'checklist_items';
    protected $fillable = ['name'];

    // A checklist item can have many uploads (one per cooperative ideally)
    public function uploads()
    {
        return $this->hasMany(CoopUploads::class, 'checklist_item_id');
    }
}